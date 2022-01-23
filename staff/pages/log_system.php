<?php
  require_once $_SERVER['DOCUMENT_ROOT'] . '/core/required/session.php';
  require_once $_SERVER['DOCUMENT_ROOT'] . '/staff/functions/auth.php';

  if ( !AuthorizeUser() )
  {
    echo "
      <div class='panel content'>
        <div class='head'>Staff Panel</div>
        <div class='body' style='padding: 5px'>
          You aren't authorized to be here.
        </div>
      </div>
    ";

    exit;
  }
?>

<div style='display: flex; flex-wrap: wrap; justify-content: center;'>
  <div style='flex-basis: 100%; width: 100%;'>
    <h3>Log System</h3>
  </div>

  <form name='log_system' id='log_system' onsubmit='event.preventDefault(); return ShowLogs(this);'>
    <table class='border-gradient' style='width: 400px;'>
      <tbody>
        <tr>
          <td colspan='1' style='width: 50%;'>
            <b>Username / ID</b>
          </td>
          <td colspan='1' style='width: 50%;'>
            <input type='text' name='log_user' />
          </td>
        </tr>
      </tbody>

      <thead>
        <tr>
          <td colspan='2'>
            <b>Log Options</b>
          </td>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>
            <b>Log Limit</b>
          </td>
          <td>
            <input type='text' value='2500' name='log_limit' />
            <br />
            <i>Leave blank for no limit.</i>
          </td>
        </tr>
      </tbody>

      <tbody>
        <tr>
          <td>
            <b>Log Type</b>
          </td>
          <td>
            <select name='log_type'>
              <option value='Battle'>Battle</option>
            </select>
          </td>
        </tr>
      </tbody>

      <tbody>
        <tr>
          <td colspan='2'>
            <button>Show Logs</button>
          </td>
        </tr>
      </tbody>
    </table>
  </form>

  <div id='Log_AJAX' style='width: 100%;'></div>
</div>
