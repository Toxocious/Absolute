<?php
    require_once $_SERVER['DOCUMENT_ROOT'] . '/backend/session/session.php';
    require_once $_SERVER['DOCUMENT_ROOT'] . '/backend/session/user_session.php';

    require_once $_SERVER['DOCUMENT_ROOT'] . '/components/_component.php';

    require_once $_SERVER['DOCUMENT_ROOT'] . '/backend/data/pokemon_data.php';
?>

<section class='pokemon-center-nicknames'>
    <?php
            for ( $i = 0; $i < 6; $i++ )
            {
                $Roster_Pokemon_Data = $User_Data['roster'][$i] ?? null;
                if ( $Roster_Pokemon_Data === null )
                {
                    component('pokemon_center/nickname_slot', [
                        'Pokemon_ID' => -1,
                        'Pokemon_Name' => 'Empty Slot',
                        'Pokemon_Forme' => null,
                        'Pokemon_Slot' => $i + 1,
                    ]);

                    continue;
                }

                component('pokemon_center/nickname_slot', [
                    'Pokemon_ID' => $Roster_Pokemon_Data['id'],
                    'Pokemon_Pokedex_ID' => $Roster_Pokemon_Data['pokedex_id'],
                    'Pokemon_Pokedex_Alt_ID' => $Roster_Pokemon_Data['alt_id'],
                    'Pokemon_Name' => $Roster_Pokemon_Data['name'],
                    'Pokemon_Forme' => $Roster_Pokemon_Data['forme'],
                    'Pokemon_Type' => $Roster_Pokemon_Data['type'],
                    'Pokemon_Gender' => $Roster_Pokemon_Data['gender'],
                    'Pokemon_Slot' => $Roster_Pokemon_Data['slot'],
                    'Pokemon_Nickname' => $Roster_Pokemon_Data['nickname'],
                ]);
            }
        ?>
</section>
