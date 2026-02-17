<?php
    require_once $_SERVER['DOCUMENT_ROOT'] . '/core/required/ajax_header.php';

    require_once $_SERVER['DOCUMENT_ROOT'] . '/pages/rankings/functions/fetch_pokemon_rankings.php';

    $Species_List = FetchPokemonSpeciesList();
    $Rankings = FetchPokemonRankings(1);
    $Top_Ranking = FetchTopPokemon();

    if ( count($Rankings) === 0 )
    {
        echo "
            <div style='margin: auto; padding: 1em;'>
                There are currently no Pok&eacute;mon to display rankings for.
            </div>
        ";

        return;
    }

    $Owner_Username = $User_Class->DisplayUsername($Top_Ranking['Owner_Current'], true, false, true);
    $Nickname = (!empty($Top_Ranking['Nickname']) ? "<br /><i>{$Top_Ranking['Nickname']}</i>" : '');
?>

<div id='pokemon-rankings-container' class='pokemon-rankings-container'>
    <!-- Pokemon Filter Controls -->
    <div style='margin: 1em auto 0;'>
        <select id='shinyFilter' onchange='UpdatePokemonRankings(1);'>
            <option value='all'>(All Types)</option>
            <option value='normal'>Normal</option>
            <option value='shiny'>Shiny</option>
        </select>
        <select id='speciesFilter' onchange='UpdatePokemonRankings(1);'>
            <option value=''>Select A Pok&eacute;mon</option>
            <?php
                foreach ( $Species_List as $Species ) {
                    $Display_Name = $Species['Pokemon'];
                    if ( $Species['Forme'] ) {
                        $Display_Name .= " {$Species['Forme']}";
                    }

                    $Species_ID = $Species['ID'];
                    if ( $Species['Alt_ID'] > 0 ) {
                        $Species_ID .= "." . $Species['Alt_ID'];
                    }

                    echo "<option value='{$Species['Pokedex_ID']}'>" .$Display_Name . "</option>";
                }
            ?>
        </select>
    </div>

    <!-- Top Ranked Pokemon -->
     <table class='border-gradient' style='margin: 1em auto; width: 350px;'>
        <thead>
            <tr>
                <th colspan='6'>Pok&eacute;mon Master</th>
            </tr>
        </thead>

        <tbody>
            <tr>
                <td colspan='3' style='width: 50%;' onclick='PokemonViewer.open("<?= $Top_Ranking["ID"]; ?>");'>
                    <img src='<?= GetSprites($Top_Ranking['Pokedex_ID'], $Top_Ranking['Alt_ID'], $Top_Ranking['Type'])['Sprite']; ?>' style='vertical-align: middle;' /><br />
                    <a href='javascript:void(0);'>
                        <b><?= $Top_Ranking['Display_Name']; ?></b>
                        <?= $Nickname; ?>
                    </a>
                </td>
                <td colspan='3' style='width: 50%;'>
                    Level <?= $Top_Ranking['Level']; ?><br />
                    (<?= number_format($Top_Ranking['Experience']); ?> Exp.)<br /><br />
                    <b>Owner</b><br /><?= $Owner_Username; ?>
                </td>
            </tr>
        </tbody>
    </table>

    <!-- Ranked Pokemon List -->
     <table class='border-gradient' id='ranked-list' style='width: 700px;'>
        <thead>
            <tr>
                <th colspan='3'>Rank</th>
                <th colspan='6'>Pok&eacute;mon</th>
                <th colspan='6'>Level</th>
                <th colspan='6'>Trainer</th>
            </tr>
        </thead>

        <tbody>
            <?php
                if ( empty($Rankings) || $Rankings['Pagination']['Total_Results'] == 0 )
                {
                    echo "
                        <div style='margin: auto; padding: 1em;'>
                            There are currently no {$Category} to display rankings for.
                        </div>
                    ";
                }
                else
                {
                    $Current_Page = isset($Rankings['Pagination']['Current_Page'])
                        ? $Rankings['Pagination']['Current_Page']
                        : $Rankings['Page'];

                    $Placement = (($Current_Page - 1) * $Rankings['Pagination']['Per_Page']) + 1;

                    foreach ( $Rankings['Pagination']['Data'] as $Entry )
                    {
                        $Owner_Username = $User_Class->DisplayUsername($Entry['Owner_Current'], true, true, true);
                        $Nickname = (!empty($Entry['Nickname']) ? "<br /><i>{$Entry['Nickname']}</i>" : '');

                        echo "
                            <tr>
                                <td colspan='3'>#{$Placement}</td>
                                <td colspan='6' onclick='PokemonViewer.open(\"{$Entry['ID']}\");'>
                                    <img src='" . GetSprites($Entry['Pokedex_ID'], $Entry['Alt_ID'], $Entry['Type'], 'icon')['Icon'] . "' style='vertical-align: middle;' /><br />
                                    <a href='javascript:void(0);'>
                                        <b>{$Entry['Display_Name']}</b>
                                        {$Nickname}
                                    </a>
                                </td>
                                <td colspan='6'>
                                    Level {$Entry['Level']}<br />
                                    (" . number_format($Entry['Experience']) . " Exp.)
                                </td>
                                <td colspan='6'>
                                    {$Owner_Username}
                                </td>
                            </tr>
                        ";

                        $Placement++;
                    }
                }
            ?>
        </tbody>

        <tbody id='pagination-container'>
            <?= $Rankings['Pagination']['Pagination']; ?>
        </tbody>
    </table>
</div>
