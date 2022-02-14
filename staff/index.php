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

  require_once $_SERVER['DOCUMENT_ROOT'] . '/staff/functions/online_list.php';
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

<?php
  switch ( $User_Data['Power'] )
  {
    case 7:
    case 6:
      echo "<script type='text/javascript' src='js/edit_pokedex.js'></script>";
      echo "<script type='text/javascript' src='js/edit_items.js'></script>";

    case 5:
      echo "<script type='text/javascript' src='js/modify_user.js'></script>";
      echo "<script type='text/javascript' src='js/modify_pokemon.js'></script>";
      echo "<script type='text/javascript' src='js/transfer_pokemon.js'></script>";
      echo "<script type='text/javascript' src='js/set_pokemon.js'></script>";
      echo "<script type='text/javascript' src='js/set_items.js'></script>";

    case 4:
      echo "<script type='text/javascript' src='js/maintenance.js'></script>";

    case 3:
      echo "<script type='text/javascript' src='js/log_system.js'></script>";
      echo "<script type='text/javascript' src='js/report.js'></script>";
      echo "<script type='text/javascript' src='js/ban.js'></script>";

    case 2:
      echo "<script type='text/javascript' src='js/sprite_list.js'></script>";
      break;
  }

  require_once $_SERVER['DOCUMENT_ROOT'] . '/core/required/layout_bottom.php';
