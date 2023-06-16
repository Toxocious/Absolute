<?php
  require_once $_SERVER['DOCUMENT_ROOT'] . '/core/required/session.php';
?>

<div id='Pokemon_Center_Nickname_AJAX'></div>

<div style='display: flex; flex-wrap: wrap; gap: 10px;'>
  <table class='border-gradient' style='width: 280px;'>
    <tbody>
      <tr>
        <td style='width: 120px;'>
          <img id='Roster_Slot_1_Sprite' src='<?= DOMAIN_SPRITES; ?>/Pokemon/Sprites/0.png' />
        </td>

        <td>
          <b id='Roster_Slot_1_Nickname'>Empty</b>
          <hr class='faded' />
          <input type='text' name='Roster_Slot_1_Nick_Input' style='width: 150px;' />
          <hr class='faded' />
          <button id='Roster_Slot_1_Button' style='width: 160px;' disabled>
            Update Nickname
          </button>
        </td>
      </tr>
    </tbody>
  </table>

  <table class='border-gradient' style='width: 280px;'>
    <tbody>
      <tr>
        <td style='width: 120px;'>
          <img id='Roster_Slot_2_Sprite' src='<?= DOMAIN_SPRITES; ?>/Pokemon/Sprites/0.png' />
        </td>

        <td>
          <b id='Roster_Slot_2_Nickname'>Empty</b>
          <hr class='faded' />
          <input type='text' name='Roster_Slot_2_Nick_Input' style='width: 150px;' />
          <hr class='faded' />
          <button id='Roster_Slot_2_Button' style='width: 160px;' disabled>
            Update Nickname
          </button>
        </td>
      </tr>
    </tbody>
  </table>

  <table class='border-gradient' style='width: 280px;'>
    <tbody>
      <tr>
        <td style='width: 120px;'>
          <img id='Roster_Slot_3_Sprite' src='<?= DOMAIN_SPRITES; ?>/Pokemon/Sprites/0.png' />
        </td>

        <td>
          <b id='Roster_Slot_3_Nickname'>Empty</b>
          <hr class='faded' />
          <input type='text' name='Roster_Slot_3_Nick_Input' style='width: 150px;' />
          <hr class='faded' />
          <button id='Roster_Slot_3_Button' style='width: 160px;' disabled>
            Update Nickname
          </button>
        </td>
      </tr>
    </tbody>
  </table>

  <table class='border-gradient' style='width: 280px;'>
    <tbody>
      <tr>
        <td style='width: 120px;'>
          <img id='Roster_Slot_4_Sprite' src='<?= DOMAIN_SPRITES; ?>/Pokemon/Sprites/0.png' />
        </td>

        <td>
          <b id='Roster_Slot_4_Nickname'>Empty</b>
          <hr class='faded' />
          <input type='text' name='Roster_Slot_4_Nick_Input' style='width: 150px;' />
          <hr class='faded' />
          <button id='Roster_Slot_4_Button' style='width: 160px;' disabled>
            Update Nickname
          </button>
        </td>
      </tr>
    </tbody>
  </table>

  <table class='border-gradient' style='width: 280px;'>
    <tbody>
      <tr>
        <td style='width: 120px;'>
          <img id='Roster_Slot_5_Sprite' src='<?= DOMAIN_SPRITES; ?>/Pokemon/Sprites/0.png' />
        </td>

        <td>
          <b id='Roster_Slot_5_Nickname'>Empty</b>
          <hr class='faded' />
          <input type='text' name='Roster_Slot_5_Nick_Input' style='width: 150px;' />
          <hr class='faded' />
          <button id='Roster_Slot_5_Button' style='width: 160px;' disabled>
            Update Nickname
          </button>
        </td>
      </tr>
    </tbody>
  </table>

  <table class='border-gradient' style='width: 280px;'>
    <tbody>
      <tr>
        <td style='width: 120px;'>
          <img id='Roster_Slot_6_Sprite' src='<?= DOMAIN_SPRITES; ?>/Pokemon/Sprites/0.png' />
        </td>

        <td>
          <b id='Roster_Slot_6_Nickname'>Empty</b>
          <hr class='faded' />
          <input type='text' name='Roster_Slot_6_Nick_Input' style='width: 150px;' />
          <hr class='faded' />
          <button id='Roster_Slot_6_Button' style='width: 160px;' disabled>
            Update Nickname
          </button>
        </td>
      </tr>
    </tbody>
  </table>
</div>
