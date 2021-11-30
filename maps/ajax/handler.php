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
   */
  if ( isset($_GET['Stats']) )
  {
    header('Content-Type: application/json');
    echo json_encode($Map->Stats());
    exit;
  }

  /**
   * Perform some server-side validation.
   */
  if ( isset($_POST['Action']) )
  {
    $Action = Purify($_POST['Action']);

    switch ( $Action )
    {
      /**
       * Update the player's map coordinates.
       */
      case 'Position':
        $x = Purify(floor($_POST['x']));
        $y = Purify(floor($_POST['y'])) + 1;
        $z = Purify($_POST['z']);

        $Map->Player->SetPosition($x, $y, $z);
        break;

      default:
        break;
    }

    exit;
  }

  /**
   * Render the map.
   */
  echo $Map->Render();
