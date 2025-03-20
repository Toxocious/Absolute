<?php
    require_once $_SERVER['DOCUMENT_ROOT'] . '/core/required/ajax_header.php';
?>

<div id='Pokemon_Center_Roster_AJAX'></div>

<div style='display: flex; flex-wrap: wrap; gap: 10px;'>
    <!-- Roster Pokemon -->
    <table class='border-gradient' style='width: 300px;'>
        <thead>
            <tr>
                <th colspan='7'>Roster</th>
            </tr>
        </thead>

        <tbody>
            <?php
                for ( $i = 1; $i <= 6; $i++ )
                {
                    echo "
                        <tbody>
                            <tr>
                                <td colspan='2' style='width: 72px;'>
                                    <img
                                        id='Roster_Slot_{$i}_Icon'
                                        src='<?= DOMAIN_SPRITES; ?>/Pokemon/Sprites/0_mini.png'
                                    />
                                </td>
                                <td colspan='5' style='width: 200px;'>
                                    <b id='Roster_Slot_{$i}_Display_Name'>Empty</b>
                                    <br />
                                    <span id='Roster_Slot_{$i}_Level'></span>
                                </td>
                            </tr>
                            <tr>
                                <td colspan='1'>
                                    <a href='javascript:void(0);' id='Roster_Slot_{$i}_Move_To_1'>1</a>
                                </td>
                                <td colspan='1'>
                                    <a href='javascript:void(0);' id='Roster_Slot_{$i}_Move_To_2'>2</a>
                                </td>
                                <td colspan='1'>
                                    <a href='javascript:void(0);' id='Roster_Slot_{$i}_Move_To_3'>3</a>
                                </td>
                                <td colspan='1'>
                                    <a href='javascript:void(0);' id='Roster_Slot_{$i}_Move_To_4'>4</a>
                                </td>
                                <td colspan='1'>
                                    <a href='javascript:void(0);' id='Roster_Slot_{$i}_Move_To_5'>5</a>
                                </td>
                                <td colspan='1'>
                                    <a href='javascript:void(0);' id='Roster_Slot_{$i}_Move_To_6'>6</a>
                                </td>
                                <td colspan='1'>
                                    <a href='javascript:void(0);' id='Roster_Slot_{$i}_Move_To_7'>x</a>
                                </td>
                            </tr>
                        </tbody>
                    ";
                }
            ?>
        </tbody>
    </table>

    <!-- Boxed Pokemon -->
    <div style='display: flex; flex-wrap: wrap; gap: 10px; width: 550px;'>
        <table class='border-gradient' style='min-height: 215px; max-height: 215px; width: 550px;'>
            <thead>
                <tr>
                    <th colspan='21'>Box</th>
                </tr>
            </thead>

            <tbody id='Box_Pagination' style='height: 30px;'>
                <tr>
                    <td colspan='21'>Loading</td>
                </tr>
            </tbody>

            <tbody id='Boxed_Pokemon'>
                <tr>
                    <td colspan='21'>
                        <div style='display: flex; align-items: center; justify-content: center;'>
                            <div class='loading-element'></div>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>

        <table style='min-height: 215px; max-height: 215px; width: 550px;'>
            <tbody id='Pokemon_Preview'>
                <tr>
                    <td></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
