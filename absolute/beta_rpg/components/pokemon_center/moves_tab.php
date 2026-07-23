<?php
    require_once $_SERVER['DOCUMENT_ROOT'] . '/backend/session/session.php';
    require_once $_SERVER['DOCUMENT_ROOT'] . '/backend/session/user_session.php';

    require_once $_SERVER['DOCUMENT_ROOT'] . '/components/_component.php';

    require_once $_SERVER['DOCUMENT_ROOT'] . '/backend/data/pokemon_data.php';
    require_once $_SERVER['DOCUMENT_ROOT'] . '/backend/data/move_data.php';

    $Move_Dropdown_Data = MoveData::GetMoveDropdownList();
    $Move_Dropdown_Options = array_map(function($move) {
        return [
            'id' => $move['id'],
            'name' => $move['name'],
        ];
    }, $Move_Dropdown_Data);
?>

<section class='pokemon-center-moves'>
    <?php
            for ( $i = 0; $i < 6; $i++ )
            {
                $Roster_Pokemon_Data = $User_Data['roster'][$i] ?? null;
                if ( $Roster_Pokemon_Data === null )
                {
                    component('pokemon_center/moves_slot', [
                        'Pokemon_ID' => -1,
                        'Pokemon_Name' => 'Empty Slot',
                        'Pokemon_Forme' => null,
                        'Pokemon_Slot' => $i + 1,
                        'Move_Dropdown_Options' => $Move_Dropdown_Options,
                    ]);

                    continue;
                }

                component('pokemon_center/moves_slot', [
                    'Pokemon_ID' => $Roster_Pokemon_Data['id'],
                    'Pokemon_Pokedex_ID' => $Roster_Pokemon_Data['pokedex_id'],
                    'Pokemon_Pokedex_Alt_ID' => $Roster_Pokemon_Data['alt_id'],
                    'Pokemon_Name' => $Roster_Pokemon_Data['name'],
                    'Pokemon_Forme' => $Roster_Pokemon_Data['forme'],
                    'Pokemon_Type' => $Roster_Pokemon_Data['type'],
                    'Pokemon_Level' => PokemonData::CalculateLevel($Roster_Pokemon_Data['experience']),
                    'Pokemon_Gender' => $Roster_Pokemon_Data['gender'],
                    'Pokemon_Experience' => $Roster_Pokemon_Data['experience'],
                    'Pokemon_Slot' => $Roster_Pokemon_Data['slot'],
                    'Pokemon_Nickname' => $Roster_Pokemon_Data['nickname'],
                    'Pokemon_Held_Item' => $Roster_Pokemon_Data['item'],
                    'Pokemon_Move_1' => $Roster_Pokemon_Data['move_1'],
                    'Pokemon_Move_2' => $Roster_Pokemon_Data['move_2'],
                    'Pokemon_Move_3' => $Roster_Pokemon_Data['move_3'],
                    'Pokemon_Move_4' => $Roster_Pokemon_Data['move_4'],
                    'Move_Dropdown_Options' => $Move_Dropdown_Options,
                ]);
            }
        ?>
</section>
