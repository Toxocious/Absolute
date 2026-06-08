/**
 * Parses all Pokemon entries to build a detailed ``api_pokedex`` database table.
 *
 * Takes in and processes the following ``poke_api/csv`` files to build the ``api_pokedex`` database table.
 *   'pokemon',
 *   'pokemon_forms',
 *   'pokemon_species',
 *   'pokemon_stats',
 *   'pokemon_abilities',
 *   'pokemon_types',
 *   'pokemon_egg_groups',
 *
 * We expect the following information:
 *   `id` int(11) NOT NULL,
 *   `pokedex_id` smallint(4) NOT NULL DEFAULT 0,
 *   `alt_id` smallint(2) NOT NULL DEFAULT 0,
 *   `pokemon` varchar(24) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'UNKNOWN',
 *   `forme` varchar(20) DEFAULT NULL,
 *   `type_primary` enum('None','Normal','Water','Fire','Grass','Electric','Ground','Flying','Fighting','Psychic','Dark','Ghost','Bug','Rock','Ice','Steel','Dragon','Poison','Fairy') NOT NULL DEFAULT 'None',
 *   `type_secondary` enum('None','Normal','Water','Fire','Grass','Electric','Ground','Flying','Fighting','Psychic','Dark','Ghost','Bug','Rock','Ice','Steel','Dragon','Poison','Fairy') NOT NULL DEFAULT 'None',
 *   `ability_1` varchar(25) DEFAULT NULL,
 *   `ability_2` varchar(25) DEFAULT NULL,
 *   `ability_hidden` varchar(25) DEFAULT NULL,
 *   `base_hp` smallint(4) NOT NULL,
 *   `base_attack` smallint(4) NOT NULL,
 *   `base_defense` smallint(4) NOT NULL,
 *   `base_sp_attack` smallint(4) NOT NULL,
 *   `base_sp_defense` smallint(4) NOT NULL,
 *   `base_speed` smallint(4) NOT NULL,
 *   `hp_ev_yield` smallint(1) NOT NULL,
 *   `attack_ev_yield` smallint(1) NOT NULL,
 *   `defense_ev_yield` smallint(1) NOT NULL,
 *   `sp_attack_ev_yield` smallint(1) NOT NULL,
 *   `sp_defense_ev_yield` smallint(1) NOT NULL,
 *   `speed_ev_yield` smallint(1) NOT NULL,
 *   `male_odds` float NOT NULL,
 *   `female_odds` float NOT NULL,
 *   `genderless_odds` float NOT NULL,
 *   `height` int(5) NOT NULL,
 *   `weight` int(5) NOT NULL,
 *   `catch_rate` smallint(3) NOT NULL,
 *   `egg_cycles` smallint(3) NOT NULL,
 *   `exp_yield` smallint(3) NOT NULL,
 *   `base_happiness` smallint(3) NOT NULL,
 *   `egg_group_1` varchar(20) NOT NULL,
 *   `egg_group_2` varchar(20) NOT NULL,
 *   `is_baby` varchar(5) NOT NULL,
 *   `is_mythical` varchar(5) NOT NULL,
 *   `is_legendary` varchar(5) NOT NULL,
 *   `sort_order` smallint(4) NOT NULL,
 */
import fs from 'fs';
import path from 'path';

import { CSV_PATH, OUTPUT_PATH } from './utility/output_const';

import { Row, parseCSV, ReadCsvFile, capitalize } from './utility/file';

function toInt(v: string | undefined, fallback = 0) {
    const n = Number(v ?? '');
    return Number.isNaN(n) ? fallback : Math.trunc(n);
}
function toFloat(v: string | undefined, fallback = 0) {
    const n = Number(v ?? '');
    return Number.isNaN(n) ? fallback : n;
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
        console.warn(`[Parse Pokedex] Failed to find file ${name}.csv / ${file}`);
        return [];
    }

    const content = fs.readFileSync(file, 'utf8');
    return parseCSV(content);
}

function statIdToKey(id: number): string | null {
    // PokeAPI / typical mapping
    switch (id) {
        case 1:
            return 'hp';
        case 2:
            return 'attack';
        case 3:
            return 'defense';
        case 4:
            return 'sp_attack';
        case 5:
            return 'sp_defense';
        case 6:
            return 'speed';
        default:
            return null;
    }
}

function boolStr(v: any) {
    return v ? 'true' : 'false';
}

