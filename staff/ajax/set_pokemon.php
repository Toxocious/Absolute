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

  if ( !empty($_GET['Action']) && in_array($_GET['Action'], ['Edit_Pokemon_Entry', 'Finalize_Pokemon_Edit', 'Show', 'Show_Location']) )
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

  switch ( $Action )
  {
    case 'Edit_Pokemon_Entry':
      $Edit_Table = ShowPokemonEditTable($Database_Table, $Pokemon_Database_ID);

      echo json_encode([
        'Edit_Table' => $Edit_Table,
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
