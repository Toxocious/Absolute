<?php
  require_once $_SERVER['DOCUMENT_ROOT'] . '/core/required/session.php';
  require_once $_SERVER['DOCUMENT_ROOT'] . '/staff/functions/set_items.php';

  if ( !empty($_GET['Database_Table']) && in_array($_GET['Database_Table'], ['shop_items']) )
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
    in_array($_GET['Action'], ['Edit_Item_Entry', 'Show', 'Show_Location'])
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

  switch ( $Action )
  {

    case 'Show':
      $Obtainable_Table = ShowObtainableItemsTable($Database_Table);

      echo json_encode([
        'Obtainable_Table' => $Obtainable_Table,
      ]);
      break;
  }
