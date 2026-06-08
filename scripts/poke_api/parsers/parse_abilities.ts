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
        console.warn(`[Parse Abilities] Missing ${name}.csv (${file})`);
        return [];
    }
    const content = fs.readFileSync(file, 'utf8');
    return parseCSV(content);
}

async function build() {
    const [abilitiesCsv, abilityNamesCsv] = await Promise.all([
        loadCSV('abilities'),
        loadCSV('ability_names'),
    ]);

    // Map ability id -> english name (local_language_id == 9)
    const abilityIdToEnglish = new Map<number, string>();
    for (const r of abilityNamesCsv) {
        const aid = toInt(r['ability_id']);
        const lang = toInt(r['local_language_id']);
        if (lang === 9) {
            abilityIdToEnglish.set(aid, String(r['name'] ?? '').trim());
        }
    }

    const out: Array<{
        identifier: string;
        name: string;
        generation_id: number;
        is_main_series: number;
    }> = [];

    for (const a of abilitiesCsv) {
        const id = toInt(a['id']);
        const identifier = String(a['identifier'] ?? `ability_${id}`).trim();
        const name =
            abilityIdToEnglish.get(id) || capitalize(identifier.replace(/-/g, ' ')) || identifier;
        const generation_id = toInt(a['generation_id']);
        const is_main_series = toInt(a['is_main_series']);
        out.push({
            identifier,
            name,
            generation_id,
            is_main_series,
        });
    }

    // Write JSON
    ensureDir(OUT_DIR);
    const jsonFile = path.join(OUT_DIR, 'api_abilities.json');
    fs.writeFileSync(jsonFile, JSON.stringify(out, null, 2), 'utf8');

    // Build SQL
    const sqlParts: string[] = [];

    // Create table: identifier as primary key to match user_pokemon.ability (string)
    sqlParts.push(`DROP TABLE IF EXISTS \`api_abilities\`;`);
    sqlParts.push(
        `CREATE TABLE \`api_abilities\` (
  \`identifier\` VARCHAR(42) NOT NULL,
  \`name\` VARCHAR(128) NOT NULL,
  \`generation_id\` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  \`is_main_series\` TINYINT UNSIGNED NOT NULL DEFAULT 1,
  PRIMARY KEY (\`identifier\`),
  KEY idx_api_abilities_name (\`name\`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;`
    );

    // Insert rows (batched)
    const insertChunks: string[] = [];
    for (const row of out) {
        const vals = [
            sqlEscape(row.identifier),
            sqlEscape(row.name),
            sqlEscape(row.generation_id),
            sqlEscape(row.is_main_series),
        ];
        insertChunks.push(`(${vals.join(',')})`);
    }
    if (insertChunks.length) {
        const batchSize = 500;
        for (let i = 0; i < insertChunks.length; i += batchSize) {
            const slice = insertChunks.slice(i, i + batchSize);
            sqlParts.push(
                `INSERT INTO \`api_abilities\` (\`identifier\`,\`name\`,\`generation_id\`,\`is_main_series\`) VALUES\n${slice.join(
                    ',\n'
                )};`
            );
        }
    }

    // Alter api_pokedex ability columns to accommodate identifier length and add FKs.
    // NOTE: these ALTERs will change column sizes and add foreign keys.
    sqlParts.push(
        `-- Ensure api_pokedex ability columns are wide enough and add FK constraints\nALTER TABLE \`api_pokedex\`\n  MODIFY \`ability_1\` VARCHAR(42) DEFAULT NULL,\n  MODIFY \`ability_2\` VARCHAR(42) DEFAULT NULL,\n  MODIFY \`ability_hidden\` VARCHAR(42) DEFAULT NULL;`
    );

    // Add indexes (if not present) and FK constraints
    sqlParts.push(
        `ALTER TABLE \`api_pokedex\`\n  ADD KEY idx_api_pokedex_ability_1 (\`ability_1\`),\n  ADD KEY idx_api_pokedex_ability_2 (\`ability_2\`),\n  ADD KEY idx_api_pokedex_ability_hidden (\`ability_hidden\`);`
    );

    sqlParts.push(
        `ALTER TABLE \`api_pokedex\`\n  ADD CONSTRAINT fk_api_pokedex_ability_1 FOREIGN KEY (\`ability_1\`) REFERENCES \`api_abilities\`(\`identifier\`) ON UPDATE CASCADE ON DELETE SET NULL,\n  ADD CONSTRAINT fk_api_pokedex_ability_2 FOREIGN KEY (\`ability_2\`) REFERENCES \`api_abilities\`(\`identifier\`) ON UPDATE CASCADE ON DELETE SET NULL,\n  ADD CONSTRAINT fk_api_pokedex_ability_hidden FOREIGN KEY (\`ability_hidden\`) REFERENCES \`api_abilities\`(\`identifier\`) ON UPDATE CASCADE ON DELETE SET NULL;`
    );

    // Add FK on user_pokemon.ability
    sqlParts.push(
        `-- Add FK from user_pokemon.ability -> api_abilities.identifier\nALTER TABLE \`user_pokemon\`\n  ADD KEY idx_user_pokemon_ability (\`ability\`),\n  ADD CONSTRAINT fk_user_pokemon_ability FOREIGN KEY (\`ability\`) REFERENCES \`api_abilities\`(\`identifier\`) ON UPDATE CASCADE ON DELETE SET NULL;`
    );

    const sqlFile = path.join(OUT_DIR, 'api_abilities.sql');
    fs.writeFileSync(sqlFile, sqlParts.join('\n\n'), 'utf8');

    console.log(`Wrote ${out.length} abilities to ${jsonFile} and ${sqlFile}`);
}

build().catch((err) => {
    console.error(err);
    process.exit(2);
});
