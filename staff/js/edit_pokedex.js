/**
 * Show a table for all unique areas where Pokemon can be obtained, given the specified database table.
 *
 * @param Pokedex_ID
 */
function ShowPokedexEntry()
{
  let Form_Data = new FormData();
  Form_Data.append('Action', 'Show');

  const Selected_Pokedex_ID = document.getElementsByName('pokedex_entries')[0].value;
  Form_Data.append('Pokedex_ID', Selected_Pokedex_ID);

  SendRequest('edit_pokedex', Form_Data)
    .then((Pokedex_Entry) => {
      const Pokedex_Entry_Data = JSON.parse(Pokedex_Entry);

      if ( typeof Pokedex_Entry_Data.Success !== 'undefined' )
      {
        document.getElementById('Edit_Pokedex_AJAX').className = Pokedex_Entry_Data.Success ? 'success' : 'error';
        document.getElementById('Edit_Pokedex_AJAX').innerHTML = Pokedex_Entry_Data.Message;
      }

      if ( typeof Pokedex_Entry_Data.Edit_Table !== 'undefined' )
        document.getElementById('Edit_Pokedex_Table').innerHTML = Pokedex_Entry_Data.Edit_Table;
    })
    .catch((Error) => console.error('[Absolute] An error occurred while displaying this Pokemon\'s dex entry:', Error));
}
