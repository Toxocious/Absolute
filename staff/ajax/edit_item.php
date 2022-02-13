<?php
  require_once $_SERVER['DOCUMENT_ROOT'] . '/core/required/session.php';
  require_once $_SERVER['DOCUMENT_ROOT'] . '/staff/functions/edit_item.php';

  $Item_ID = null;
  if ( !empty($_GET['Item_ID']) )
    $Item_ID = Purify($_GET['Item_ID']);

  try
  {
    $Get_Item_Entry_Data = $PDO->prepare("
      SELECT `Item_ID`
      FROM `item_dex`
      WHERE `Item_ID` = ?
      LIMIT 1
    ");
    $Get_Item_Entry_Data->execute([ $Item_ID ]);
    $Get_Item_Entry_Data->setFetchMode(PDO::FETCH_ASSOC);
    $Item_Entry_Data = $Get_Item_Entry_Data->fetch();
  }
  catch ( PDOException $e )
  {
    HandleError($e);
  }

  if ( empty($Item_ID) || empty($Item_Entry_Data) )
  {
    echo json_encode([
      'Success' => false,
      'Message' => "The item entry that you have requested doesn't exist.",
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
      $Item_Edit_Table = ShowItemEditTable($Item_ID);

      echo json_encode([
        'Item_Edit_Table' => $Item_Edit_Table,
      ]);
      break;
  }
