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
   * Generate an encounter for the player.
   */
  if ( isset($_GET['Encounter']) )
  {
    header('Content-Type: application/json');

    $Steps_Till_Encounter = $Map->Player->GetStepsTillEncounter();
    if ( $Steps_Till_Encounter !== -1 )
    {
      echo json_encode([ 'Generated_Encounter' => 'Invalid Encounter' ]);
    }
    else
    {
      $Encounter = Encounter::Generate($Map->Player->GetMap(), $Map->Player->GetMapLevelAndExp()['Map_Level']);
      echo json_encode([ 'Generated_Encounter' => $Encounter ]);
    }

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
       * Handle object interaction.
       */
      case 'Interact':
        $x = Purify(floor($_POST['x']));
        $y = Purify(floor($_POST['y']));
        $z = Purify($_POST['z']);

        $Interaction_Check = $Map->Player->CheckInteraction($x, $y, $z);

        header('Content-Type: application/json');
        echo json_encode($Interaction_Check);
        break;

      /**
       * Handle player movement.
       *  - Update player's map coordinates.
       *  - Check for encounters.
       */
      case 'Movement':
        $x = Purify(floor($_POST['x']));
        $y = Purify(floor($_POST['y'])) + 1;
        $z = Purify($_POST['z']);

        $Map->Player->SetPosition($x, $y, $z);
        User::UpdateStat($User_Data['ID'], 'Map_Steps_Taken', 1);

        $Encounter_Tile = Purify($_POST['Encounter_Tile']);
        if ( isset($Encounter_Tile) && $Encounter_Tile === 'true' )
          $Map->Player->SetStepsTillEncounter();

        header('Content-Type: application/json');
        echo json_encode($Map->Stats());
        break;

      /**
       * Handle map warping.
       */
      case 'Warp':
        $x = Purify(floor($_POST['x']));
        $y = Purify(floor($_POST['y']));
        $z = Purify($_POST['z']);

        $Warp_Data = false;
        $Warp_Tile = Purify($_POST['Warp_Tile']);
        if ( isset($Warp_Tile) && $Warp_Tile === 'true' )
          $Warp_Data = $Map->Player->ProcessWarp($x, $y, $z, $Warp_Tile);

        header('Content-Type: application/json');
        echo json_encode($Warp_Data);
        break;

      /**
       * Catch the active encounter.
       */
      case 'Catch':
        $Catch_Encounter = Encounter::Catch();
        header('Content-Type: application/json');
        echo json_encode($Catch_Encounter);
        break;

      /**
       * Release the active encounter.
       */
      case 'Release':
        $Release_Encounter = Encounter::Release();
        header('Content-Type: application/json');
        echo json_encode($Release_Encounter);
        break;

      /**
       * Run from the active encounter.
       */
      case 'Run':
        $Run_From_Encounter = Encounter::Run();
        header('Content-Type: application/json');
        echo json_encode($Run_From_Encounter);
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
