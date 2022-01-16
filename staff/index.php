<?php
	require_once '../core/required/layout_top.php';
	require_once 'auth/power_level.php';

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

<?php
	require_once 'required/ajax_script.php';
	require_once '../core/required/layout_bottom.php';
