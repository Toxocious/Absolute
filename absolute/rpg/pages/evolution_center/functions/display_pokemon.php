<?php
    require_once $_SERVER['DOCUMENT_ROOT'] . '/core/required/ajax_header.php';

    /**
     * Return the user's roster as a data array.
     */
    function DisplayRoster()
    {
        global $User_Data;

        $Roster = [];

        foreach ( $User_Data['Roster'] as $Roster_Pokemon )
        {
            $Pokemon = GetPokemonData($Roster_Pokemon['ID']);

            $Roster[] = [
                'ID' => $Pokemon['ID'],
                'Display_Name' => $Pokemon['Display_Name'],
                'Sprite' => $Pokemon['Sprite'],
            ];
        }

        return $Roster;
    }

    /**
     * Return evolution data for the selected Pokemon.
     */
    function DisplayEvolutions($Pokemon_ID)
    {
        global $PDO, $Time;

        if ( empty($Pokemon_ID) ) {
            return [
                'Success' => false,
                'Message' => 'The Pok&eacute;mon that you selected does not exist.'
            ];
        }

        $Pokemon = GetPokemonData($Pokemon_ID);

        if ( empty($Pokemon) || !$Pokemon )
        {
            return [
                'Success' => false,
                'Message' => 'The Pok&eacute;mon that you selected does not exist.'
            ];
        }

        /**
         * Fetch the evolutions.
         */
        try
        {
            $Fetch_Evolutions = $PDO->prepare("SELECT * FROM `evolution_data` WHERE `poke_id` = ? AND `alt_id` = ?");
            $Fetch_Evolutions->execute([ $Pokemon['Pokedex_ID'], $Pokemon['Alt_ID'] ]);
            $Fetch_Evolutions->setFetchMode(PDO::FETCH_ASSOC);

            $Num_Of_Evos = $Fetch_Evolutions->rowCount();
        }
        catch ( PDOException $e )
        {
            HandleError($e);
        }

        $Time_Of_Day = (date('G') > 7 && date('G') < 19) ? 'Day' : 'Night';

        $Evolutions = [];
        if ( $Num_Of_Evos > 0 )
        {
            while ( $Evolution = $Fetch_Evolutions->fetch() )
            {
                $Evolution_Data = GetPokedexData($Evolution['to_poke_id'], $Evolution['to_alt_id'], $Pokemon['Type']);

                $Can_Evolve = true;
                if ( $Evolution['level'] && $Evolution['level'] != 0 && ( $Pokemon['Level'] < $Evolution['level']) )
                    $Can_Evolve = false;
                if ( $Evolution['min_happy'] && $Evolution['min_happy'] > $Pokemon['Happiness'] )
                    $Can_Evolve = false;
                if ( $Evolution['item'] && $Evolution['item'] != $Pokemon['Item'] )
                    $Can_Evolve = false;
                if ( $Evolution['held_item'] && $Evolution['held_item'] != $Pokemon['Item'] )
                    $Can_Evolve = false;
                if ( $Evolution['gender'] && ucfirst($Evolution['gender']) != $Pokemon['Gender'] )
                    $Can_Evolve = false;
                if ( $Evolution['time'] && $Evolution['time'] !== $Time )
                    $Can_Evolve = false;

                $Evolutions[] = [
                    'Pokedex_ID' => $Evolution_Data['Pokedex_ID'],
                    'Alt_ID' => $Evolution_Data['Alt_ID'],
                    'Display_Name' => $Evolution_Data['Display_Name'],
                    'Icon' => $Evolution_Data['Icon'],
                    'Can_Evolve' => $Can_Evolve,
                    'Requirements' => [
                        'Level' => ($Evolution['level'] > 0) ? (int) $Evolution['level'] : null,
                        'Gender' => $Evolution['gender'] ? ucfirst($Evolution['gender']) : null,
                        'Held_Item' => $Evolution['held_item'] ?: null,
                        'Use_Item' => $Evolution['item'] ?: null,
                        'Time' => $Evolution['time'] ?: null,
                        'Happiness' => $Evolution['min_happy'] ? (int) $Evolution['min_happy'] : null,
                    ],
                ];
            }
        }

        return [
            'Success' => true,
            'Pokemon' => [
                'ID' => $Pokemon['ID'],
                'Display_Name' => $Pokemon['Display_Name'],
                'Icon' => $Pokemon['Icon'],
                'Level' => $Pokemon['Level'],
                'Gender' => $Pokemon['Gender'],
                'Item' => $Pokemon['Item'] ?: null,
                'Happiness' => $Pokemon['Happiness'],
            ],
            'Time_Of_Day' => $Time_Of_Day,
            'Evolutions' => $Evolutions,
        ];
    }
