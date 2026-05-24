<?php
    declare(strict_types=1);

    require_once $_SERVER['DOCUMENT_ROOT'] . '/backend/session/database.php';

    require_once $_SERVER['DOCUMENT_ROOT'] . '/backend/data/pokedex_data.php';
    require_once $_SERVER['DOCUMENT_ROOT'] . '/backend/data/pokemon_data.php';
    require_once $_SERVER['DOCUMENT_ROOT'] . '/backend/data/user_data.php';

    final class PokemonCenterService
    {
        public static function fetchPokemon(int $userId, int $pokemonId): array
        {
            $execution_time_start = time();

            $row = Database::get()
                ->table('user_pokemon')
                ->where('owner_current', '=', $userId)
                ->where('id', '=', $pokemonId)
                ->first();

            if (!$row) {
                throw new RuntimeException('Pokemon not found.');
            }

            return [
                'pokemon' => self::mapPokemonRow($row),
                'execution_time_ms' => (time() - $execution_time_start) * 1000,
            ];
        }

        public static function fetchTeam(int $userId): array
        {
            $rows = Database::get()
                ->table('user_pokemon')
                ->where('owner_current', '=', $userId)
                ->whereRaw('LOWER(location) = :location', ['location' => 'roster'])
                ->orderBy('slot', 'ASC')
                ->limit(6)
                ->get();

            return array_map([self::class, 'mapPokemonRow'], $rows);
        }

        public static function fetchBox(int $userId, int $page, int $perPage, ?array $filter): array
        {
            if ( isset($filter['species']) ) {
                $filter['species_id'] = explode('.', $filter['species'])[0];
                $filter['alt_id'] = explode('.', $filter['species'])[1];
            }

            $offset = ($page - 1) * $perPage;

            $baseQuery = Database::get()
                ->table('user_pokemon')
                ->where('owner_current', '=', $userId)
                ->whereRaw('LOWER(location) = :location', ['location' => 'box']);

            if ( is_array($filter) ) {
                if ( isset($filter['type']) && is_string($filter['type']) && $filter['type'] !== 'all' ) {
                    $baseQuery->where('type', '=', $filter['type']);
                }

                if ( isset($filter['gender']) && is_string($filter['gender']) && in_array($filter['gender'], ['female', 'male', 'genderless', 'ungendered'], true) ) {
                    $filter_gender = $filter['gender'] === 'ungendered' ? '(?)' : $filter['gender'];
                    $baseQuery->where('gender', '=', $filter_gender);
                }

                if ( isset($filter['species']) && is_string($filter['species']) ) {
                    $baseQuery->where('pokedex_id', '=', $filter['species_id']);
                    $baseQuery->where('alt_id', '=', $filter['alt_id']);
                }
            }

            $total = $baseQuery->count();

            $rows = Database::get()
                ->table('user_pokemon')
                ->where('owner_current', '=', $userId)
                ->whereRaw('LOWER(location) = :location', ['location' => 'box']);

            if ( is_array($filter) ) {
                if ( isset($filter['type']) && is_string($filter['type']) && $filter['type'] !== 'all' ) {
                    $rows->where('type', '=', $filter['type']);
                }

                if ( isset($filter['gender']) && is_string($filter['gender']) && in_array($filter['gender'], ['female', 'male', 'genderless', 'ungendered'], true) ) {
                    $filter_gender = $filter['gender'] === 'ungendered' ? '(?)' : $filter['gender'];
                    $rows->where('gender', '=', $filter_gender);
                }

                if ( isset($filter['species']) && is_string($filter['species']) ) {
                    $rows->where('pokedex_id', '=', $filter['species_id']);
                    $rows->where('alt_id', '=', $filter['alt_id']);
                }
            }

            $rows = $rows
                ->orderBy('id', 'ASC')
                ->limit($perPage)
                ->offset($offset)
                ->get();

            return [
                'pokemon' => array_map([self::class, 'mapPokemonRow'], $rows),
                'total' => $total,
            ];
        }

        private static function mapPokemonRow(array $row): array
        {
            $name = (string)($row['name'] ?? 'Unknown');
            $type = (string)($row['type'] ?? 'Normal');
            $forme = array_key_exists('forme', $row) && $row['forme'] !== null
                ? (string)$row['forme']
                : null;

            $images = PokemonData::FetchPokemonImages(
                (int)($row['pokedex_id'] ?? 0),
                (int)($row['alt_id'] ?? 0),
                $type,
                $name,
                $forme
            );

            return [
                'id' => (int)($row['id'] ?? 0),
                'pokedex_id' => (int)($row['pokedex_id'] ?? 0),
                'alt_id' => (int)($row['alt_id'] ?? 0),
                'name' => $name,
                'forme' => $forme,
                'type' => $type,
                'gender' => (string)($row['gender'] ?? ''),
                'level' => PokemonData::CalculateLevel((int)($row['experience'] ?? 0)),
                'experience' => (int)($row['experience'] ?? 0),
                'slot' => isset($row['slot']) ? (int)$row['slot'] : null,
                'location' => strtolower((string)($row['location'] ?? '')),
                'nickname' => (string)($row['nickname'] ?? ''),
                'held_item' => (string)($row['item'] ?? ''),
                'images' => [
                    'icon' => [
                        'path' => $images['Icon']['Sprite_Path'] ?? '',
                        'alt' => $images['Icon']['Alt_Text'] ?? $name,
                    ],
                    'sprite' => [
                        'path' => $images['Sprite']['Sprite_Path'] ?? '',
                        'alt' => $images['Sprite']['Alt_Text'] ?? $name,
                    ],
                ],
            ];
        }

        private static function VerifyOwnership(int $userId, int $pokemonId): bool
        {
            $exists = Database::get()
                ->table('user_pokemon')
                ->where('id', '=', $pokemonId)
                ->first();

            return $exists['owner_current'] === $userId;
        }

        private static function SendPokemonToRoster(int $userId, int $pokemonId, int $slot): void
        {
            Database::get()
                ->table('user_pokemon')
                ->where('owner_current', '=', $userId)
                ->where('id', '=', $pokemonId)
                ->update([
                    'location' => 'roster',
                    'slot' => $slot,
                ]);
        }

        private static function SendPokemonToBox(int $userId, int $pokemonId): void
        {
            Database::get()
                ->table('user_pokemon')
                ->where('owner_current', '=', $userId)
                ->where('id', '=', $pokemonId)
                ->update([
                    'location' => 'box',
                    'slot' => 0,
                ]);
        }

        private static function NormalizeRosterSlots(int $userId): void
        {
            $roster = self::fetchTeam($userId);

            foreach ($roster as $index => $pokemon) {
                $expectedSlot = $index + 1;
                if ($pokemon['slot'] !== $expectedSlot) {
                    Database::get()
                        ->table('user_pokemon')
                        ->where('owner_current', '=', $userId)
                        ->where('id', '=', $pokemon['id'])
                        ->update(['slot' => $expectedSlot]);
                }
            }
        }

        public static function MovePokemon(int $userId, int $pokemonId, string $moveLocation, ?int $slot = null, ?int $swapSlot = null): void
        {
            $moveLocation = strtolower($moveLocation);
            $validLocations = ['roster', 'box'];

            if (!in_array($moveLocation, $validLocations, true)) {
                throw new InvalidArgumentException(
                    'Invalid move location specified. Valid locations are: ' . implode(', ', $validLocations)
                );
            }

            Database::get()->transaction(function () use ($userId, $pokemonId, $moveLocation, $slot, $swapSlot): void {
                $sourcePokemon = Database::get()
                    ->table('user_pokemon')
                    ->where('owner_current', '=', $userId)
                    ->where('id', '=', $pokemonId)
                    ->first();

                if (!$sourcePokemon) {
                    throw new RuntimeException('You do not own this Pokemon.');
                }

                if ($moveLocation === 'box') {
                    self::SendPokemonToBox($userId, $pokemonId);
                    return;
                }

                if ($swapSlot !== null) {
                    $swapPokemonId = $swapSlot;

                    if ($swapPokemonId === $pokemonId) {
                        return;
                    }

                    $targetPokemon = Database::get()
                        ->table('user_pokemon')
                        ->where('owner_current', '=', $userId)
                        ->where('id', '=', $swapPokemonId)
                        ->first();

                    if (!$targetPokemon) {
                        throw new RuntimeException('Swap target is invalid or not owned by this user.');
                    }

                    $sourceLocation = strtolower((string)($sourcePokemon['location'] ?? ''));
                    $targetLocation = strtolower((string)($targetPokemon['location'] ?? ''));

                    if ($sourceLocation !== 'roster' || $targetLocation !== 'roster') {
                        throw new InvalidArgumentException('Both Pokemon must be in roster to swap.');
                    }

                    $sourceSlot = (int)($sourcePokemon['slot'] ?? 0);
                    $targetSlot = (int)($targetPokemon['slot'] ?? 0);

                    if (
                        $sourceSlot < 1 || $sourceSlot > 6 ||
                        $targetSlot < 1 || $targetSlot > 6
                    ) {
                        throw new InvalidArgumentException('Invalid roster slot state.');
                    }

                    if ($sourceSlot === $targetSlot) {
                        return;
                    }

                    $tempSlot = 7;

                    self::SendPokemonToRoster($userId, (int)$sourcePokemon['id'], $tempSlot);
                    self::SendPokemonToRoster($userId, (int)$targetPokemon['id'], $sourceSlot);
                    self::SendPokemonToRoster($userId, (int)$sourcePokemon['id'], $targetSlot);

                    return;
                }

                if ($slot === null || $slot < 1 || $slot > 6) {
                    throw new InvalidArgumentException('Invalid slot number for roster.');
                }

                $occupied = Database::get()
                    ->table('user_pokemon')
                    ->where('owner_current', '=', $userId)
                    ->whereRaw('LOWER(location) = :location', ['location' => 'roster'])
                    ->where('slot', '=', $slot)
                    ->first();

                if ($occupied && (int)$occupied['id'] !== $pokemonId) {
                    self::SendPokemonToBox($userId, (int)$occupied['id']);
                }

                self::SendPokemonToRoster($userId, $pokemonId, $slot);
            });

            self::NormalizeRosterSlots($userId);
            UserData::UpdateRosterHash($userId);
        }
    }
