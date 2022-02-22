/**
 * Load the specified inventory tab.
 *
 * @param Inventory_Tab
 */
async function ShowInventoryTab(Inventory_Tab)
{
  let Form_Data = new FormData();
  Form_Data.append('Action', 'Show_Inventory');
  Form_Data.append('Inventory_Tab', Inventory_Tab);

  await SendRequest('inventory', Form_Data)
    .then((Inventory_Data) => {
      Inventory_Data = JSON.parse(Inventory_Data);

      if ( Inventory_Data.Items.length === 0 )
      {
        document.getElementById('Inventory_Items').innerText = 'Your bag is empty.';
      }
      else
      {
        let Item_HTML = '';
        for ( const Item of Inventory_Data.Items )
        {
          Item_HTML += `
            <div onclick='ShowItemPreview(${Item.Item_ID});' style='align-items: center; display: flex; gap: 5px; width: 49%;'>
              <div>
                <img src='/images/Items/${Item.Item_Name}.png' />
              </div>
              <div style='width: 100%;'>
                <b>${Item.Item_Name}</b>
                <br />
                Owned: ${Item.Quantity}
              </div>
            </div>
          `;
        }

        document.getElementById('Inventory_Items').innerHTML = `
          <div style='align-items: center; display: flex; flex-direction: row; flex-wrap: wrap; gap: 3px;'>
            ${Item_HTML}
          </div>
        `;
      }
    })
    .catch((Error) => console.error('Error:', Error));
}

/**
 * Get all items that are equipped to the user's Pokemon.
 */
async function ShowEquippedItems()
{
  let Form_Data = new FormData();
  Form_Data.append('Action', 'Show_Equipped_Items');

  await SendRequest('inventory', Form_Data)
    .then((Equipped_Items) => {
      Equipped_Items = JSON.parse(Equipped_Items);

      if ( Equipped_Items.Equipped_Items.length === 0 )
      {
        document.getElementById('Equipped_Items').innerHTML = `
          <div style='padding: 10px;'>
            None of your items are equipped to Pok&eacute;mon.
          </div>
        `;
      }
      else
      {
        let Item_HTML = '';
        for ( const Equipped_Data of Equipped_Items.Equipped_Items )
        {
          Item_HTML += `
            <div onclick='UnequipItem(${Equipped_Data.Pokemon.ID});' style='align-items: center; display: flex; gap: 5px; width: 49%;'>
              <div>
                <img src='${Equipped_Data.Item.Icon}' />
              </div>
              <div style='width: 100%;'>
                <b>${Equipped_Data.Pokemon.Name}</b>
                <br />
                ${Equipped_Data.Item.Name}
              </div>
            </div>
          `;
        }

        document.getElementById('Equipped_Items').innerHTML = `
          <div style='display: flex; flex-direction: row; flex-wrap: wrap; gap: 3px; height: 100px;'>
            ${Item_HTML}
          </div>
        `;
      }
    })
    .catch((Error) => console.error('Error:', Error));
}

/**
 * Show a preview of the selected item.
 *
 * @param Item_ID
 */
async function ShowItemPreview(Item_ID)
{
  let Form_Data = new FormData();
  Form_Data.append('Action', 'Show_Item_Preview');
  Form_Data.append('Item_ID', Item_ID);

  await SendRequest('inventory', Form_Data)
    .then((Item_Preview) => {
      Item_Preview = JSON.parse(Item_Preview);

      document.getElementById('Item_Preview').innerHTML = Item_Preview.Item_Data;
    })
    .catch((Error) => console.error('Error:', Error));
}

/**
 * Attach the item to the specified Pokemon.
 *
 * @param Item_ID
 * @param Pokemon_ID
 */
async function EquipItem(Item_ID, Pokemon_ID)
{
  if ( !confirm('Are you sure you want to equip this item?') )
    return;

  let Form_Data = new FormData();
  Form_Data.append('Action', 'Equip_Item');
  Form_Data.append('Item_ID', Item_ID);
  Form_Data.append('Pokemon_ID', Pokemon_ID);

  await SendRequest('inventory', Form_Data)
    .then((Equip_Item) => {
      Equip_Item = JSON.parse(Equip_Item);

      document.getElementById('Pokemon_Center_Moves_AJAX').className = Equip_Item.Success ? 'success' : 'error';
      document.getElementById('Pokemon_Center_Moves_AJAX').innerHTML = Equip_Item.Message;

      document.getElementById('Item_Preview').innerHTML = `
        <tr>
          <td style='padding: 10px;'>
            Click on an item to view more information.
          </td>
        </tr>
      `;

      ShowInventoryTab('Held Item');
      ShowEquippedItems();
    })
    .catch((Error) => console.error('Error:', Error));
}

/**
 * Unattach the item from the specified Pokemon.
 *
 * @param Pokemon_ID
 */
async function UnequipItem(Pokemon_ID)
{
  if ( !confirm('Are you sure you want to unequip this item?') )
    return;

  let Form_Data = new FormData();
  Form_Data.append('Action', 'Unequip_Item');
  Form_Data.append('Pokemon_ID', Pokemon_ID);

  await SendRequest('inventory', Form_Data)
    .then((Unequip_Item) => {
      Unequip_Item = JSON.parse(Unequip_Item);

      document.getElementById('Pokemon_Center_Moves_AJAX').className = Unequip_Item.Success ? 'success' : 'error';
      document.getElementById('Pokemon_Center_Moves_AJAX').innerHTML = Unequip_Item.Message;

      ShowInventoryTab('Held Item');
      ShowEquippedItems();
    })
    .catch((Error) => console.error('Error:', Error));
}
