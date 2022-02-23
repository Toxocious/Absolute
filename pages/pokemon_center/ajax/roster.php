<?php
  require_once $_SERVER['DOCUMENT_ROOT'] . '/core/required/session.php';
  require_once $_SERVER['DOCUMENT_ROOT'] . '/pages/pokemon_center/functions/roster.php';

  if ( !empty($_GET['Action']) && in_array($_GET['Action'], ['Get_Roster', 'Get_Box', 'Move_Pokemon', 'Preview_Pokemon']) )
    $Action = Purify($_GET['Action']);

  if ( empty($Action) )
  {
    echo json_encode([
      'Success' => false,
      'Message' => 'An invalid action was selected.',
    ]);

    exit;
  }

  $Page = 1;
  if ( !empty($_GET['Page']) )
    $Page = Purify($_GET['Page']);

  $Pokemon_ID = null;
  if ( !empty($_GET['Pokemon_ID']) )
    $Pokemon_ID = Purify($_GET['Pokemon_ID']);

  $Slot = 1;
  if ( !empty($_GET['Slot']) )
    $Slot = Purify($_GET['Slot']);

  switch ( $Action )
  {
    case 'Get_Box':
      echo json_encode([
        GetBoxedPokemon($Page)
      ]);
      break;

    case 'Get_Roster':
      echo json_encode([
        GetRosterJSON()
      ]);
      break;

    case 'Move_Pokemon':
      $Move_Pokemon = MovePokemon($Pokemon_ID, $Slot);

      echo json_encode([
        'Success' => $Move_Pokemon['Type'],
        'Message' => $Move_Pokemon['Message']
      ]);
      break;

    case 'Preview_Pokemon':
      echo json_encode([
        GetPokemonPreview($Pokemon_ID)
      ]);
      break;
  }
