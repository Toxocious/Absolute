<?php
  require_once $_SERVER['DOCUMENT_ROOT'] . '/core/required/session.php';
  require_once $_SERVER['DOCUMENT_ROOT'] . '/staff/functions/modify_pokemon.php';

  if ( !empty($_GET['Pokemon_Value']) )
    $Pokemon_Value = Purify($_GET['Pokemon_Value']);

  $Pokemon_Info = $Poke_Class->FetchPokemonData($Pokemon_Value);

  if ( empty($Pokemon_Value) || !$Pokemon_Info )
  {
    echo json_encode([
      'Success' => false,
      'Message' => "The Pok&eacute;mon you are trying to modify doesn't exist.",
    ]);

    exit;
  }

  if ( !empty($_GET['Pokemon_Action']) && in_array($_GET['Pokemon_Action'], ['Delete', 'Freeze', 'Move_List', 'Show', 'Update', 'Update_Move']) )
    $Pokemon_Action = Purify($_GET['Pokemon_Action']);

  if ( empty($Pokemon_Action) )
  {
    echo json_encode([
      'Success' => false,
      'Message' => 'An invalid action was selected.',
    ]);

    exit;
  }

  $Pokemon_Frozen_Status = 0;
  if ( !empty($_GET['Pokemon_Frozen_Status']) )
    $Pokemon_Frozen_Status = Purify($_GET['Pokemon_Frozen_Status']);

  $Pokemon_Move_Slot = 1;
  if ( !empty($_GET['Pokemon_Move_Slot']) )
    $Pokemon_Move_Slot = Purify($_GET['Pokemon_Move_Slot']);

  $Pokemon_Move_Value = 1;
  if ( !empty($_GET['Pokemon_Move_Value']) )
    $Pokemon_Move_Value = Purify($_GET['Pokemon_Move_Value']);

  try
  {
    $Check_Pokemon_Existence = $PDO->prepare("
      SELECT `ID`
      FROM `pokemon`
      WHERE `ID` = ?
      LIMIT 1
    ");
    $Check_Pokemon_Existence->execute([
      $Pokemon_Value
    ]);
    $Check_Pokemon_Existence->setFetchMode(PDO::FETCH_ASSOC);
    $Pokemon_Existence = $Check_Pokemon_Existence->fetch();
  }
  catch ( PDOException $e )
  {
    HandleError($e);
  }

  if ( empty($Pokemon_Existence) )
  {
    echo json_encode([
      'Success' => false,
      'Message' => 'This Pok&eacute;mon does not exist.',
    ]);

    exit;
  }

  switch ( $Pokemon_Action )
  {
    case 'Delete':
      $Delete_Pokemon = DeletePokemon($Pokemon_Existence['ID']);

      echo json_encode([
        'Success' => $Delete_Pokemon['Success'],
        'Message' => $Delete_Pokemon['Message'],
      ]);
      break;

    case 'Freeze':
      $Toggle_Pokemon_Freeze = ToggleFreeze($Pokemon_Existence['ID'], $Pokemon_Frozen_Status);

      echo json_encode([
        'Success' => $Toggle_Pokemon_Freeze['Success'],
        'Message' => $Toggle_Pokemon_Freeze['Message'],
        'Modification_Table' => $Toggle_Pokemon_Freeze['Modification_Table'],
      ]);
      break;

    case 'Move_List':
      $Move_List = ShowMoveList($Pokemon_Existence['ID'], $Pokemon_Move_Slot);

      echo json_encode([
        'Move_List' => $Move_List,
      ]);
      break;

    case 'Show':
      $Modification_Table = ShowPokemonModTable($Pokemon_Existence['ID']);

      echo json_encode([
        'Modification_Table' => $Modification_Table,
      ]);
      break;

    case 'Update':
      $Update_User = UpdateUser($Pokemon_Existence['ID'], $New_User_Avatar, $New_User_Password);

      echo json_encode([
        'Success' => $Update_User['Success'],
        'Message' => $Update_User['Message'],
        'Modification_Table' => $Update_User['New_Table_HTML'],
      ]);
      break;

    case 'Update_Move':
      $Update_Move = UpdateMove($Pokemon_Existence['ID'], $Pokemon_Move_Slot, $Pokemon_Move_Value);

      echo json_encode([
        'Success' => $Update_Move['Success'],
        'Message' => $Update_Move['Message'],
      ]);
      break;
  }
