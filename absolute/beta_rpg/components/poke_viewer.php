<?php
    require_once $_SERVER['DOCUMENT_ROOT'] . '/backend/session/session.php';
    require_once $_SERVER['DOCUMENT_ROOT'] . '/backend/session/user_session.php';

    require_once $_SERVER['DOCUMENT_ROOT'] . '/backend/pokemon/pokemon_data.php';

    require_once $_SERVER['DOCUMENT_ROOT'] . '/backend/utility/time_to_date.php';

    if ( isset($_GET['id']) )
    {
        $Pokemon_Database_ID = htmlspecialchars($_GET['id'], ENT_QUOTES, 'UTF-8');
    }
    else
    {
        $Pokemon_Database_ID = 0;
    }

    $Pokemon = PokemonData::FetchById($Pokemon_Database_ID);
    $Pokemon['images'] = PokemonData::FetchPokemonImages(
        Pokedex_ID: (int)($Pokemon['pokedex_id'] ?? 0),
        Alt_ID: (int)($Pokemon['alt_id'] ?? 0),
        Type: $Pokemon['type'] ?? 'Normal',
        Forme: $Pokemon['forme'] ?? null
    );

    if ( !$Pokemon )
    {
        echo "This Pokemon doesn't exist.";
        return;
    }

    $Owner_Current_Username = 'Jess';
    $Owner_Original_Username = 'Jess';

    $Move_1 = 'Unknown';
    $Move_2 = 'Unknown';
    $Move_3 = 'Unknown';
    $Move_4 = 'Unknown';

    $Stat_Dictionary = [
        [
            'Name' => 'hp',
            'Label' => 'HP',
        ],
        [
            'Name' => 'attack',
            'Label' => 'Attack',
        ],
        [
            'Name' => 'defense',
            'Label' => 'Defense',
        ],
        [
            'Name' => 'special_attack',
            'Label' => 'Sp. Attack',
        ],
        [
            'Name' => 'special_defense',
            'Label' => 'Sp. Defense',
        ],
        [
            'Name' => 'speed',
            'Label' => 'Speed',
        ],
    ];
?>

