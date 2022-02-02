let Move_Changing = false;

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

      document.getElementById('Modification_Table').innerHTML = Pokemon_Data.Modification_Table;
    })
    .catch((Error) => console.error('Error:', Error));
}
