<?php
    declare(strict_types=1);

    require_once $_SERVER['DOCUMENT_ROOT'] . '/backend/session/database.php';
    require_once $_SERVER['DOCUMENT_ROOT'] . '/backend/utility/weighter.php';

    require_once $_SERVER['DOCUMENT_ROOT'] . '/backend/data/pokedex_data.php';

    /**
     * Items have one primary category and various sub-categories.
     */
    $Item_Categories = [
        'battle' => 'Battle Items',
        'medicine' => 'Medicine',
        'berries' => 'Berries',
        // 'machines' => 'Machines',
        // 'mail' => 'Mail',
        'pokeballs' => 'Pokeballs',
        'key' => 'Key Items',
        'misc' => 'Misc.',
    ];

    final class ItemData
    {
        /**
         * Fetch the full data of an item from the database provided an identifier.
         * Throws an exception if the provided identifier does not correlate to an existing item.
         *
         * @param string $identifier - The item's identifier.
         *
         * @return array - The data of the returned item.
         */
        public static function FetchByIdentifier(string $identifier): ?array {
            $Item_Data = Database::get()->selectOne(
                'SELECT *
                 FROM `api_items`
                 WHERE `identifier` = :identifier
                 LIMIT 1',
                [ 'identifier' => $identifier]
            );

            if ( !$Item_Data ) {
                throw new RuntimeException("Failed to fetch item data for '{$identifier}'.");
            }

            $Item_Data['sprite'] = self::GetItemSprite($identifier);

            return $Item_Data;
        }

        /**
         * Fetch the data of an owned item provided its identifier and the user's id.
         *
         * @param string $identifier - The item's identifier.
         * @param int $user_id - The user's id.
         *
         * @return array - The data of the returned item or an empty array if the user doesn't own the item.
         */
        public static function FetchOwnedItem(string $identifier, int $user_id): ?array {
            $Owned_Item_Data = Database::get()->selectOne(
                'SELECT *
                FROM `user_items`
                WHERE `item_identifier` = :identifier
                    AND `owner_id` = :user_id
                LIMIT 1',
                [
                    'item_identifier' => $identifier,
                    'user_id' => $user_id
                ]
            );

            if ( !$Owned_Item_Data ) {
                return [];
            }

            return $Owned_Item_Data;
        }

        /**
         * Updates the quantity of an owned item provided an identifier and user id.
         *
         * @param string $identifier - The item's identifier.
         * @param int $user_id - The user's id.
         * @param int $quantity - The amount to decrease or increase the user's item quantity by.
         */
        public static function UpdateItemQuantity(string $identifier, int $user_id, int $quantity): void {
            $Check_If_Owned = self::FetchOwnedItem($identifier, $user_id);

            if ( count($Check_If_Owned) > 0 ) {
                Database::get()
                    ->table('user_items')
                    ->where('item_identifier', '=', $identifier)
                    ->where('owner_id', '=', $user_id)
                    ->where('quantity', '>', 0)
                    ->update([
                        'quantity' => $Check_If_Owned['quantity'] + $quantity
                    ]);
            }
            else
            {
                Database::get()
                    ->table('user_items')
                    ->insert([
                        'item_identifier' => $identifier,
                        'owner_id' => $user_id,
                        'quantity' => $quantity
                    ]);
            }
        }

        /**
         * Gets the filename of an item provided the item's identifier.
         *
         * @param string $identifier - The item's identifier.
         *
         * @return string - The path to the item's sprite.
         */
        private static function GetItemSprite(string $identifier): string {
            $Item_Name = str_replace('-', ' ', $identifier);
            return "/assets/images/Items/{$Item_Name}.png";
        }
    }
