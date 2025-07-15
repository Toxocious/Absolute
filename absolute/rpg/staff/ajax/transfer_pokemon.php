<?php
  require_once $_SERVER['DOCUMENT_ROOT'] . '/core/required/session.php';
  require_once $_SERVER['DOCUMENT_ROOT'] . '/staff/functions/transfer_pokemon.php';

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

  if ( !empty($_GET['Pokemon_Action']) && in_array($_GET['Pokemon_Action'], ['Show', 'Transfer']) )
    $Pokemon_Action = Purify($_GET['Pokemon_Action']);

  if ( empty($Pokemon_Action) )
  {
    echo json_encode([
      'Success' => false,
      'Message' => 'An invalid action was selected.',
    ]);

    exit;
  }

  $Transfer_To_User_ID = null;
  if ( !empty($_GET['Transfer_To_User_ID']) )
    $Transfer_To_User_ID = Purify($_GET['Transfer_To_User_ID']);

  switch ( $Pokemon_Action )
  {
    case 'Show':
      $Modification_Table = ShowPokemonModTable($Pokemon_Info['ID']);

      echo json_encode([
        'Modification_Table' => $Modification_Table,
      ]);
      break;

    case 'Transfer':
      $Transfer_Pokemon = TransferPokemon($Pokemon_Info['ID'], $Transfer_To_User_ID);

      echo json_encode([
        'Success' => $Transfer_Pokemon['Success'],
        'Message' => $Transfer_Pokemon['Message'],
        'Modification_Table' => '',
      ]);
      break;
  }
