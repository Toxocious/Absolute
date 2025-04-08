<?php
    require_once $_SERVER['DOCUMENT_ROOT'] . '/core/required/ajax_header.php';
?>

<div style="margin: 0.5em 1em 1em;">
    Nicknaming your Pok&eacute;mon is free! Nicknames are not permanent, and you can change them at any time.
</div>

<div id='Pokemon_Center_Nickname_AJAX'></div>

<div style='display: flex; flex-wrap: wrap; gap: 10px;'>
    <?php
        if ( $User_Data['Roster'] )
        {
            $Slot = 1;
            foreach ( $User_Data['Roster'] as $Roster_Pokemon )
            {
                echo "
                    <table class='border-gradient' style='width: 280px;'>
                        <tbody>
                            <tr>
                                <td style='width: 120px;'>
                                    <img id='Roster_Slot_" . $Slot . "_Sprite' src='" . DOMAIN_SPRITES . "/Pokemon/Sprites/0.png' />
                                </td>

                                <td>
                                    <b id='Roster_Slot_" . $Slot . "_Nickname'>Empty</b>
                                    <hr class='faded' />

                                    <input type='text' name='Roster_Slot_" . $Slot . "_Nick_Input' style='width: 150px;' />
                                    <hr class='faded' />

                                    <button id='Roster_Slot_" . $Slot . "_Button' style='width: 160px;' disabled>
                                        Update Nickname
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                ";

                $Slot++;
            }
        }
    ?>
</div>
