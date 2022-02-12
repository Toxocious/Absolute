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
    in_array($_GET['Action'], ['Edit_Item_Entry', 'Finalize_Item_Edit', 'Show', 'Show_Location'])
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

  $Item_Database_ID = null;
  if ( !empty($_GET['Item_Database_ID']) )
    $Item_Database_ID = Purify($_GET['Item_Database_ID']);

  $Item_ID = null;
  if ( !empty($_GET['Item_ID']) )
    $Item_ID = Purify($_GET['Item_ID']);

  $Is_Item_Active = null;
  if ( !empty($_GET['Is_Item_Active']) )
    $Is_Item_Active = Purify($_GET['Is_Item_Active']);

  $Items_Remaining = null;
  if ( !empty($_GET['Items_Remaining']) )
    $Items_Remaining = Purify($_GET['Items_Remaining']);

  $Money_Cost = null;
  if ( !empty($_GET['Money_Cost']) )
    $Money_Cost = Purify($_GET['Money_Cost']);

  $Abso_Coins_Cost = null;
  if ( !empty($_GET['Abso_Coins_Cost']) )
    $Abso_Coins_Cost = Purify($_GET['Abso_Coins_Cost']);

  switch ( $Action )
  {
    case 'Edit_Item_Entry':
      $Edit_Table = ShowItemEditTable($Database_Table, $Item_Database_ID);

      echo json_encode([
        'Edit_Table' => $Edit_Table,
      ]);
      break;

    case 'Finalize_Item_Edit':
      $Finalize_Item_Edit = FinalizeItemEdit(
        $Database_Table,
        $Item_Database_ID,
        $Item_ID,
        $Is_Item_Active,
        $Items_Remaining,
        $Money_Cost,
        $Abso_Coins_Cost
      );

      echo json_encode([
        'Success' => $Finalize_Item_Edit['Success'],
        'Message' => $Finalize_Item_Edit['Message'],
        'Finalized_Edit_Table' => $Finalize_Item_Edit['Finalized_Edit_Table']
      ]);
      break;

    case 'Show':
      $Obtainable_Table = ShowObtainableItemsTable($Database_Table);

      echo json_encode([
        'Obtainable_Table' => $Obtainable_Table,
      ]);
      break;

    case 'Show_Location':
      $Obtainable_Table = ShowAreaObtainableItems($Database_Table, $Obtainable_Location);

      echo json_encode([
        'Obtainable_Table' => $Obtainable_Table,
      ]);
      break;
  }
