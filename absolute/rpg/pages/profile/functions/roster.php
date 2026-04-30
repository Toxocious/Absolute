<?php
    require_once $_SERVER['DOCUMENT_ROOT'] . '/core/required/ajax_header.php';

    /**
     * Return the user's roster as a data array.
     */
    function GetRoster($User_ID)
    {
        global $PDO;

        try
        {
            $Get_Roster_Pokemon = $PDO->prepare("
                SELECT `ID`, `Pokedex_ID`, `Alt_ID`, `Name`, `Forme`, `Type`, `Experience`, `Slot`, `Nickname`, `Gender`
                FROM `pokemon`
                WHERE `Owner_Current` = ? AND `Location` = 'Roster'
                ORDER BY `Slot` ASC
                LIMIT 6
            ");
            $Get_Roster_Pokemon->execute([
                $User_ID
            ]);
            $Get_Roster_Pokemon->setFetchMode(PDO::FETCH_ASSOC);
            $Roster_Pokemon = $Get_Roster_Pokemon->fetchAll();
        }
        catch ( PDOException $e )
        {
            HandleError($e);
        }

        foreach ( $Roster_Pokemon as $Slot => $Pokemon )
        {
            $Pokemon_Info = GetPokemonData($Pokemon['ID']);

            $Roster_Pokemon[$Slot]['Level'] = $Pokemon_Info['Level'];
            $Roster_Pokemon[$Slot]['Display_Name'] = $Pokemon_Info['Display_Name'];
            $Roster_Pokemon[$Slot]['Sprite'] = $Pokemon_Info['Sprite'];
            $Roster_Pokemon[$Slot]['Gender'] = $Pokemon_Info['Gender'];
            $Roster_Pokemon[$Slot]['Gender_Icon'] = $Pokemon_Info['Gender_Icon'];
        }

        return $Roster_Pokemon;
    }
