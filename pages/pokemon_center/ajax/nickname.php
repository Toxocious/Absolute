<?php
  require_once $_SERVER['DOCUMENT_ROOT'] . '/core/required/session.php';
  require_once $_SERVER['DOCUMENT_ROOT'] . '/pages/pokemon_center/functions/roster.php';
  require_once $_SERVER['DOCUMENT_ROOT'] . '/pages/pokemon_center/functions/nickname.php';

  if ( !empty($_GET['Action']) && in_array($_GET['Action'], ['Get_Roster', 'Update_Nickname']) )
    $Action = Purify($_GET['Action']);

  if ( empty($Action) )
  {
    echo json_encode([
      'Success' => false,
      'Message' => 'An invalid action was selected.',
    ]);

    exit;
  }

  $Pokemon_ID = null;
  if ( !empty($_GET['Pokemon_ID']) )
    $Pokemon_ID = Purify($_GET['Pokemon_ID']);

  $Nickname = null;
  if ( !empty($_GET['Nickname']) )
    $Nickname = Purify($_GET['Nickname']);

  switch ( $Action )
  {
    case 'Get_Roster':
      echo json_encode([
        'Roster_Pokemon' => GetRosterJSON()
      ]);
      break;

    case 'Update_Nickname':
      $Update_Nickname = UpdateNickname($Pokemon_ID, $Nickname);

      echo json_encode([
        'Success' => $Update_Nickname['Success'],
        'Message' => $Update_Nickname['Message']
      ]);
      break;
  }
