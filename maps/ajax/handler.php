<?php
  require_once '../../core/required/session.php';

  spl_autoload_register(function($Class)
  {
    $Map_Directory = dirname(__DIR__, 1);
    $Class = strtolower($Class);

    if (file_exists($Map_Directory . "/classes/{$Class}.php"))
      require_once $Map_Directory . "/classes/{$Class}.php";
  });

  $Player = Player::GetInstance();
  $Map = new Map();

  /**
   * Handle loading.
   */
  if ( isset($_GET['Load']) )
  {
    header('Content-Type: application/json');
    echo json_encode($Map->Load());
    exit;
  }

  /**
   * Fetch some of the player's map stats.
   *  - Map Level
   *  - Exp To Next Map Level
   *  - Shiny Odds
   *    - 1 / (4096 - Map Level)
   *    - Capped at 1 / 2048 (0.00048828125%)
   *  - Next Encounter In Steps
   */
  if ( isset($_GET['Stats']) )
  {
    header('Content-Type: application/json');
    echo json_encode($Map->Stats());
    exit;
  }
  /**
   * Render the map.
   */
  echo $Map->Render();
