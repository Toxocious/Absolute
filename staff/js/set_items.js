/**
 * Show a table for all unique areas where items can be obtained, given the specified database table.
 *
 * @param Database_Table
 */
function ShowObtainableItemsByTable(Database_Table)
{
  let Form_Data = new FormData();
  Form_Data.append('Database_Table', Database_Table);
  Form_Data.append('Action', 'Show');

  SendRequest('set_items', Form_Data)
    .then((Obtainable_Items) => {
      const Obtainable_Items_Data = JSON.parse(Obtainable_Items);

      document.getElementById('Set_Items_Table').innerHTML = Obtainable_Items_Data.Obtainable_Table;
    })
    .catch((Error) => console.error('[Absolute] An error occurred while displaying items by their table:', Error));
}

/**
 * Show all obtainable Items given a specified database table and location.
 *
 * @param Database_Table
 * @param Obtainable_Location
 */
function ShowObtainableItemsByLocation(Database_Table, Obtainable_Location)
{
  let Form_Data = new FormData();
  Form_Data.append('Database_Table', Database_Table);
  Form_Data.append('Obtainable_Location', Obtainable_Location);
  Form_Data.append('Action', 'Show_Location');

  SendRequest('set_items', Form_Data)
    .then((Obtainable_Items) => {
      const Obtainable_Items_Data = JSON.parse(Obtainable_Items);

      document.getElementById('Set_Items_Table').innerHTML = Obtainable_Items_Data.Obtainable_Table;
    })
    .catch((Error) => console.error('[Absolute] An error occurred while showing obtainable items by location:', Error));
}
