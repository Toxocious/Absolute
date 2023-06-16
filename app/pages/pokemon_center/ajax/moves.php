<?php
  require_once $_SERVER['DOCUMENT_ROOT'] . '/core/required/session.php';
  require_once $_SERVER['DOCUMENT_ROOT'] . '/pages/pokemon_center/functions/roster.php';
  require_once $_SERVER['DOCUMENT_ROOT'] . '/pages/pokemon_center/functions/moves.php';

  if ( !empty($_GET['Action']) && in_array($_GET['Action'], ['Get_Roster', 'Select_Move', 'Update_Move']) )
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

  $Move_Slot = 1;
  if ( !empty($_GET['Move_Slot']) && in_array($Move_Slot, [1, 2, 3, 4]) )
    $Move_Slot = Purify($_GET['Move_Slot']);

  $Move_ID = null;
  if ( !empty($_GET['Move_ID']) )
    $Move_ID = Purify($_GET['Move_ID']);

  if ( !empty($Move_ID) )
  {
    try
    {
      $Check_Move_Existence = $PDO->prepare("
        SELECT `ID`
        FROM `moves`
        WHERE `ID` = ?
        LIMIT 1
      ");
      $Check_Move_Existence->execute([ $Move_ID ]);
      $Check_Move_Existence->setFetchMode(PDO::FETCH_ASSOC);
      $Move_Existence = $Check_Move_Existence->fetch();
    }
    catch ( PDOException $e )
    {
      HandleError($e);
    }

    if ( empty($Move_Existence) )
    {
      echo json_encode([
        'Success' => false,
        'Message' => 'An invalid move was selected.'
      ]);

      exit;
    }
  }

  switch ( $Action )
  {
    case 'Get_Roster':
      echo json_encode([
        GetRosterJSON()
      ]);
      break;

    case 'Select_Move':
      echo json_encode([
        'Dropdown_HTML' => GetMoveDropdown($Pokemon_ID, $Move_Slot)
      ]);
      break;

    case 'Update_Move':
      $Update_Move = UpdatePokemonMove($Pokemon_ID, $Move_Slot, $Move_ID);

      echo json_encode([
        'Success' => $Update_Move['Success'],
        'Message' => $Update_Move['Message'],
      ]);
      break;
  }
