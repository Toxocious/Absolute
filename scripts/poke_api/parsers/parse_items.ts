import fs from 'fs';
import path from 'path';

import { CSV_PATH, OUTPUT_PATH } from './utility/output_const';
import { Row, parseCSV } from './utility/file';

function toInt(v: string | undefined, fallback = 0): number {
    const n = Number(v ?? '');
    return Number.isNaN(n) ? fallback : Math.trunc(n);
}

function ensureDir(dir: string): void {
    if (!fs.existsSync(dir)) {
        fs.mkdirSync(dir, { recursive: true });
    }
}

function sqlEscape(v: any): string {
    if (v === null || v === undefined) {
        return 'NULL';
    }

    if (typeof v === 'number') {
        return String(v);
    }

    return "'" + String(v).replace(/\\/g, '\\\\').replace(/'/g, "\\'") + "'";
}

async function loadCSV(name: string): Promise<Row[]> {
    const file = path.join(CSV_PATH, `${name}.csv`);

    if (!fs.existsSync(file)) {
        console.warn(`[Parse Items] Missing ${name}.csv (${file})`);
        return [];
    }

    return parseCSV(fs.readFileSync(file, 'utf8'));
}

async function build(): Promise<void> {
    const [items, itemNames, itemFlavors, categories, pockets] = await Promise.all([
        loadCSV('items'),
        loadCSV('item_names'),
        loadCSV('item_flavor_text'),
        loadCSV('item_categories'),
        loadCSV('item_pockets'),
    ]);

    /**
     * item_categories.csv
     *
     * category_id -> identifier
     * category_id -> pocket_id
     */
    const categoryIdToIdentifier = new Map<number, string>();
    const categoryIdToPocketId = new Map<number, number>();

    for (const row of categories) {
        const categoryId = toInt(row['id']);

        categoryIdToIdentifier.set(categoryId, String(row['identifier'] ?? '').trim());

        categoryIdToPocketId.set(categoryId, toInt(row['pocket_id']));
    }

    /**
     * item_pockets.csv
     *
     * pocket_id -> identifier
     */
    const pocketIdToIdentifier = new Map<number, string>();

    for (const row of pockets) {
        pocketIdToIdentifier.set(toInt(row['id']), String(row['identifier'] ?? '').trim());
    }

    /**
     * item_names.csv
     *
     * English names only (local_language_id = 9)
     */
    const nameByItemId = new Map<number, string>();

    for (const row of itemNames) {
        const itemId = toInt(row['item_id']);
        const languageId = toInt(row['local_language_id']);

        if (languageId !== 9) {
            continue;
        }

        nameByItemId.set(itemId, String(row['name'] ?? '').trim());
    }

    /**
     * item_flavor_text.csv
     *
     * English flavor text only (language_id = 9)
     */
    const flavorByItemId = new Map<
        number,
        {
            versionGroupId: number;
            text: string;
        }
    >();

    for (const row of itemFlavors) {
        const itemId = toInt(row['item_id']);
        const languageId = toInt(row['language_id']);

        if (languageId !== 9) {
            continue;
        }

        let text = String(row['flavor_text'] ?? '')
            .replace(/\r\n/g, '\n')
            .replace(/\r/g, '\n')
            .replace(/\f/g, ' ')
            .trim();

        const versionGroupId = toInt(row['version_group_id']);

        const current = flavorByItemId.get(itemId);

        if (
            !current ||
            text.length > current.text.length ||
            (text.length === current.text.length && versionGroupId > current.versionGroupId)
        ) {
            flavorByItemId.set(itemId, {
                versionGroupId,
                text,
            });
        }
    }

    const output: Record<string, any>[] = [];

    for (const row of items) {
        const itemId = toInt(row['id']);

        const identifier = String(row['identifier'] ?? `item_${itemId}`).trim();

        const categoryId = toInt(row['category_id']);

        const category = categoryIdToIdentifier.get(categoryId) ?? null;

        const pocketId = categoryIdToPocketId.get(categoryId) ?? null;

        const pocket = pocketId !== null ? pocketIdToIdentifier.get(pocketId) ?? null : null;

        const cost = toInt(row['cost']);

        const flingPower = row['fling_power'] ? toInt(row['fling_power']) : null;

        const flingEffectId = row['fling_effect_id'] ? toInt(row['fling_effect_id']) : null;

        const name = nameByItemId.get(itemId) ?? identifier.replace(/-/g, ' ');

        let flavor = flavorByItemId.get(itemId)?.text ?? null;

        if (flavor) {
            flavor = flavor
                .replace(/\r?\n+/g, ' ')
                .replace(/\s+/g, ' ')
                .trim();
        }

        output.push({
            identifier,
            item_id: itemId,
            name,
            pocket,
            category,
            cost,
            fling_power: flingPower,
            fling_effect_id: flingEffectId,
            flavor_text: flavor,
        });
    }

    ensureDir(OUTPUT_PATH);

    /**
     * JSON
     */
    const jsonFile = path.join(OUTPUT_PATH, 'api_items.json');

    fs.writeFileSync(jsonFile, JSON.stringify(output, null, 2), 'utf8');

    /**
     * SQL
     */
    const sqlParts: string[] = [];

    sqlParts.push('DROP TABLE IF EXISTS `api_items`;');

    sqlParts.push(
        `
CREATE TABLE \`api_items\` (
    \`identifier\` VARCHAR(64) NOT NULL,
    \`item_id\` INT UNSIGNED NOT NULL,
    \`name\` VARCHAR(128) NOT NULL,

    \`pocket\` VARCHAR(64) DEFAULT NULL,
    \`category\` VARCHAR(64) DEFAULT NULL,

    \`cost\` INT NOT NULL DEFAULT 0,
    \`fling_power\` INT DEFAULT NULL,
    \`fling_effect_id\` SMALLINT UNSIGNED DEFAULT NULL,
    \`flavor_text\` TEXT DEFAULT NULL,

    \`tradeable\` TINYINT(1) NOT NULL DEFAULT 1,

    PRIMARY KEY (\`identifier\`),
    KEY idx_api_items_name (\`name\`),
    KEY idx_api_items_pocket (\`pocket\`),
    KEY idx_api_items_category (\`category\`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
`.trim()
    );

    const values: string[] = [];

    for (const item of output) {
        values.push(`(
            ${sqlEscape(item.identifier)},
            ${sqlEscape(item.item_id)},
            ${sqlEscape(item.name)},
            ${sqlEscape(item.pocket)},
            ${sqlEscape(item.category)},
            ${sqlEscape(item.cost)},
            ${item.fling_power === null ? 'NULL' : sqlEscape(item.fling_power)},
            ${item.fling_effect_id === null ? 'NULL' : sqlEscape(item.fling_effect_id)},
            ${item.flavor_text === null ? 'NULL' : sqlEscape(item.flavor_text)}
        )`);
    }

    const batchSize = 500;

    for (let i = 0; i < values.length; i += batchSize) {
        const batch = values.slice(i, i + batchSize);

        sqlParts.push(
            `
INSERT INTO \`api_items\`
(
    \`identifier\`,
    \`item_id\`,
    \`name\`,
    \`pocket\`,
    \`category\`,
    \`cost\`,
    \`fling_power\`,
    \`fling_effect_id\`,
    \`flavor_text\`
)
VALUES
${batch.join(',\n')};
`.trim()
        );
    }

    const sqlFile = path.join(OUTPUT_PATH, 'api_items.sql');

    fs.writeFileSync(sqlFile, sqlParts.join('\n\n'), 'utf8');

    console.log(`Wrote ${output.length} items to ${jsonFile} and ${sqlFile}`);
}

build().catch((err) => {
    console.error(err);
    process.exit(1);
});
