<?php
    require_once $_SERVER['DOCUMENT_ROOT'] . '/core/required/ajax_header.php';

    /**
     * Return the user's boxed Pokemon.
     *
     * @param $Pokemon_Type
     * @param $Page
     *
     * @return array
     */
    function GetBoxedPokemon
    (
        $Pokemon_Type,
        $Page
    )
    {
        global $User_Data;

        $Page = (int) Purify($Page);

        $Pagination = Pagination(
            'SELECT p.`ID`, p.`Pokedex_ID`, p.`Forme`, p.`Type`, p.`Trade_Interest`, CONCAT(pd.`Pokemon`, CASE WHEN pd.`Forme` IS NOT NULL THEN CONCAT(" ", pd.`Forme`) ELSE "" END) as `Display_Name` FROM `pokemon` p JOIN `pokedex` pd ON p.`Pokedex_ID` = pd.`Pokedex_ID` AND p.`Alt_ID` = pd.`Alt_ID` WHERE p.`Owner_Current` = ? AND p.`Location` = "Box" AND p.`Type` = ? ORDER BY p.`ID` ASC, p.`Pokedex_ID` ASC, p.`Alt_ID` ASC',
            'SELECT COUNT(*) FROM `pokemon` WHERE `Owner_Current` = ? AND `Location` = "Box" AND `Type` = ?',
            [ $User_Data['ID'], $Pokemon_Type ],
            $Page,
            30,
            3,
            "GetBoxedPokemon('{$Pokemon_Type}', [PAGE]); return false;"
        );

        return [
            'Pagination' => $Pagination,
        ];
    }

    /**
     * Update the trade interest for a Pokémon.
     *
     * @param $Pokemon_ID
     * @param $Trade_Interest
     *
     * @return bool
     */
    function UpdatePokemonInterest
    (
        $Pokemon_ID,
        $Trade_Interest
    )
    {
        global $PDO, $User_Data;

        $Pokemon_ID = (int) Purify($Pokemon_ID);
        $Trade_Interest = Purify($Trade_Interest);

        try {
            $Update_Pokemon_Trade_Interest = $PDO->prepare('UPDATE `pokemon` SET `Trade_Interest` = ? WHERE `ID` = ? AND `Owner_Current` = ? LIMIT 1');
            $Update_Pokemon_Trade_Interest->execute([ $Trade_Interest, $Pokemon_ID, $User_Data['ID'] ]);

            return true;
        } catch (PDOException $e) {
            HandleError($e);

            return false;
        }
    }
