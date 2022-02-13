<?php
  require_once $_SERVER['DOCUMENT_ROOT'] . '/core/required/session.php';
  require_once $_SERVER['DOCUMENT_ROOT'] . '/staff/functions/edit_pokedex.php';

  $Pokedex_ID = null;
  if ( !empty($_GET['Pokedex_ID']) )
    $Pokedex_ID = Purify($_GET['Pokedex_ID']);

  try
  {
    $Get_Pokedex_Entry_Data = $PDO->prepare("
      SELECT *
      FROM `pokedex`
      WHERE `ID` = ?
      LIMIT 1
    ");
    $Get_Pokedex_Entry_Data->execute([ $Pokedex_ID ]);
    $Get_Pokedex_Entry_Data->setFetchMode(PDO::FETCH_ASSOC);
    $Pokedex_Entry_Data = $Get_Pokedex_Entry_Data->fetch();
  }
  catch ( PDOException $e )
  {
    HandleError($e);
  }

  if ( empty($Pokedex_ID) || empty($Pokedex_Entry_Data) )
  {
    echo json_encode([
      'Success' => false,
      'Message' => "The Pok&eacute;dex entry that you have requested doesn't exist.",
    ]);

    exit;
  }

  if ( !empty($_GET['Action']) && in_array($_GET['Action'], ['Show', 'Update']) )
    $Action = Purify($_GET['Action']);

  if ( empty($Action) )
  {
    echo json_encode([
      'Success' => false,
      'Message' => 'An invalid action was selected.',
    ]);

    exit;
  }


  switch ( $Action )
  {
    case 'Show':
      $Edit_Table = ShowEntryEditTable($Pokedex_ID);

      echo json_encode([
        'Edit_Table' => $Edit_Table,
      ]);
      break;
  }