async function build() {
    const [
        pokemonRows,
        formRows,
        speciesRows,
        statRows,
        abilityRows,
        typeRows,
        eggGroupRows,
        typesCsv,
        abilitiesCsv,
        eggGroupsCsv,
    ] = await Promise.all([
        loadCSV('pokemon'),
        loadCSV('pokemon_forms'),
        loadCSV('pokemon_species'),
        loadCSV('pokemon_stats'),
        loadCSV('pokemon_abilities'),
        loadCSV('pokemon_types'),
        loadCSV('pokemon_egg_groups'),
        loadCSV('types'),
        loadCSV('abilities'),
        loadCSV('egg_groups'),
    ]);

    console.log(pokemonRows.length);

    const pokemonById = new Map<number, Row>();
    for (const r of pokemonRows) pokemonById.set(toInt(r['id']), r);

    const formsByPokemonId = new Map<number, Row[]>();
    for (const r of formRows) {
        const pid = toInt(r['pokemon_id'] ?? r['pokemon']);
        if (!formsByPokemonId.has(pid)) formsByPokemonId.set(pid, []);
        formsByPokemonId.get(pid)!.push(r);
    }

    const speciesById = new Map<number, Row>();
    for (const r of speciesRows) speciesById.set(toInt(r['id']), r);

    const statsByPokemonId = new Map<number, Row[]>();
    for (const r of statRows) {
        const pid = toInt(r['pokemon_id'] ?? r['pokemon']);
        if (!statsByPokemonId.has(pid)) statsByPokemonId.set(pid, []);
        statsByPokemonId.get(pid)!.push(r);
    }

    const abilitiesByPokemonId = new Map<number, Row[]>();
    for (const r of abilityRows) {
        const pid = toInt(r['pokemon_id'] ?? r['pokemon']);
        if (!abilitiesByPokemonId.has(pid)) abilitiesByPokemonId.set(pid, []);
        abilitiesByPokemonId.get(pid)!.push(r);
    }

    const typesByPokemonId = new Map<number, Row[]>();
    for (const r of typeRows) {
        const pid = toInt(r['pokemon_id'] ?? r['pokemon']);
        if (!typesByPokemonId.has(pid)) typesByPokemonId.set(pid, []);
        typesByPokemonId.get(pid)!.push(r);
    }

    const eggGroupsBySpeciesId = new Map<number, number[]>();
    for (const r of eggGroupRows) {
        const sid = toInt(r['species_id']);
        const eg = toInt(r['egg_group_id']);
        if (!eggGroupsBySpeciesId.has(sid)) eggGroupsBySpeciesId.set(sid, []);
        eggGroupsBySpeciesId.get(sid)!.push(eg);
    }

    const typeIdToName = new Map<number, string>();
    for (const r of typesCsv)
        typeIdToName.set(toInt(r['id']), r['identifier'] ?? r['name'] ?? `Type_${r['id']}`);

    const abilityIdToName = new Map<number, string>();
    for (const r of abilitiesCsv)
        abilityIdToName.set(toInt(r['id']), r['identifier'] ?? r['name'] ?? `Ability_${r['id']}`);

    const eggGroupIdToName = new Map<number, string>();
    for (const r of eggGroupsCsv)
        eggGroupIdToName.set(toInt(r['id']), r['identifier'] ?? r['name'] ?? `Group_${r['id']}`);

    const out: any[] = [];

    for (const p of pokemonRows) {
        const id = toInt(p['id']);
        const species_id = toInt(p['species_id']);
        const is_default = (p['is_default'] || '1') === '1';

        // base fields
        const species = speciesById.get(species_id) ?? {};
        const rawIdentifier = String(p['identifier'] ?? p['pokemon'] ?? '').trim();
        const speciesIdentifier = String(species['identifier'] ?? species['name'] ?? '').trim();

        let baseName = speciesIdentifier || rawIdentifier;
        let formeName: string | null = null;

        // prefer explicit non-default form entry from pokemon_forms.csv
        const forms = formsByPokemonId.get(id) ?? [];
        const nonDefaultForm = forms.find(
            (fr) =>
                String(fr['is_default'] ?? '') !== '1' &&
                (fr['form_identifier'] || fr['form_name'] || fr['identifier'])
        );
        if (nonDefaultForm) {
            const fname =
                nonDefaultForm['form_name'] ||
                nonDefaultForm['form_identifier'] ||
                nonDefaultForm['identifier'];
            formeName = String(fname).trim();
        }

        // if row identifier differs from species identifier, strip known suffix
        if (speciesIdentifier && rawIdentifier && rawIdentifier !== speciesIdentifier) {
            if (rawIdentifier.startsWith(speciesIdentifier + '-')) {
                baseName = capitalize(speciesIdentifier);
                const suffix = rawIdentifier.slice(speciesIdentifier.length + 1);
                if (!formeName && suffix) formeName = suffix.replace(/-/g, ' ');
            } else {
                // fallback: if speciesIdentifier exists, prefer it; treat remainder as forme
                baseName = capitalize(speciesIdentifier);
                const maybeSuffix = rawIdentifier.replace(
                    new RegExp(`^${speciesIdentifier}-?`),
                    ''
                );
                if (!formeName && maybeSuffix && maybeSuffix !== speciesIdentifier) {
                    formeName = maybeSuffix.replace(/-/g, ' ');
                }
            }
        }

        // normalize forme formatting and casing
        if (formeName) {
            formeName = String(formeName).trim();
            if (!formeName.startsWith('(')) formeName = formeName;
            formeName = '(' + capitalize(formeName.toLowerCase()) + ')';
        }

        // types
        const types = (typesByPokemonId.get(id) ?? [])
            .slice()
            .sort((a, b) => toInt(a['slot']) - toInt(b['slot']));
        const type_primary = capitalize(typeIdToName.get(toInt(types[0]?.['type_id'])) ?? 'None');
        const type_secondary = capitalize(typeIdToName.get(toInt(types[1]?.['type_id'])) ?? 'None');

        // abilities
        const abilRows = (abilitiesByPokemonId.get(id) ?? [])
            .slice()
            .sort((a, b) => toInt(a['slot']) - toInt(b['slot']));
        let ability_1 = null,
            ability_2 = null,
            ability_hidden = null;
        for (const ar of abilRows) {
            const nameA =
                abilityIdToName.get(toInt(ar['ability_id'])) ?? `Ability_${ar['ability_id']}`;
            if ((ar['is_hidden'] || ar['is_hidden'] === '1') && String(ar['is_hidden']) !== '0') {
                ability_hidden = nameA;
                continue;
            }
            const slot = toInt(ar['slot']);
            if (slot === 1 && !ability_1) ability_1 = nameA;
            else if (slot === 2 && !ability_2) ability_2 = nameA;
            else if (!ability_1) ability_1 = nameA;
            else if (!ability_2) ability_2 = nameA;
        }

        // stats & ev yields
        const statsFor = statsByPokemonId.get(id) ?? [];
        const statMap: Record<string, number> = {
            hp: 0,
            attack: 0,
            defense: 0,
            sp_attack: 0,
            sp_defense: 0,
            speed: 0,
        };
        const evMap: Record<string, number> = {
            hp: 0,
            attack: 0,
            defense: 0,
            sp_attack: 0,
            sp_defense: 0,
            speed: 0,
        };
        for (const s of statsFor) {
            const sid = toInt(s['stat_id'] ?? s['stat']);
            const key = statIdToKey(sid);
            if (!key) continue;
            statMap[key] = toInt(s['base_stat'] ?? s['base_stat']);
            evMap[key] = toInt(s['effort'] ?? s['effort']);
        }

        // egg groups
        const eggList = eggGroupsBySpeciesId.get(species_id) ?? [];
        const egg_group_1 =
            eggGroupIdToName.get(eggList[0] ?? 0) ??
            (eggList[0] ? `Group_${eggList[0]}` : 'Unknown');
        const egg_group_2 =
            eggGroupIdToName.get(eggList[1] ?? 0) ?? (eggList[1] ? `Group_${eggList[1]}` : 'None');

        // gender odds
        const gender_rate = toInt(
            species['gender_rate'] ?? species['genderRate'] ?? species['gender_rate']
        );
        let male_odds = 0,
            female_odds = 0,
            genderless_odds = 0;
        if (gender_rate === -1 || String(species['gender_rate']).toLowerCase() === '255') {
            genderless_odds = 1;
            male_odds = 0;
            female_odds = 0;
        } else if (!Number.isNaN(gender_rate)) {
            female_odds = gender_rate / 8;
            male_odds = (8 - gender_rate) / 8;
        } else {
            male_odds = 0.5;
            female_odds = 0.5;
        }

        const record = {
            id,
            pokedex_id: species_id,
            alt_id: is_default ? 0 : 1,
            pokemon: capitalize(baseName),
            forme: formeName,
            type_primary,
            type_secondary,
            ability_1,
            ability_2,
            ability_hidden,
            base_hp: statMap.hp,
            base_attack: statMap.attack,
            base_defense: statMap.defense,
            base_sp_attack: statMap.sp_attack,
            base_sp_defense: statMap.sp_defense,
            base_speed: statMap.speed,
            hp_ev_yield: evMap.hp,
            attack_ev_yield: evMap.attack,
            defense_ev_yield: evMap.defense,
            sp_attack_ev_yield: evMap.sp_attack,
            sp_defense_ev_yield: evMap.sp_defense,
            speed_ev_yield: evMap.speed,
            male_odds,
            female_odds,
            genderless_odds,
            height: toInt(p['height']),
            weight: toInt(p['weight']),
            catch_rate: toInt(
                species['capture_rate'] ?? species['captureRate'] ?? species['capture_rate'] ?? 0
            ),
            egg_cycles: toInt(
                species['hatch_counter'] ?? species['egg_cycles'] ?? species['hatchCounter'] ?? 0
            ),
            exp_yield: toInt(p['base_experience'] ?? p['baseExperience'] ?? 0),
            base_happiness: toInt(species['base_happiness'] ?? species['baseHappiness'] ?? 70),
            egg_group_1,
            egg_group_2,
            is_baby: String(species['is_baby'] ?? species['isBaby'] ?? 'false'),
            is_mythical: String(species['is_mythical'] ?? species['isMythical'] ?? 'false'),
            is_legendary: String(species['is_legendary'] ?? species['isLegendary'] ?? 'false'),
            sort_order: toInt(p['order'] ?? species['order'] ?? 0),
        };

        out.push(record);
    }

    console.log('All records', out);

    // write outputs
    ensureDir(OUT_DIR);
    const outJson = path.join(OUT_DIR, 'api_pokedex.json');
    fs.writeFileSync(outJson, JSON.stringify(out, null, 2), 'utf8');

    const sqlFile = path.join(OUT_DIR, 'api_pokedex.sql');
    const columns = Object.keys(out[0] ?? {})
        .map((c) => `\`${c}\``)
        .join(', ');
    const chunks: string[] = [];
    for (const row of out) {
        const vals = Object.keys(row).map((k) => sqlEscape(row[k]));
        chunks.push(`(${vals.join(',')})`);
    }
    const batchSize = 500;
    const writer: string[] = [];
    for (let i = 0; i < chunks.length; i += batchSize) {
        const slice = chunks.slice(i, i + batchSize);
        writer.push(`INSERT INTO \`api_pokedex\` (${columns}) VALUES\n${slice.join(',\n')};`);
    }
    // Prepend DROP + CREATE so the SQL is self-contained
    const header = `DROP TABLE IF EXISTS \`api_pokedex\`;
        CREATE TABLE \`api_pokedex\` (
        \`id\` INT NOT NULL,
        \`pokedex_id\` SMALLINT UNSIGNED DEFAULT NULL,
        \`alt_id\` SMALLINT UNSIGNED DEFAULT 0,
        \`pokemon\` VARCHAR(64) NOT NULL,
        \`forme\` VARCHAR(64) DEFAULT NULL,
        \`type_primary\` VARCHAR(32) DEFAULT NULL,
        \`type_secondary\` VARCHAR(32) DEFAULT NULL,
        \`ability_1\` VARCHAR(42) DEFAULT NULL,
        \`ability_2\` VARCHAR(42) DEFAULT NULL,
        \`ability_hidden\` VARCHAR(42) DEFAULT NULL,
        \`base_hp\` SMALLINT DEFAULT NULL,
        \`base_attack\` SMALLINT DEFAULT NULL,
        \`base_defense\` SMALLINT DEFAULT NULL,
        \`base_sp_attack\` SMALLINT DEFAULT NULL,
        \`base_sp_defense\` SMALLINT DEFAULT NULL,
        \`base_speed\` SMALLINT DEFAULT NULL,
        \`hp_ev_yield\` TINYINT UNSIGNED DEFAULT 0,
        \`attack_ev_yield\` TINYINT UNSIGNED DEFAULT 0,
        \`defense_ev_yield\` TINYINT UNSIGNED DEFAULT 0,
        \`sp_attack_ev_yield\` TINYINT UNSIGNED DEFAULT 0,
        \`sp_defense_ev_yield\` TINYINT UNSIGNED DEFAULT 0,
        \`speed_ev_yield\` TINYINT UNSIGNED DEFAULT 0,
        \`male_odds\` DOUBLE DEFAULT NULL,
        \`female_odds\` DOUBLE DEFAULT NULL,
        \`genderless_odds\` TINYINT(1) DEFAULT 0,
        \`height\` SMALLINT DEFAULT NULL,
        \`weight\` SMALLINT DEFAULT NULL,
        \`catch_rate\` SMALLINT DEFAULT NULL,
        \`egg_cycles\` SMALLINT DEFAULT NULL,
        \`exp_yield\` INT DEFAULT NULL,
        \`base_happiness\` SMALLINT DEFAULT NULL,
        \`egg_group_1\` VARCHAR(64) DEFAULT NULL,
        \`egg_group_2\` VARCHAR(64) DEFAULT NULL,
        \`is_baby\` TINYINT(1) NOT NULL DEFAULT 0,
        \`is_mythical\` TINYINT(1) NOT NULL DEFAULT 0,
        \`is_legendary\` TINYINT(1) NOT NULL DEFAULT 0,
        \`sort_order\` INT DEFAULT NULL,
        PRIMARY KEY (\`id\`),
        KEY \`idx_api_pokedex_pokemon\` (\`pokemon\`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    `;

    // write combined file
    fs.writeFileSync(sqlFile, header + writer.join('\n\n'), 'utf8');

    console.log(`Wrote ${out.length} records to ${outJson} and ${sqlFile}`);
}

build().catch((err) => {
    console.error(err);
    process.exit(2);
});
