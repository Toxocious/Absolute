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

  require_once $_SERVER['DOCUMENT_ROOT'] . '/staff/functions/staff_logs.php';
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


<?php
  if ( CheckUserPermission('Staff') )
  {
    echo "<script type='text/javascript' src='js/ajax_functions.js'></script>";
  }

  if ( CheckUserPermission('Graphics') )
  {
    echo "<script type='text/javascript' src='js/sprite_list.js'></script>";
  }

  if ( CheckUserPermission('Logs') )
  {
    echo "<script type='text/javascript' src='js/log_system.js'></script>";
  }

  if ( CheckUserPermission('Reports') )
  {
    echo "<script type='text/javascript' src='js/report.js'></script>";
  }

  if ( CheckUserPermission('Bans') )
  {
    echo "<script type='text/javascript' src='js/ban.js'></script>";
  }

  if ( CheckUserPermission('User Management') )
  {
    echo "<script type='text/javascript' src='js/modify_user.js'></script>";
  }

  if ( CheckUserPermission('Pokemon Management') )
  {
    echo "<script type='text/javascript' src='js/modify_pokemon.js'></script>";
  }

  if ( CheckUserPermission('Transfer Pokemon') )
  {
    echo "<script type='text/javascript' src='js/transfer_pokemon.js'></script>";
  }

  if ( CheckUserPermission('Maintenance') )
  {
    echo "<script type='text/javascript' src='js/maintenance.js'></script>";
  }

  if ( CheckUserPermission('Set Obtainables') )
  {
    echo "<script type='text/javascript' src='js/set_pokemon.js'></script>";
    echo "<script type='text/javascript' src='js/set_items.js'></script>";
  }

  if ( CheckUserPermission('Database Edits') )
  {
    echo "<script type='text/javascript' src='js/edit_pokedex.js'></script>";
    echo "<script type='text/javascript' src='js/edit_moves.js'></script>";
    echo "<script type='text/javascript' src='js/edit_items.js'></script>";
  }

  if ( CheckUserPermission('Spawn') )
  {
    echo "<script type='text/javascript' src='js/spawn_pokemon.js'></script>";
    echo "<script type='text/javascript' src='js/spawn_items.js'></script>";
  }

  if ( CheckUserPermission('Staff Management') )
  {
    echo "<script type='text/javascript' src='js/manage_staff.js'></script>";
  }

  require_once $_SERVER['DOCUMENT_ROOT'] . '/core/required/layout_bottom.php';
