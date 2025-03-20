<?php
    require_once $_SERVER['DOCUMENT_ROOT'] . '/core/required/session.php';
    require_once $_SERVER['DOCUMENT_ROOT'] . '/core/required/user_session.php';

    /**
     * Handle evolvution of a Pokemon.
     */
    function HandleEvolution($Pokemon_ID, $Evolution_ID, $Evolution_Alt_ID)
    {
        global $PDO, $User_Data;

        if ( !isset($Pokemon_ID) ) {
            return "
                <thead>
                    <tr>
                        <th colspan='7'>
                            Selected Pok&eacute;mon
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan='7' style='padding: 5px;'>
                            The Pok&eacute;mon that you selected does not exist.
                        </td>
                    </tr>
                </tbody>
            ";
        }

        $Pokemon = GetPokemonData($Pokemon_ID);

        if ( !isset($Pokemon) || !isset($Evolution_ID) || !isset($Evolution_Alt_ID) )
        {
            return "
                <thead>
                    <tr>
                        <th colspan='7'>
                            Selected Pok&eacute;mon
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan='7' style='padding: 5px;'>
                            The Pok&eacute;mon that you selected does not exist.
                        </td>
                    </tr>
                </tbody>
            ";
        }

        try
        {
            $Evolution_Data = $PDO->prepare("SELECT * FROM `evolution_data` WHERE `poke_id` = ?");
            $Evolution_Data->execute([ $Pokemon['Pokedex_ID'] ]);
            $Evolution_Data->setFetchMode(PDO::FETCH_ASSOC);
            $Evolution = $Evolution_Data->fetch();
        }
        catch ( PDOException $e )
        {
            HandleError($e);
        }

        $Evolution_Data = GetPokedexData($Evolution_ID, $Evolution_Alt_ID, $Pokemon['Type']);

        $Time = (date('G') > 7 && date('G') < 19) ? 'day' : 'night';

        /**
         * Double check to ensure that the Pokemon is able to evolve.
         */
        $Error = false;
        if ( $Evolution['level'] != null && $Evolution['level'] != 0 && ( $Pokemon['Level'] < $Evolution['level']) )
        {
            $Error = true;
        }

        if ( $Evolution['min_happy'] != null && $Evolution['min_happy'] > $Pokemon['Happiness'] )
        {
            $Error = true;
        }

        if ( $Evolution['item'] != null && $Evolution['item'] != $Pokemon['Item'] )
        {
            $Error = true;
        }

        if ( $Evolution['held_item'] != null && $Evolution['held_item'] != $Pokemon['Item'] )
        {
            $Error = true;
        }

        if ( $Evolution['gender'] != null && ucfirst($Evolution['gender']) != $Pokemon['Gender'] )
        {
            $Error = true;
        }

        if ( $Evolution['time'] != null )
        {
            if ( $Evolution['time'] != $Time )
            {
                $Error = true;
            }
        }

        /**
         * The Pokemon is truly able to evolve.
        * Process the evolution here.
        */
        if ( $Error )
        {
            $Evolution_Text = "
                <tr>
                    <td colspan='7'>
                        <div class='error' style='margin: 0 auto;'>
                            <b>This Pok&eacute;mon does not meet the requirements necessary to evolve.</b>
                        </div>
                    </td>
                </tr>
            ";
        }
        else
        {
            $Evolution_Text = "
                <tr>
                    <td colspan='7'>
                        <div class='success' style='margin: 0 auto;'>
                            <img src='{$Evolution_Data['Sprite']}' />
                            <br />
                            <b>You have successfully evolved your {$Pokemon['Display_Name']} into {$Evolution_Data['Display_Name']}!</b>
                        </div>
                    </td>
                </tr>
            ";

            try
            {
                $Update = $PDO->prepare("UPDATE `pokemon` SET `Name` = ?, `Forme` = ?, `Pokedex_ID`= ? , `Alt_ID` = ? WHERE `ID` = ? AND `Owner_Current` = ? LIMIT 1");
                $Update->execute([
                    $Evolution_Data['Name'],
                    $Evolution_Data['Forme'],
                    $Evolution_Data['Pokedex_ID'],
                    $Evolution_Data['Alt_ID'],
                    $Pokemon['ID'],
                    $User_Data['ID']
                ]);
            }
            catch ( PDOException $e )
            {
                handleError($e);
            }
        }

        return "
            <thead>
                <tr>
                    <th colspan='7'>Evolutions</th>
                </tr>
            </thead>

            <tbody>
                {$Evolution_Text}
                <tr>
                    <td colspan='7' style='padding: 5px;'>
                        Please select the Pok&eacute;mon that you wish to evolve.
                    </td>
                </tr>
            </tbody>
        ";
    }
