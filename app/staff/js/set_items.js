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
    .catch((Error) => console.error('[ECRPG] An error occurred while displaying items by their table:', Error));
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
    .catch((Error) => console.error('[ECRPG] An error occurred while showing obtainable items by location:', Error));
}

/**
 * Show a table to allow cutomization of a new item for the selected location.
 *
 * @param Database_Table
 * @param Obtainable_Location
 */
function ShowItemCreationTable(Database_Table, Obtainable_Location)
{
  let Form_Data = new FormData();
  Form_Data.append('Database_Table', Database_Table);
  Form_Data.append('Obtainable_Location', Obtainable_Location);
  Form_Data.append('Action', 'Create_Item_Entry');

  SendRequest('set_items', Form_Data)
    .then((Create_New_Item) => {
      const Create_New_Item_Data = JSON.parse(Create_New_Item);

      document.getElementById('Set_Items_Table').innerHTML = Create_New_Item_Data.Creation_Table;
    })
    .catch((Error) => console.error('[ECRPG] An error occurred while displaying the create a item table:', Error));
}

/**
 * Edit the specified Pokemon from the specified table.
 *
 * @param Database_Table
 * @param Pokemon_Database_ID
 */
function EditSetItem(Database_Table, Item_Database_ID)
{
  let Form_Data = new FormData();
  Form_Data.append('Database_Table', Database_Table);
  Form_Data.append('Item_Database_ID', Item_Database_ID);
  Form_Data.append('Action', 'Edit_Item_Entry');

  SendRequest('set_items', Form_Data)
    .then((Edit_Item) => {
      const Edit_Item_Data = JSON.parse(Edit_Item);

      document.getElementById('Set_Items_Table').innerHTML = Edit_Item_Data.Edit_Table;
    })
    .catch((Error) => console.error('[ECRPG] An error occurred while displaying this item\'s edit table:', Error));
}

/**
 * Finalize editing the specified item.
 *
 * @param Database_Table
 * @param Pokemon_Database_ID
 */
function FinalizeItemCreation(Database_Table, Item_Database_ID)
{
  let Form_Data = new FormData();
  Form_Data.append('Database_Table', Database_Table);
  Form_Data.append('Item_Database_ID', Item_Database_ID);
  Form_Data.append('Action', 'Finalize_Item_Creation');

  const Obtainable_Location = document.getElementsByName('Obtainable_Location')[0].value;
  Form_Data.append('Obtainable_Location', Obtainable_Location);

  const Is_Item_Active = document.getElementsByName('Is_Item_Active')[0].value;
  Form_Data.append('Is_Item_Active', Is_Item_Active);

  const Item_ID = document.getElementsByName('Item_ID')[0].value;
  Form_Data.append('Item_ID', Item_ID);

  const Items_Remaining = document.getElementsByName('Items_Remaining')[0].value;
  Form_Data.append('Items_Remaining', Items_Remaining);

  const Money_Cost = document.getElementsByName('Money_Cost')[0].value;
  Form_Data.append('Money_Cost', Money_Cost);

  const Abso_Coins_Cost = document.getElementsByName('Abso_Coins_Cost')[0].value;
  Form_Data.append('Abso_Coins_Cost', Abso_Coins_Cost);

  SendRequest('set_items', Form_Data)
    .then((Finalize_Item_Creation) => {
      const Finalize_Item_Creation_Data = JSON.parse(Finalize_Item_Creation);

      document.getElementById('Set_Items_AJAX').className = Finalize_Item_Creation_Data.Success ? 'success' : 'error';
      document.getElementById('Set_Items_AJAX').innerHTML = Finalize_Item_Creation_Data.Message;
    })
    .catch((Error) => console.error('[ECRPG] An error occurred while creating the specified item:', Error));
}

/**
 * Finalize editing the specified item.
 *
 * @param Database_Table
 * @param Pokemon_Database_ID
 */
function FinalizeItemEdit(Database_Table, Item_Database_ID)
{
  let Form_Data = new FormData();
  Form_Data.append('Database_Table', Database_Table);
  Form_Data.append('Item_Database_ID', Item_Database_ID);
  Form_Data.append('Action', 'Finalize_Item_Edit');

  const Is_Item_Active = document.getElementsByName('Is_Item_Active')[0].value;
  Form_Data.append('Is_Item_Active', Is_Item_Active);

  const Item_ID = document.getElementsByName('Item_ID')[0].value;
  Form_Data.append('Item_ID', Item_ID);

  const Items_Remaining = document.getElementsByName('Items_Remaining')[0].value;
  Form_Data.append('Items_Remaining', Items_Remaining);

  const Money_Cost = document.getElementsByName('Money_Cost')[0].value;
  Form_Data.append('Money_Cost', Money_Cost);

  const Abso_Coins_Cost = document.getElementsByName('Abso_Coins_Cost')[0].value;
  Form_Data.append('Abso_Coins_Cost', Abso_Coins_Cost);

  SendRequest('set_items', Form_Data)
    .then((Finalize_Item_Edit) => {
      const Finalize_Item_Edit_Data = JSON.parse(Finalize_Item_Edit);

      document.getElementById('Set_Items_AJAX').className = Finalize_Item_Edit_Data.Success ? 'success' : 'error';
      document.getElementById('Set_Items_AJAX').innerHTML = Finalize_Item_Edit_Data.Message;

      document.getElementById('Set_Items_Table').innerHTML = Finalize_Item_Edit_Data.Finalized_Edit_Table;
    })
    .catch((Error) => console.error('[ECRPG] An error occurred while editing this item\'s entry:', Error));
}
