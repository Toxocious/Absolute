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
    .catch((Error) => console.error('[ECRPG] An error occurred while displaying Pokemon by their table:', Error));
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
    .catch((Error) => console.error('[ECRPG] An error occurred while displaying Pokemon by their table and location:', Error));
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
    .catch((Error) => console.error('[ECRPG] An error occurred while editing this Pokemon\'s entry:', Error));
}

/**
 * Finalize edited Pokemon values.
 *
 * @param Database_Table
 * @param Pokemon_Database_ID
 */
function FinalizePokemonEdit(Database_Table, Pokemon_Database_ID)
{
  let Form_Data = new FormData();
  Form_Data.append('Database_Table', Database_Table);
  Form_Data.append('Pokemon_Database_ID', Pokemon_Database_ID);
  Form_Data.append('Action', 'Finalize_Pokemon_Edit');

  const Pokemon_Active = document.getElementsByName('Is_Pokemon_Active')[0].value;
  Form_Data.append('Pokemon_Active', Pokemon_Active);

  const Pokemon_Dex_ID = document.getElementsByName('Pokemon_Species')[0].value;
  Form_Data.append('Pokemon_Dex_ID', Pokemon_Dex_ID);

  const Obtained_Text = document.getElementsByName('Obtained_Text')[0].value;
  Form_Data.append('Obtained_Text', Obtained_Text);

  if ( document.getElementsByName('Encounter_Weight').length > 0 )
  {
    const Encounter_Weight = document.getElementsByName('Encounter_Weight')[0].value;
    Form_Data.append('Encounter_Weight', Encounter_Weight);
  }

  if ( document.getElementsByName('Min_Level').length > 0 )
  {
    const Min_Level = document.getElementsByName('Min_Level')[0].value;
    Form_Data.append('Min_Level', Min_Level);
  }

  if ( document.getElementsByName('Max_Level').length > 0 )
  {
    const Max_Level = document.getElementsByName('Max_Level')[0].value;
    Form_Data.append('Max_Level', Max_Level);
  }

  if ( document.getElementsByName('Min_Map_Exp').length > 0 )
  {
    const Min_Map_Exp = document.getElementsByName('Min_Map_Exp')[0].value;
    Form_Data.append('Min_Map_Exp', Min_Map_Exp);
  }

  if ( document.getElementsByName('Max_Map_Exp').length > 0 )
  {
    const Max_Map_Exp = document.getElementsByName('Max_Map_Exp')[0].value;
    Form_Data.append('Max_Map_Exp', Max_Map_Exp);
  }

  if ( document.getElementsByName('Pokemon_Remaining').length > 0 )
  {
    const Pokemon_Remaining = document.getElementsByName('Pokemon_Remaining')[0].value;
    Form_Data.append('Pokemon_Remaining', Pokemon_Remaining);
  }

  if ( document.getElementsByName('Pokemon_Type').length > 0 )
  {
    const Pokemon_Type = document.getElementsByName('Pokemon_Type')[0].value;
    Form_Data.append('Pokemon_Type', Pokemon_Type);
  }

  if ( document.getElementsByName('Money_Cost').length > 0 )
  {
    const Money_Cost = document.getElementsByName('Money_Cost')[0].value;
    Form_Data.append('Money_Cost', Money_Cost);
  }

  if ( document.getElementsByName('Abso_Coins_Cost').length > 0 )
  {
    const Abso_Coins_Cost = document.getElementsByName('Abso_Coins_Cost')[0].value;
    Form_Data.append('Abso_Coins_Cost', Abso_Coins_Cost);
  }

  SendRequest('set_pokemon', Form_Data)
    .then((Finalize_Pokemon_Edit) => {
      const Finalize_Pokemon_Edit_Data = JSON.parse(Finalize_Pokemon_Edit);

      document.getElementById('Set_Pokemon_AJAX').className = Finalize_Pokemon_Edit_Data.Success ? 'success' : 'error';
      document.getElementById('Set_Pokemon_AJAX').innerHTML = Finalize_Pokemon_Edit_Data.Message;

      document.getElementById('Set_Pokemon_Table').innerHTML = Finalize_Pokemon_Edit_Data.Finalized_Edit_Table;
    })
    .catch((Error) => console.error('[ECRPG] An error occurred while editing this Pokemon\'s entry:', Error));
}

/**
 * Show a table to allow cutomization of a new Pokemon for the selected location.
 *
 * @param Database_Table
 * @param Obtainable_Location
 */
