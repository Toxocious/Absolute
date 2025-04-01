<?php
    require_once '../required/session.php';

    if ( isset($_GET['id']) )
        $Poke_ID = Purify($_GET['id']);
    else
        $Poke_ID = 0;

    $Pokemon = GetPokemonData($Poke_ID);

    if ( !$Pokemon )
    {
        echo "This Pokemon doesn't exist.";
        return;
    }

    $Owner_Current_Username = $User_Class->DisplayUsername($Pokemon['Owner_Current'], true, false, true);
    $Owner_Original_Username = $User_Class->DisplayUsername($Pokemon['Owner_Original'], true, false, true);

    $Move_1 = GetMoveData($Pokemon['Move_1']);
    $Move_2 = GetMoveData($Pokemon['Move_2']);
    $Move_3 = GetMoveData($Pokemon['Move_3']);
    $Move_4 = GetMoveData($Pokemon['Move_4']);

    $Item_Icon = '';
    if ( $Pokemon['Item'] != null )
    {
        $Item_Icon = "
            <div class='border-gradient' style='position: absolute; margin-left: 101px; margin-top: -20px; height: 30px; width: 30px; z-index: 1;'>
                <div style='padding: 0;'>
                <img src='{$Pokemon['Item_Icon']}' />
                </div>
            </div>
        ";
    }

    $Gender_Icon = '';
    if ( in_array($Pokemon['Gender_Short'], ['F', 'M']) && $Pokemon['Gender_Icon'] != null )
    {
        $Gender_Icon = "
            <div class='border-gradient' style='position: absolute; margin-left: -40px; margin-top: -20px; height: 30px; width: 30px; z-index: 1;'>
                <div>
                <img src='{$Pokemon['Gender_Icon']}' style='height: 20px; width: 20px;' />
                </div>
            </div>
        ";
    }

    $Stat_Dictionary = [
        [
            'Name' => 'HP',
            'Label' => 'HP',
        ],
        [
            'Name' => 'Attack',
            'Label' => 'Attack',
        ],
        [
            'Name' => 'Defense',
            'Label' => 'Defense',
        ],
        [
            'Name' => 'SpAttack',
            'Label' => 'Sp. Attack',
        ],
        [
            'Name' => 'SpDefense',
            'Label' => 'Sp. Defense',
        ],
        [
            'Name' => 'Speed',
            'Label' => 'Speed',
        ],
    ];
?>

