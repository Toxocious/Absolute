<?php
  require_once $_SERVER['DOCUMENT_ROOT'] . '/core/required/session.php';
  require_once $_SERVER['DOCUMENT_ROOT'] . '/staff/functions/auth.php';

  if ( !AuthorizeUser() )
  {
    echo "
      <div style='padding: 5px;'>
        You aren't authorized to be here.
      </div>
    ";

    exit;
  }
?>

<div style='display: flex; flex-wrap: wrap; justify-content: center;'>
  <div style='flex-basis: 100%; width: 100%;'>
    <h3>Ban User</h3>
  </div>

  <div id='Ban_AJAX'></div>

  <form name='ban_user_form' id='ban_user_form' onsubmit='event.preventDefault(); return BanUser(this);'>
    <table class='border-gradient' style='width: 600px;'>
      <thead>
        <tr>
          <th colspan='2'>Selected User</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td colspan='2' style=' padding: 5px; width: 100%;'>
            <b>Username / ID</b>
            <br />
            <input type='text' name='User_Value' />
          </td>
        </tr>
      </tbody>

      <thead>
        <tr>
          <th colspan='2'>Ban Options</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td colspan='1' style='width: 50%;'>
            <b>Ban Type</b>
          </td>
          <td colspan='1' style='width: 50%;'>
            <select name='Ban_Type'>
              <option value='RPG'>RPG</option>
              <option value='Chat'>Chat</option>
            </select>
          </td>
        </tr>
        <tr>
          <td colspan='1' style='width: 50%;'>
            <b>Ban Until</b>
            <br />
            If left blank, the ban lasts undefinitely.
          </td>
          <td colspan='1' style='width: 50%;'>
            <input type='text' name='Unban_Date' placeholder='Date Format: dd/mm/yy' />
          </td>
        </tr>
      </tbody>

      <thead>
        <tr>
          <th colspan='2'>Ban Reason</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td colspan='2' style='width: 100%;'>
            <textarea rows='7' cols='70' name='Ban_Reason'></textarea>
          </td>
        </tr>
      </tbody>

      <thead>
        <tr>
          <th colspan='2'>Staff Notes</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td colspan='2' style='width: 100%;'>
            <textarea rows='7' cols='70' name='Staff_Notes'></textarea>
          </td>
        </tr>
      </tbody>

      <tbody>
        <tr>
          <td colspan='2'>
            <button>
              Ban User
            </button>
          </td>
        </tr>
      </tbody>
    </table>
  </form>
</div>
