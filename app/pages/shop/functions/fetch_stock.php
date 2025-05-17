<?php
    /**
     * Fetch specific shop data.
     * @param int|string $Shop_ID
     */
    function FetchShopData
    (
        $Shop_ID
    )
    {
        global $PDO;

        if ( !$Shop_ID )
        {
            return false;
        }

        try
        {
            $Fetch_Shop = $PDO->prepare("SELECT * FROM `shops` WHERE `ID` = ? OR `obtained_place` = ? LIMIT 1");
            $Fetch_Shop->execute([ $Shop_ID, $Shop_ID ]);
            $Fetch_Shop->setFetchMode(PDO::FETCH_ASSOC);
            $Shop = $Fetch_Shop->fetch();
        }
        catch ( PDOException $e )
        {
            HandleError($e);
        }

        if ( !$Shop )
        {
            return false;
        }

        return [
            'ID' => $Shop['ID'],
            'Name' => $Shop['Name'],
            'Description' => $Shop['Description'],
            'Obtained_Place' => $Shop['Obtained_Place'],
            'Shiny_Odds' => $Shop['Shiny_Odds'],
            'Ungendered_Odds' => $Shop['Ungendered_Odds'],
        ];
    }

    /**
     * Fetch all Pokemon that are being sold by a given shop.
     * @param int|string $Shop_ID
     */
    function FetchShopPokemon
    (
        $Shop_ID
    )
    {
        global $PDO;

        if ( !$Shop_ID )
        {
            return false;
        }

        $Shop_Data = FetchShopData($Shop_ID);
        if ( !$Shop_Data )
        {
            return false;
        }

        try
        {
            $Fetch_Shop_Objects = $PDO->prepare("SELECT * FROM `shop_pokemon` WHERE `obtained_place` = ? AND `Active` = 1");
            $Fetch_Shop_Objects->execute([ $Shop_Data['Obtained_Place'] ]);
            $Fetch_Shop_Objects->setFetchMode(PDO::FETCH_ASSOC);
            $Shop_Objects = $Fetch_Shop_Objects->fetchAll();
        }
        catch ( PDOException $e )
        {
            HandleError($e);
        }

        if ( !$Shop_Objects )
        {
            return false;
        }

        return $Shop_Objects;
    }

    /**
     * Fetch all items that are being sold by a given shop.
     * @param int|string $Shop_ID
     */
    function FetchShopItems
    (
        $Shop_ID
    )
    {
    global $PDO;

        if ( !$Shop_ID )
        {
            return false;
        }

        $Shop_Data = FetchShopData($Shop_ID);
        if ( !$Shop_Data )
        {
            return false;
        }

        try
        {
            $Fetch_Shop_Objects = $PDO->prepare("SELECT * FROM `shop_items` WHERE `obtained_place` = ? AND `Active` = 1");
            $Fetch_Shop_Objects->execute([ $Shop_Data['Obtained_Place'] ]);
            $Fetch_Shop_Objects->setFetchMode(PDO::FETCH_ASSOC);
            $Shop_Objects = $Fetch_Shop_Objects->fetchAll();
        }
        catch ( PDOException $e )
        {
            HandleError($e);
        }

        if ( !isset($Shop_Objects) || count($Shop_Objects) === 0 )
        {
            return false;
        }

        return $Shop_Objects;
    }

    /**
     * Fetch the specific data of a given object.
     * @param int|string $Object_ID
     * @param string $Object_Type
     */
    function FetchObjectData
    (
        $Object_ID,
        $Object_Type
    )
    {
        global $PDO;

        if ( !$Object_ID || !$Object_Type )
        {
            return false;
        }

        if ( !in_array($Object_Type, ['Items', 'Pokemon']) )
        {
            return false;
        }

        try
        {
            if ( $Object_Type == 'Items' )
            {
                $Fetch_Object_Data = $PDO->prepare("SELECT * FROM `shop_items` WHERE `ID` = ? LIMIT 1");
            }
            else
            {
                $Fetch_Object_Data = $PDO->prepare("SELECT * FROM `shop_pokemon` WHERE `ID` = ? LIMIT 1");
            }

            $Fetch_Object_Data->execute([ $Object_ID ]);
            $Fetch_Object_Data->setFetchMode(PDO::FETCH_ASSOC);
            $Object_Data = $Fetch_Object_Data->fetchAll();
        }
        catch ( PDOException $e )
        {
            HandleError($e);
        }

        if ( !$Object_Data )
        {
            return false;
        }

        return $Object_Data;
    }

    /**
     * Parse a given string of prices.
     * @param string $Prices
     */
    function FetchPriceList
    (
        $Prices
    )
    {
        $Price_List = json_decode($Prices, true);

        return $Price_List;
    }