<!DOCTYPE html>
<html>
	<head>
		<title>Pok&eacute;mon Statistics &mdash; The Pok&eacute;mon Absolute</title>
		<link href='<?= DOMAIN_SPRITES; ?>/Pokemon/Icons/Normal/359-mega.png' rel='shortcut icon'>

		<link type='text/css' rel='stylesheet' href='/themes/root.css' />
		<link type='text/css' rel='stylesheet' href='/themes/main.css' />
		<link type='text/css' rel='stylesheet' href='/themes/components/poke_viewer.css' />
		<link type='text/css' rel='stylesheet' href='/themes/styles/<?= (isset($User_Data['Theme']) ? $User_Data['Theme'] : 'absol'); ?>.css' />
	</head>

	<body style='background-color: var(--color-quadternary);'>
        <section class='pokemon-preview-container'>
            <div class='pokemon-preview'>
                <div class='pokemon-header'>
                    <div class='pokemon-metadata'>
                        <img src='/assets/images/Pokemon/Misc/Level.png' /> <?= $Pokemon['level'] ?? '0'; ?>
                    </div>

                    <div class='pokemon-icons'>
                        <?php
                            if ( $Pokemon['frozen'] )
                            {
                                echo "<img src='/assets/images/Pokemon/Misc/Frozen.png' alt='This Pokemon is frozen and can not be sold, traded, or released.' title='This Pokemon is frozen and can not be sold, traded, or released.' tooltip='This Pokemon is frozen and can not be sold, traded, or released.' />";
                            }
                        ?>
                    </div>
                </div>
                <img src='<?= $Pokemon['images']['Sprite']['Sprite_Path']; ?>' />
                <div class='pokemon-footer'>
                    <div>
                        <?= $Pokemon['name_data']['display_name']; ?>
                    </div>
                    <div>
                        <?php
                            if ( $Pokemon['name_data']['nickname'] )
                            {
                                echo "(<i>{$Pokemon['name_data']['nickname']}</i>)";
                            }
                        ?>
                    </div>
                    <div class='pokemon-gender'>
                        <?php
                            if ( in_array($Pokemon['gender'], ['Female', 'Male']) )
                            {
                                $Gender_Icon_Path = "/assets/images/Pokemon/Misc/{$Pokemon['gender']}.svg";
                                echo "<img src='{$Gender_Icon_Path}' alt='{$Pokemon['gender']}' />";
                            }
                        ?>
                    </div>
                </div>
            </div>

            <div class='pokemon-info-container'>
                <section class='pokemon-info-tabs'>
                    <button id='pokemon-info-button'>Details</button>
                    <button id='pokemon-ivs-button'>IVs</button>
                    <button id='pokemon-evs-button'>EVs</button>
                    <button id='pokemon-moves-button'>Moves</button>
                    <!-- <button id='pokemon-ribbons-button'>Ribbons</button> -->
                </section>

                <div class='pokemon-info-content'>
                    <div class='pokemon-info' id='pokemon-info'>
                        <div>
                            <div>
                                <div class='label'>Trainer</div>
                                <div class='value'><?= $Owner_Current_Username ?? 'Unknown'; ?></div>
                            </div>

                            <div>
                                <div class='label'>Ability</div>
                                <div class='value'><?= $Pokemon['ability'] ?? 'Unknown'; ?></div>
                            </div>

                            <div>
                                <div class='label'>Nature</div>
                                <div class='value'><?= $Pokemon['nature'] ?? 'Unknown'; ?></div>
                            </div>
                        </div>

                        <div>
                            <div>
                                <div class='label'>Caught On</div>
                                <div class='value'><?= Time_To_Date($Pokemon['created_at']) ?? 'Unknown'; ?></div>
                            </div>

                            <div>
                            </div>

                            <div>
                                <div class='label'>Caught Level</div>
                                <div class='value'><?= $Pokemon['created_level'] ?? 'Unknown'; ?></div>
                            </div>
                        </div>

                        <!-- -->
                        <div>
                        <?php
                            foreach ( array_slice($Stat_Dictionary, 0, 3, true) as $Index => $Stat )
                            {
                                $Stat_Value = $Pokemon['stats'][$Stat['Name']];
                                $Stat_IV = $Pokemon['iv_' . $Stat['Name']];
                                $Stat_EV = $Pokemon['ev_' . $Stat['Name']];

                                $Beneficial_Nature = $Pokemon['nature_modifier']['plus'] == $Stat['Name'];
                                $Negative_Nature = $Pokemon['nature_modifier']['minus'] == $Stat['Name'];

                                $Nature_Color = '';
                                if ( $Beneficial_Nature )
                                {
                                    $Nature_Color = 'color: green;';
                                }
                                else if ( $Negative_Nature )
                                {
                                    $Nature_Color = 'color: red;';
                                }

                                echo "
                                    <div>
                                        <div class='label'>
                                            " . ($Stat['Name'] == 'hp' ? 'HP' : ucfirst($Stat['Name'])) . "
                                        </div>
                                        <div class='value' style='{$Nature_Color}'>
                                            " . number_format($Stat_Value ?? 0) . "
                                        </div>
                                    </div>
                                ";
                            }
                        ?>
                        </div>

                        <div>
                        <?php
                            foreach ( array_slice($Stat_Dictionary, 3, 3, true) as $Index => $Stat )
                            {
                                if ( $Stat['Name'] == 'hp' )
                                {
                                    $Stat_Label = 'HP';
                                }
                                else
                                {
                                    $Stat_Label = ucwords(str_replace('_', ' ', $Stat['Name']));
                                }

                                $Stat_Value = $Pokemon['stats'][$Stat['Name']];
                                $Stat_IV = $Pokemon['iv_' . $Stat['Name']];
                                $Stat_EV = $Pokemon['ev_' . $Stat['Name']];

                                $Beneficial_Nature = $Pokemon['nature_modifier']['plus'] == $Stat['Name'];
                                $Negative_Nature = $Pokemon['nature_modifier']['minus'] == $Stat['Name'];

                                $Nature_Color = '';
                                if ( $Beneficial_Nature )
                                {
                                    $Nature_Color = 'color: green;';
                                }
                                else if ( $Negative_Nature )
                                {
                                    $Nature_Color = 'color: red;';
                                }

                                echo "
                                    <div>
                                        <div class='label'>
                                            " . $Stat_Label . "
                                        </div>
                                        <div class='value' style='{$Nature_Color}'>
                                            " . number_format($Stat_Value ?? 0) . "
                                        </div>
                                    </div>
                                ";
                            }
                        ?>
                        </div>
                        <!-- -->
                    </div>

                    <div class='pokemon-info' id='pokemon-ivs' style='display: none;'>
                        <!-- -->
                        <div>
                        <?php
                            foreach ( array_slice($Stat_Dictionary, 0, 3, true) as $Index => $Stat )
                            {
                                if ( $Stat['Name'] == 'hp' )
                                {
                                    $Stat_Label = 'HP';
                                }
                                else
                                {
                                    $Stat_Label = ucwords(str_replace('_', ' ', $Stat['Name']));
                                }

                                $Stat_IV = $Pokemon['iv_' . $Stat['Name']];

                                $Maxed_Color = $Stat_IV == 31 ? 'color: gold; font-weight: bold;' : '';

                                echo "
                                    <div>
                                        <div class='label'>
                                            " . $Stat_Label . "
                                        </div>
                                        <div class='value' style='{$Maxed_Color}'>
                                            " . number_format($Stat_IV ?? 0) . "
                                        </div>
                                    </div>
                                ";
                            }
                        ?>
                        </div>

                        <div>
                        <?php
                            foreach ( array_slice($Stat_Dictionary, 3, 3, true) as $Index => $Stat )
                            {
                                if ( $Stat['Name'] == 'hp' )
                                {
                                    $Stat_Label = 'HP';
                                }
                                else
                                {
                                    $Stat_Label = ucwords(str_replace('_', ' ', $Stat['Name']));
                                }

                                $Stat_IV = $Pokemon['iv_' . $Stat['Name']];

                                $Maxed_Color = $Stat_IV == 31 ? 'color: gold; font-weight: bold;' : '';

                                echo "
                                    <div>
                                        <div class='label'>
                                            " . $Stat_Label . "
                                        </div>
                                        <div class='value' style='{$Maxed_Color}'>
                                            " . number_format($Stat_IV ?? 0) . "
                                        </div>
                                    </div>
                                ";
                            }
                        ?>
                        </div>
                        <!-- -->

                        <div class='pokemon-iv-reroll'>
                            <div>You may reroll your IVs for a chance at better stats.</div>
                            <button id='reroll-ivs-button'>Reroll</button>
                        </div>
                    </div>

                    <div class='pokemon-info' id='pokemon-evs' style='display: none;'>
                        <!-- -->
                        <div>
                        <?php
                            foreach ( array_slice($Stat_Dictionary, 0, 3, true) as $Index => $Stat )
                            {
                                if ( $Stat['Name'] == 'hp' )
                                {
                                    $Stat_Label = 'HP';
                                }
                                else
                                {
                                    $Stat_Label = ucwords(str_replace('_', ' ', $Stat['Name']));
                                }

                                $Stat_EV = $Pokemon['ev_' . $Stat['Name']];

                                $Maxed_Color = $Stat_EV == 252 ? 'color: gold; font-weight: bold;' : '';

                                echo "
                                    <div>
                                        <div class='label'>
                                            " . $Stat_Label . "
                                        </div>
                                        <div class='value' style='{$Maxed_Color}'>
                                            " . number_format($Stat_EV ?? 0) . "
                                        </div>
                                    </div>
                                ";
                            }
                        ?>
                        </div>

                        <div>
                        <?php
                            foreach ( array_slice($Stat_Dictionary, 3, 3, true) as $Index => $Stat )
                            {
                                if ( $Stat['Name'] == 'hp' )
                                {
                                    $Stat_Label = 'HP';
                                }
                                else
                                {
                                    $Stat_Label = ucwords(str_replace('_', ' ', $Stat['Name']));
                                }

                                $Stat_EV = $Pokemon['ev_' . $Stat['Name']];

                                $Maxed_Color = $Stat_EV == 252 ? 'color: gold; font-weight: bold;' : '';

                                echo "
                                    <div>
                                        <div class='label'>
                                            " . $Stat_Label . "
                                        </div>
                                        <div class='value' style='{$Maxed_Color}'>
                                            " . number_format($Stat_EV ?? 0) . "
                                        </div>
                                    </div>
                                ";
                            }
                        ?>
                        </div>
                        <!-- -->

                        <div class='pokemon-ev-allocation'>
                            <div>You may allocate or reset EVs.</div>
                            <div>
                                <button id='allocate-evs-button'>Reset</button>
                                <button id='allocate-evs-button'>Apply</button>
                            </div>
                        </div>
                    </div>

                    <div class='pokemon-info' id='pokemon-moves' style='display: none;'>
                        <?php
                            for ( $i = 1; $i <= 4; $i++ )
                            {
                                $Move_Name = $Pokemon['move_' . $i . '_name'] ?? 'Unknown';
                                $Move_Power = mt_rand(50, 150);
                                $Move_Category = ['Physical', 'Special', 'Status'][array_rand(['Physical', 'Special', 'Status'])];
                                $Move_Type = ['Normal', 'Fire', 'Water', 'Grass', 'Electric', 'Ice', 'Fighting', 'Poison', 'Ground', 'Flying', 'Psychic', 'Bug', 'Rock', 'Ghost', 'Dragon', 'Dark', 'Steel', 'Fairy'][array_rand(['Normal', 'Fire', 'Water', 'Grass', 'Electric', 'Ice', 'Fighting', 'Poison', 'Ground', 'Flying', 'Psychic', 'Bug', 'Rock', 'Ghost', 'Dragon', 'Dark', 'Steel', 'Fairy'])];
                                $Move_Accuracy = mt_rand(70, 100);

                                echo "
                                    <div class='pokemon-preview-move'>
                                        <div>
                                            <div>
                                                <img src='/assets/images/Battle/Move Category/Move_{$Move_Category}.png' alt='{$Move_Category} Move' class='pokemon-move-category' />
                                                <b>{$Move_Name}</b>
                                            </div>
                                            <div>
                                                <img src='/assets/images/Battle/Types/{$Move_Type}.png' alt='{$Move_Type} Type' class='pokemon-move-type' />
                                            </div>
                                        </div>
                                        <div>
                                            <div>
                                                <b>Power</b>: <span>{$Move_Power}</span>
                                            </div>
                                            <div>
                                                <b>Accuracy</b>: <span>{$Move_Accuracy}%</span>
                                            </div>
                                        </div>
                                    </div>
                                ";
                            }
                        ?>
                    </div>

                    <div class='pokemon-info' id='pokemon-ribbons' style='display: none;'>
                        ribbons
                    </div>
                </div>
            </div>
        </section>

        <script src='/js/components/poke_viewer.js'></script>
	</body>
</html>
