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

  require_once $_SERVER['DOCUMENT_ROOT'] . '/staff/functions/maintenance.php';
?>

<div style='display: flex; flex-wrap: wrap; gap: 10px; justify-content: center;'>
  <div style='flex-basis: 100%; width: 100%;'>
    <h3>Game Maintenance</h3>
  </div>

  <div class='description'>
    Pages may be toggled offline and online here, whether for maintenance purposes, or if a game-breaking bug was found.
    <br />
    Click on a table cell to toggle maintenance mode for the page.
  </div>

  <div id='Maintenance_AJAX'></div>
  <div id='Maintenance_Table'>
    <?php
      echo ShowMaintenanceTable();
    ?>
  </div>
</div>
