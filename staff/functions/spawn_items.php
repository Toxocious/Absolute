<?php
  /**
   * Show an HTML dropdown of all possible item entries.
   */
  function ShowSpawnableItemDropdown()
  {
    global $PDO;

    try
    {
      $Get_Item_Entries = $PDO->prepare("
        SELECT *
        FROM `item_dex`
        ORDER BY `Item_ID` ASC
      ");
      $Get_Item_Entries->execute([ ]);
      $Get_Item_Entries->setFetchMode(PDO::FETCH_ASSOC);
      $Item_Entries = $Get_Item_Entries->fetchAll();
    }
    catch ( PDOException $e )
    {
      HandleError($e);
    }

    if ( empty($Item_Entries) )
    {
      return "
        <div style='display: flex; align-items: center; justify-content: center; width: 100%;'>
          <select name='Item_entries'>
            <option>There are no item entries</option>
          </select>
        </div>
      ";
    }

    $Dropdown_Entries = '';
    foreach ( $Item_Entries as $Item )
    {
      $Dropdown_Entries .= "
        <option value='{$Item['Item_ID']}'>
          {$Item['Item_Name']}
        </option>
      ";
    }

    return "
      <div style='display: flex; align-items: center; justify-content: center; width: 100%;'>
        <select name='item_entries'>
          <option>Select An Item</option>
          {$Dropdown_Entries}
        </select>
      </div>
    ";
  }


  /**
   * Given the specified item id, recipient, and quantity, spawn the item.
   *
   * @param $Item_ID
   * @param $Recipient
   * @param $Amount
   */
  function SpawnItem
  (
    $Item_ID,
    $Recipient,
    $Amount
  )
  {
    global $Item_Class, $User_Class, $User_Data;

    $Recipient_Data = $User_Class->FetchUserData($Recipient);
    if ( !$Recipient_Data )
    {
      return [
        'Success' => false,
        'Message' => 'Please enter a valid Recipient.',
      ];
    }

    if ( empty($Amount) || is_nan($Amount) || $Amount < 0 )
      $Amount = 1;

    $Item_Class->SpawnItem($Recipient_Data['ID'], $Item_ID, $Amount);

    LogStaffAction('Item Spawned', $User_Data['ID']);

    return [
      'Success' => true,
      'Message' => "You have spawned x{$Amount} of this item for {$Recipient_Data['Username']}.",
    ];
  }
