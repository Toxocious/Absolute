<?php
    declare(strict_types=1);

    require_once $_SERVER['DOCUMENT_ROOT'] . '/backend/session/database.php';
    require_once $_SERVER['DOCUMENT_ROOT'] . '/backend/utility/weighter.php';

    final class PokedexData
    {
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

        public static function GenerateRandomAbility(array $abilities, bool $includeHidden = false): ?string
        {
            $BASE_ABILITY_WEIGHT = 100;
            $HIDDEN_ABILITY_WEIGHT = 3;

            if (empty($abilities)) {
                return null;
            }

            $Ability_Weighter = new Weighter();
            foreach ($abilities as $index => $ability) {
                if (empty($ability)) {
                    continue;
                }

                $weight = $BASE_ABILITY_WEIGHT;
                if ($includeHidden && $index === 2 && isset($abilities[2])) {
                    $weight = $HIDDEN_ABILITY_WEIGHT;
                }

                $Ability_Weighter->Add($ability, $weight);
            }

            return $Ability_Weighter->Pick();
        }

        public static function GenerateRandomGender(float $maleOdds, float $femaleOdds, float $genderlessOdds): string
        {
            $Gender_Weighter = new Weighter();

            $Gender_Weighter->Add('male', (int)($maleOdds * 1000));
            $Gender_Weighter->Add('female', (int)($femaleOdds * 1000));
            $Gender_Weighter->Add('genderless', (int)($genderlessOdds * 1000));

            return $Gender_Weighter->Pick();
        }

        public static function GenerateRandomIVs(): array
        {
            return [
                'hp_iv' => mt_rand(0, 31),
                'attack_iv' => mt_rand(0, 31),
                'defense_iv' => mt_rand(0, 31),
                'special_attack_iv' => mt_rand(0, 31),
                'special_defense_iv' => mt_rand(0, 31),
                'speed_iv' => mt_rand(0, 31),
            ];
        }

        public static function GenerateRandomNature(): string
        {
            $Natures = [
                'Hardy', 'Lonely', 'Brave', 'Adamant', 'Naughty',
                'Bold', 'Docile', 'Relaxed', 'Impish', 'Lax',
                'Timid', 'Hasty', 'Serious', 'Jolly', 'Naive',
                'Modest', 'Mild', 'Quiet', 'Bashful', 'Rash',
                'Calm', 'Gentle', 'Sassy', 'Careful', 'Quirky'
            ];

            return $Natures[array_rand($Natures)];
        }
    }
