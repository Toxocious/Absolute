<?php
    require_once $_SERVER['DOCUMENT_ROOT'] . '/core/required/ajax_header.php';

    /**
     * Handle evolution of a Pokemon.
     */
    function HandleEvolution($Pokemon_ID, $Evolution_ID, $Evolution_Alt_ID)
    {
        global $PDO, $User_Data;

        if ( !isset($Pokemon_ID) ) {
            return [
                'Success' => false,
                'Message' => 'The Pok&eacute;mon that you selected does not exist.',
            ];
        }

        $Pokemon = GetPokemonData($Pokemon_ID);

        if ( !isset($Pokemon) || !isset($Evolution_ID) || !isset($Evolution_Alt_ID) )
        {
            return [
                'Success' => false,
                'Message' => 'The Pok&eacute;mon that you selected does not exist.',
            ];
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

        if ( $Error )
        {
            return [
                'Success' => false,
                'Message' => 'This Pok&eacute;mon does not meet the requirements necessary to evolve.',
            ];
        }

        /**
         * The Pokemon is truly able to evolve.
         * Process the evolution here.
         */
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

        return [
            'Success' => true,
            'Message' => "You have successfully evolved your {$Pokemon['Display_Name']} into {$Evolution_Data['Display_Name']}!",
            'Pokemon_Name' => $Pokemon['Display_Name'],
            'Evolution_Name' => $Evolution_Data['Display_Name'],
            'Evolution_Sprite' => $Evolution_Data['Sprite'],
        ];
    }
