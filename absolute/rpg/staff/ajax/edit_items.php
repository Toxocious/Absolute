<?php
  require_once $_SERVER['DOCUMENT_ROOT'] . '/core/required/session.php';
  require_once $_SERVER['DOCUMENT_ROOT'] . '/staff/functions/edit_items.php';

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

  $Item_Description = null;
  if ( !empty($_GET['Item_Description']) )
    $Item_Description = Purify($_GET['Item_Description']);

  $Can_Take_Item = null;
  if ( !empty($_GET['Can_Take_Item']) )
    $Can_Take_Item = Purify($_GET['Item_Description']);

  $Natural_Gift_Power = null;
  if ( !empty($_GET['Natural_Gift_Power']) )
    $Natural_Gift_Power = Purify($_GET['Natural_Gift_Power']);

  $Natural_Gift_Type = null;
  if ( !empty($_GET['Natural_Gift_Type']) )
    $Natural_Gift_Type = Purify($_GET['Natural_Gift_Type']);

  $Fling_Power = null;
  if ( !empty($_GET['Fling_Power']) )
    $Fling_Power = Purify($_GET['Fling_Power']);

  $Attack_Boost = null;
  if ( !empty($_GET['Attack_Boost']) )
    $Attack_Boost = Purify($_GET['Attack_Boost']);

  $Defense_Boost = null;
  if ( !empty($_GET['Defense_Boost']) )
    $Defense_Boost = Purify($_GET['Defense_Boost']);

  $Sp_Attack_Boost = null;
  if ( !empty($_GET['Sp_Attack_Boost']) )
    $Sp_Attack_Boost = Purify($_GET['Sp_Attack_Boost']);

  $Sp_Defense_Boost = null;
  if ( !empty($_GET['Sp_Defense_Boost']) )
    $Sp_Defense_Boost = Purify($_GET['Sp_Defense_Boost']);

  $Speed_Boost = null;
  if ( !empty($_GET['Speed_Boost']) )
    $Speed_Boost = Purify($_GET['Speed_Boost']);

  $Accuracy_Boost = null;
  if ( !empty($_GET['Accuracy_Boost']) )
    $Accuracy_Boost = Purify($_GET['Accuracy_Boost']);

  $Evasion_Boost = null;
  if ( !empty($_GET['Evasion_Boost']) )
    $Evasion_Boost = Purify($_GET['Evasion_Boost']);

  switch ( $Action )
  {
    case 'Show':
      $Item_Edit_Table = ShowItemEditTable($Item_ID);

      echo json_encode([
        'Item_Edit_Table' => $Item_Edit_Table,
      ]);
      break;

    case 'Update':
      $Update_Item_Entry = UpdateItemEntry(
        $Item_ID,
        $Item_Description,
        $Can_Take_Item,
        $Natural_Gift_Power,
        $Natural_Gift_Type,
        $Fling_Power,
        $Attack_Boost,
        $Defense_Boost,
        $Sp_Attack_Boost,
        $Sp_Defense_Boost,
        $Speed_Boost,
        $Accuracy_Boost,
        $Evasion_Boost
      );

      echo json_encode([
        'Success' => $Update_Item_Entry['Success'],
        'Message' => $Update_Item_Entry['Message'],
        'Item_Edit_Table' => ShowItemEditTable($Item_ID),
      ]);
      break;
  }
