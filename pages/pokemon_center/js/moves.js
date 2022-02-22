let Changing_Move = false;

/**
 * Get the user's currently rostered Pokemon.
 */
async function GetMoveTabRoster()
{
  let Form_Data = new FormData();
  Form_Data.append('Action', 'Get_Roster');

  await SendRequest('moves', Form_Data)
    .then((Roster_Data) => {
      Roster_Data = JSON.parse(Roster_Data)[0];

      for ( let Slot = 0; Slot < 6; Slot++ )
      {
        const Pokemon_Slot = Roster_Data[Slot];

        if ( typeof Pokemon_Slot === 'undefined' )
        {
          Sprite_Path = '/images/Pokemon/Sprites/';
          Sprite_Name = '0';
          Display_Name = 'Empty';
        }
        else
        {
          Sprite_Path = `/images/Pokemon/Sprites/${Pokemon_Slot.Type}`;
          Sprite_Name = String(Pokemon_Slot.Pokedex_ID).padStart(3, '0');
          if ( Pokemon_Slot.Forme )
            Sprite_Name += `-${Pokemon_Slot.Forme}`;

          if ( !Pokemon_Slot.Forme )
            Pokemon_Slot.Forme = '';

          Level = Math.cbrt(Pokemon_Slot.Experience);
          Display_Name = Pokemon_Slot.Type != 'Normal' ? `${Pokemon_Slot.Type}${Pokemon_Slot.Name}${Pokemon_Slot.Forme}` : `${Pokemon_Slot.Name}${Pokemon_Slot.Forme}`;
        }

        document.getElementById(`Roster_Slot_${Slot + 1}_Sprite`).setAttribute('src', `${Sprite_Path}/${Sprite_Name}.png`);
        document.getElementById(`Roster_Slot_${Slot + 1}_Display_Name`).innerText = Display_Name;

        if ( typeof Pokemon_Slot !== 'undefined' )
        {
          Object.keys(Pokemon_Slot.Move_Data).forEach(function(Move_Slot)
          {
            document.getElementById(`Roster_Slot_${Slot + 1}_Move_${Move_Slot}`).setAttribute('onclick', `SelectMoveSlot(${Slot + 1}, ${Pokemon_Slot.ID}, ${Move_Slot})`);
            document.getElementById(`Roster_Slot_${Slot + 1}_Move_${Move_Slot}`).innerHTML = `<b>${Pokemon_Slot.Move_Data[Move_Slot].Name}</b>`;
          });
        }
      }
    })
    .catch((Error) => console.error('Error:', Error));
}

/**
 * Select a move and prepare it for being changed.
 *
 * @param Pokemon_ID
 * @param Move_Slot
 */
async function SelectMoveSlot(Roster_Slot, Pokemon_ID, Move_Slot)
{
  if ( Changing_Move )
    return false;

  Changing_Move = true;

  let Form_Data = new FormData();
  Form_Data.append('Action', 'Select_Move');
  Form_Data.append('Pokemon_ID', Pokemon_ID);
  Form_Data.append('Move_Slot', Move_Slot);

  await SendRequest('moves', Form_Data)
    .then((Move_Data) => {
      Move_Data = JSON.parse(Move_Data);

      document.getElementById(`Roster_Slot_${Roster_Slot}_Move_${Move_Slot}`).innerHTML = Move_Data.Dropdown_HTML;
    })
    .catch((Error) => console.error('Error:', Error));
}

/**
 * Update the specified move with the new desired move.
 *
 * @param Pokemon_ID
 * @param Move_Slot
 */
async function UpdateMoveSlot(Pokemon_ID, Move_Slot)
{
  if ( !Changing_Move )
    return false;

  Changing_Move = false;

  const Move_ID = document.getElementsByName(`${Pokemon_ID}_Move_${Move_Slot}`)[0].value;

  let Form_Data = new FormData();
  Form_Data.append('Action', 'Update_Move');
  Form_Data.append('Pokemon_ID', Pokemon_ID);
  Form_Data.append('Move_Slot', Move_Slot);
  Form_Data.append('Move_ID', Move_ID);

  await SendRequest('moves', Form_Data)
    .then((Move_Data) => {
      Move_Data = JSON.parse(Move_Data);

      document.getElementById('Pokemon_Center_Moves_AJAX').className = Move_Data.Success ? 'success' : 'error';
      document.getElementById('Pokemon_Center_Moves_AJAX').innerHTML = Move_Data.Message;

      GetMoveTabRoster();
    })
    .catch((Error) => console.error('Error:', Error));
}
