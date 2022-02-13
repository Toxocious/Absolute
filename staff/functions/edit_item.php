<?php
  /**
   * Show an HTML dropdown of all possible item entries.
   */
  function ShowItemDropdown()
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
        <select name='Item_entries'>
          <option>There are no item entries</option>
        </select>
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
      <select name='item_entries' onchange='ShowItemEntry();'>
        <option>Select An Item</option>
        {$Dropdown_Entries}
      </select>
    ";
  }
