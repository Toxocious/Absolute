/**
 * Given the specified information, spawn the item.
 */
function SpawnItem()
{
  let Form_Data = new FormData();
  Form_Data.append('Action', 'Spawn');

  const Item_ID = document.getElementsByName('item_entries')[0].value;
  if ( typeof Item_ID === 'undefined' || Item_ID == '' )
  {
    alert('Select a valid item.');
    return;
  }

  const Recipient = document.getElementsByName('Recipient')[0].value;
  if ( Recipient == '' )
  {
    alert('Enter a valid recipient username or ID.');
    return;
  }

  const Amount = document.getElementsByName('Amount')[0].value;
  if ( Amount == '' || Amount < 0 )
  {
    alert('Enter a valid numeric amount.');
    return;
  }

  Form_Data.append('Item_ID', Item_ID);
  Form_Data.append('Recipient', Recipient);
  Form_Data.append('Amount', Amount);

  SendRequest('spawn_items', Form_Data)
    .then((Spawn_Item) => {
      const Spawn_Item_Data = JSON.parse(Spawn_Item);

      document.getElementById('Spawn_Item_AJAX').className = Spawn_Item_Data.Success ? 'success' : 'error';
      document.getElementById('Spawn_Item_AJAX').innerHTML = Spawn_Item_Data.Message;
    })
    .catch((Error) => console.error('[ECRPG] An error occurred while spawning the specified item:', Error));
}
