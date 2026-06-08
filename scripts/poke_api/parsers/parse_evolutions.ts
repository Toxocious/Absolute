import fs from 'fs';
import path from 'path';

import { CSV_PATH, OUTPUT_PATH } from './utility/output_const';
import { Row, parseCSV } from './utility/file';

function toInt(v: string | undefined, fallback = 0) {
    const n = Number(v ?? '');
    return Number.isNaN(n) ? fallback : Math.trunc(n);
}
function toBool(v: string | undefined) {
    if (v === undefined || v === null || v === '') return false;
    const s = String(v).trim().toLowerCase();
    return s === '1' || s === 'true' || s === 'yes';
}

function ensureDir(dir: string) {
    if (!fs.existsSync(dir)) fs.mkdirSync(dir, { recursive: true });
}

function sqlEscape(v: any): string {
    if (v === null || v === undefined) return 'NULL';
    if (typeof v === 'number' || typeof v === 'boolean') return String(v ? 1 : 0);
    return "'" + String(v).replace(/\\/g, '\\\\').replace(/'/g, "\\'") + "'";
}

const CSV_DIR = CSV_PATH;
const OUT_DIR = OUTPUT_PATH;

async function loadCSV(name: string): Promise<Row[]> {
    const file = path.join(CSV_DIR, `${name}.csv`);
    if (!fs.existsSync(file)) {
        console.warn(`[Parse Evolutions] Missing ${name}.csv (${file})`);
        return [];
    }
    const content = fs.readFileSync(file, 'utf8');
    return parseCSV(content);
}

async function build() {
    const [evoRows, speciesRows, triggerRows, itemRows, moveRows, pokemonRows] = await Promise.all([
        loadCSV('pokemon_evolution'),
        loadCSV('pokemon_species'),
        loadCSV('evolution_triggers'),
        loadCSV('items'),
        loadCSV('moves'),
        loadCSV('pokemon'),
    ]);

    const speciesById = new Map<number, string>();
    const evolvesFromBySpeciesId = new Map<number, number | null>();
    for (const r of speciesRows) {
        const id = toInt(r['id']);
        speciesById.set(id, String(r['identifier'] ?? '').trim());
        const fromId = r['evolves_from_species_id'] ? toInt(r['evolves_from_species_id']) : null;
        evolvesFromBySpeciesId.set(id, fromId);
    }

    const triggerById = new Map<number, string>();
    for (const r of triggerRows)
        triggerById.set(toInt(r['id']), String(r['identifier'] ?? '').trim());

    const itemById = new Map<number, string>();
    for (const r of itemRows) itemById.set(toInt(r['id']), String(r['identifier'] ?? '').trim());

    const moveById = new Map<number, string>();
    for (const r of moveRows) moveById.set(toInt(r['id']), String(r['identifier'] ?? '').trim());

    // Build mapping of species_id -> list of pokemon forms (with pokemon_id + identifier)
    const formsBySpeciesId = new Map<number, Array<{ pokemon_id: number; identifier: string }>>();
    for (const r of pokemonRows) {
        const pid = toInt(r['id']);
        const sid = toInt(r['species_id']);
        const ident = String(r['identifier'] ?? '').trim();
        if (!ident) continue;
        if (!formsBySpeciesId.has(sid)) formsBySpeciesId.set(sid, []);
        formsBySpeciesId.get(sid)!.push({ pokemon_id: pid, identifier: ident });
    }

    // find max existing evolution id to generate unique ids for extra "form" rows
    const maxOriginalId = evoRows.reduce((m, r) => Math.max(m, toInt(r['id'])), 0);
    let nextId = maxOriginalId + 1;

    const out: any[] = [];

    // detect evolution trigger id for "use-item"
    const useItemTriggerId =
        [...triggerById.entries()].find(([, v]) => v === 'use-item')?.[0] ?? null;

    // synthesize mega-evolution rows for forms like "<species>-mega"
    if (useItemTriggerId !== null) {
        for (const [speciesId, forms] of formsBySpeciesId) {
            const speciesIdent = speciesById.get(speciesId) ?? '';
            for (const f of forms) {
                if (!f.identifier.includes('-mega')) continue;
                // try to find matching mega stone item (e.g., "absolite")
                const expectedStone = `${speciesIdent}ite`;
                const stoneEntry =
                    [...itemById.entries()].find(([, ident]) => ident === expectedStone) ||
                    [...itemById.entries()].find(
                        ([, ident]) => ident.includes(speciesIdent) && ident.endsWith('ite')
                    );
                const triggerItemIdForMega = stoneEntry ? stoneEntry[0] : null;

                const baseForms = formsBySpeciesId.get(speciesId) ?? [];
                const canonical =
                    baseForms.find((b) => b.identifier === speciesIdent) ?? baseForms[0] ?? null;
                const canonicalPid = canonical ? canonical.pokemon_id : null;
                const canonicalIdent = canonical ? canonical.identifier : speciesIdent;

                const synth = {
                    id: nextId++,
                    evolved_species_id: speciesId,
                    evolved_species: speciesIdent,
                    evolved_pokemon_id: f.pokemon_id,
                    evolved_pokemon: f.identifier,
                    prev_species_id: speciesId,
                    prev_species: speciesIdent,
                    prev_pokemon_id: canonicalPid,
                    prev_pokemon: canonicalIdent,
                    evolution_trigger_id: useItemTriggerId,
                    trigger: triggerById.get(useItemTriggerId) ?? 'use-item',
                    trigger_item_id: triggerItemIdForMega,
                    trigger_item: triggerItemIdForMega
                        ? itemById.get(triggerItemIdForMega) ?? null
                        : null,
                    minimum_level: null,
                    gender_id: null,
                    location_id: null,
                    held_item_id: null,
                    held_item: null,
                    time_of_day: null,
                    known_move_id: null,
                    known_move: null,
                    known_move_type_id: null,
                    minimum_happiness: null,
                    minimum_beauty: null,
                    minimum_affection: null,
                    relative_physical_stats: null,
                    party_species_id: null,
                    party_species: null,
                    party_type_id: null,
                    trade_species_id: null,
                    trade_species: null,
                    needs_overworld_rain: false,
                    turn_upside_down: false,
                    needs_multiplayer: false,
                    region_id: null,
                    base_form_id: null,
                    used_move_id: null,
                    used_move: null,
                    minimum_move_count: null,
                    minimum_steps: null,
                    minimum_damage_taken: null,
                };
                out.push(synth);
            }
        }
    }

    for (const r of evoRows) {
        const id = toInt(r['id']);
        const evolved_species_id = toInt(r['evolved_species_id']);
        const evolution_trigger_id = toInt(r['evolution_trigger_id']);
        const trigger_item_id = r['trigger_item_id'] ? toInt(r['trigger_item_id']) : null;
        const minimum_level = r['minimum_level'] ? toInt(r['minimum_level']) : null;
        const gender_id = r['gender_id'] ? toInt(r['gender_id']) : null;
        const location_id = r['location_id'] ? toInt(r['location_id']) : null;
        const held_item_id = r['held_item_id'] ? toInt(r['held_item_id']) : null;
        const time_of_day = (r['time_of_day'] ?? null) || null;
        const known_move_id = r['known_move_id'] ? toInt(r['known_move_id']) : null;
        const known_move_type_id = r['known_move_type_id'] ? toInt(r['known_move_type_id']) : null;
        const minimum_happiness = r['minimum_happiness'] ? toInt(r['minimum_happiness']) : null;
        const minimum_beauty = r['minimum_beauty'] ? toInt(r['minimum_beauty']) : null;
        const minimum_affection = r['minimum_affection'] ? toInt(r['minimum_affection']) : null;
        const relative_physical_stats = r['relative_physical_stats']
            ? toInt(r['relative_physical_stats'])
            : null;
        const party_species_id = r['party_species_id'] ? toInt(r['party_species_id']) : null;
        const party_type_id = r['party_type_id'] ? toInt(r['party_type_id']) : null;
        const trade_species_id = r['trade_species_id'] ? toInt(r['trade_species_id']) : null;
        const needs_overworld_rain = toBool(r['needs_overworld_rain']);
        const turn_upside_down = toBool(r['turn_upside_down']);
        const needs_multiplayer = toBool(r['needs_multiplayer']);
        const region_id = r['region_id'] ? toInt(r['region_id']) : null;
        const base_form_id = r['base_form_id'] ? toInt(r['base_form_id']) : null;
        const used_move_id = r['used_move_id'] ? toInt(r['used_move_id']) : null;
        const minimum_move_count = r['minimum_move_count'] ? toInt(r['minimum_move_count']) : null;
        const minimum_steps = r['minimum_steps'] ? toInt(r['minimum_steps']) : null;
        const minimum_damage_taken = r['minimum_damage_taken']
            ? toInt(r['minimum_damage_taken'])
            : null;

        const speciesIdent = speciesById.get(evolved_species_id) ?? null;

        // canonical evolved pokemon
        const evolvedForms = formsBySpeciesId.get(evolved_species_id) ?? [];
        const canonicalEvolved =
            evolvedForms.find((f) => f.identifier === speciesIdent) ?? evolvedForms[0] ?? null;
        const canonicalPokemonId = canonicalEvolved ? canonicalEvolved.pokemon_id : null;
        const canonicalPokemonIdentifier = canonicalEvolved
            ? canonicalEvolved.identifier
            : speciesIdent;

        // previous species (evolves_from_species_id from species data)
        const prev_species_id = evolvesFromBySpeciesId.get(evolved_species_id) ?? null;
        const prev_species = prev_species_id ? speciesById.get(prev_species_id) ?? null : null;
        // canonical prev pokemon (if any)
        const prevForms = prev_species_id ? formsBySpeciesId.get(prev_species_id) ?? [] : [];
        const canonicalPrev =
            prevForms.find((f) => f.identifier === prev_species) ?? prevForms[0] ?? null;
        const canonicalPrevPokemonId = canonicalPrev ? canonicalPrev.pokemon_id : null;
        const canonicalPrevPokemonIdentifier = canonicalPrev
            ? canonicalPrev.identifier
            : prev_species;

        const baseRow = {
            id,
            evolved_species_id,
            evolved_species: speciesIdent,
            evolved_pokemon_id: canonicalPokemonId,
            evolved_pokemon: canonicalPokemonIdentifier,
            prev_species_id,
            prev_species,
            prev_pokemon_id: canonicalPrevPokemonId,
            prev_pokemon: canonicalPrevPokemonIdentifier,
            evolution_trigger_id,
            trigger: triggerById.get(evolution_trigger_id) ?? null,
            trigger_item_id,
            trigger_item: trigger_item_id ? itemById.get(trigger_item_id) ?? null : null,
            minimum_level,
            gender_id,
            location_id,
            held_item_id,
            held_item: held_item_id ? itemById.get(held_item_id) ?? null : null,
            time_of_day,
            known_move_id,
            known_move: known_move_id ? moveById.get(known_move_id) ?? null : null,
            known_move_type_id,
            minimum_happiness,
            minimum_beauty,
            minimum_affection,
            relative_physical_stats,
            party_species_id,
            party_species: party_species_id ? speciesById.get(party_species_id) ?? null : null,
            party_type_id,
            trade_species_id,
            trade_species: trade_species_id ? speciesById.get(trade_species_id) ?? null : null,
            needs_overworld_rain,
            turn_upside_down,
            needs_multiplayer,
            region_id,
            base_form_id,
            used_move_id,
            used_move: used_move_id ? moveById.get(used_move_id) ?? null : null,
            minimum_move_count,
            minimum_steps,
            minimum_damage_taken,
        };

        out.push(baseRow);

        // Emit extra rows for every non-canonical form so form-specific identifiers have matching evolution entries.
        // For prev_pokemon fields we keep canonical prev (to avoid exponential duplication).
        for (const f of evolvedForms) {
            if (f.identifier === canonicalPokemonIdentifier) continue;
            const extraRow = {
                ...baseRow,
                id: nextId++,
                evolved_pokemon_id: f.pokemon_id,
                evolved_pokemon: f.identifier,
            };
            out.push(extraRow);
        }
    }

    ensureDir(OUT_DIR);
    const jsonFile = path.join(OUT_DIR, 'api_evolutions.json');
    fs.writeFileSync(jsonFile, JSON.stringify(out, null, 2), 'utf8');

    // SQL
    const sqlParts: string[] = [];
    sqlParts.push('DROP TABLE IF EXISTS `api_evolutions`;');
    sqlParts.push(`CREATE TABLE \`api_evolutions\` (
  \`id\` INT NOT NULL,
  \`evolved_species_id\` INT NOT NULL,
  \`evolved_species\` VARCHAR(64) DEFAULT NULL,
  \`evolved_pokemon_id\` INT DEFAULT NULL,
  \`evolved_pokemon\` VARCHAR(64) DEFAULT NULL,
  \`prev_species_id\` INT DEFAULT NULL,
  \`prev_species\` VARCHAR(64) DEFAULT NULL,
  \`prev_pokemon_id\` INT DEFAULT NULL,
  \`prev_pokemon\` VARCHAR(64) DEFAULT NULL,
  \`evolution_trigger_id\` SMALLINT UNSIGNED DEFAULT NULL,
  \`trigger\` VARCHAR(64) DEFAULT NULL,
  \`trigger_item_id\` INT DEFAULT NULL,
  \`trigger_item\` VARCHAR(64) DEFAULT NULL,
  \`minimum_level\` SMALLINT DEFAULT NULL,
  \`gender_id\` SMALLINT DEFAULT NULL,
  \`location_id\` SMALLINT DEFAULT NULL,
  \`held_item_id\` INT DEFAULT NULL,
  \`held_item\` VARCHAR(64) DEFAULT NULL,
  \`time_of_day\` VARCHAR(16) DEFAULT NULL,
  \`known_move_id\` INT DEFAULT NULL,
  \`known_move\` VARCHAR(64) DEFAULT NULL,
  \`known_move_type_id\` SMALLINT DEFAULT NULL,
  \`minimum_happiness\` SMALLINT DEFAULT NULL,
  \`minimum_beauty\` SMALLINT DEFAULT NULL,
  \`minimum_affection\` SMALLINT DEFAULT NULL,
  \`relative_physical_stats\` SMALLINT DEFAULT NULL,
  \`party_species_id\` INT DEFAULT NULL,
  \`party_species\` VARCHAR(64) DEFAULT NULL,
  \`party_type_id\` SMALLINT DEFAULT NULL,
  \`trade_species_id\` INT DEFAULT NULL,
  \`trade_species\` VARCHAR(64) DEFAULT NULL,
  \`needs_overworld_rain\` TINYINT(1) NOT NULL DEFAULT 0,
  \`turn_upside_down\` TINYINT(1) NOT NULL DEFAULT 0,
  \`needs_multiplayer\` TINYINT(1) NOT NULL DEFAULT 0,
  \`region_id\` SMALLINT DEFAULT NULL,
  \`base_form_id\` INT DEFAULT NULL,
  \`used_move_id\` INT DEFAULT NULL,
  \`used_move\` VARCHAR(64) DEFAULT NULL,
  \`minimum_move_count\` SMALLINT DEFAULT NULL,
  \`minimum_steps\` INT DEFAULT NULL,
  \`minimum_damage_taken\` SMALLINT DEFAULT NULL,
  PRIMARY KEY (\`id\`),
  KEY idx_api_evolutions_evolved (\`evolved_species_id\`),
  KEY idx_api_evolutions_prev (\`prev_species_id\`),
  KEY idx_api_evolutions_trigger (\`evolution_trigger_id\`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;`);

    const chunks: string[] = [];
    for (const row of out) {
        const vals = [
            sqlEscape(row.id),
            sqlEscape(row.evolved_species_id),
            sqlEscape(row.evolved_species),
            row.evolved_pokemon_id === null ? 'NULL' : sqlEscape(row.evolved_pokemon_id),
            sqlEscape(row.evolved_pokemon),
            row.prev_species_id === null ? 'NULL' : sqlEscape(row.prev_species_id),
            sqlEscape(row.prev_species),
            row.prev_pokemon_id === null ? 'NULL' : sqlEscape(row.prev_pokemon_id),
            sqlEscape(row.prev_pokemon),
            sqlEscape(row.evolution_trigger_id),
            sqlEscape(row.trigger),
            sqlEscape(row.trigger_item_id),
            sqlEscape(row.trigger_item),
            row.minimum_level === null ? 'NULL' : sqlEscape(row.minimum_level),
            row.gender_id === null ? 'NULL' : sqlEscape(row.gender_id),
            row.location_id === null ? 'NULL' : sqlEscape(row.location_id),
            row.held_item_id === null ? 'NULL' : sqlEscape(row.held_item_id),
            sqlEscape(row.held_item),
            row.time_of_day === null ? 'NULL' : sqlEscape(row.time_of_day),
            row.known_move_id === null ? 'NULL' : sqlEscape(row.known_move_id),
            sqlEscape(row.known_move),
            row.known_move_type_id === null ? 'NULL' : sqlEscape(row.known_move_type_id),
            row.minimum_happiness === null ? 'NULL' : sqlEscape(row.minimum_happiness),
            row.minimum_beauty === null ? 'NULL' : sqlEscape(row.minimum_beauty),
            row.minimum_affection === null ? 'NULL' : sqlEscape(row.minimum_affection),
            row.relative_physical_stats === null ? 'NULL' : sqlEscape(row.relative_physical_stats),
            row.party_species_id === null ? 'NULL' : sqlEscape(row.party_species_id),
            sqlEscape(row.party_species),
            row.party_type_id === null ? 'NULL' : sqlEscape(row.party_type_id),
            row.trade_species_id === null ? 'NULL' : sqlEscape(row.trade_species_id),
            sqlEscape(row.trade_species),
            sqlEscape(row.needs_overworld_rain ? 1 : 0),
            sqlEscape(row.turn_upside_down ? 1 : 0),
            sqlEscape(row.needs_multiplayer ? 1 : 0),
            row.region_id === null ? 'NULL' : sqlEscape(row.region_id),
            row.base_form_id === null ? 'NULL' : sqlEscape(row.base_form_id),
            row.used_move_id === null ? 'NULL' : sqlEscape(row.used_move_id),
            sqlEscape(row.used_move),
            row.minimum_move_count === null ? 'NULL' : sqlEscape(row.minimum_move_count),
            row.minimum_steps === null ? 'NULL' : sqlEscape(row.minimum_steps),
            row.minimum_damage_taken === null ? 'NULL' : sqlEscape(row.minimum_damage_taken),
        ];
        chunks.push(`(${vals.join(',')})`);
    }

    const batchSize = 500;
    for (let i = 0; i < chunks.length; i += batchSize) {
        const slice = chunks.slice(i, i + batchSize);
        sqlParts.push(`INSERT INTO \`api_evolutions\` VALUES\n${slice.join(',\n')};`);
    }

    const sqlFile = path.join(OUT_DIR, 'api_evolutions.sql');
    fs.writeFileSync(sqlFile, sqlParts.join('\n\n'), 'utf8');

    console.log(`Wrote ${out.length} evolution rows to ${jsonFile} and ${sqlFile}`);
}

build().catch((err) => {
    console.error(err);
    process.exit(2);
});
