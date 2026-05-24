<?php
    declare(strict_types=1);

    require_once $_SERVER['DOCUMENT_ROOT'] . '/backend/session/database.php';
    require_once $_SERVER['DOCUMENT_ROOT'] . '/backend/utility/weighter.php';

    final class PokedexData
    {
        /**
         * Fetches a single Pokedex entry by its Pokedex ID and optional Alt ID.
         *
         * @param int $pokedexId The Pokedex ID of the Pokemon.
         * @param int $altId The alternative form ID of the Pokemon (default is 0).
         *
         * @return array|null An associative array of the Pokedex entry data, or null if not found.
         */
        public static function fetch(int $pokedexId, int $altId = 0): ?array
        {
            $db = Database::get();

            $row = $db->selectOne(
                'SELECT *
                FROM pokedex
                WHERE pokedex_id = :pokedex_id AND alt_id = :alt_id
                LIMIT 1',
                [
                    'pokedex_id' => $pokedexId,
                    'alt_id' => $altId,
                ]
            );

            if ( $row )
            {
                return $row;
            }

            return null;
        }

        /**
         * Fetches a single Pokedex entry by its unique database ID.
         *
         * @param int $id The unique database ID of the Pokedex entry.
         *
         * @return array|null An associative array of the Pokedex entry data, or null if not found.
         */
        public static function fetchById(int $id): ?array
        {
            return Database::get()->selectOne(
                'SELECT *
                FROM pokedex
                WHERE id = :id
                LIMIT 1',
                ['id' => $id]
            );
        }

        /**
         * Fetches multiple Pokedex entries by an array of Pokedex IDs and optional Alt ID.
         *
         * @param array $pokedexIds An array of Pokedex IDs to fetch.
         * @param int $altId The alternative form ID of the Pokemon (default is 0).
         *
         * @return array An array of associative arrays, each representing a Pokedex entry.
         */
        public static function fetchManyByDexIds(array $pokedexIds, int $altId = 0): array
        {
            $cleanIds = self::cleanDexIds($pokedexIds);
            if ($cleanIds === []) {
                return [];
            }

            $params = ['alt_id' => $altId];
            $placeholders = [];

            foreach ($cleanIds as $i => $dexId) {
                $key = 'dex_' . $i;
                $placeholders[] = ':' . $key;
                $params[$key] = $dexId;
            }

            $sql = '
                SELECT *
                FROM pokedex
                WHERE alt_id = :alt_id
                AND pokedex_id IN (' . implode(', ', $placeholders) . ')
                ORDER BY sort_order ASC, pokedex_id ASC
            ';

            return Database::get()->select($sql, $params);
        }

        /**
         * Searches for Pokedex entries by a name query, matching against the Pokemon name and forme.
         *
         * @param string $query The search query string.
         * @param int $limit The maximum number of results to return (default is 25).
         * @param int $offset The number of results to skip for pagination (default is 0).
         *
         * @return array An array of associative arrays, each representing a Pokedex entry that matches the search query.
         */
        public static function searchByName(string $query, int $limit = 25, int $offset = 0): array
        {
            $db = Database::get();

            $query = trim($query);
            if ($query === '') {
                return [];
            }

            $limit = max(1, min(100, $limit));
            $offset = max(0, $offset);

            return $db->select(
                'SELECT *
                FROM pokedex
                WHERE pokemon LIKE :name
                    OR CONCAT(pokemon, " ", COALESCE(forme, "")) LIKE :name
                ORDER BY sort_order ASC, pokedex_id ASC
                LIMIT ' . $limit . ' OFFSET ' . $offset,
                ['name' => '%' . $query . '%']
            );
        }

        /**
         * Cleans an array of Pokedex IDs by ensuring they are valid integers within a reasonable range, removing duplicates, and sorting them.
         *
         * @param array $ids An array of Pokedex IDs to clean.
         *
         * @return array An array of cleaned, unique, and sorted Pokedex IDs.
         */
        private static function cleanDexIds(array $ids): array
        {
            $clean = [];

            foreach ($ids as $value) {
                if (is_int($value)) {
                    $id = $value;
                } elseif (is_string($value) && ctype_digit($value)) {
                    $id = (int)$value;
                } else {
                    continue;
                }

                if ($id > 0 && $id <= 20000) {
                    $clean[] = $id;
                }
            }

            $clean = array_values(array_unique($clean));
            sort($clean);

            return $clean;
        }

        public static function GetPokemonBaseStats(int $pokedexId, int $altId = 0): ?array
        {
            return Database::get()->selectOne(
                'SELECT base_hp, base_attack, base_defense, base_sp_attack, base_sp_defense, base_speed
                FROM pokedex
                WHERE pokedex_id = :pokedex_id AND alt_id = :alt_id
                LIMIT 1',
                [
                    'pokedex_id' => $pokedexId,
                    'alt_id' => $altId,
                ]
             );
        }

        public static function GetPokemonDropdownList(): array
        {
            return Database::get()->select(
                'SELECT id, pokedex_id, alt_id, pokemon, forme
                FROM pokedex
                ORDER BY pokedex_id ASC, alt_id ASC'
            );
        }
    }
