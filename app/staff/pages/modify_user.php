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

<div style='display: flex; flex-wrap: wrap; gap: 10px; justify-content: center;'>
  <div style='flex-basis: 100%; width: 100%;'>
    <h3>Modify User</h3>
  </div>

  <table class='border-gradient' style='width: 300px;'>
    <tbody>
      <tr>
        <td>
          <input type='text' name='Modify_User_Param' />
        </td>
      </tr>
      <tr>
        <td>
          <button onclick='ShowUser();'>Select User</button>
        </td>
      </tr>
    </tbody>
  </table>

  <div id='Modify_User_AJAX'></div>
  <div id='Modify_User_Table'></div>
</div>
