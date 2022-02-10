<?php
  require_once $_SERVER['DOCUMENT_ROOT'] . '/core/required/session.php';
  require_once $_SERVER['DOCUMENT_ROOT'] . '/staff/functions/set_pokemon.php';

  if ( !empty($_GET['Database_Table']) && in_array($_GET['Database_Table'], ['map_encounters', 'shop_pokemon']) )
    $Database_Table = Purify($_GET['Database_Table']);

  if ( empty($Database_Table) )
  {
    echo json_encode([
      'Success' => false,
      'Message' => "The location that you have requested doesn't exist.",
    ]);

    exit;
  }

  if
  (
    !empty($_GET['Action']) &&
    in_array($_GET['Action'], ['Create_New_Pokemon', 'Edit_Pokemon_Entry', 'Finalize_Pokemon_Creation', 'Finalize_Pokemon_Edit', 'Show', 'Show_Location'])
  )
    $Action = Purify($_GET['Action']);

  if ( empty($Action) )
  {
    echo json_encode([
      'Success' => false,
      'Message' => 'An invalid action was selected.',
    ]);

    exit;
  }

  $Obtainable_Location = null;
  if ( !empty($_GET['Obtainable_Location']) )
    $Obtainable_Location = Purify($_GET['Obtainable_Location']);

  $Pokemon_Database_ID = null;
  if ( !empty($_GET['Pokemon_Database_ID']) )
    $Pokemon_Database_ID = Purify($_GET['Pokemon_Database_ID']);

  $Pokemon_Active = null;
  if ( !empty($_GET['Pokemon_Active']) )
    $Pokemon_Active = Purify($_GET['Pokemon_Active']);

  $Pokemon_Dex_ID = null;
  if ( !empty($_GET['Pokemon_Dex_ID']) )
    $Pokemon_Dex_ID = Purify($_GET['Pokemon_Dex_ID']);

  $Obtained_Text = null;
  if ( !empty($_GET['Obtained_Text']) )
    $Obtained_Text = Purify($_GET['Obtained_Text']);

  $Encounter_Weight = null;
  if ( !empty($_GET['Encounter_Weight']) )
    $Encounter_Weight = Purify($_GET['Encounter_Weight']);

  $Encounter_Zone = null;
  if ( !empty($_GET['Encounter_Zone']) )
    $Encounter_Zone = Purify($_GET['Encounter_Zone']);

  $Min_Level = null;
  if ( !empty($_GET['Min_Level']) )
    $Min_Level = Purify($_GET['Min_Level']);

  $Max_Level = null;
  if ( !empty($_GET['Max_Level']) )
    $Max_Level = Purify($_GET['Max_Level']);

  $Min_Map_Exp = null;
  if ( !empty($_GET['Min_Map_Exp']) )
    $Min_Map_Exp = Purify($_GET['Min_Map_Exp']);

  $Max_Map_Exp = null;
  if ( !empty($_GET['Max_Map_Exp']) )
    $Max_Map_Exp = Purify($_GET['Max_Map_Exp']);

  $Pokemon_Type = null;
  if ( !empty($_GET['Pokemon_Type']) )
    $Pokemon_Type = Purify($_GET['Pokemon_Type']);

  $Pokemon_Remaining = null;
  if ( !empty($_GET['Pokemon_Remaining']) )
    $Pokemon_Remaining = Purify($_GET['Pokemon_Remaining']);

  $Money_Cost = null;
  if ( !empty($_GET['Money_Cost']) )
    $Money_Cost = Purify($_GET['Money_Cost']);

  $Abso_Coins_Cost = null;
  if ( !empty($_GET['Abso_Coins_Cost']) )
    $Abso_Coins_Cost = Purify($_GET['Abso_Coins_Cost']);

  switch ( $Action )
  {
    case 'Create_New_Pokemon':
      $Creation_Table = ShowPokemonCreationTable($Database_Table, $Obtainable_Location);

      echo json_encode([
        'Creation_Table' => $Creation_Table,
      ]);
      break;

    case 'Edit_Pokemon_Entry':
      $Edit_Table = ShowPokemonEditTable($Database_Table, $Pokemon_Database_ID);

      echo json_encode([
        'Edit_Table' => $Edit_Table,
      ]);
      break;

    case 'Finalize_Pokemon_Creation':
      $Finalize_Pokemon_Creation = FinalizePokemonCreation(
        $Database_Table,
        $Pokemon_Database_ID,
        $Pokemon_Active,
        $Pokemon_Dex_ID,
        $Obtained_Text,
        $Encounter_Weight,
        $Encounter_Zone,
        $Min_Level,
        $Max_Level,
        $Min_Map_Exp,
        $Max_Map_Exp,
        $Pokemon_Type,
        $Pokemon_Remaining,
        $Money_Cost,
        $Abso_Coins_Cost
      );

      echo json_encode([
        'Success' => $Finalize_Pokemon_Creation['Success'],
        'Message' => $Finalize_Pokemon_Creation['Message']
      ]);
      break;

    case 'Finalize_Pokemon_Edit':
      $Finalize_Pokemon_Edit = FinalizePokemonEdit(
        $Database_Table,
        $Pokemon_Database_ID,
        $Pokemon_Active,
        $Pokemon_Dex_ID,
        $Obtained_Text,
        $Encounter_Weight,
        $Min_Level,
        $Max_Level,
        $Min_Map_Exp,
        $Max_Map_Exp,
        $Pokemon_Type,
        $Pokemon_Remaining,
        $Money_Cost,
        $Abso_Coins_Cost
      );

      echo json_encode([
        'Success' => $Finalize_Pokemon_Edit['Success'],
        'Message' => $Finalize_Pokemon_Edit['Message'],
        'Finalized_Edit_Table' => $Finalize_Pokemon_Edit['Finalized_Edit_Table']
      ]);
      break;

    case 'Show':
      $Obtainable_Table = ShowObtainablePokemonTable($Database_Table);

      echo json_encode([
        'Obtainable_Table' => $Obtainable_Table,
      ]);
      break;

    case 'Show_Location':
      $Obtainable_Table = ShowAreaObtainablePokemon($Database_Table, $Obtainable_Location);

      echo json_encode([
        'Obtainable_Table' => $Obtainable_Table,
      ]);
      break;
  }
