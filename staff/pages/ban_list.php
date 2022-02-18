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

  require_once $_SERVER['DOCUMENT_ROOT'] . '/staff/functions/ban.php';

  $Banned_Users = GetBannedUsers();
?>

<div style='display: flex; flex-wrap: wrap; justify-content: center;'>
  <div style='flex-basis: 100%; width: 100%;'>
    <h3>Banned Users</h3>
  </div>

  <div style='flex-basis: 100%; width: 100%;'>
    <h4>There are <?= number_format(count($Banned_Users)); ?> banned users.</h3>
  </div>

  <div id='Ban_AJAX'></div>

  <div style='display: flex; flex-wrap: wrap; gap: 10px;' id='Banned_User_List'>
    <?php
      echo ShowBannedUsers($Banned_Users);
    ?>
  </div>
</div>
