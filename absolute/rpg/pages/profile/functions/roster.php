<?php
    require_once $_SERVER['DOCUMENT_ROOT'] . '/core/required/ajax_header.php';

    /**
     * Return the user's roster as an HTML blob.
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

        $Roster_HTML = '';

        foreach ( $Roster_Pokemon as $Slot => $Pokemon )
        {
            $Pokemon_Info = GetPokemonData($Pokemon['ID']);

            $Roster_Pokemon[$Slot]['Level'] = $Pokemon_Info['Level'];
            $Roster_Pokemon[$Slot]['Display_Name'] = $Pokemon_Info['Display_Name'];

            $Gender_Icon = '';
            if ( in_array($Pokemon_Info['Gender'], ['Male', 'Female']) ) {
                $Gender_Icon = "<img src='{$Pokemon_Info['Gender_Icon']}' class='gender-icon' alt='{$Pokemon_Info['Gender']}' />";
            }

            $Roster_HTML .= "
                <div class='pokemon-card' onclick='PokemonViewer.open(\"{$Roster_Pokemon[$Slot]['ID']}\")'>
                    <img src='{$Pokemon_Info['Sprite']}' class='pokemon-sprite' alt='{$Roster_Pokemon[$Slot]['Display_Name']}' />
                    <div class='pokemon-info'>
                        <h3 class='pokemon-name'>{$Roster_Pokemon[$Slot]['Display_Name']}</h3>
                        <div class='pokemon-details'>
                            {$Gender_Icon}
                            <span class='pokemon-level'>Lv. {$Roster_Pokemon[$Slot]['Level']}</span>
                        </div>
                    </div>
                </div>
            ";
        }

        return $Roster_HTML;
    }
