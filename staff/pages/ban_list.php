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

  require_once $_SERVER['DOCUMENT_ROOT'] . '/staff/functions/ban.php';
?>

<div style='display: flex; flex-wrap: wrap; justify-content: center;'>
  <div style='flex-basis: 100%; width: 100%;'>
    <h3>Banned Users</h3>
  </div>

  <div id='Ban_AJAX'></div>

  <div style='display: flex; flex-wrap: wrap; gap: 10px;' id='Banned_User_List'>
    <?php
      echo GetBannedUsers();
    ?>
  </div>
</div>
