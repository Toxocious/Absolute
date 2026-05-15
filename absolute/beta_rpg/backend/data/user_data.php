<?php
    declare(strict_types=1);

    require_once $_SERVER['DOCUMENT_ROOT'] . '/backend/session/database.php';

    final class UserData
    {
        public static function Fetch(int $userId): ?array
        {
            $Fetched_User = Database::get()
                ->table('users')
                ->where('id', '=', $userId)
                ->first();

            if ( !$Fetched_User )
            {
                return null;
            }

            $User_Rpg_State = Database::get()
                ->table('user_rpg_state')
                ->where('user_id', '=', $Fetched_User['id'])
                ->first();

            if ( !$User_Rpg_State )
            {
                $User_Rpg_State = [];
            }
            else
            {
                unset($User_Rpg_State['user_id']);
            }

            return array_merge($Fetched_User, self::FetchRpgState($Fetched_User['id']));
        }

        public static function FetchRoster(int $userId): array
        {
            return Database::get()
                ->table('user_pokemon')
                ->where('owner_current', '=', $userId)
                ->whereRaw('LOWER(location) = :location', ['location' => 'roster'])
                ->orderBy('slot', 'ASC')
                ->orderBy('id', 'ASC')
                ->get();
        }

        public static function FetchRpgState(int $userId): ?array
        {
            $User_Rpg_State = Database::get()
                ->table('user_rpg_state')
                ->where('user_id', '=', $userId)
                ->first();

            if ( !$User_Rpg_State )
            {
                return null;
            }

            unset($User_Rpg_State['user_id']);

            return $User_Rpg_State;
        }

        public static function HashRoster(int $userId): string
        {
            $Hash = 0;

            $roster = self::FetchRoster($userId);

            foreach ( $roster as $Pokemon )
            {
                $Hash .= $Pokemon['id'] . '-' . $userId;
            }

            return md5($Hash);
        }

        public static function UpdateRosterHash(int $userId): void
        {
            $New_Hash = self::HashRoster($userId);

            Database::get()
                ->table('users')
                ->where('id', '=', $userId)
                ->update([
                    'roster_hash' => $New_Hash,
                ]);
        }
    }
