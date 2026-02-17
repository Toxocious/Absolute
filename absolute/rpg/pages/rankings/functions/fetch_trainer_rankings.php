<?php
    require_once $_SERVER['DOCUMENT_ROOT'] . '/core/required/ajax_header.php';

    function FetchTrainerRankings
    (
        $Page = 1,
        $Results_Per_Page = 30
    )
    {
        $Page = (int) Purify($Page);

        $Limit_Start = ($Page - 1) * $Results_Per_Page;
        if ( $Limit_Start < 0 )
        {
            $Limit_Start = 0;
        }

        // $Pagination = Pagination(
        //     'SELECT `ID`, `Pokedex_ID`, `Alt_ID`, `Name`, `Forme`, `Type`, `Experience` FROM `pokemon` ORDER BY `Experience` DESC, `ID` ASC, `Pokedex_ID` ASC, `Alt_ID` ASC',
        //     'SELECT COUNT(*) FROM `pokemon`',
        //     [ ],
        //     $Page,
        //     $Results_Per_Page,
        //     3,
        //     "Update(); return false;"
        // );

        // foreach ( $Pagination['Data'] as $_Index => $Pokemon )
        // {
        //     // $Pokemon_Info = GetPokemonData($Pokemon['ID']);

        //     $Pagination['Data'][$_Index]['Level'] = FetchLevel($Pokemon['Experience'], 'Pokemon');

        //     $Pokedex_Info = GetPokedexData($Pokemon['Pokedex_ID'], $Pokemon['Alt_ID'], $Pokemon['Type']);
        //     $Pagination['Data'][$_Index]['Display_Name'] = $Pokedex_Info['Display_Name'];
        // }

        return [
            'Pagination' => [],
            'Page' => $Page
        ];
    }
