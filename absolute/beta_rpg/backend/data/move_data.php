<?php
    declare(strict_types=1);

    require_once $_SERVER['DOCUMENT_ROOT'] . '/backend/session/database.php';

    final class MoveData
    {
        /**
         * Fetches a single move entry by its unique database ID.
         *
         * @param int $id The unique database ID of the move entry.
         *
         * @return array|null An associative array of the move entry data, or null if not found.
         */
        public static function fetchById(int $id): ?array
        {
            return Database::get()->selectOne(
                'SELECT *
                FROM api_moves
                WHERE id = :id
                LIMIT 1',
                ['id' => $id]
            );
         }

         public static function fetchMoveNameById(string $id): ?string
         {
             $move = Database::get()->selectOne(
                'SELECT `name`
                FROM `api_moves`
                WHERE `id` = :id
                LIMIT 1',
                ['id' => $id]
             );

             return $move ? $move['name'] : null;
         }

        /**
         * Fetches a list of moves for populating a dropdown, specifically their ID and name.
         *
         * @return array An array of associative arrays, each representing a move for the dropdown.
         */
        public static function GetMoveDropdownList(): array
        {
            return Database::get()->select(
                'SELECT id, name
                FROM api_moves
                ORDER BY name ASC'
            );
        }
    }

