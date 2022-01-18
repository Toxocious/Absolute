<?php
	require_once $_SERVER['DOCUMENT_ROOT'] . '/core/required/layout_top.php';
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

  require_once 'functions/online_list.php';
?>

<div class='panel content'>
	<div class='head'>Staff Panel</div>
	<div class='body' id='Staff_Content' style='padding: 5px'>
    <br />
    <h3>
      Welcome, <?= $User_Username = $User_Class->DisplayUsername($User_Data['ID'], true, false, true); ?>
    </h3>
    <br />

    <?php
      $Online_Users = GetOnlineUsers();
      echo $Online_Users;
    ?>
	</div>
</div>

<script type='text/javascript' src='js/ajax_functions.js'></script>
<script type='text/javascript' src='js/sprite_list.js'></script>

<?php
	require_once $_SERVER['DOCUMENT_ROOT'] . '/core/required/layout_bottom.php';
