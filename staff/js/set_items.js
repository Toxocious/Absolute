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
