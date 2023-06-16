/**
 * Get the user's currently rostered Pokemon.
 */
async function GetNicknameTabRoster()
{
  let Form_Data = new FormData();
  Form_Data.append('Action', 'Get_Roster');

  await SendRequest('nickname', Form_Data)
    .then((Roster_Data) => {
      Roster_Data = JSON.parse(Roster_Data);

      for ( let Slot = 0; Slot < 6; Slot++ )
      {
        const Pokemon_Slot = Roster_Data.Roster_Pokemon[Slot];

        if ( typeof Pokemon_Slot === 'undefined' )
        {
          Sprite_Path = '/images/Pokemon/Sprites/';
          Sprite_Name = '0';
          Nickname = 'Empty';
        }
        else
        {
          Sprite_Path = `/images/Pokemon/Sprites/${Pokemon_Slot.Type}`;
          Sprite_Name = String(Pokemon_Slot.Pokedex_ID).padStart(3, '0');
          if ( Pokemon_Slot.Forme )
            Sprite_Name += `-${Pokemon_Slot.Forme}`;

          if ( !Pokemon_Slot.Forme )
            Pokemon_Slot.Forme = '';

          if ( Pokemon_Slot.Nickname )
            Nickname = Pokemon_Slot.Nickname;
          else
            Nickname = Pokemon_Slot.Type != 'Normal' ? `${Pokemon_Slot.Type}${Pokemon_Slot.Name}${Pokemon_Slot.Forme}` : `${Pokemon_Slot.Name}${Pokemon_Slot.Forme}`;
        }

        document.getElementById(`Roster_Slot_${Slot + 1}_Sprite`).setAttribute('src', `${Sprite_Path}/${Sprite_Name}.png`);
        document.getElementById(`Roster_Slot_${Slot + 1}_Nickname`).innerText = Nickname;

        if ( typeof Pokemon_Slot !== 'undefined' )
        {
          document.getElementById(`Roster_Slot_${Slot + 1}_Button`).disabled = false;
          document.getElementById(`Roster_Slot_${Slot + 1}_Button`).setAttribute('onclick', `UpdateNickname(${Pokemon_Slot.ID}, ${Slot + 1})`);
        }
      }
    })
    .catch((Error) => console.error('Error:', Error));
}

/**
 * Update the nickname of the specified Pokemon.
 *
 * @param Pokemon_ID
 * @param Roster_Slot
 */
async function UpdateNickname(Pokemon_ID, Roster_Slot)
{
  const Nickname = document.getElementsByName(`Roster_Slot_${Roster_Slot}_Nick_Input`)[0].value;

  let Form_Data = new FormData();
  Form_Data.append('Action', 'Update_Nickname');
  Form_Data.append('Pokemon_ID', Pokemon_ID);
  Form_Data.append('Nickname', Nickname);

  await SendRequest('nickname', Form_Data)
    .then((Nickname_Data) => {
      Nickname_Data = JSON.parse(Nickname_Data);

      document.getElementById('Pokemon_Center_Nickname_AJAX').className = Nickname_Data.Success ? 'success' : 'error';
      document.getElementById('Pokemon_Center_Nickname_AJAX').innerHTML = Nickname_Data.Message;

      GetNicknameTabRoster();
    })
    .catch((Error) => console.error('Error:', Error));
}
