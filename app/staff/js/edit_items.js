/**
 * Show a table allowing for edits to the specified item's database fields.
 */
function ShowItemEntry()
{
  let Form_Data = new FormData();
  Form_Data.append('Action', 'Show');

  const Selected_Item_ID = document.getElementsByName('item_entries')[0].value;
  Form_Data.append('Item_ID', Selected_Item_ID);

  SendRequest('edit_items', Form_Data)
    .then((Item_Entry) => {
      const Item_Entry_Data = JSON.parse(Item_Entry);

      if ( typeof Item_Entry_Data.Success !== 'undefined' )
      {
        document.getElementById('Edit_Item_AJAX').className = Item_Entry_Data.Success ? 'success' : 'error';
        document.getElementById('Edit_Item_AJAX').innerHTML = Item_Entry_Data.Message;
      }

      if ( typeof Item_Entry_Data.Item_Edit_Table !== 'undefined' )
        document.getElementById('Edit_Item_Table').innerHTML = Item_Entry_Data.Item_Edit_Table;
    })
    .catch((Error) => console.error('[ECRPG] There was en error while displaying the editable table for the selected item.', Error));
}

/**
 * Update the specified pokedex entry in the database.
 *
 * @param Item_ID
 */
function UpdateItemEntry(Item_ID)
{
  let Form_Data = new FormData();
  Form_Data.append('Action', 'Update');
  Form_Data.append('Item_ID', Item_ID);

  const Item_Description = document.getElementsByName('Item_Description')[0].value;
  const Can_Take_Item = document.getElementsByName('Can_Take_Item')[0].value;
  const Natural_Gift_Power = document.getElementsByName('Natural_Gift_Power')[0].value;
  const Natural_Gift_Type = document.getElementsByName('Natural_Gift_Type')[0].value;
  const Fling_Power = document.getElementsByName('Fling_Power')[0].value;
  const Attack_Boost = document.getElementsByName('Attack_Boost')[0].value;
  const Defense_Boost = document.getElementsByName('Defense_Boost')[0].value;
  const Sp_Attack_Boost = document.getElementsByName('Sp_Attack_Boost')[0].value;
  const Sp_Defense_Boost = document.getElementsByName('Sp_Defense_Boost')[0].value;
  const Speed_Boost = document.getElementsByName('Speed_Boost')[0].value;

  Form_Data.append('Item_Description', Item_Description);
  Form_Data.append('Can_Take_Item', Can_Take_Item);
  Form_Data.append('Natural_Gift_Power', Natural_Gift_Power);
  Form_Data.append('Natural_Gift_Type', Natural_Gift_Type);
  Form_Data.append('Fling_Power', Fling_Power);
  Form_Data.append('Attack_Boost', Attack_Boost);
  Form_Data.append('Defense_Boost', Defense_Boost);
  Form_Data.append('Sp_Attack_Boost', Sp_Attack_Boost);
  Form_Data.append('Sp_Defense_Boost', Sp_Defense_Boost);
  Form_Data.append('Speed_Boost', Speed_Boost);

  SendRequest('edit_items', Form_Data)
    .then((Update_Item_Entry) => {
      const Update_Item_Entry_Data = JSON.parse(Update_Item_Entry);

      if ( typeof Update_Item_Entry_Data.Success !== 'undefined' )
      {
        document.getElementById('Edit_Item_AJAX').className = Update_Item_Entry_Data.Success ? 'success' : 'error';
        document.getElementById('Edit_Item_AJAX').innerHTML = Update_Item_Entry_Data.Message;
      }

      if ( typeof Update_Item_Entry_Data.Item_Edit_Table !== 'undefined' )
        document.getElementById('Edit_Item_Table').innerHTML = Update_Item_Entry_Data.Item_Edit_Table;
    })
    .catch((Error) => console.error('[ECRPG] There was en error while updating the selected item.', Error));
}
