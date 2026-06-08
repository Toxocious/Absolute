import fs from 'fs';
import path from 'path';

import { CSV_PATH, OUTPUT_PATH } from './utility/output_const';
import { Row, parseCSV, ReadCsvFile, capitalize } from './utility/file';

function toInt(v: string | undefined, fallback = 0) {
    const n = Number(v ?? '');
    return Number.isNaN(n) ? fallback : Math.trunc(n);
}

function ensureDir(dir: string) {
    if (!fs.existsSync(dir)) fs.mkdirSync(dir, { recursive: true });
}

function sqlEscape(v: any): string {
    if (v === null || v === undefined) return 'NULL';
    if (typeof v === 'number') return String(v);
    return "'" + String(v).replace(/\\/g, '\\\\').replace(/'/g, "\\'") + "'";
}

const CSV_DIR = CSV_PATH;
const OUT_DIR = OUTPUT_PATH;

async function loadCSV(name: string): Promise<Row[]> {
    const file = path.join(CSV_DIR, `${name}.csv`);
    if (!fs.existsSync(file)) {
        console.warn(`[Parse Natures] Missing ${name}.csv (${file})`);
        return [];
    }
    const content = fs.readFileSync(file, 'utf8');
    return parseCSV(content);
}

async function build() {
    const [naturesCsv, natureNamesCsv] = await Promise.all([
        loadCSV('natures'),
        loadCSV('nature_names'),
    ]);

    // Map nature_id -> english name (local_language_id == 9)
    const natureIdToEnglish = new Map<number, string>();
    for (const r of natureNamesCsv) {
        const nid = toInt(r['nature_id']);
        const lang = toInt(r['local_language_id']);
        if (lang === 9) natureIdToEnglish.set(nid, String(r['name'] ?? '').trim());
    }

    const out: Array<{
        identifier: string;
        name: string;
        decreased_stat_id: number;
        increased_stat_id: number;
        hates_flavor_id: number;
        likes_flavor_id: number;
        game_index: number;
    }> = [];

    for (const n of naturesCsv) {
        const id = toInt(n['id']);
        const identifier = String(n['identifier'] ?? `nature_${id}`).trim();
        const name = natureIdToEnglish.get(id) ?? capitalize(identifier.replace(/-/g, ' '));
        const decreased_stat_id = toInt(n['decreased_stat_id']);
        const increased_stat_id = toInt(n['increased_stat_id']);
        const hates_flavor_id = toInt(n['hates_flavor_id']);
        const likes_flavor_id = toInt(n['likes_flavor_id']);
        const game_index = toInt(n['game_index']);
        out.push({
            identifier,
            name,
            decreased_stat_id,
            increased_stat_id,
            hates_flavor_id,
            likes_flavor_id,
            game_index,
        });
    }

    ensureDir(OUT_DIR);
    const jsonFile = path.join(OUT_DIR, 'api_natures.json');
    fs.writeFileSync(jsonFile, JSON.stringify(out, null, 2), 'utf8');

    const sqlParts: string[] = [];
    sqlParts.push('DROP TABLE IF EXISTS `api_natures`;');
    sqlParts.push(`CREATE TABLE \`api_natures\` (
  \`identifier\` VARCHAR(42) NOT NULL,
  \`name\` VARCHAR(128) NOT NULL,
  \`decreased_stat_id\` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  \`increased_stat_id\` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  \`hates_flavor_id\` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  \`likes_flavor_id\` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  \`game_index\` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (\`identifier\`),
  KEY idx_api_natures_name (\`name\`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;`);

    const insertChunks: string[] = [];
    for (const r of out) {
        const vals = [
            sqlEscape(r.identifier),
            sqlEscape(r.name),
            sqlEscape(r.decreased_stat_id),
            sqlEscape(r.increased_stat_id),
            sqlEscape(r.hates_flavor_id),
            sqlEscape(r.likes_flavor_id),
            sqlEscape(r.game_index),
        ];
        insertChunks.push(`(${vals.join(',')})`);
    }
    if (insertChunks.length) {
        const batchSize = 500;
        for (let i = 0; i < insertChunks.length; i += batchSize) {
            const slice = insertChunks.slice(i, i + batchSize);
            sqlParts.push(
                `INSERT INTO \`api_natures\` (\`identifier\`,\`name\`,\`decreased_stat_id\`,\`increased_stat_id\`,\`hates_flavor_id\`,\`likes_flavor_id\`,\`game_index\`) VALUES\n${slice.join(
                    ',\n'
                )};`
            );
        }
    }

    sqlParts.push(`
        ALTER TABLE \`user_pokemon\`
        MODIFY \`nature\` VARCHAR(128) DEFAULT NULL,
        ADD KEY idx_user_pokemon_nature (\`nature\`);

        ALTER TABLE \`user_pokemon\`
        ADD CONSTRAINT fk_user_pokemon_nature
            FOREIGN KEY (\`nature\`)
            REFERENCES \`api_natures\` (\`name\`)
            ON UPDATE CASCADE
            ON DELETE SET NULL;
    `);

    const sqlFile = path.join(OUT_DIR, 'api_natures.sql');
    fs.writeFileSync(sqlFile, sqlParts.join('\n\n'), 'utf8');

    console.log(`Wrote ${out.length} natures to ${jsonFile} and ${sqlFile}`);
}

build().catch((err) => {
    console.error(err);
    process.exit(2);
});
