<?php
  /**
   * Display a table of obtainable Items in the specified database table.
   *
   * @param $Obtainable_Location
   */
  function ShowObtainableItemsTable
  (
    $Database_Table
  )
  {
    global $PDO, $Item_Class;

    switch ( $Database_Table )
    {
      default:
        $Obtainable_Location_Name = 'Shop Items';
        $Obtainable_Items_Locations_Query = "SELECT DISTINCT(`Obtained_Place`) FROM `shop_items` ORDER BY `Obtained_Place` ASC";
        $Obtainable_Items_Query = "SELECT * FROM `shop_items` WHERE `Obtained_Place` = ?";
        break;
    }

    try
    {
      $Get_Obtainable_Items_Locations = $PDO->prepare($Obtainable_Items_Locations_Query);
      $Get_Obtainable_Items_Locations->execute([ ]);
      $Get_Obtainable_Items_Locations->setFetchMode(PDO::FETCH_ASSOC);
      $Obtainable_Items_Locations = $Get_Obtainable_Items_Locations->fetchAll();
    }
    catch ( PDOException $e )
    {
      HandleError($e);
    }

    $Obtainable_Items_Table_Text = "
      <div style='width: 100%;'>
        <h2>{$Obtainable_Location_Name}</h2>
      </div>
    ";

    if ( empty($Obtainable_Items_Locations) )
    {
      return "
        {$Obtainable_Items_Table_Text}

        <table class='border-gradient' style='width: 600px;'>
          <thead>
            <tr>
              <th colspan='2'>
                `{$Database_Table}`
              </th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td colspan='2'>
                No items are set in the '{$Database_Table}' database table.
              </td>
            </tr>
          </tbody>
        </table>
      ";
    }

    foreach ( $Obtainable_Items_Locations as $Items_Location )
    {
      switch ( $Database_Table )
      {
        default:
          $Database_Field_Identifier = $Items_Location['Obtained_Place'];
          break;
      }

      try
      {
        $Get_Obtainable_Items = $PDO->prepare($Obtainable_Items_Query);
        $Get_Obtainable_Items->execute([ $Database_Field_Identifier ]);
        $Get_Obtainable_Items->setFetchMode(PDO::FETCH_ASSOC);
        $Obtainable_Items = $Get_Obtainable_Items->fetchAll();
      }
      catch ( PDOException $e )
      {
        HandleError($e);
      }

      if ( empty($Obtainable_Items) )
      {
        $Obtainable_Items_Table_Text .= "
          <table class='border-gradient' style='width: 600px;'>
            <thead>
              <tr>
                <th colspan='2'>
                  {$Database_Field_Identifier}
                </th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td colspan='2' style='padding: 10px;'>
                  No items are set here.
                </td>
              </tr>
            </tbody>
          </table>
        ";
      }
      else
      {
        $Obtainable_Items_Table_Row_Text = '';

        foreach ( $Obtainable_Items as $Items )
        {
          $Items_Icon = $Item_Class->FetchItemData($Items['Item_ID']);
          $Obtainable_Items_Table_Row_Text .= "<img src='{$Items_Icon['Icon']}' item_id='{$Items['Item_ID']}' />";
        }

        $Obtainable_Items_Table_Text .= "
          <table class='border-gradient' style='width: 750px;'>
            <tbody>
              <tr>
                <td colspan='1' style='width: 150px;'>
                  <h3>{$Items_Location['Obtained_Place']}</h3>
                  <button onclick='ShowObtainableItemsByLocation(\"{$Database_Table}\", \"{$Database_Field_Identifier}\");'>
                    Edit Items
                  </button>
                </td>
                <td colspan='2'>
                  {$Obtainable_Items_Table_Row_Text}
                </td>
              </tr>
            </tbody>
          </table>
        ";
      }
    }

    return $Obtainable_Items_Table_Text;
  }

  /**
   * Display a table of obtainable items in the specified table and area.
   *
   * @param $Database_Table
   * @param $Obtainable_Location
   */
  function ShowAreaObtainableItems
  (
    $Database_Table,
    $Obtainable_Location
  )
  {
    $Database_Table = Purify($Database_Table);
    $Obtainable_Location = Purify($Obtainable_Location);

    switch ( $Database_Table )
    {
      default:
        return GetObtainableShopItems($Database_Table, $Obtainable_Location);
        break;
    }
  }

  /**
   * Show a table that allows creation of a new obtainable item.
   *
   * @param $Database_Table
   * @param $Obtainable_Location
   */
  function ShowItemCreationTable
  (
    $Database_Table,
    $Obtainable_Location
  )
  {
    global $PDO;

    $Item_Cost_Text = '';
    $Available_Currencies = ['Money', 'Abso_Coins'];
    foreach ( $Available_Currencies as $Currency )
    {
      $Item_Cost_Text .= "
        <tr>
          <td colspan='2'>
            <img src='" . DOMAIN_SPRITES . "/Assets/{$Currency}.png' />
          </td>
          <td colspan='2'>
            <input type='number' name='{$Currency}_Cost' value='0' />
          </td>
        </tr>
      ";
    }

    $Active_Text = '';
    $Active_Options = [ 'Yes', 'No' ];
    foreach ( $Active_Options as $Active )
    {
      $Active_Text .= "
        <option value='{$Active}'>
          {$Active}
        </option>
      ";
    }

    try
    {
      $Fetch_Item_Dex = $PDO->prepare("
        SELECT `Item_ID`, `Item_Name`
        FROM `item_dex`
        ORDER BY `Item_Name` ASC, `Item_ID` ASC
      ");
      $Fetch_Item_Dex->execute();
      $Fetch_Item_Dex->setFetchMode(PDO::FETCH_ASSOC);
      $Item_Dex = $Fetch_Item_Dex->fetchAll();
    }
    catch ( PDOException $e )
    {
      HandleError( $e->getMessage() );
    }

    $Item_Dex_Dropdown_List = '';
    foreach ( $Item_Dex as $Item_Dex_Entry )
    {
      $Item_Dex_Dropdown_List .= "
        <option value='{$Item_Dex_Entry['Item_ID']}'>
          {$Item_Dex_Entry['Item_Name']}
        </option>
      ";
    }

    return "
      <div style='width: 100%;'>
        <h2>{$Database_Table}</h2>
        <h3>Creating An Item</h3>
      </div>

      <input type='hidden' name='Obtainable_Location' value='{$Obtainable_Location}' />
      <table class='border-gradient' style='width: 600px;'>
        <thead>
          <tr>
            <th colspan='4' style='width: 100%;'>
              {$Obtainable_Location}
            </th>
          </tr>
        </thead>

        <tbody>
          <tr>
            <td colspan='2' style='width: 50%;'>
              <b>Active</b>
            </td>
            <td colspan='2' style='width: 50%;'>
              <select name='Is_Item_Active'>
                {$Active_Text}
              </select>
            </td>
          </tr>

          <tr>
            <td colspan='2' style='width: 50%;'>
              <b>Item</b>
            </td>
            <td colspan='2' style='width: 50%;'>
              <select name='Item_ID'>
                {$Item_Dex_Dropdown_List}
              </select>
            </td>
          </tr>

          <tr>
            <td colspan='2' style='width: 50%;'>
              <b>Items Remaining</b>
            </td>
            <td colspan='2' style='width: 50%;'>
              <input type='number' name='Items_Remaining' value='0' />
            </td>
          </tr>

          {$Item_Cost_Text}
        </tbody>

        <tbody>
          <tr>
            <td colspan='4'>
              <button onclick='FinalizeItemCreation(\"{$Database_Table}\", \"{$Obtainable_Location}\");'>
                Create Item
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    ";
  }

  /**
   * Show an editable table for the specified item.
   *
   * @param $Database_Table
   * @param $Obtainable_Item_Database_ID
   */
  function ShowItemEditTable
  (
    $Database_Table,
    $Obtainable_Item_Database_ID
  )
  {
    global $PDO, $Item_Class;

    switch ( $Database_Table )
    {
      case 'shop_items':
        $Table_Name = 'Shop Items';
        $Item_Entry_Query = "SELECT * FROM `shop_items` WHERE `ID` = ? LIMIT 1";
        break;
    }

    try
    {
      $Get_Item_Entry = $PDO->prepare($Item_Entry_Query);
      $Get_Item_Entry->execute([
        $Obtainable_Item_Database_ID
      ]);
      $Get_Item_Entry->setFetchMode(PDO::FETCH_ASSOC);
      $Item_Entry = $Get_Item_Entry->fetch();
    }
    catch ( PDOException $e )
    {
      HandleError($e);
    }

    if ( empty($Item_Entry) )
    {
      return "
        <table class='border-gradient' style='width: 600px;'>
          <tbody>
            <tr>
              <td colspan='1' style='padding: 10px;'>
                The item that you selected to edit doesn't exist.
              </td>
            </tr>
          </tbody>
        </table>
      ";
    }

    $Item_Info = $Item_Class->FetchItemData($Item_Entry['Item_ID']);

    $Price_List = json_decode($Item_Entry['Prices'], true);

    $Item_Cost_Text = '';
    $Available_Currencies = ['Money', 'Abso_Coins'];
    foreach ( $Available_Currencies as $Currency )
    {
      $Amount = 0;
      if ( !empty($Price_List[0][$Currency]) )
        $Amount = $Price_List[0][$Currency];

      $Item_Cost_Text .= "
        <tr>
          <td colspan='2'>
            <img src='" . DOMAIN_SPRITES . "/Assets/{$Currency}.png' />
          </td>
          <td colspan='2'>
            <input type='number' name='{$Currency}_Cost' value='{$Amount}' />
          </td>
        </tr>
      ";
    }

    $Additional_Table_Rows = "
      <tr>
        <td colspan='2' style='width: 50%;'>
          <b>Items Remaining</b>
        </td>
        <td colspan='2' style='width: 50%;'>
          <input type='number' name='Items_Remaining' value='{$Item_Entry['Remaining']}' />
        </td>
      </tr>

      {$Item_Cost_Text}
    ";

    $Active_Text = '';
    $Active_Options = [ 'Yes' => 1, 'No' => 0 ];
    foreach ( $Active_Options as $Active => $Bool )
    {
      $Active_Text .= "
        <option value='{$Active}' " . ($Item_Entry['Active'] === $Bool ? 'selected' : '') . ">
          {$Active}
        </option>
      ";
    }

    try
    {
      $Fetch_Item_Dex = $PDO->prepare("
        SELECT `Item_ID`, `Item_Name`
        FROM `item_dex`
        ORDER BY `Item_Name` ASC, `Item_ID` ASC
      ");
      $Fetch_Item_Dex->execute();
      $Fetch_Item_Dex->setFetchMode(PDO::FETCH_ASSOC);
      $Item_Dex = $Fetch_Item_Dex->fetchAll();
    }
    catch ( PDOException $e )
    {
      HandleError( $e->getMessage() );
    }

    $Item_Dex_Dropdown_List = '';
    foreach ( $Item_Dex as $Item_Dex_Entry )
    {
      $Active_Selection = $Item_Entry['Item_ID'] === $Item_Dex_Entry['Item_ID'];

      $Item_Dex_Dropdown_List .= "
        <option value='{$Item_Dex_Entry['Item_ID']}' " . ($Active_Selection ? 'selected' : '') . ">
          {$Item_Dex_Entry['Item_Name']}
        </option>
      ";
    }

    return "
      <div style='width: 100%;'>
        <h2>{$Table_Name}</h2>
        <h3>Editing Item</h3>
      </div>

      <table class='border-gradient' style='width: 600px;'>
        <thead>
          <tr>
            <th colspan='4' style='width: 100%;'>
              Configure `{$Table_Name}` Item #{$Item_Entry['ID']}
            </th>
          </tr>
        </thead>

        <tbody>
          <tr>
            <td colspan='4'>
              <img src='{$Item_Info['Icon']}' />
              <br />
              <b>{$Item_Info['Name']}</b>
            </td>
          </tr>
        </tbody>

        <thead>
          <tr>
            <th colspan='4'>
              Configuration Options
            </th>
          </tr>
        </thead>

        <tbody>
          <tr>
            <td colspan='2' style='width: 50%;'>
              <b>Active</b>
            </td>
            <td colspan='2' style='width: 50%;'>
              <select name='Is_Item_Active'>
                {$Active_Text}
              </select>
            </td>
          </tr>

          <tr>
            <td colspan='2' style='width: 50%;'>
              <b>Item Name</b>
            </td>
            <td colspan='2' style='width: 50%;'>
              <select name='Item_ID'>
                {$Item_Dex_Dropdown_List}
              </select>
            </td>
          </tr>

          {$Additional_Table_Rows}
        </tbody>

        <tbody>
          <tr>
            <td colspan='4'>
              <button onclick='FinalizeItemEdit(\"{$Database_Table}\", {$Item_Entry['ID']});'>
                Update Item
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    ";
  }

  /**
   * Get all obtainable items from the specified location.
   *
   * @param $Database_Table
   * @param $Obtainable_Location
   */
  function GetObtainableShopItems
  (
    $Database_Table,
    $Obtainable_Location
  )
  {
    global $PDO, $Item_Class;

    switch ( $Database_Table )
    {
      case 'shop_items':
        $Obtainable_Items_Query = "SELECT * FROM `shop_items`";
        break;
    }

    try
    {
      $Get_Area_Items = $PDO->prepare($Obtainable_Items_Query);
      $Get_Area_Items->execute([ ]);
      $Get_Area_Items->setFetchMode(PDO::FETCH_ASSOC);
      $Area_Items = $Get_Area_Items->fetchAll();
    }
    catch ( PDOException $e )
    {
      HandleError($e);
    }

    $Item_Area_Table_Text = "
      <div style='width: 100%;'>
        <h2>Obtainable Items</h2>
        <h3>{$Obtainable_Location}</h3>
        <button onclick='ShowItemCreationTable(\"{$Database_Table}\", \"{$Obtainable_Location}\");'>
          Add New Item
        </button>
      </div>
    ";

    if ( empty($Area_Items) )
    {
      return "
        {$Item_Area_Table_Text}

        <table class='border-gradient' style='width: 600px;'>
          <tbody>
            <tr>
              <td colspan='1'>
                No items are set to be obtainable in this area.
              </td>
            </tr>
          </tbody>
        </table>
      ";
    }

    $Item_Table_Row_Text = '';
    foreach ( $Area_Items as $Item )
    {
      $Item_Data = $Item_Class->FetchItemData($Item['Item_ID']);

      $Price_List = json_decode($Item['Prices'], true);
      $Item_Cost_Text = '';
      foreach ( $Price_List[0] as $Currency => $Amount )
      {
        $Item_Cost_Text .= "
          <div style='align-items: center; display: flex; gap: 10px; justify-content: left; width: 50%;'>
            <div>
              <img src='" . DOMAIN_SPRITES . "/Assets/{$Currency}.png' />
            </div>
            <div>
              " . number_format($Amount) . "
            </div>
          </div>
        ";
      }

      $Item_Table_Row_Text .= "
        <tr>
          <td colspan='1' style='width: 50px;'>
            <img src='{$Item_Data['Icon']}' item_id='{$Item_Data['ID']}' item_name='{$Item_Data['Name']}' />
          </td>
          <td colspan='1' style='width: 250px;'>
            <a href='javascript:void(0);' onclick='EditSetItem(\"{$Database_Table}\", {$Item['ID']});'>
                <b style='font-size: 16px;'>
                  {$Item_Data['Name']}
                </b>
              </a>
          </td>
          <td colspan='1' style='display: flex; flex-wrap: wrap; width: 300px;'>
            {$Item_Cost_Text}
          </td>
        </tr>
      ";
    }

    return "
      {$Item_Area_Table_Text}

      <table class='border-gradient' style='width: 600px;'>
        <tbody>
          {$Item_Table_Row_Text}
        </tbody>
      </table>
    ";
  }

  /**
   * Finalize creating a new obtainable item.
   *
   * @param $Database_Table
   * @param $Obtainable_Location
   * @param $Item_ID
   * @param $Item_Active
   * @param $Items_Remaining
   * @param $Money_Cost
   * @param $Abso_Coins_Cost
   */
  function FinalizeItemCreation
  (
    $Database_Table,
    $Obtainable_Location,
    $Item_ID,
    $Item_Active,
    $Items_Remaining,
    $Money_Cost,
    $Abso_Coins_Cost
  )
  {
    global $PDO;

    $Item_Active = $Item_Active == 'Yes' ? 1 : 0;
    $Price_JSON = json_encode([
      'Money' => $Money_Cost,
      'Abso_Coins' => $Abso_Coins_Cost
    ]);

    switch ( $Database_Table )
    {
      case 'shop_items':
        $Item_Creation_Query = "INSERT INTO `shop_items` (`Obtained_Place`, `Item_ID`, `Active`, `Remaining`, `Prices`) VALUES (?, ?, ?, ?, ?)";
        $Item_Creation_Query_Params = [ $Obtainable_Location, $Item_ID, $Item_Active, $Items_Remaining, "[{$Price_JSON}]" ];
        break;
    }

    try
    {
      $PDO->beginTransaction();

      $Update_Item_Entry = $PDO->prepare($Item_Creation_Query);
      $Update_Item_Entry->execute($Item_Creation_Query_Params);

      $PDO->commit();
    }
    catch ( PDOException $e )
    {
      $PDO->rollBack();

      HandleError($e);
    }

    return [
      'Success' => true,
      'Message' => 'You have made this item obtainable.'
    ];
  }

  /**
   * Finalize the edited item.
   *
   * @param $Database_Table
   * @param $Obtainable_Item_Database_ID
   * @param $Item_Active
   * @param $Item_Remaining
   * @param $Money_Cost
   * @param $Abso_Coins_Cost
   */
  function FinalizeItemEdit
  (
    $Database_Table,
    $Obtainable_Item_Database_ID,
    $Item_ID,
    $Item_Active,
    $Items_Remaining,
    $Money_Cost,
    $Abso_Coins_Cost
  )
  {
    global $PDO;

    $Item_Active = $Item_Active == 'Yes' ? 1 : 0;
    $Price_JSON = json_encode([
      'Money' => $Money_Cost,
      'Abso_Coins' => $Abso_Coins_Cost
    ]);

    switch ( $Database_Table )
    {
      case 'shop_items':
        $Item_Update_Query = "UPDATE `shop_items` SET `Item_ID` = ?, `Active` = ?, `Remaining` = ?, `Prices` = ? WHERE `ID` = ? LIMIT 1";
        $Item_Update_Query_Params = [ $Item_ID, $Item_Active, $Items_Remaining, "[{$Price_JSON}]", $Obtainable_Item_Database_ID ];
        break;
    }

    try
    {
      $PDO->beginTransaction();

      $Update_Item_Entry = $PDO->prepare($Item_Update_Query);
      $Update_Item_Entry->execute($Item_Update_Query_Params);

      $PDO->commit();
    }
    catch ( PDOException $e )
    {
      $PDO->rollBack();

      HandleError($e);
    }

    return [
      'Success' => true,
      'Message' => 'You have updated this item.',
      'Finalized_Edit_Table' => ShowItemEditTable($Database_Table, $Obtainable_Item_Database_ID),
    ];
  }
