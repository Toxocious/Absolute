<?php
    require_once $_SERVER['DOCUMENT_ROOT'] . '/core/required/ajax_header.php';

    require_once $_SERVER['DOCUMENT_ROOT'] . '/core/functions/pokemon.php';

    require_once $_SERVER['DOCUMENT_ROOT'] . '/pages/shop/functions/fetch_stock.php';
    require_once $_SERVER['DOCUMENT_ROOT'] . '/pages/shop/functions/rng_checks.php';

    /**
     * Purchase a given object from a shop, given it's object ID and type.
     * @param string $Shop_ID
     * @param string $Object_ID
     * @param string $Object_Type
     */
    function PurchaseObject
    (
        $Shop_ID,
        $Object_ID,
        $Object_Type
    )
    {
        global $PDO, $User_Class, $User_Data, $Item_Class;

        if ( !$Object_ID || !$Object_Type )
        {
            return false;
        }

        $Object_Data = FetchObjectData($Object_ID, $Object_Type);
        if ( !$Object_Data )
        {
            return false;
        }

        try
        {
            if ( $Object_Type == 'Items' )
            {
                $Fetch_Object = $PDO->prepare("SELECT * FROM `shop_items` WHERE `ID` = ? LIMIT 1");
            }
            else
            {
                $Fetch_Object = $PDO->prepare("SELECT * FROM `shop_pokemon` WHERE `ID` = ? LIMIT 1");
            }

            $Fetch_Object->execute([ $Object_ID ]);
            $Fetch_Object->setFetchMode(PDO::FETCH_ASSOC);
            $Object = $Fetch_Object->fetch();
        }
        catch ( PDOException $e )
        {
            HandleError($e);
        }

        $Shop_Data = FetchShopData($Object['Obtained_Place']);
        if ( !$Shop_Data )
        {
            return false;
        }

        if
        (
            !$Object ||
            !$Object['Prices'] ||
            !$Object['Active']
        )
        {
            return false;
        }

        $Can_Afford = true;
        $Price_Array = FetchPriceList($Object['Prices']);
        foreach ( $Price_Array[0] as $Currency => $Amount )
        {
            if ( $User_Data[$Currency] < $Amount )
            {
                $Can_Afford = false;
                break;
            }
            else
            {
                $Can_Afford = true;
            }
        }

        if ( !$Can_Afford )
        {
            return false;
        }

        if ( $Object_Type == 'Pokemon' )
        {
            $Shiny_Alert = false;
            $Ungendered_Alert = false;

            if ( $Object['Type'] == 'Normal' )
            {
                $Shiny_Check = ShinyCheck($Object['ID'], $Shop_Data['Shiny_Odds']);
                if ( $Shiny_Check )
                {
                    $Shiny_Alert = true;
                    $Object['Type'] = 'Shiny';
                }
                else
                {
                    $Object['Type'] = 'Normal';
                }
            }

            $Ungendered_Check = UngenderedCheck($Object['ID'], $Shop_Data['Ungendered_Odds']);
            if ( $Ungendered_Check )
            {
                $Ungendered_Alert = true;
                $Object['Gender'] = '(?)';
            }
            else
            {
                $Object['Gender'] = GenerateGender($Object['Pokedex_ID'], $Object['Alt_ID']);
            }

            $Spawn_Pokemon = CreatePokemon(
                $User_Data['ID'],
                $Object['Pokedex_ID'],
                $Object['Alt_ID'],
                5,
                $Object['Type'],
                $Object['Gender'],
                $Shop_Data['Name'],
            );

            if ( !$Spawn_Pokemon )
            {
                return false;
            }

            foreach ( $Price_Array[0] as $Currency => $Amount )
            {
                $User_Class->RemoveCurrency($User_Data['ID'], $Currency, $Amount);
            }

            InsertLog(
                $Shop_Data['Name'],
                null,
                $Spawn_Pokemon['ID'],
                $Object['Pokedex_ID'],
                $Object['Alt_ID'],
                $Object['Type'],
                $Object['Gender'],
                $Object['Prices'],
                $User_Data['ID'],
                time()
            );

            return [
                'Display_Name' => $Spawn_Pokemon['Display_Name'],
                'Stats' => $Spawn_Pokemon['Stats'],
                'IVs' => $Spawn_Pokemon['IVs'],
                'EVs' => $Spawn_Pokemon['EVs'],
                'Ability' => $Spawn_Pokemon['Ability'],
                'Nature' => $Spawn_Pokemon['Nature'],
                'Gender' => $Spawn_Pokemon['Gender'],
                'Sprite' => $Spawn_Pokemon['Sprite'],
                'Icon' => $Spawn_Pokemon['Icon'],
                'Shiny_Alert' => $Shiny_Alert,
                'Ungendered_Alert' => $Ungendered_Alert,
            ];
        }
        else
        {
            $Spawn_Item = $Item_Class->SpawnItem($User_Data['ID'], $Object['Item_ID'], 1);
            if ( !$Spawn_Item )
            {
                return false;
            }

            foreach ( $Price_Array[0] as $Currency => $Amount )
            {
                $User_Class->RemoveCurrency($User_Data['ID'], $Currency, $Amount);
            }

            InsertLog(
                $Shop_Data['Name'],
                $Object['ID'],
                null, null, null, null, null,
                $Object['Prices'],
                $User_Data['ID'],
                time()
            );

            $Item_Data = $Item_Class->FetchItemData($Object['Item_ID']);

            return [
                'Display_Name' => $Item_Data['Name'],
                'Description' => $Item_Data['Description'] ?? 'No description is set for this item.',
                'Category' => $Item_Data['Category'] ?? 'Unknown',
                'Icon' => $Item_Data['Icon'],
            ];
        }
    }

    /**
     * Insert a new purchase log into the `shop_logs` database table.
     *
     * @param {string} $Shop_Name
     * @param {int} $Item_ID
     * @param {int} $Pokemon_ID
     * @param {int} $Pokemon_Pokedex_ID
     * @param {int} $Pokemon_Alt_ID
     * @param {int} $Pokemon_Type
     * @param {int} $Pokemon_Gender
     * @param {string} $Bought_With
     * @param {int} $Bought_By
     * @param {int} $Timestamp
     */
    function InsertLog
    (
        $Shop_Name,
        $Item_ID,
        $Pokemon_ID,
        $Pokemon_Pokedex_ID,
        $Pokemon_Alt_ID,
        $Pokemon_Type,
        $Pokemon_Gender,
        $Bought_With,
        $Bought_By,
        $Timestamp
    )
    {
        global $PDO;

        try
        {
            $PDO->beginTransaction();

            $Insert_Shop_Log = $PDO->prepare("
                INSERT INTO `shop_logs` (
                    `Shop_Name`,
                    `Item_ID`,
                    `Pokemon_ID`,
                    `Pokemon_Pokedex_ID`,
                    `Pokemon_Alt_ID`,
                    `Pokemon_Type`,
                    `Pokemon_Gender`,
                    `Bought_With`,
                    `Bought_By`,
                    `Timestamp`
                ) VALUES ( ?, ?, ?, ?, ?, ?, ?, ?, ?, ? )
            ");
            $Insert_Shop_Log->execute(func_get_args());

            $PDO->commit();
        }
        catch ( \PDOException $e )
        {
            $PDO->rollBack();

            HandleError($e);
        }
    }
