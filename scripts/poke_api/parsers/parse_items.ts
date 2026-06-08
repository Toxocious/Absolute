import fs from 'fs';
import path from 'path';

import { CSV_PATH, OUTPUT_PATH } from './utility/output_const';
import { Row, parseCSV } from './utility/file';

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
        console.warn(`[Parse Items] Missing ${name}.csv (${file})`);
        return [];
    }
    const content = fs.readFileSync(file, 'utf8');
    return parseCSV(content);
}

async function build() {
    const [items, itemNames, itemFlavors, categories] = await Promise.all([
        loadCSV('items'),
        loadCSV('item_names'),
        loadCSV('item_flavor_text'),
        loadCSV('item_categories'),
    ]);

    // category id -> identifier
    const categoryIdToIdent = new Map<number, string>();
    for (const c of categories) {
        categoryIdToIdent.set(toInt(c['id']), String(c['identifier'] ?? '').trim());
    }

    // item id -> English name (local_language_id == 9)
    const nameByItemId = new Map<number, string>();
    for (const n of itemNames) {
        const iid = toInt(n['item_id']);
        const lang = toInt(n['local_language_id']);
        if (lang === 9) nameByItemId.set(iid, String(n['name'] ?? '').trim());
    }

    // item id -> preferred English flavor text (choose highest version_group_id)
    const flavorByItemId = new Map<number, { vg: number; text: string }>();
    for (const f of itemFlavors) {
        const iid = toInt(f['item_id']);
        const lang = toInt(f['language_id']);
        if (lang !== 9) continue;
        const vg = toInt(f['version_group_id']);
        const text = String(f['flavor_text'] ?? '')
            .replace(/\r/g, '\n')
            .trim();
        const cur = flavorByItemId.get(iid);
        if (!cur || vg > cur.vg) flavorByItemId.set(iid, { vg, text });
    }

    const out: Array<Record<string, any>> = [];

    for (const it of items) {
        const item_id = toInt(it['id']);
        const identifier = String(it['identifier'] ?? `item_${item_id}`).trim();
        const category = categoryIdToIdent.get(toInt(it['category_id'])) ?? null;
        const cost = toInt(it['cost']);
        const fling_power = it['fling_power'] ? toInt(it['fling_power']) : null;
        const fling_effect_id = it['fling_effect_id'] ? toInt(it['fling_effect_id']) : null;
        const name = nameByItemId.get(item_id) ?? identifier.replace(/-/g, ' ');
        const flavor = flavorByItemId.get(item_id)?.text ?? null;

        out.push({
            identifier,
            item_id,
            name,
            category,
            cost,
            fling_power,
            fling_effect_id,
            flavor_text: flavor,
        });
    }

    // write JSON
    ensureDir(OUT_DIR);
    const jsonFile = path.join(OUT_DIR, 'api_items.json');
    fs.writeFileSync(jsonFile, JSON.stringify(out, null, 2), 'utf8');

    // build SQL
    const sqlParts: string[] = [];
    sqlParts.push('DROP TABLE IF EXISTS `api_items`;');
    sqlParts.push(`CREATE TABLE \`api_items\` (
  \`identifier\` VARCHAR(64) NOT NULL,
  \`item_id\` INT UNSIGNED NOT NULL,
  \`name\` VARCHAR(128) NOT NULL,
  \`category\` VARCHAR(64) DEFAULT NULL,
  \`cost\` INT NOT NULL DEFAULT 0,
  \`fling_power\` INT DEFAULT NULL,
  \`fling_effect_id\` SMALLINT UNSIGNED DEFAULT NULL,
  \`flavor_text\` TEXT DEFAULT NULL,
  PRIMARY KEY (\`identifier\`),
  KEY idx_api_items_name (\`name\`),
  KEY idx_api_items_category (\`category\`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;`);

    const insertChunks: string[] = [];
    for (const r of out) {
        const vals = [
            sqlEscape(r.identifier),
            sqlEscape(r.item_id),
            sqlEscape(r.name),
            sqlEscape(r.category),
            sqlEscape(r.cost),
            r.fling_power === null ? 'NULL' : sqlEscape(r.fling_power),
            r.fling_effect_id === null ? 'NULL' : sqlEscape(r.fling_effect_id),
            r.flavor_text === null ? 'NULL' : sqlEscape(r.flavor_text),
        ];
        insertChunks.push(`(${vals.join(',')})`);
    }

    const batchSize = 500;
    for (let i = 0; i < insertChunks.length; i += batchSize) {
        const slice = insertChunks.slice(i, i + batchSize);
        sqlParts.push(
            `INSERT INTO \`api_items\` (\`identifier\`,\`item_id\`,\`name\`,\`category\`,\`cost\`,\`fling_power\`,\`fling_effect_id\`,\`flavor_text\`) VALUES\n${slice.join(
                ',\n'
            )};`
        );
    }

    const sqlFile = path.join(OUT_DIR, 'api_items.sql');
    fs.writeFileSync(sqlFile, sqlParts.join('\n\n'), 'utf8');

    console.log(`Wrote ${out.length} items to ${jsonFile} and ${sqlFile}`);
}

build().catch((err) => {
    console.error(err);
    process.exit(2);
});
