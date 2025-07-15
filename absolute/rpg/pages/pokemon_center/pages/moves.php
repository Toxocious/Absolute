<?php
    require_once $_SERVER['DOCUMENT_ROOT'] . '/core/required/ajax_header.php';
?>

<div id='Pokemon_Center_Moves_AJAX'></div>

<div style="margin: 0.5em 1em 1em;">
    Click on a Pokemon's move to spawn a menu allowing you to change its move.
</div>

<div style='display: flex; flex-wrap: wrap; gap: 10px;'>
    <?php
        for ( $i = 1; $i <= 6; $i++ )
        {
            echo "
                <table class='border-gradient' style='width: 350px;'>
                    <tbody>
                        <tr>
                            <td rowspan='4' style='width: 150px;'>
                                <img id='Roster_Slot_{$i}_Sprite' src='<?= DOMAIN_SPRITES; ?>/Pokemon/Sprites/0.png' />
                                <br />
                                <b id='Roster_Slot_{$i}_Display_Name'>Empty</b>
                            </td>
                            <td id='Roster_Slot_{$i}_Move_1'>
                                <b>Unknown</b>
                            </td>
                        </tr>
                        <tr>
                            <td id='Roster_Slot_{$i}_Move_2'>
                                <b>Unknown</b>
                            </td>
                        </tr>
                        <tr>
                            <td id='Roster_Slot_{$i}_Move_3'>
                                <b>Unknown</b>
                            </td>
                        </tr>
                        <tr>
                            <td id='Roster_Slot_{$i}_Move_4'>
                                <b>Unknown</b>
                            </td>
                        </tr>
                    </tbody>
                </table>
            ";
        }
    ?>
</div>
