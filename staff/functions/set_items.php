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
