/**
 * Transfer the Pokemon to the specified user.
 */
function TransferPokemon()
{
  const Pokemon_Value = document.getElementsByName('Pokemon_ID_To_Transfer')[0].value;
  if ( typeof Pokemon_Value === 'undefined' )
    return;

  const Transfer_To_User_ID = document.getElementsByName('Transfer_To_User_ID')[0].value;
  if ( typeof Transfer_To_User_ID === 'undefined' )
    return;

  if ( !confirm('Are you sure you want to transfer this Pokemon?') )
    return;

  let Form_Data = new FormData();
  Form_Data.append('Pokemon_Value', Pokemon_Value);
  Form_Data.append('Pokemon_Action', 'Transfer');
  Form_Data.append('Transfer_To_User_ID', Transfer_To_User_ID);

  SendRequest('transfer_pokemon', Form_Data)
    .then((Pokemon_Data) => {
      Pokemon_Data = JSON.parse(Pokemon_Data);

      document.getElementById('Modification_AJAX').className = Pokemon_Data.Success ? 'success' : 'error';
      document.getElementById('Modification_AJAX').innerHTML = Pokemon_Data.Message;

      document.getElementById('Modification_Table').innerHTML = Pokemon_Data.Modification_Table;
    })
    .catch((Error) => console.error('Error:', Error));
}