function ShowPokemonCreationTable(Database_Table, Obtainable_Location)
{
  let Form_Data = new FormData();
  Form_Data.append('Database_Table', Database_Table);
  Form_Data.append('Obtainable_Location', Obtainable_Location);
  Form_Data.append('Action', 'Create_New_Pokemon');

  SendRequest('set_pokemon', Form_Data)
    .then((Create_New_Pokemon) => {
      const Create_New_Pokemon_Data = JSON.parse(Create_New_Pokemon);

      document.getElementById('Set_Pokemon_Table').innerHTML = Create_New_Pokemon_Data.Creation_Table;
    })
    .catch((Error) => console.error('[ECRPG] An error occurred while displaying the create a Pokemon table:', Error));
}


/**
 * Finalize edited Pokemon values.
 *
 * @param Database_Table
 * @param Pokemon_Database_ID
 */
function FinalizePokemonCreation(Database_Table, Pokemon_Database_ID)
{
  let Form_Data = new FormData();
  Form_Data.append('Database_Table', Database_Table);
  Form_Data.append('Pokemon_Database_ID', Pokemon_Database_ID);
  Form_Data.append('Action', 'Finalize_Pokemon_Creation');

  const Pokemon_Active = document.getElementsByName('Is_Pokemon_Active')[0].value;
  Form_Data.append('Pokemon_Active', Pokemon_Active);

  const Pokemon_Dex_ID = document.getElementsByName('Pokemon_Species')[0].value;
  Form_Data.append('Pokemon_Dex_ID', Pokemon_Dex_ID);

  const Obtained_Text = document.getElementsByName('Obtained_Text')[0].value;
  Form_Data.append('Obtained_Text', Obtained_Text);

  if ( document.getElementsByName('Encounter_Weight').length > 0 )
  {
    const Encounter_Weight = document.getElementsByName('Encounter_Weight')[0].value;
    Form_Data.append('Encounter_Weight', Encounter_Weight);
  }

  if ( document.getElementsByName('Encounter_Zone').length > 0 )
  {
    const Encounter_Zone = document.getElementsByName('Encounter_Zone')[0].value;
    Form_Data.append('Encounter_Zone', Encounter_Zone);
  }

  if ( document.getElementsByName('Min_Level').length > 0 )
  {
    const Min_Level = document.getElementsByName('Min_Level')[0].value;
    Form_Data.append('Min_Level', Min_Level);
  }

  if ( document.getElementsByName('Max_Level').length > 0 )
  {
    const Max_Level = document.getElementsByName('Max_Level')[0].value;
    Form_Data.append('Max_Level', Max_Level);
  }

  if ( document.getElementsByName('Min_Map_Exp').length > 0 )
  {
    const Min_Map_Exp = document.getElementsByName('Min_Map_Exp')[0].value;
    Form_Data.append('Min_Map_Exp', Min_Map_Exp);
  }

  if ( document.getElementsByName('Max_Map_Exp').length > 0 )
  {
    const Max_Map_Exp = document.getElementsByName('Max_Map_Exp')[0].value;
    Form_Data.append('Max_Map_Exp', Max_Map_Exp);
  }

  if ( document.getElementsByName('Pokemon_Remaining').length > 0 )
  {
    const Pokemon_Remaining = document.getElementsByName('Pokemon_Remaining')[0].value;
    Form_Data.append('Pokemon_Remaining', Pokemon_Remaining);
  }

  if ( document.getElementsByName('Pokemon_Type').length > 0 )
  {
    const Pokemon_Type = document.getElementsByName('Pokemon_Type')[0].value;
    Form_Data.append('Pokemon_Type', Pokemon_Type);
  }

  if ( document.getElementsByName('Money_Cost').length > 0 )
  {
    const Money_Cost = document.getElementsByName('Money_Cost')[0].value;
    Form_Data.append('Money_Cost', Money_Cost);
  }

  if ( document.getElementsByName('Abso_Coins_Cost').length > 0 )
  {
    const Abso_Coins_Cost = document.getElementsByName('Abso_Coins_Cost')[0].value;
    Form_Data.append('Abso_Coins_Cost', Abso_Coins_Cost);
  }

  SendRequest('set_pokemon', Form_Data)
    .then((Finalize_Pokemon_Creation) => {
      const Finalize_Pokemon_Creation_Data = JSON.parse(Finalize_Pokemon_Creation);

      document.getElementById('Set_Pokemon_AJAX').className = Finalize_Pokemon_Creation_Data.Success ? 'success' : 'error';
      document.getElementById('Set_Pokemon_AJAX').innerHTML = Finalize_Pokemon_Creation_Data.Message;
    })
    .catch((Error) => console.error('[ECRPG] An error occurred while creating this Pokemon\'s entry:', Error));
}
