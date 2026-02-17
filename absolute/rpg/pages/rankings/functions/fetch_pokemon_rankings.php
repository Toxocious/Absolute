<?php
    require_once $_SERVER['DOCUMENT_ROOT'] . '/core/required/ajax_header.php';

    function FetchPokemonSpeciesList()
    {
        global $PDO;

        try {
            $Fetch_Pokemon_Species = $PDO->prepare("
                SELECT `ID`, `Pokedex_ID`, `Alt_ID`, `Pokemon`, `Forme`
                FROM `pokedex`
                ORDER BY `Pokedex_ID` ASC, `Alt_ID` ASC
            ");
            $Fetch_Pokemon_Species->execute();
            $Fetch_Pokemon_Species->setFetchMode(PDO::FETCH_ASSOC);

            return $Fetch_Pokemon_Species->fetchAll();
        } catch ( PDOException $e ) {
            HandleError($e);
        }

        return [];
    }

    function FetchTopPokemon()
    {
        global $PDO;

        try
        {
            $Fetch_Top_Pokemon = $PDO->prepare("
                SELECT
                    `ID`, `Pokedex_ID`, `Alt_ID`, `Name`, `Forme`, `Type`, `Experience`, `Nickname`, `Owner_Current`
                FROM
                    `pokemon`
                ORDER BY
                    `Experience` DESC,
                    `ID` ASC,
                    `Pokedex_ID` ASC,
                    `Alt_ID` ASC
                LIMIT 1
            ");
            $Fetch_Top_Pokemon->execute();
            $Fetch_Top_Pokemon->setFetchMode(PDO::FETCH_ASSOC);
            $Top_Pokemon = $Fetch_Top_Pokemon->fetch();
        }
        catch ( PDOException $e )
        {
            HandleError($e);
        }

        $Top_Pokemon['Level'] = FetchLevel($Top_Pokemon['Experience'], 'Pokemon');

        $Pokedex_Info = GetPokedexData($Top_Pokemon['Pokedex_ID'], $Top_Pokemon['Alt_ID'], $Top_Pokemon['Type']);
        $Top_Pokemon['Display_Name'] = $Pokedex_Info['Display_Name'];

        return $Top_Pokemon;
    }

    function FetchPokemonRankings
    (
        $Page = 1,
        $Results_Per_Page = 30,
        $Species = null,
        $Type = null
    )
    {
        $Page = (int) Purify($Page);

        if ($Species !== null && $Species !== '')
        {
            $Species = (int) Purify($Species);
            if ($Species <= 0) { $Species = null; }
        }

        if ($Type !== null && $Type !== '')
        {
            $Type = strtolower(Purify($Type));
            if ( !in_array($Type, [ 'normal','shiny','all' ]) )
            {
                $Type = 'all';
            }
        }
        else
        {
            $Type = 'all';
        }

        $Where = [];
        $Params = [];

        if ($Species !== null)
        {
            $Where[] = "`Pokedex_ID` = :species";
            $Params[':species'] = $Species;
        }

        if ($Type !== 'all')
        {
            $Where[] = "`Type` = :type";
            $Params[':type'] = ($Type === 'shiny' ? 'Shiny' : 'Normal');
        }

        $Where_SQL = count($Where) ? ('WHERE ' . implode(' AND ', $Where)) : '';

        $Select_SQL = "
            SELECT
                `ID`, `Pokedex_ID`, `Alt_ID`, `Name`, `Forme`, `Type`,
                `Experience`, `Nickname`, `Owner_Current`
            FROM `pokemon`
            $Where_SQL
            ORDER BY
                `Experience` DESC,
                `ID` ASC,
                `Pokedex_ID` ASC,
                `Alt_ID` ASC
        ";

        $Count_SQL = "
            SELECT COUNT(*) FROM `pokemon`
            $Where_SQL
        ";

        $Pagination = Pagination(
            $Select_SQL,
            $Count_SQL,
            $Params,
            $Page,
            $Results_Per_Page,
            3,
            "UpdatePokemonRankings([PAGE]); return false;"
        );

        if (empty($Pagination) || !is_array($Pagination)) {
            $Pagination = [
                'Data' => [],
                'Total_Results' => 0,
                'Total_Pages' => 1,
                'Pagination' => '',
                'PageStart' => 1,
            ];
        }

        foreach ( $Pagination['Data'] as $_Index => $Pokemon )
        {
            $Pagination['Data'][$_Index]['Level'] = FetchLevel($Pokemon['Experience'], 'Pokemon');

            $Pokedex_Info = GetPokedexData($Pokemon['Pokedex_ID'], $Pokemon['Alt_ID'], $Pokemon['Type']);
            $Pagination['Data'][$_Index]['Display_Name'] = $Pokedex_Info['Display_Name'];
        }

        return [
            'Pagination' => $Pagination,
            'Page' => $Page,
            'Filters' => [
                'Species' => $Species,
                'Type' => $Type
            ]
        ];
    }
