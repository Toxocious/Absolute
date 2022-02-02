let Move_Changing = false;

/**
 * Display HTML select/options for changing moves.
 *
 * @param Pokemon_ID
 * @param Move_Slot
 */
function SelectMove(Pokemon_ID, Move_Slot)
{
  if ( Move_Changing )
    return;

  Move_Changing = true;

  let Form_Data = new FormData();
  Form_Data.append('Pokemon_Value', Pokemon_ID);
  Form_Data.append('Pokemon_Action', 'Move_List');
  Form_Data.append('Pokemon_Move_Slot', Move_Slot);

  SendRequest('modify_pokemon', Form_Data)
    .then((Pokemon_Data) => {
      Pokemon_Data = JSON.parse(Pokemon_Data);

      document.getElementById(`${Pokemon_ID}_Move_${Move_Slot}`).innerHTML = Pokemon_Data.Move_List;
    })
    .catch((Error) => console.error('Error:', Error));
}

/**
 * Update the selected move w/ the desired move.
 *
 * @param Pokemon_ID
 * @param Move_Slot
 */
function UpdateMove(Pokemon_ID, Move_Slot)
{
  Move_Changing = false;

  const Selected_Move = document.getElementsByName(`${Pokemon_ID}_Move_${Move_Slot}`)[0];
  const Move_Value = Selected_Move[Selected_Move.selectedIndex].value;
  const Move_Name = Selected_Move[Selected_Move.selectedIndex].text;

  let Form_Data = new FormData();
  Form_Data.append('Pokemon_Value', Pokemon_ID);
  Form_Data.append('Pokemon_Action', 'Update_Move');
  Form_Data.append('Pokemon_Move_Slot', Move_Slot);
  Form_Data.append('Pokemon_Move_Value', Move_Value);

  SendRequest('modify_pokemon', Form_Data)
    .then((Pokemon_Data) => {
      Pokemon_Data = JSON.parse(Pokemon_Data);

      document.getElementById('Modification_AJAX').className = Pokemon_Data.Success ? 'success' : 'error';
      document.getElementById('Modification_AJAX').innerHTML = Pokemon_Data.Message;

      document.getElementById(`${Pokemon_ID}_Move_${Move_Slot}`).innerHTML = `<b>${Move_Name}</b>`;
    })
    .catch((Error) => console.error('Error:', Error));
}

/**
 * Show the modification table for the selected Pokemon.
 */
function ShowPokemon()
{
  const Pokemon_Value = document.getElementsByName('Pokemon_Search')[0].value;
  if ( typeof Pokemon_Value === 'undefined' )
    return;

  let Form_Data = new FormData();
  Form_Data.append('Pokemon_Value', Pokemon_Value);
  Form_Data.append('Pokemon_Action', 'Show');

  SendRequest('modify_pokemon', Form_Data)
    .then((Pokemon_Data) => {
      Pokemon_Data = JSON.parse(Pokemon_Data);

      if ( typeof Pokemon_Data.Success !== 'undefined' )
      {
        document.getElementById('Modification_AJAX').className = Pokemon_Data.Success ? 'success' : 'error';
        document.getElementById('Modification_AJAX').innerHTML = Pokemon_Data.Message;
      }

      if ( typeof Pokemon_Data.Modification_Table !== 'undefined' )
        document.getElementById('Modification_Table').innerHTML = Pokemon_Data.Modification_Table;
    })
    .catch((Error) => console.error('Error:', Error));
}

/**
 * Toggle whether or not the selected Pokemon is frozen.
 */
function TogglePokemonFreeze()
{
  const Pokemon_Value = document.getElementsByName('Pokemon_ID_To_Update')[0].value;
  if ( typeof Pokemon_Value === 'undefined' )
    return;

  const Pokemon_Freeze_Status = document.getElementsByName('Pokemon_Freeze_Status')[0].value;
  if ( typeof Pokemon_Freeze_Status === 'undefined' )
    return;

  let Form_Data = new FormData();
  Form_Data.append('Pokemon_Value', Pokemon_Value);
  Form_Data.append('Pokemon_Action', 'Freeze');
  Form_Data.append('Pokemon_Frozen_Status', Pokemon_Freeze_Status);

  SendRequest('modify_pokemon', Form_Data)
    .then((Pokemon_Data) => {
      Pokemon_Data = JSON.parse(Pokemon_Data);

      document.getElementById('Modification_AJAX').className = Pokemon_Data.Success ? 'success' : 'error';
      document.getElementById('Modification_AJAX').innerHTML = Pokemon_Data.Message;
      document.getElementById('Modification_Table').innerHTML = Pokemon_Data.Modification_Table;
    })
    .catch((Error) => console.error('Error:', Error));
}

/**
 * Delete the Pokemon.
 */
function DeletePokemon()
{
  if ( !confirm('Are you sure you want to delete this Pokemon?') )
    return;

  const Pokemon_Value = document.getElementsByName('Pokemon_ID_To_Update')[0].value;
  if ( typeof Pokemon_Value === 'undefined' )
    return;

  let Form_Data = new FormData();
  Form_Data.append('Pokemon_Value', Pokemon_Value);
  Form_Data.append('Pokemon_Action', 'Delete');

  SendRequest('modify_pokemon', Form_Data)
    .then((Pokemon_Data) => {
      Pokemon_Data = JSON.parse(Pokemon_Data);

      document.getElementById('Modification_AJAX').className = Pokemon_Data.Success ? 'success' : 'error';
      document.getElementById('Modification_AJAX').innerHTML = Pokemon_Data.Message;
      document.getElementById('Modification_Table').innerHTML = '';
    })
    .catch((Error) => console.error('Error:', Error));
}

/**
 * Update the Pokemon.
 */
function UpdatePokemon()
{
  if ( !confirm('Are you sure you want to update this Pokemon?') )
    return;

  const Pokemon_Value = document.getElementsByName('Pokemon_ID_To_Update')[0].value;
  if ( typeof Pokemon_Value === 'undefined' )
    return;

  const Pokemon_Level = document.getElementsByName('Level')[0].value ?? 5;
  const Pokemon_Gender = document.getElementsByName('Gender')[0].value;
  const Pokemon_Nature = document.getElementsByName('Nature')[0].value;
  const Pokemon_Ability = document.getElementsByName('Ability')[0].value;

  let Form_Data = new FormData();
  Form_Data.append('Pokemon_Value', Pokemon_Value);
  Form_Data.append('Pokemon_Action', 'Update_Pokemon');
  Form_Data.append('Pokemon_Level', Pokemon_Level);
  Form_Data.append('Pokemon_Gender', Pokemon_Gender);
  Form_Data.append('Pokemon_Nature', Pokemon_Nature);
  Form_Data.append('Pokemon_Ability', Pokemon_Ability);

  SendRequest('modify_pokemon', Form_Data)
    .then((Pokemon_Data) => {
      Pokemon_Data = JSON.parse(Pokemon_Data);

      document.getElementById('Modification_AJAX').className = Pokemon_Data.Success ? 'success' : 'error';
      document.getElementById('Modification_AJAX').innerHTML = Pokemon_Data.Message;
      document.getElementById('Modification_Table').innerHTML = Pokemon_Data.Modification_Table;
    })
    .catch((Error) => console.error('Error:', Error));
}
