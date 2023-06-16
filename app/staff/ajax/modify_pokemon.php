<?php
  require_once $_SERVER['DOCUMENT_ROOT'] . '/core/required/session.php';
  require_once $_SERVER['DOCUMENT_ROOT'] . '/staff/functions/modify_pokemon.php';

  if ( !empty($_GET['Pokemon_Value']) )
    $Pokemon_Value = Purify($_GET['Pokemon_Value']);

  $Pokemon_Info = GetPokemonData($Pokemon_Value);

  if ( empty($Pokemon_Value) || !$Pokemon_Info )
  {
    echo json_encode([
      'Success' => false,
      'Message' => "The Pok&eacute;mon you are trying to modify doesn't exist.",
    ]);

    exit;
  }

  if ( !empty($_GET['Pokemon_Action']) && in_array($_GET['Pokemon_Action'], ['Delete', 'Freeze', 'Move_List', 'Show', 'Update_Pokemon', 'Update_Move']) )
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

  $Pokemon_Level = 1;
  if ( !empty($_GET['Pokemon_Level']) )
    $Pokemon_Level = Purify($_GET['Pokemon_Level']);

  $Pokemon_Gender = 1;
  if ( !empty($_GET['Pokemon_Gender']) )
    $Pokemon_Gender = Purify($_GET['Pokemon_Gender']);

  $Pokemon_Nature = 1;
  if ( !empty($_GET['Pokemon_Nature']) )
    $Pokemon_Nature = Purify($_GET['Pokemon_Nature']);

  $Pokemon_Ability = 1;
  if ( !empty($_GET['Pokemon_Ability']) )
    $Pokemon_Ability = Purify($_GET['Pokemon_Ability']);

  switch ( $Pokemon_Action )
  {
    case 'Delete':
      $Delete_Pokemon = DeletePokemon($Pokemon_Info['ID']);

      echo json_encode([
        'Success' => $Delete_Pokemon['Success'],
        'Message' => $Delete_Pokemon['Message'],
      ]);
      break;

    case 'Freeze':
      $Toggle_Pokemon_Freeze = ToggleFreeze($Pokemon_Info['ID'], $Pokemon_Frozen_Status);

      echo json_encode([
        'Success' => $Toggle_Pokemon_Freeze['Success'],
        'Message' => $Toggle_Pokemon_Freeze['Message'],
        'Modification_Table' => $Toggle_Pokemon_Freeze['Modification_Table'],
      ]);
      break;

    case 'Move_List':
      $Move_List = ShowMoveList($Pokemon_Info['ID'], $Pokemon_Move_Slot);

      echo json_encode([
        'Move_List' => $Move_List,
      ]);
      break;

    case 'Show':
      $Modification_Table = ShowPokemonModTable($Pokemon_Info['ID']);

      echo json_encode([
        'Modification_Table' => $Modification_Table,
      ]);
      break;

    case 'Update_Pokemon':
      $Update_Pokemon = UpdatePokemon($Pokemon_Info['ID'], $Pokemon_Level, $Pokemon_Gender, $Pokemon_Nature, $Pokemon_Ability);

      echo json_encode([
        'Success' => $Update_Pokemon['Success'],
        'Message' => $Update_Pokemon['Message'],
        'Modification_Table' => $Update_Pokemon['Modification_Table'],
      ]);
      break;

    case 'Update_Move':
      $Update_Move = UpdateMove($Pokemon_Info['ID'], $Pokemon_Move_Slot, $Pokemon_Move_Value);

      echo json_encode([
        'Success' => $Update_Move['Success'],
        'Message' => $Update_Move['Message'],
      ]);
      break;
  }
