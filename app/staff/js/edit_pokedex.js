/**
 * Show a table allowing for edits to the specified Pokemon's database fields.
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
    .catch((Error) => console.error('[ECRPG] An error occurred while displaying this Pokemon\'s dex entry:', Error));
}

/**
 * Update the specified pokedex entry in the database.
 *
 * @param Pokedex_ID
 */
function UpdatePokedexEntry(Pokedex_ID)
{
  let Form_Data = new FormData();
  Form_Data.append('Action', 'Update');
  Form_Data.append('Pokedex_ID', Pokedex_ID);

  const Pokemon = document.getElementsByName('Pokemon')[0].value;
  const Forme = document.getElementsByName('Forme')[0].value;
  const Type_Primary = document.getElementsByName('Type_Primary')[0].value;
  const Type_Secondary = document.getElementsByName('Type_Secondary')[0].value;
  const Base_HP = document.getElementsByName('HP')[0].value;
  const Base_Attack = document.getElementsByName('Attack')[0].value;
  const Base_Defense = document.getElementsByName('Defense')[0].value;
  const Base_Sp_Attack = document.getElementsByName('SpAttack')[0].value;
  const Base_Sp_Defense = document.getElementsByName('SpDefense')[0].value;
  const Base_Speed = document.getElementsByName('Speed')[0].value;
  const HP_EV = document.getElementsByName('EV_HP')[0].value;
  const Attack_EV = document.getElementsByName('EV_Attack')[0].value;
  const Defense_EV = document.getElementsByName('EV_Defense')[0].value;
  const Sp_Attack_EV = document.getElementsByName('EV_SpAttack')[0].value;
  const Sp_Defense_EV = document.getElementsByName('EV_SpDefense')[0].value;
  const Speed_EV = document.getElementsByName('EV_Speed')[0].value;
  const Female_Odds = document.getElementsByName('Female')[0].value;
  const Male_Odds = document.getElementsByName('Male')[0].value;
  const Genderless_Odds = document.getElementsByName('Genderless')[0].value;
  const Height = document.getElementsByName('Height')[0].value;
  const Weight = document.getElementsByName('Weight')[0].value;
  const Exp_Yield = document.getElementsByName('Exp_Yield')[0].value;
  const Is_Baby = document.getElementsByName('Is_Baby')[0].value;
  const Is_Mythical = document.getElementsByName('Is_Mythical')[0].value;
  const Is_Legendary = document.getElementsByName('Is_Legendary')[0].value;

  Form_Data.append('Pokemon', Pokemon);
  Form_Data.append('Forme', Forme);
  Form_Data.append('Type_Primary', Type_Primary);
  Form_Data.append('Type_Secondary', Type_Secondary);
  Form_Data.append('Base_HP', Base_HP);
  Form_Data.append('Base_Attack', Base_Attack);
  Form_Data.append('Base_Defense', Base_Defense);
  Form_Data.append('Base_Sp_Attack', Base_Sp_Attack);
  Form_Data.append('Base_Sp_Defense', Base_Sp_Defense);
  Form_Data.append('Base_Speed', Base_Speed);
  Form_Data.append('HP_EV', HP_EV);
  Form_Data.append('Attack_EV', Attack_EV);
  Form_Data.append('Defense_EV', Defense_EV);
  Form_Data.append('Sp_Attack_EV', Sp_Attack_EV);
  Form_Data.append('Sp_Defense_EV', Sp_Defense_EV);
  Form_Data.append('Speed_EV', Speed_EV);
  Form_Data.append('Female_Odds', Female_Odds);
  Form_Data.append('Male_Odds', Male_Odds);
  Form_Data.append('Genderless_Odds', Genderless_Odds);
  Form_Data.append('Height', Height);
  Form_Data.append('Weight', Weight);
  Form_Data.append('Exp_Yield', Exp_Yield);
  Form_Data.append('Is_Baby', Is_Baby);
  Form_Data.append('Is_Mythical', Is_Mythical);
  Form_Data.append('Is_Legendary', Is_Legendary);

  SendRequest('edit_pokedex', Form_Data)
    .then((Update_Pokedex_Entry) => {
      const Update_Pokedex_Entry_Data = JSON.parse(Update_Pokedex_Entry);

      if ( typeof Update_Pokedex_Entry_Data.Success !== 'undefined' )
      {
        document.getElementById('Edit_Pokedex_AJAX').className = Update_Pokedex_Entry_Data.Success ? 'success' : 'error';
        document.getElementById('Edit_Pokedex_AJAX').innerHTML = Update_Pokedex_Entry_Data.Message;
      }

      if ( typeof Update_Pokedex_Entry_Data.Pokedex_Edit_Table !== 'undefined' )
        document.getElementById('Edit_Pokedex_Table').innerHTML = Update_Pokedex_Entry_Data.Pokedex_Edit_Table;
    })
    .catch((Error) => console.error('[ECRPG] An error occurred while updating this Pokemon\'s dex entry:', Error));
}
