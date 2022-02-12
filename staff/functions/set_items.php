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
