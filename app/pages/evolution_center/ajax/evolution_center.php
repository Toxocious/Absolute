<?php
  require_once $_SERVER['DOCUMENT_ROOT'] . '/pages/evolution_center/functions/display_pokemon.php';
  require_once $_SERVER['DOCUMENT_ROOT'] . '/pages/evolution_center/functions/handle_evolution.php';

  if ( !empty($_GET['Action']) && in_array($_GET['Action'], ['Display_Pokemon', 'Display_Evolutions', 'Handle_Evolution']) )
    $Action = Purify($_GET['Action']);

  if ( empty($Action) )
  {
    echo json_encode([
      'Success' => false,
      'Message' => 'An invalid action was selected.',
    ]);

    exit;
  }

  $Selected_Pokemon = null;
  if ( isset($_GET['Pokemon_ID']) )
    $Selected_Pokemon = Purify($_GET['Pokemon_ID']);

  $Evolution_ID = null;
  if ( isset($_GET['Evolution_ID']) )
    $Evolution_ID = Purify($_GET['Evolution_ID']);

  $Evolution_Alt_ID = null;
  if ( isset($_GET['Evolution_Alt_ID']) )
    $Evolution_Alt_ID = (integer) Purify($_GET['Evolution_Alt_ID'], 'integer');

  switch ( $Action )
  {
    case 'Display_Pokemon':
      echo json_encode([
        'Roster_Pokemon' => DisplayRoster()
      ]);
      break;

    case 'Display_Evolutions':
      echo json_encode([
        'Evolution_Data' => DisplayEvolutions($Selected_Pokemon)
      ]);
      break;

    case 'Handle_Evolution':
      echo json_encode([
        'Evolution_Status' => HandleEvolution($Selected_Pokemon, $Evolution_ID, $Evolution_Alt_ID)
      ]);
      break;
  }
