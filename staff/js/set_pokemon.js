/**
 * Show a table for all unique areas where Pokemon can be obtained, given the specified database table.
 *
 * @param Database_Table
 */
function ShowObtainablePokemonByTable(Database_Table)
{
  let Form_Data = new FormData();
  Form_Data.append('Database_Table', Database_Table);
  Form_Data.append('Action', 'Show');

  SendRequest('set_pokemon', Form_Data)
    .then((Obtainable_Pokemon) => {
      const Obtainable_Pokemon_Data = JSON.parse(Obtainable_Pokemon);

      document.getElementById('Set_Pokemon_Table').innerHTML = Obtainable_Pokemon_Data.Obtainable_Table;
    })
    .catch((Error) => console.error('[Absolute] An error occurred while displaying Pokemon by their table:', Error));
}

/**
 * Show all obtainable Pokemon given a specified database table and location.
 *
 * @param Database_Table
 * @param Obtainable_Location
 */
function ShowObtainablePokemonByLocation(Database_Table, Obtainable_Location)
{
  let Form_Data = new FormData();
  Form_Data.append('Database_Table', Database_Table);
  Form_Data.append('Obtainable_Location', Obtainable_Location);
  Form_Data.append('Action', 'Show_Location');

  SendRequest('set_pokemon', Form_Data)
    .then((Obtainable_Pokemon) => {
      const Obtainable_Pokemon_Data = JSON.parse(Obtainable_Pokemon);

      document.getElementById('Set_Pokemon_Table').innerHTML = Obtainable_Pokemon_Data.Obtainable_Table;
    })
    .catch((Error) => console.error('[Absolute] An error occurred while displaying Pokemon by their table and location:', Error));
}

/**
 * Edit the specified Pokemon from the specified table.
 *
 * @param Database_Table
 * @param Pokemon_Database_ID
 */
function EditSetPokemon(Database_Table, Pokemon_Database_ID)
{
  let Form_Data = new FormData();
  Form_Data.append('Database_Table', Database_Table);
  Form_Data.append('Pokemon_Database_ID', Pokemon_Database_ID);
  Form_Data.append('Action', 'Edit_Pokemon_Entry');

  SendRequest('set_pokemon', Form_Data)
    .then((Edit_Pokemon) => {
      const Edit_Pokemon_Data = JSON.parse(Edit_Pokemon);

      document.getElementById('Set_Pokemon_Table').innerHTML = Edit_Pokemon_Data.Edit_Table;
    })
    .catch((Error) => console.error('[Absolute] An error occurred while editing this Pokemon\'s entry:', Error));
}
