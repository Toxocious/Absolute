<?php
  /**
   * Get the user's items within the specified inventory tab.
   *
   * @param $Inventory_Tab
   */
  function GetInventoryItems
  (
    $Inventory_Tab
  )
  {
    global $PDO, $User_Data;

    try
    {
      $Get_Owned_Items = $PDO->prepare("
        SELECT *
        FROM `items`
        WHERE `Owner_Current` = ? AND `Item_Type` = ? AND `Quantity` > 0
        ORDER BY `Item_Name` ASC
      ");
      $Get_Owned_Items->execute([
        $User_Data['ID'],
        $Inventory_Tab
      ]);
      $Get_Owned_Items->setFetchMode(PDO::FETCH_ASSOC);
      $Owned_Items = $Get_Owned_Items->fetchAll();
    }
    catch ( PDOException $e )
    {
      HandleError($e);
    }

    return $Owned_Items;
  }

  /**
   * Get the user's items that are currently equipped to Pokemon.
   */
  function GetEquippedItems()
  {
    global $PDO, $User_Data, $Poke_Class, $Item_Class;

    try
    {
      $Get_Equipped_Items = $PDO->prepare("
        SELECT `ID`, `Item`
        FROM `pokemon`
        WHERE `Item` != 0 AND `Owner_Current` = ?
      ");
      $Get_Equipped_Items->execute([
        $User_Data['ID']
      ]);
      $Get_Equipped_Items->setFetchMode(PDO::FETCH_ASSOC);
      $Equipped_Items = $Get_Equipped_Items->fetchAll();
    }
    catch ( PDOException $e )
    {
      HandleError($e);
    }

    if ( !empty($Equipped_Items) )
    {
      foreach ( $Equipped_Items as $Index => $Equipped_Item )
      {
        $Equipped_Pokemon = $Poke_Class->FetchPokemonData($Equipped_Item['ID']);
			  $Equipped_Item = $Item_Class->FetchItemData($Equipped_Item['Item']);

        $Equipped_Items[$Index] = [
          'Pokemon' => [
            'ID' => $Equipped_Pokemon['ID'],
            'Name' => $Equipped_Pokemon['Display_Name'],
            'Icon' => $Equipped_Pokemon['Icon'],
          ],
          'Item' => [
            'ID' => $Equipped_Item['ID'],
            'Name' => $Equipped_Item['Name'],
            'Icon' => $Equipped_Item['Icon']
          ]
        ];
      }
    }

    return $Equipped_Items;
  }

  /**
   * Show a preview of the specified item.
   *
   * @param $Item_ID
   */
  function GetItemPreview
  (
    $Item_ID
  )
  {
    global $Item_Class, $Poke_Class, $User_Data;

    $Item_Data = $Item_Class->FetchItemData($Item_ID);

    $Slot_Text = '';
    if ( $User_Data['Roster'] )
    {
      for ( $i = 0; $i < 6; $i++ )
      {
        if ( isset($User_Data['Roster'][$i]['ID']) )
        {
          $Pokemon = $Poke_Class->FetchPokemonData($User_Data['Roster'][$i]['ID']);

          if ( !$Pokemon['Item'] )
          {
            $Slot_Text .= "
              <td colspan='1' style='width: calc(100% / 6);'>
                <img src='{$Pokemon['Icon']}' onclick=\"EquipItem({$Item_Data['ID']}, {$Pokemon['ID']});\" />
              </td>
            ";
          }
          else
          {
            $Slot_Text .= "
              <td colspan='1' style='width: calc(100% / 6);'>
                <img src='{$Pokemon['Icon']}' style='filter: grayscale(100%);' />
              </td>
            ";
          }
        }
        else
        {
          $Pokemon['Icon'] = DOMAIN_SPRITES . "/Pokemon/Sprites/0_mini.png";

          $Slot_Text .= "
            <td colspan='1' style='width: calc(100% / 6);'>
              <img src='{$Pokemon['Icon']}' style='filter: grayscale(100%);' />
            </td>
          ";
        }
      }
    }
    else
    {
      for ( $i = 0; $i < 6; $i++ )
      {
        $Slot_Text .= "
          <td colspan='1' style='width: calc(100% / 6);'>
            <img src='" . DOMAIN_SPRITES . "/Pokemon/Sprites/0.png' style='filter: grayscale(100%);' />
          </td>
        ";
      }
    }

    return "
      <tr>
        <td colspan='3'>
          <img src='{$Item_Data['Icon']}' />
        </td>
        <td colspan='3'>
          <b>{$Item_Data['Name']}</b>
        </td>
      </tr>
      <tr>
        <td colspan='6' style='padding: 5px;'>
          {$Item_Data['Description']}
        </td>
      </tr>
      <tr>
        {$Slot_Text}
      </tr>
    ";
  }

  /**
   * Equip the specified item to the specified Pokemon.
   *
   * @param $Item_ID
   * @param $Pokemon_ID
   */
  function EquipItem
  (
    $Item_ID,
    $Pokemon_ID
  )
  {
    global $Item_Class, $Poke_Class, $User_Data;

    $Item_Data = $Item_Class->FetchOwnedItem($User_Data['ID'], $Item_ID);
    $Poke_Data = $Poke_Class->FetchPokemonData($Pokemon_ID);

    if ( $Item_Data['Quantity'] < 1 )
    {
      return [
        'Success' => false,
        'Message' => 'You do not own enough of this item to attach it to a Pok&eacute;mon.'
      ];
    }

    if ( $Poke_Data['Owner_Current'] != $User_Data['ID'] )
    {
      return [
        'Success' => false,
        'Message' => 'You may not attach an item to a Pok&eacute;mon that does not belong to you.'
      ];
    }

    $Attach_Item = $Item_Class->Attach($Item_ID, $Pokemon_ID, $User_Data['ID']);

    if ( $Attach_Item )
    {
      return [
        'Success' => true,
        'Message' => "You have attached a {$Item_Data['Name']} to your {$Poke_Data['Display_Name']}."
      ];
    }

    return [
      'Success' => false,
      'Message' => 'You do not own enough of this item to attach it to a Pok&eacute;mon.'
    ];
  }

  /**
   * Unequip the item of the specified Pokemon.
   *
   * @param $Pokemon_ID
   */
  function UnequipItem
  (
    $Pokemon_ID
  )
  {
    global $Item_Class, $Poke_Class, $User_Data;

    $Pokemon_Info = $Poke_Class->FetchPokemonData($Pokemon_ID);
    if ( !$Pokemon_Info || $Pokemon_Info['Owner_Current'] != $User_Data['ID'] )
    {
      return [
        'Success' => false,
        'Message' => 'This Pok&eacute;mon does not belong to you.'
      ];
    }

    $Remove_Item = $Item_Class->Unequip($Pokemon_Info['ID'], $User_Data['ID']);

    return [
      'Success' => $Remove_Item['Type'] == 'success' ? true : false,
      'Message' => $Remove_Item['Message']
    ];
  }
