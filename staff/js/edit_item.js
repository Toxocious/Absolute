/**
 * Show a table allowing for edits to the specified item's database fields.
 */
function ShowItemEntry()
{
  let Form_Data = new FormData();
  Form_Data.append('Action', 'Show');

  const Selected_Item_ID = document.getElementsByName('item_entries')[0].value;
  Form_Data.append('Item_ID', Selected_Item_ID);

  SendRequest('edit_item', Form_Data)
    .then((Item_Entry) => {
      const Item_Entry_Data = JSON.parse(Item_Entry);

      if ( typeof Item_Entry_Data.Success !== 'undefined' )
      {
        document.getElementById('Edit_Item_AJAX').className = Item_Entry_Data.Success ? 'success' : 'error';
        document.getElementById('Edit_Item_AJAX').innerHTML = Item_Entry_Data.Message;
      }

      if ( typeof Item_Entry_Data.Item_Edit_Table !== 'undefined' )
        document.getElementById('Edit_item_Table').innerHTML = Item_Entry_Data.Item_Edit_Table;
    });
}
