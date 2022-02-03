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
      Welcome, <?= $User_Class->DisplayUsername($User_Data['ID'], true, false, true); ?>
    </h3>
    <br />

    <?php
      echo GetOnlineUsers();
    ?>
	</div>
</div>

<script type='text/javascript' src='js/ajax_functions.js'></script>
<script type='text/javascript' src='js/sprite_list.js'></script>
<script type='text/javascript' src='js/log_system.js'></script>
<script type='text/javascript' src='js/report.js'></script>
<script type='text/javascript' src='js/ban.js'></script>
<script type='text/javascript' src='js/modify_user.js'></script>
<script type='text/javascript' src='js/modify_pokemon.js'></script>
<script type='text/javascript' src='js/transfer_pokemon.js'></script>

<?php
	require_once $_SERVER['DOCUMENT_ROOT'] . '/core/required/layout_bottom.php';
