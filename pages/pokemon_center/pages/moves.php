<?php
  require_once $_SERVER['DOCUMENT_ROOT'] . '/core/required/session.php';
?>

<div id='Pokemon_Center_Moves_AJAX'></div>

<div class='description'>
  To change the move of a Pokemon, simply click on the move that you wish to change, and a dropdown menu will appear in it's place, allowing you to select the move that you desire.
</div>

<div style='display: flex; flex-wrap: wrap; gap: 10px;'>
  <table class='border-gradient' style='width: 350px;'>
    <tbody>
      <tr>
        <td rowspan='4' style='width: 150px;'>
          <img id='Roster_Slot_1_Sprite' src='<?= DOMAIN_SPRITES; ?>/Pokemon/Sprites/0.png' />
          <br />
          <b id='Roster_Slot_1_Display_Name'>Empty</b>
        </td>
        <td id='Roster_Slot_1_Move_1'>
          <b>Unknown</b>
        </td>
      <tr>
        <td id='Roster_Slot_1_Move_2'>
          <b>Unknown</b>
        </td>
      </tr>
      <tr>
        <td id='Roster_Slot_1_Move_3'>
          <b>Unknown</b>
        </td>
      </tr>
      <tr>
        <td id='Roster_Slot_1_Move_4'>
          <b>Unknown</b>
        </td>
      </tr>
    </tbody>
  </table>

  <table class='border-gradient' style='width: 350px;'>
    <tbody>
      <tr>
        <td rowspan='4' style='width: 150px;'>
          <img id='Roster_Slot_2_Sprite' src='<?= DOMAIN_SPRITES; ?>/Pokemon/Sprites/0.png' />
          <br />
          <b id='Roster_Slot_2_Display_Name'>Empty</b>
        </td>
        <td id='Roster_Slot_2_Move_1'>
          <b>Unknown</b>
        </td>
      </tr>
      <tr>
        <td id='Roster_Slot_2_Move_2'>
          <b>Unknown</b>
        </td>
      </tr>
      <tr>
        <td id='Roster_Slot_2_Move_3'>
          <b>Unknown</b>
        </td>
      </tr>
      <tr>
        <td id='Roster_Slot_2_Move_4'>
          <b>Unknown</b>
        </td>
      </tr>
    </tbody>
  </table>

  <table class='border-gradient' style='width: 350px;'>
    <tbody>
      <tr>
        <td rowspan='4' style='width: 150px;'>
          <img id='Roster_Slot_3_Sprite' src='<?= DOMAIN_SPRITES; ?>/Pokemon/Sprites/0.png' />
          <br />
          <b id='Roster_Slot_3_Display_Name'>Empty</b>
        </td>
        <td id='Roster_Slot_3_Move_1'>
          <b>Unknown</b>
        </td>
      </tr>
      <tr>
        <td id='Roster_Slot_3_Move_2'>
          <b>Unknown</b>
        </td>
      </tr>
      <tr>
        <td id='Roster_Slot_3_Move_3'>
          <b>Unknown</b>
        </td>
      </tr>
      <tr>
        <td id='Roster_Slot_3_Move_4'>
          <b>Unknown</b>
        </td>
      </tr>
    </tbody>
  </table>

  <table class='border-gradient' style='width: 350px;'>
    <tbody>
      <tr>
        <td rowspan='4' style='width: 150px;'>
          <img id='Roster_Slot_4_Sprite' src='<?= DOMAIN_SPRITES; ?>/Pokemon/Sprites/0.png' />
          <br />
          <b id='Roster_Slot_4_Display_Name'>Empty</b>
        </td>
        <td id='Roster_Slot_4_Move_1'>
          <b>Unknown</b>
        </td>
      </tr>
      <tr>
        <td id='Roster_Slot_4_Move_2'>
          <b>Unknown</b>
        </td>
      </tr>
      <tr>
        <td id='Roster_Slot_4_Move_3'>
          <b>Unknown</b>
        </td>
      </tr>
      <tr>
        <td id='Roster_Slot_4_Move_4'>
          <b>Unknown</b>
        </td>
      </tr>
    </tbody>
  </table>

  <table class='border-gradient' style='width: 350px;'>
    <tbody>
      <tr>
        <td rowspan='4' style='width: 150px;'>
          <img id='Roster_Slot_5_Sprite' src='<?= DOMAIN_SPRITES; ?>/Pokemon/Sprites/0.png' />
          <br />
          <b id='Roster_Slot_5_Display_Name'>Empty</b>
        </td>
        <td id='Roster_Slot_5_Move_1'>
          <b>Unknown</b>
        </td>
      </tr>
      <tr>
        <td id='Roster_Slot_5_Move_2'>
          <b>Unknown</b>
        </td>
      </tr>
      <tr>
        <td id='Roster_Slot_5_Move_3'>
          <b>Unknown</b>
        </td>
      </tr>
      <tr>
        <td id='Roster_Slot_5_Move_4'>
          <b>Unknown</b>
        </td>
      </tr>
    </tbody>
  </table>

  <table class='border-gradient' style='width: 350px;'>
    <tbody>
      <tr>
        <td rowspan='4' style='width: 150px;'>
          <img id='Roster_Slot_6_Sprite' src='<?= DOMAIN_SPRITES; ?>/Pokemon/Sprites/0.png' />
          <br />
          <b id='Roster_Slot_6_Display_Name'>Empty</b>
        </td>
        <td id='Roster_Slot_6_Move_1'>
          <b>Unknown</b>
        </td>
      </tr>
      <tr>
        <td id='Roster_Slot_6_Move_2'>
          <b>Unknown</b>
        </td>
      </tr>
      <tr>
        <td id='Roster_Slot_6_Move_3'>
          <b>Unknown</b>
        </td>
      </tr>
      <tr>
        <td id='Roster_Slot_6_Move_4'>
          <b>Unknown</b>
        </td>
      </tr>
    </tbody>
  </table>
</div>
