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
   * Render the map.
   */
  echo $Map->Render();
