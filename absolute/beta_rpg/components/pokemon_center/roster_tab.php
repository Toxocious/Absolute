<?php
    require_once $_SERVER['DOCUMENT_ROOT'] . '/backend/session/session.php';
    require_once $_SERVER['DOCUMENT_ROOT'] . '/backend/session/user_session.php';

    require_once $_SERVER['DOCUMENT_ROOT'] . '/components/_component.php';

    require_once $_SERVER['DOCUMENT_ROOT'] . '/backend/data/pokedex_data.php';
    require_once $_SERVER['DOCUMENT_ROOT'] . '/backend/data/pokemon_data.php';
?>

<!-- Player's active roster -->
<section class='pokemon-center-roster'>
    <div data-team-list>
        <?php
            for ( $i = 0; $i < 6; $i++ )
            {
                $Roster_Pokemon_Data = $User_Data['roster'][$i] ?? null;
                if ( $Roster_Pokemon_Data === null )
                {
                    component('pokemon_center/roster_slot', [
                        'Pokemon_ID' => -1,
                        'Pokemon_Name' => 'Empty Slot',
                        'Pokemon_Forme' => null,
                        'Pokemon_Slot' => $i + 1,
                    ]);

                    continue;
                }

                component('pokemon_center/roster_slot', [
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
                ]);
            }
        ?>
    </div>
</section>

<!-- Player's boxed Pokemon -->
<section class='pokemon-center-box panel'>
    <div class='panel-content'>
        <section class='pokemon-center-box-container'>
            <div class='panel'>
                <div class='panel-header'>
                    Boxed Pokemon
                </div>

                <div class='panel-content pokemon-center-box-content' data-box-list>
                    <p class='pokemon-center-status'>You have no boxed Pokemon.</p>
                </div>

                <div class='pokemon-center-box-pagination'>
                    <div id='pokemon-center-box-results'>0 Pokemon</div>

                    <div>
                        <button type='button' data-box-prev disabled>Previous</button>
                        <div data-box-page>Page 1 / 1</div>
                        <button type='button' data-box-next disabled>Next</button>
                    </div>

                    <div>
                        <button type='button' id='pokemon-center-box-filter-toggle'>Filter</button>
                    </div>
                </div>

                <div class='panel-content pokemon-center-box-filter display-none'>
                    <table>
                        <tbody>
                            <tr>
                                <td colspan='8'>
                                    <select id='pokemon-center-box-filter-species' class='full' data-box-filter-pokemon>
                                        <button>
                                            <selectedcontent></selectedcontent>
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <path d="m6 9 6 6 6-6"></path>
                                            </svg>
                                        </button>

                                        <option value='null'>Select a Pokemon</option>
                                        <?php
                                            $Pokemon_Options = PokedexData::GetPokemonDropdownList();
                                            foreach ( $Pokemon_Options as $Option )
                                            {
                                                $Option_Name = $Option['pokemon'] . ($Option['forme'] ? " {$Option['forme']}" : '');
                                                echo "<option value='{$Option['pokedex_id']}.{$Option['alt_id']}'>{$Option_Name}</option>";
                                            }
                                        ?>
                                    </select>
                                </td>
                            </tr>

                            <tr id='pokemon-center-box-filter-type'>
                                <td colspan='2'>
                                    <a href='javascript:void(0);'
                                    data-box-filter-type='all'
                                    class='active'>All Types</a>
                                </td>
                                <td colspan='2'>
                                    <a href='javascript:void(0);'
                                    data-box-filter-type='normal'>Normal</a>
                                </td>
                                <td colspan='2'>
                                    <a href='javascript:void(0);'
                                    data-box-filter-type='shiny'>Shiny</a>
                                </td>
                                <td colspan='2'>
                                    <a href='javascript:void(0);'
                                    data-box-filter-type='event'>Event</a>
                                </td>
                            </tr>

                            <tr id='pokemon-center-box-filter-gender'>
                                <td colspan='2'>
                                    <a href='javascript:void(0);' data-box-filter-gender='female'>Female</a>
                                </td>
                                <td colspan='2'>
                                    <a href='javascript:void(0);' data-box-filter-gender='male'>Male</a>
                                </td>
                                <td colspan='2'>
                                    <a href='javascript:void(0);' data-box-filter-gender='genderless'>Genderless</a>
                                </td>
                                <td colspan='2'>
                                    <a href='javascript:void(0);' data-box-filter-gender='ungendered'>(?)</a>
                                </td>
                            </tr>
                            <tr>
                                <td colspan='8'>
                                    <hr />
                                </td>
                            </tr>
                            <tr>
                                <td colspan='2'></td>
                                <td colspan='4'>
                                    <a href='javascript:void(0);' id='pokemon-center-box-filter-search' data-box-filter-search>Search</a>
                                </td>
                                <td colspan='2'></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </div>
</section>
