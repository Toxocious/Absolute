<?php
  require_once $_SERVER['DOCUMENT_ROOT'] . '/core/required/session.php';
  require_once $_SERVER['DOCUMENT_ROOT'] . '/pages/pokemon_center/functions/release.php';

  if ( !empty($_GET['Action']) && in_array($_GET['Action'], ['Get_Releasable_Pokemon', 'Process_Selected_Pokemon', 'Release_Pokemon']) )
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
  if ( !empty($_GET['Selected_Pokemon']) )
    $Selected_Pokemon = Purify($_GET['Selected_Pokemon']);

  switch ( $Action )
  {
    case 'Get_Releasable_Pokemon':
      echo json_encode([
        GetReleasablePokemon()
      ]);
      break;

    case 'Process_Selected_Pokemon':
      $Process_Selected_Pokemon = ProcessSelectedPokemon($Selected_Pokemon);

      echo json_encode([
        'Success' => !empty($Process_Selected_Pokemon['Success']) ? $Process_Selected_Pokemon['Success'] : null,
        'Message' => !empty($Process_Selected_Pokemon['Message']) ? $Process_Selected_Pokemon['Message'] : null,
        'Pokemon' => !empty($Process_Selected_Pokemon['Pokemon']) ? $Process_Selected_Pokemon['Pokemon'] : null,
      ]);
      break;

    case 'Release_Pokemon':
    $Release_Pokemon = FinalizeRelease($Selected_Pokemon);

    echo json_encode([
      'Success' => $Release_Pokemon['Success'],
      'Message' => $Release_Pokemon['Message'],
    ]);
    break;
  }
