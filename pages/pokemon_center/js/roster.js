/**
 * Get the user's currently rostered Pokemon.
 */
async function GetRoster()
{
  let Form_Data = new FormData();
  Form_Data.append('Action', 'Get_Roster');

  await SendRequest('roster', Form_Data)
    .then((Roster_Data) => {
      Roster_Data = JSON.parse(Roster_Data)[0];

      for ( let Slot = 0; Slot < 6; Slot++ )
      {
        const Pokemon_Slot = Roster_Data[Slot];

        if ( typeof Pokemon_Slot === 'undefined' )
        {
          Icon_Path = '/images/Pokemon/Sprites/';
          Icon_Name = '0_mini';
          Display_Name = 'Empty';
        }
        else
        {
          Icon_Path = `/images/Pokemon/Icons/Normal`;
          Icon_Name = String(Pokemon_Slot.Pokedex_ID).padStart(3, '0');
          if ( Pokemon_Slot.Forme )
            Icon_Name += `-${Pokemon_Slot.Forme}`;

          if ( !Pokemon_Slot.Forme )
            Pokemon_Slot.Forme = '';

          Level = Math.cbrt(Pokemon_Slot.Experience);
          Display_Name = Pokemon_Slot.Type != 'Normal' ? `${Pokemon_Slot.Type}${Pokemon_Slot.Name}${Pokemon_Slot.Forme}` : `${Pokemon_Slot.Name}${Pokemon_Slot.Forme}`;
        }

        document.getElementById(`Roster_Slot_${Slot + 1}_Icon`).setAttribute('src', `${Icon_Path}/${Icon_Name}.png`);
        document.getElementById(`Roster_Slot_${Slot + 1}_Display_Name`).innerText = Display_Name;

        if ( typeof Pokemon_Slot !== 'undefined' )
        {
          document.getElementById(`Roster_Slot_${Slot + 1}_Level`).innerText = `Level: ${Level}`;

          const Move_Options = [1, 2, 3, 4, 5, 6, 7];
          for ( const Move_Option of Move_Options )
          {
            document.getElementById(`Roster_Slot_${Slot + 1}_Move_To_${Move_Option}`).setAttribute('onclick', `MovePokemon(${Pokemon_Slot.ID}, ${Move_Option})`);
          }
        }
      }
    })
    .catch((Error) => console.error('Error:', Error));
}

/**
 * Get the user's currently boxed Pokemon.
 *
 * @param Page
 */
async function GetBoxedPokemon(Page = 1)
{
  let Form_Data = new FormData();
  Form_Data.append('Action', 'Get_Box');
  Form_Data.append('Page', Page);

  await SendRequest('roster', Form_Data)
    .then((Boxed_Pokemon) => {
      Boxed_Pokemon = JSON.parse(Boxed_Pokemon)[0];

      if ( Boxed_Pokemon.Boxed_Pokemon.length === 0 )
      {
        document.querySelector('#Boxed_Pokemon > tr > td').innerHTML = 'You have no Pok&eacute;mon in your box.';
      }
      else
      {
        document.getElementById('Box_Pagination').innerHTML = Boxed_Pokemon.Pagination;

        document.querySelector('#Boxed_Pokemon > tr > td').innerHTML = '';

        for ( const Pokemon of Boxed_Pokemon.Boxed_Pokemon )
        {
          Icon_Path = `/images/Pokemon/Icons/Normal`;
          Icon_Name = String(Pokemon.Pokedex_ID).padStart(3, '0');
          if ( Pokemon.Forme )
            Icon_Name += `-${Pokemon.Forme}`;

          if ( !Pokemon.Forme )
            Pokemon.Forme = '';

          document.querySelector('#Boxed_Pokemon > tr > td').innerHTML += `
            <img
              src='${Icon_Path}/${Icon_Name}.png'
              onclick='PreviewPokemon(${Pokemon.ID});'
            />
          `;
        }
      }
    })
    .catch((Error) => console.error('Error:', Error));
}

/**
 * Get information about the selected Pokemon, and preview it to the user.
 *
 * @param Pokemon_ID
 */
async function PreviewPokemon(Pokemon_ID)
{
  let Form_Data = new FormData();
  Form_Data.append('Action', 'Preview_Pokemon');
  Form_Data.append('Pokemon_ID', Pokemon_ID);

  await SendRequest('roster', Form_Data)
    .then((Preview_Pokemon) => {
      Preview_Pokemon = JSON.parse(Preview_Pokemon)[0];

      document.querySelector('#Pokemon_Preview > tr > td').innerHTML = Preview_Pokemon.Pokemon_Data;
    })
    .catch((Error) => console.error('Error:', Error));
}

/**
 * Handle moving a Pokemon into and out of the user's roster.
 *
 * @param Pokemon_ID
 * @param Slot
 */
async function MovePokemon(Pokemon_ID, Slot)
{
  let Form_Data = new FormData();
  Form_Data.append('Action', 'Move_Pokemon');
  Form_Data.append('Pokemon_ID', Pokemon_ID);
  Form_Data.append('Slot', Slot);

  await SendRequest('roster', Form_Data)
    .then((Move_Pokemon) => {
      Move_Pokemon = JSON.parse(Move_Pokemon);

      document.getElementById('Pokemon_Center_Roster_AJAX').className = Move_Pokemon.Success;
      document.getElementById('Pokemon_Center_Roster_AJAX').innerHTML = Move_Pokemon.Message;

      document.querySelector('#Pokemon_Preview > tr > td').innerHTML = 'Click on a Pok&eacute;mon to view more information.';

      GetRoster();
      GetBoxedPokemon();
    });
    //.catch((Error) => console.error('Error:', Error));
}