<!DOCTYPE html>
<html>
	<head>
		<title>Pok&eacute;mon Statistics &mdash; The Pok&eacute;mon Absolute</title>
		<link href='<?= DOMAIN_SPRITES; ?>/Pokemon/Icons/Normal/359-mega.png' rel='shortcut icon'>

		<link type='text/css' rel='stylesheet' href='/themes/css/styles/<?= (isset($User_Data['Theme']) ? $User_Data['Theme'] : 'absol'); ?>.css' />
		<link type='text/css' rel='stylesheet' href='/themes/css/root.css' />
		<link type='text/css' rel='stylesheet' href='/themes/css/structure.css' />
		<link type='text/css' rel='stylesheet' href='/themes/css/theme.css' />
	</head>

	<body style='background-color: var(--color-quadternary);'>
        <div style='display: flex; align-items: center; justify-content: center; flex-direction: column; gap: 1em; height: 100%;'>
            <div style='display: flex; flex-direction: row; gap: 1em;'>
                <div style='display: flex; flex-direction: column; align-items: center; justify-content: center; min-width: 200px;'>
                    <div>
                        <?= $Gender_Icon; ?>
                        <?= $Item_Icon; ?>
                        <img src='<?= $Pokemon['Sprite']; ?>' />
                    </div>

                    <table class='border-gradient' style='min-width: 200px;'>
                        <tbody>
                            <tr>
                                <td colspan='1'>
                                    <b>
                                        <?= $Pokemon['Display_Name']; ?>
                                    </b>
                                    <?php
                                        if ( $Pokemon['Nickname'] )
                                        {
                                            echo "<br />(<i>{$Pokemon['Nickname']}</i>)";
                                        }
                                    ?>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <table class='border-gradient' style='min-width: 400px;'>
                    <thead>
                        <tr>
                            <th style='width: 25%;'></th>
                            <th style='width: 25%;'>Base</th>
                            <th style='width: 25%;'>IVs</th>
                            <th style='width: 25%;'>EVs</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            foreach ( $Stat_Dictionary as $Index => $Stat )
                            {
                                $Stat_Name = $Pokemon['Stats'][$Index];
                                $Stat_IV = $Pokemon['IVs'][$Index];
                                $Stat_EV = $Pokemon['EVs'][$Index];

                                $Nature_Color = '';
                                $Beneficial_Nature = $Pokemon['Nature_Modifiers']['Plus'] == $Stat['Name'];
                                $Negative_Nature = $Pokemon['Nature_Modifiers']['Minus'] == $Stat['Name'];

                                if ( $Beneficial_Nature )
                                {
                                    $Nature_Color = 'color: green;';
                                }
                                else if ( $Negative_Nature )
                                {
                                    $Nature_Color = 'color: red;';
                                }

                                echo "
                                    <tr>
                                        <td><b style='{$Nature_Color}'>{$Stat['Label']}</b></td>
                                        <td>" . number_format($Pokemon['Stats'][$Index]) . "</td>
                                        <td style='" . ($Pokemon['IVs'][$Index] == 31 ? "color: gold;" : "") . "'>" . number_format($Pokemon['IVs'][$Index]) . "</td>
                                        <td>" . number_format($Pokemon['EVs'][$Index]) . "</td>
                                    </tr>
                                ";
                            }
                        ?>
                        <!-- <tr>
                            <td><b style='<?= ($Pokemon['Nature_Modifiers']['Plus'] == '' ? 'color: green;' : ''); ?>'>HP</b></td>
                            <td><?= number_format($Pokemon['Stats'][0]); ?></td>
                            <td style='<?= ($Pokemon['IVs'][0] == 31 ? "color: gold;" : "") ?>'><?= number_format($Pokemon['IVs'][0]); ?></td>
                            <td><?= number_format($Pokemon['EVs'][0]); ?></td>
                        </tr>
                        <tr>
                            <td><b style='<?= ($Pokemon['Nature_Modifiers']['Plus'] == '' ? 'color: green;' : ''); ?>'>Attack</b></td>
                            <td><?= number_format($Pokemon['Stats'][1]); ?></td>
                            <td style='<?= ($Pokemon['IVs'][1] == 31 ? "color: gold;" : "") ?>'><?= number_format($Pokemon['IVs'][1]); ?></td>
                            <td><?= number_format($Pokemon['EVs'][1]); ?></td>
                        </tr>
                        <tr>
                            <td><b style='<?= ($Pokemon['Nature_Modifiers']['Plus'] == '' ? 'color: green;' : ''); ?>'>Defense</b></td>
                            <td><?= number_format($Pokemon['Stats'][2]); ?></td>
                            <td style='<?= ($Pokemon['IVs'][2] == 31 ? "color: gold;" : "") ?>'><?= number_format($Pokemon['IVs'][2]); ?></td>
                            <td><?= number_format($Pokemon['EVs'][2]); ?></td>
                        </tr>
                        <tr>
                            <td><b style='<?= ($Pokemon['Nature_Modifiers']['Plus'] == '' ? 'color: green;' : ''); ?>'>Sp. Attack</b></td>
                            <td><?= number_format($Pokemon['Stats'][3]); ?></td>
                            <td style='<?= ($Pokemon['IVs'][3] == 31 ? "color: gold;" : "") ?>'><?= number_format($Pokemon['IVs'][3]); ?></td>
                            <td><?= number_format($Pokemon['EVs'][3]); ?></td>
                        </tr>
                        <tr>
                            <td><b style='<?= ($Pokemon['Nature_Modifiers']['Plus'] == '' ? 'color: green;' : ''); ?>'>Sp. Defense</b></td>
                            <td><?= number_format($Pokemon['Stats'][4]); ?></td>
                            <td style='<?= ($Pokemon['IVs'][4] == 31 ? "color: gold;" : "") ?>'><?= number_format($Pokemon['IVs'][4]); ?></td>
                            <td><?= number_format($Pokemon['EVs'][4]); ?></td>
                        </tr>
                        <tr>
                            <td><b style='<?= ($Pokemon['Nature_Modifiers']['Plus'] == '' ? 'color: green;' : ''); ?>'>Speed</b></td>
                            <td><?= number_format($Pokemon['Stats'][5]); ?></td>
                            <td style='<?= ($Pokemon['IVs'][5] == 31 ? "color: gold;" : "") ?>'><?= number_format($Pokemon['IVs'][5]); ?></td>
                            <td><?= number_format($Pokemon['EVs'][5]); ?></td>
                        </tr> -->
                    </tbody>
                </table>
            </div>

            <div style='display: flex; flex-direction: column; gap: 1em; min-width: 700px;'>
                <table class='border-gradient' style='min-width: 616px;'>
                    <thead>
                        <tr>
                        <th colspan='4'>Pok&eacute;mon Details</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr>
                            <td style='width: 25%;'><b>Owner</b></td>
                            <td style='width: 25%;'><?= $Owner_Current_Username; ?></td>
                            <td colspan='1'>
                                <b>Original Owner</b>
                            </td>
                            <td colspan='1'>
                                <?= $Owner_Original_Username; ?>
                            </td>
                        </tr>
                        <tr>
                            <td><b>Nature</b></td>
                            <td><?= $Pokemon['Nature']; ?></td>
                            <td style='width: 25%;'><b>Location</b></td>
                            <td style='width: 25%;'><?= $Pokemon['Location']; ?></td>
                        </tr>
                        <tr>
                            <td><b>Level</b></td>
                            <td><?= $Pokemon['Level']; ?></td>
                            <td><b>Experience</b></td>
                            <td><?= $Pokemon['Experience']; ?></td>
                        </tr>
                        <tr>
                            <td><b>Trade Interest</b></td>
                            <td><?= $Pokemon['Trade_Interest']; ?></td>
                            <td colspan='1'><b>Obtained On</b></td>
                            <td colspan='1'>
                                <?= $Pokemon['Creation_Date']; ?>
                            </td>
                        </tr>
                        <tr>
                            <td colspan='1'><b>Place Obtained</b></td>
                            <td colspan='3'><?= $Pokemon['Creation_Location']; ?></td>
                        </tr>
                    </tbody>
                </table>

                <table class='border-gradient' style='min-width: 616px;'>
                    <thead>
                        <tr>
                            <th colspan='4'>Moves</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan='1' style='width: 25%;'>
                                <?= $Move_1['Name']; ?>
                            </td>
                            <td colspan='1' style='width: 25%;'>
                                <?= $Move_2['Name']; ?>
                            </td>
                            <td colspan='1' style='width: 25%;'>
                                <?= $Move_3['Name']; ?>
                            </td>
                            <td colspan='1' style='width: 25%;'>
                                <?= $Move_4['Name']; ?>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
	</body>
</html>
