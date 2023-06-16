<?php
  require_once $_SERVER['DOCUMENT_ROOT'] . '/core/required/session.php';
?>

<div id='Pokemon_Center_Roster_AJAX'></div>

<div style='display: flex; flex-wrap: wrap; gap: 10px;'>
  <table class='border-gradient' style='width: 300px;'>
    <thead>
      <tr>
        <th colspan='7'>
          Roster
        </th>
      </tr>
    </thead>

    <tbody>
      <tbody>
        <tr>
          <td colspan='2' style='width: 72px;'>
            <img
              id='Roster_Slot_1_Icon'
              src='<?= DOMAIN_SPRITES; ?>/Pokemon/Sprites/0_mini.png'
            />
          </td>
          <td colspan='5' style='width: 200px;'>
            <b id='Roster_Slot_1_Display_Name'>
              Empty
            </b>
            <br />
            <span id='Roster_Slot_1_Level'></span>
          </td>
        </tr>
        <tr>
          <td colspan='1'>
            <a href='javascript:void(0);' id='Roster_Slot_1_Move_To_1'>1</a>
          </td>
          <td colspan='1'>
            <a href='javascript:void(0);' id='Roster_Slot_1_Move_To_2'>2</a>
          </td>
          <td colspan='1'>
            <a href='javascript:void(0);' id='Roster_Slot_1_Move_To_3'>3</a>
          </td>
          <td colspan='1'>
            <a href='javascript:void(0);' id='Roster_Slot_1_Move_To_4'>4</a>
          </td>
          <td colspan='1'>
            <a href='javascript:void(0);' id='Roster_Slot_1_Move_To_5'>5</a>
          </td>
          <td colspan='1'>
            <a href='javascript:void(0);' id='Roster_Slot_1_Move_To_6'>6</a>
          </td>
          <td colspan='1'>
            <a href='javascript:void(0);' id='Roster_Slot_1_Move_To_7'>x</a>
          </td>
        </tr>
      </tbody>

      <tbody>
        <tr>
          <td colspan='2' style='width: 72px;'>
            <img
              id='Roster_Slot_2_Icon'
              src='<?= DOMAIN_SPRITES; ?>/Pokemon/Sprites/0_mini.png'
            />
          </td>
          <td colspan='5' style='width: 200px;'>
            <b id='Roster_Slot_2_Display_Name'>
              Empty
            </b>
            <br />
            <span id='Roster_Slot_2_Level'></span>
          </td>
        </tr>
        <tr>
          <td colspan='1'>
            <a href='javascript:void(0);' id='Roster_Slot_2_Move_To_1'>1</a>
          </td>
          <td colspan='1'>
            <a href='javascript:void(0);' id='Roster_Slot_2_Move_To_2'>2</a>
          </td>
          <td colspan='1'>
            <a href='javascript:void(0);' id='Roster_Slot_2_Move_To_3'>3</a>
          </td>
          <td colspan='1'>
            <a href='javascript:void(0);' id='Roster_Slot_2_Move_To_4'>4</a>
          </td>
          <td colspan='1'>
            <a href='javascript:void(0);' id='Roster_Slot_2_Move_To_5'>5</a>
          </td>
          <td colspan='1'>
            <a href='javascript:void(0);' id='Roster_Slot_2_Move_To_6'>6</a>
          </td>
          <td colspan='1'>
            <a href='javascript:void(0);' id='Roster_Slot_2_Move_To_7'>x</a>
          </td>
        </tr>
      </tbody>

      <tbody>
        <tr>
          <td colspan='2' style='width: 72px;'>
            <img
              id='Roster_Slot_3_Icon'
              src='<?= DOMAIN_SPRITES; ?>/Pokemon/Sprites/0_mini.png'
            />
          </td>
          <td colspan='5' style='width: 200px;'>
            <b id='Roster_Slot_3_Display_Name'>
              Empty
            </b>
            <br />
            <span id='Roster_Slot_3_Level'></span>
          </td>
        </tr>
        <tr>
          <td colspan='1'>
            <a href='javascript:void(0);' id='Roster_Slot_3_Move_To_1'>1</a>
          </td>
          <td colspan='1'>
            <a href='javascript:void(0);' id='Roster_Slot_3_Move_To_2'>2</a>
          </td>
          <td colspan='1'>
            <a href='javascript:void(0);' id='Roster_Slot_3_Move_To_3'>3</a>
          </td>
          <td colspan='1'>
            <a href='javascript:void(0);' id='Roster_Slot_3_Move_To_4'>4</a>
          </td>
          <td colspan='1'>
            <a href='javascript:void(0);' id='Roster_Slot_3_Move_To_5'>5</a>
          </td>
          <td colspan='1'>
            <a href='javascript:void(0);' id='Roster_Slot_3_Move_To_6'>6</a>
          </td>
          <td colspan='1'>
            <a href='javascript:void(0);' id='Roster_Slot_3_Move_To_7'>x</a>
          </td>
        </tr>
      </tbody>

      <tbody>
        <tr>
          <td colspan='2' style='width: 72px;'>
            <img
              id='Roster_Slot_4_Icon'
              src='<?= DOMAIN_SPRITES; ?>/Pokemon/Sprites/0_mini.png'
            />
          </td>
          <td colspan='5' style='width: 200px;'>
            <b id='Roster_Slot_4_Display_Name'>
              Empty
            </b>
            <br />
            <span id='Roster_Slot_4_Level'></span>
          </td>
        </tr>
        <tr>
          <td colspan='1'>
            <a href='javascript:void(0);' id='Roster_Slot_4_Move_To_1'>1</a>
          </td>
          <td colspan='1'>
            <a href='javascript:void(0);' id='Roster_Slot_4_Move_To_2'>2</a>
          </td>
          <td colspan='1'>
            <a href='javascript:void(0);' id='Roster_Slot_4_Move_To_3'>3</a>
          </td>
          <td colspan='1'>
            <a href='javascript:void(0);' id='Roster_Slot_4_Move_To_4'>4</a>
          </td>
          <td colspan='1'>
            <a href='javascript:void(0);' id='Roster_Slot_4_Move_To_5'>5</a>
          </td>
          <td colspan='1'>
            <a href='javascript:void(0);' id='Roster_Slot_4_Move_To_6'>6</a>
          </td>
          <td colspan='1'>
            <a href='javascript:void(0);' id='Roster_Slot_4_Move_To_7'>x</a>
          </td>
        </tr>
      </tbody>

      <tbody>
        <tr>
          <td colspan='2' style='width: 72px;'>
            <img
              id='Roster_Slot_5_Icon'
              src='<?= DOMAIN_SPRITES; ?>/Pokemon/Sprites/0_mini.png'
            />
          </td>
          <td colspan='5' style='width: 200px;'>
            <b id='Roster_Slot_5_Display_Name'>
              Empty
            </b>
            <br />
            <span id='Roster_Slot_5_Level'></span>
          </td>
        </tr>
        <tr>
          <td colspan='1'>
            <a href='javascript:void(0);' id='Roster_Slot_5_Move_To_1'>1</a>
          </td>
          <td colspan='1'>
            <a href='javascript:void(0);' id='Roster_Slot_5_Move_To_2'>2</a>
          </td>
          <td colspan='1'>
            <a href='javascript:void(0);' id='Roster_Slot_5_Move_To_3'>3</a>
          </td>
          <td colspan='1'>
            <a href='javascript:void(0);' id='Roster_Slot_5_Move_To_4'>4</a>
          </td>
          <td colspan='1'>
            <a href='javascript:void(0);' id='Roster_Slot_5_Move_To_5'>5</a>
          </td>
          <td colspan='1'>
            <a href='javascript:void(0);' id='Roster_Slot_5_Move_To_6'>6</a>
          </td>
          <td colspan='1'>
            <a href='javascript:void(0);' id='Roster_Slot_5_Move_To_7'>x</a>
          </td>
        </tr>
      </tbody>

      <tbody>
        <tr>
          <td colspan='2' style='width: 72px;'>
            <img
              id='Roster_Slot_6_Icon'
              src='<?= DOMAIN_SPRITES; ?>/Pokemon/Sprites/0_mini.png'
            />
          </td>
          <td colspan='5' style='width: 200px;'>
            <b id='Roster_Slot_6_Display_Name'>
              Empty
            </b>
            <br />
            <span id='Roster_Slot_6_Level'></span>
          </td>
        </tr>
        <tr>
          <td colspan='1'>
            <a href='javascript:void(0);' id='Roster_Slot_6_Move_To_1'>1</a>
          </td>
          <td colspan='1'>
            <a href='javascript:void(0);' id='Roster_Slot_6_Move_To_2'>2</a>
          </td>
          <td colspan='1'>
            <a href='javascript:void(0);' id='Roster_Slot_6_Move_To_3'>3</a>
          </td>
          <td colspan='1'>
            <a href='javascript:void(0);' id='Roster_Slot_6_Move_To_4'>4</a>
          </td>
          <td colspan='1'>
            <a href='javascript:void(0);' id='Roster_Slot_6_Move_To_5'>5</a>
          </td>
          <td colspan='1'>
            <a href='javascript:void(0);' id='Roster_Slot_6_Move_To_6'>6</a>
          </td>
          <td colspan='1'>
            <a href='javascript:void(0);' id='Roster_Slot_6_Move_To_7'>x</a>
          </td>
        </tr>
      </tbody>
    </tbody>
  </table>

  <div style='display: flex; flex-wrap: wrap; gap: 10px; width: 550px;'>
    <table class='border-gradient' style='min-height: 215px; max-height: 215px; width: 550px;'>
      <thead>
        <tr>
          <th colspan='21'>
            Box
          </th>
        </tr>
      </thead>

      <tbody id='Box_Pagination' style='height: 30px;'>
        <tr>
          <td colspan='21'>
            Loading
          </td>
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

    <table class='border-gradient' style='min-height: 215px; max-height: 215px; width: 550px;'>
      <tbody id='Pokemon_Preview'>
        <tr>
          <td>
            Click on a Pok&eacute;mon to view more information.
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</div>
