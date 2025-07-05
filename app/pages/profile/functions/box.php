<?php
    /**
     * Return the user's boxed Pokemon as a string.
     *
     * @param $Profile_ID
     * @param $Page
     */
    function GetBoxedPokemon
    (
        $Profile_ID,
        $Page
    )
    {
        $Page = (int) Purify($Page);

        $Limit_Start = ($Page - 1) * 48;
        if ( $Limit_Start < 0 )
        {
            $Limit_Start = 0;
        }

        $Pagination = Pagination(
            'SELECT `ID`, `Pokedex_ID`, `Alt_ID`, `Name`, `Forme`, `Type`, `Experience` FROM `pokemon` WHERE `Owner_Current` = ? AND `Location` = "Box" ORDER BY `ID` ASC, `Pokedex_ID` ASC, `Alt_ID` ASC',
            'SELECT COUNT(*) FROM `pokemon` WHERE `Owner_Current` = ? AND `Location` = "Box"',
            [ $Profile_ID ],
            $Page,
            22,
            3,
            "GetBox({$Profile_ID}, [PAGE]); return false;"
        );

        foreach ( $Pagination['Data'] as $_Index => $Pokemon )
        {
            // $Pokemon_Info = GetPokemonData($Pokemon['ID']);

            $Pagination['Data'][$_Index]['Level'] = FetchLevel($Pokemon['Experience'], 'Pokemon');

            $Pokedex_Info = GetPokedexData($Pokemon['Pokedex_ID'], $Pokemon['Alt_ID'], $Pokemon['Type']);
            $Pagination['Data'][$_Index]['Display_Name'] = $Pokedex_Info['Display_Name'];
        }

        return [
            'Pagination' => $Pagination,
            'Page' => $Page
        ];
    }
