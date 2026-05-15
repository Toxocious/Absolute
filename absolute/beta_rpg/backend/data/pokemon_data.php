<?php
    declare(strict_types=1);

    require_once $_SERVER['DOCUMENT_ROOT'] . '/backend/session/database.php';
    require_once $_SERVER['DOCUMENT_ROOT'] . '/backend/utility/weighter.php';

    require_once $_SERVER['DOCUMENT_ROOT'] . '/backend/data/pokedex_data.php';

    final class PokemonData
    {
        /**
         * Fetches a Pokemon's data by its unique ID. Returns null if the Pokemon cannot be found. The returned data includes a 'Nature_Modifier' key which contains the nature's stat modifications.
         *
         * @param int $id The unique ID of the Pokemon to fetch.
         *
         * @return array|null An associative array containing the Pokemon's data, including a 'Nature_Modifier' key with the nature's stat modifications, or null if the Pokemon cannot be found.
         */
        public static function FetchById(int $id): ?array
        {
            $Pokemon_Data = Database::get()->selectOne(
                'SELECT *
                FROM user_pokemon
                WHERE id = :id
                LIMIT 1',
                ['id' => $id]
            );

            $Pokemon_Data['name_data'] = self::GetDisplayName(
                name: $Pokemon_Data['name'] ?? 'Unknown',
                forme: $Pokemon_Data['forme'] ?? null,
                type: $Pokemon_Data['type'] ?? 'Normal',
                nickname:$Pokemon_Data['nickname'] ?? null
            );
            $Pokemon_Data['level'] = self::CalculateLevel((int)($Pokemon_Data['experience'] ?? 0));
            $Pokemon_Data['nature_modifier'] = self::GetNatureModifier($Pokemon_Data['nature']);

            $Pokemon_Data['stats'] = self::CalculateAllStats(
                PokedexData::GetPokemonBaseStats(
                    $Pokemon_Data['pokedex_id'], $Pokemon_Data['alt_id']
                ),
                [
                    'iv_hp' => (int)($Pokemon_Data['iv_hp'] ?? 0),
                    'iv_attack' => (int)($Pokemon_Data['iv_attack'] ?? 0),
                    'iv_defense' => (int)($Pokemon_Data['iv_defense'] ?? 0),
                    'iv_special_attack' => (int)($Pokemon_Data['iv_special_attack'] ?? 0),
                    'iv_special_defense' => (int)($Pokemon_Data['iv_special_defense'] ?? 0),
                    'iv_speed' => (int)($Pokemon_Data['iv_speed'] ?? 0),
                ],
                [
                    'ev_hp' => (int)($Pokemon_Data['ev_hp'] ?? 0),
                    'ev_attack' => (int)($Pokemon_Data['ev_attack'] ?? 0),
                    'ev_defense' => (int)($Pokemon_Data['ev_defense'] ?? 0),
                    'ev_special_attack' => (int)($Pokemon_Data['ev_special_attack'] ?? 0),
                    'ev_special_defense' => (int)($Pokemon_Data['ev_special_defense'] ?? 0),
                    'ev_speed' => (int)($Pokemon_Data['ev_speed'] ?? 0),
                ],
                (int)($Pokemon_Data['level'] ?? 1),
                $Pokemon_Data['nature_modifier']
            );


            return $Pokemon_Data ?: null;
        }

        /**
         * Fetches the current owner's basic data (ID and username) for a given Pokemon ID. Returns null if the Pokemon or owner cannot be found.
         *
         * @param int $id The ID of the Pokemon to fetch the owner data for.
         *
         * @return array|null An associative array containing the owner's ID and username, or null if not found.
         */
        public static function FetchCurrentOwnerData(int $id): ?array
        {
            return Database::get()->selectOne(
                'SELECT u.id, u.username
                FROM users u
                JOIN user_pokemon up ON up.owner_current = u.id
                WHERE up.id = :id
                LIMIT 1',
                ['id' => $id]
            );
        }

        public static function GetDisplayName(string $name, ?string $forme, string $type, ?string $nickname): array
        {
            $displayName = ($type !== 'Normal' ? "{$type}" : '') . $name;
            if ( $forme !== null )
            {
                $displayName .= " ({$forme})";
            }

            return [
                'display_name' => $displayName,
                'nickname' => $nickname,
            ];
        }

        /**
         * Calculates a Pokemon's level based on the provided experience points value.
         *
         * @param int $experience The total experience points of the Pokemon.
         *
         * @return int The calculated level of the Pokemon. The minimum level returned is 1.
         */
        public static function CalculateLevel(int $experience): int
        {
            if ( $experience < 1 )
            {
                return 1;
            }

            return (int)floor(pow($experience + 1, 1 / 3));
        }

        /**
         * Calculates a Pokemon's stat value based on the provided base stat, IV, EV, level, nature modifier, and whether the stat is HP. The formula used is based on the standard Pokemon stat calculation formulas.
         *
         * @param int $baseStat The base stat value for the Pokemon species and stat.
         * @param int $iv The individual value (IV) for the stat, an integer between 0 and 31.
         * @param int $ev The effort value (EV) for the stat, an integer between 0 and 255.
         * @param int $level The level of the Pokemon, an integer greater than or equal to 1.
         * @param string|null $natureModifier The nature modifier for the stat, which can be 'plus', 'minus', or null if the nature does not affect this stat.
         * @param bool $isHP Whether the stat being calculated is HP, which uses a different formula than other stats.
         *
         * @return int The calculated stat value for the Pokemon, with a minimum of 1.
         */
        public static function CalculateStat(int $baseStat, int $iv, int $ev, int $level, ?string $natureModifier, ?bool $isHP = false): int
        {
            if ( $natureModifier === 'plus' )
            {
                $natureMultiplier = 1.1;
            }
            elseif ( $natureModifier === 'minus' )
            {
                $natureMultiplier = 0.9;
            }
            else
            {
                $natureMultiplier = 1.0;
            }

            if ( $isHP )
            {
                $stat = floor((2 * $baseStat + $iv + floor($ev / 4)) * $level / 100) + $level + 10;
            }
            else
            {
                $stat = floor(round(floor(round(2 * $baseStat + $iv + floor($ev / 4)) * $level / 100) + 5) * $natureMultiplier);
            }

            return (int) $stat;
        }

        public static function CalculateAllStats(array $baseStats, array $ivs, array $evs, int $level, ?array $natureModifier): array
        {
            return [
                'hp' => self::CalculateStat(
                    baseStat: $baseStats['base_hp'],
                    iv: $ivs['iv_hp'],
                    ev: $evs['ev_hp'],
                    level: $level,
                    natureModifier: null,
                    isHP: true
                ),
                'attack' => self::CalculateStat(
                    baseStat: $baseStats['base_attack'],
                    iv: $ivs['iv_attack'],
                    ev: $evs['ev_attack'],
                    level: $level,
                    natureModifier: $natureModifier['plus'] === 'Attack' ? 'plus' : ($natureModifier['minus'] === 'Attack' ? 'minus' : null)
                ),
                'defense' => self::CalculateStat(
                    baseStat: $baseStats['base_defense'],
                    iv: $ivs['iv_defense'],
                    ev: $evs['ev_defense'],
                    level: $level,
                    natureModifier: $natureModifier['plus'] === 'Defense' ? 'plus' : ($natureModifier['minus'] === 'Defense' ? 'minus' : null)
                ),
                'special_attack' => self::CalculateStat(
                    baseStat: $baseStats['base_sp_attack'],
                    iv: $ivs['iv_special_attack'],
                    ev: $evs['ev_special_attack'],
                    level: $level,
                    natureModifier: $natureModifier['plus'] === 'SpAttack' ? 'plus' : ($natureModifier['minus'] === 'SpAttack' ? 'minus' : null)
                ),
                'special_defense' => self::CalculateStat(
                    baseStat: $baseStats['base_sp_defense'],
                    iv: $ivs['iv_special_defense'],
                    ev: $evs['ev_special_defense'],
                    level: $level,
                    natureModifier: $natureModifier['plus'] === 'SpDefense' ? 'plus' : ($natureModifier['minus'] === 'SpDefense' ? 'minus' : null)
                ),
                'speed' => self::CalculateStat(
                    baseStat: $baseStats['base_speed'],
                    iv: $ivs['iv_speed'],
                    ev: $evs['ev_speed'],
                    level: $level,
                    natureModifier: $natureModifier['plus'] === 'Speed' ? 'plus' : ($natureModifier['minus'] === 'Speed' ? 'minus' : null)
                ),
            ];
        }

        /**
         * Generates a random ability for a Pokemon based on the provided abilities and whether to include the hidden ability.
         *
         * @param array $abilities An array of abilities for the Pokemon, where index 0 is the first ability, index 1 is the second ability (if applicable), and index 2 is the hidden ability (if applicable).
         * @param bool $includeHidden Whether to include the hidden ability in the random selection.
         *
         * @return string|null The randomly selected ability, or null if no abilities are available.
         */
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

        /**
         * Generates a random gender for a Pokemon based on the provided odds for each gender.
         *
         * @param float $maleOdds The odds of the Pokemon being male (between 0.0f and 100.0f).
         * @param float $femaleOdds The odds of the Pokemon being female (between 0.0f and 100.0f).
         * @param float $genderlessOdds The odds of the Pokemon being genderless (between 0.0f and 100.0f).
         *
         * @return string The randomly selected gender ('male', 'female', or 'genderless').
         */
        public static function GenerateRandomGender(float $maleOdds, float $femaleOdds, float $genderlessOdds): string
        {
            $Gender_Weighter = new Weighter();

            $Gender_Weighter->Add('male', (int)($maleOdds * 1000));
            $Gender_Weighter->Add('female', (int)($femaleOdds * 1000));
            $Gender_Weighter->Add('genderless', (int)($genderlessOdds * 1000));

            return $Gender_Weighter->Pick();
        }

        /**
         * Generates random IVs for a Pokemon. Each IV is an integer between 0 and 31.
         *
         * @return array An associative array containing the IVs for each stat.
         */
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

        /**
         * Generates a random nature for a Pokemon.
         *
         * @return string The randomly selected nature.
         */
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

        /**
         * Fetches the appropriate sprite and icon paths for a given Pokemon based on its Pokedex ID, alternate form ID, type, name, and forme.
         *
         * @param int $Pokedex_ID The Pokedex ID of the Pokemon species.
         * @param int $Alt_ID The alternate form ID for the Pokemon (default is 0, which represents the default form).
         * @param string $Type The type of the Pokemon (e.g., 'Normal', 'Shiny'). Default is 'Normal'.
         * @param string|null $Pokemon_Name The name of the Pokemon (used for alt text). Default is 'Unknown'.
         * @param string|null $Forme The forme of the Pokemon (used for alt text, if applicable). Default is null.
         */
        public static function FetchPokemonImages(
            int $Pokedex_ID,
            int $Alt_ID = 0,
            string $Type = 'Normal',
            ?string $Pokemon_Name = 'Unknown',
            ?string $Forme = null,
        ): array
        {
            $Type = ($Type === 'Shiny') ? 'Shiny' : 'Normal';

            $Alt_Text = '';
            if ($Type === 'Shiny')
            {
                $Alt_Text .= 'Shiny';
            }

            $Alt_Text .= $Pokemon_Name;

            $Filename = (string)$Pokedex_ID;

            if ($Forme !== null)
            {
                $Alt_Text .= ' (' . $Forme . ')';
                $Filename .= '-' . strtolower(str_replace(' ', '-', $Forme));
            }

            $Filename .= '.png';

            $Alt_Text = htmlspecialchars($Alt_Text, ENT_QUOTES, 'UTF-8');

            $Build_Image = function (string $Category) use (
                $Type,
                $Filename,
                $Alt_Text,
            ): array
            {
                $Relative_Path = "/assets/images/Pokemon/{$Category}/{$Type}/{$Filename}";
                $Absolute_Path = $_SERVER['DOCUMENT_ROOT'] . $Relative_Path;

                if ( !file_exists($Absolute_Path) )
                {
                    $Relative_Path = "/assets/images/Pokemon/{$Category}/Normal/{$Filename}";
                }

                return [
                    'Sprite_Path' => $Relative_Path,
                    'Alt_Text' => $Alt_Text,
                ];
            };

            return [
                'Icon'   => $Build_Image('Icons'),
                'Sprite' => $Build_Image('Sprites'),
            ];
        }

        public static function GetNatureModifier(?string $nature): array
        {
            $Natures = self::Natures();

            if ( $nature === null || !array_key_exists($nature, $Natures) )
            {
                return [
                    'plus' => null,
                    'minus' => null,
                ];
            }

            return $Natures[$nature];
        }

        /**
         * Returns an associative array of Pokemon natures, where each nature has a 'plus' stat that it boosts and a 'minus' stat that it hinders. Natures that do not boost or hinder any stats have both 'plus' and 'minus' set to null.
         */
        public static function Natures()
        {
            return [
                'Adamant' => [
                    'plus' => 'attack',
                    'minus' => 'special_attack'
                ],
                'Brave' => [
                    'plus' => 'attack',
                    'minus' => 'speed'
                ],
                'Lonely' => [
                    'plus' => 'attack',
                    'minus' => 'defense'
                ],
                'Naughty' => [
                    'plus' => 'attack',
                    'minus' => 'special_defense'
                ],

                'Bold' => [
                    'plus' => 'defense',
                    'minus' => 'attack'
                ],
                'Impish' => [
                    'plus' => 'defense',
                    'minus' => 'special_attack'
                ],
                'Lax' => [
                    'plus' => 'defense',
                    'minus' => 'special_defense'
                ],
                'Relaxed' => [
                    'plus' => 'defense',
                    'minus' => 'speed'
                ],

                'Modest' => [
                    'plus' => 'special_attack',
                    'minus' => 'attack'
                ],
                'Mild' => [
                    'plus' => 'special_attack',
                    'minus' => 'defense'
                ],
                'Quiet' => [
                    'plus' => 'special_attack',
                    'minus' => 'special_defense'
                ],
                'Rash' => [
                    'plus' => 'special_attack',
                    'minus' => 'speed'
                ],

                'Calm' => [
                    'plus' => 'special_defense',
                    'minus' => 'attack'
                ],
                'Careful' => [
                    'plus' => 'special_defense',
                    'minus' => 'special_attack'
                ],
                'Gentle' => [
                    'plus' => 'special_defense',
                    'minus' => 'defense'
                ],
                'Sassy' => [
                    'plus' => 'special_defense',
                    'minus' => 'speed'
                ],

                'Hasty' => [
                    'plus' => 'speed',
                    'minus' => 'defense'
                ],
                'Jolly' => [
                    'plus' => 'speed',
                    'minus' => 'special_attack'
                ],
                'Naive' => [
                    'plus' => 'speed',
                    'minus' => 'special_defense'
                ],
                'Timid' => [
                    'plus' => 'speed',
                    'minus' => 'attack'
                ],

                'Bashful' => [
                    'plus' => null,
                    'minus' => null
                ],
                'Docile' => [
                    'plus' => null,
                    'minus' => null
                ],
                'Hardy' => [
                    'plus' => null,
                    'minus' => null
                ],
                'Quirky' => [
                    'plus' => null,
                    'minus' => null
                ],
                'Serious' => [
                    'plus' => null,
                    'minus' => null
                ],
            ];
        }
    }
