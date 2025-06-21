/**
 * Show a table allowing for edits to the specified Pokemon's database fields.
 */
function ShowSpawnPokemonTable()
{
  let Form_Data = new FormData();
  Form_Data.append('Action', 'Show');

  const Selected_Pokedex_ID = document.getElementsByName('pokedex_entries')[0].value;
  Form_Data.append('Pokedex_ID', Selected_Pokedex_ID);

  SendRequest('spawn_pokemon', Form_Data)
    .then((Spawn_Pokemon) => {
      const Spawn_Pokemon_Data = JSON.parse(Spawn_Pokemon);

      if ( typeof Spawn_Pokemon_Data.Success !== 'undefined' )
      {
        document.getElementById('Spawn_Pokemon_AJAX').className = Spawn_Pokemon_Data.Success ? 'success' : 'error';
        document.getElementById('Spawn_Pokemon_AJAX').innerHTML = Spawn_Pokemon_Data.Message;
      }

      if ( typeof Spawn_Pokemon_Data.Spawn_Table !== 'undefined' )
        document.getElementById('Spawn_Pokemon_Table').innerHTML = Spawn_Pokemon_Data.Spawn_Table;
    })
    .catch((Error) => console.error('[ECRPG] An error occurred while displaying this Pokemon\'s spawn table:', Error));
}

/**
 * Given the specified information, spawn the Pokemon.
 *
 * @param Pokedex_ID
 */
function SpawnPokemon(Pokedex_ID)
{
  let Form_Data = new FormData();
  Form_Data.append('Pokedex_ID', Pokedex_ID);
  Form_Data.append('Action', 'Spawn');

  const Recipient = document.getElementsByName('Recipient')[0].value;
  if ( Recipient == '' )
  {
    alert('Enter a valid recipient username or ID.');
    return;
  }

  const Creation_Location = document.getElementsByName('Creation_Location')[0].value;
  const Level = document.getElementsByName('Level')[0].value;
  const Frozen = document.getElementsByName('Frozen')[0].value;
  const Gender = document.getElementsByName('Gender')[0].value;
  const Type = document.getElementsByName('Type')[0].value;
  const Nature = document.getElementsByName('Nature')[0].value;
  const Ability = document.getElementsByName('Ability')[0].value;
  const IV_HP = document.getElementsByName('IV_HP')[0].value;
  const IV_Attack = document.getElementsByName('IV_Attack')[0].value;
  const IV_Defense = document.getElementsByName('IV_Defense')[0].value;
  const IV_Sp_Attack = document.getElementsByName('IV_Sp_Attack')[0].value;
  const IV_Sp_Defense = document.getElementsByName('IV_Sp_Defense')[0].value;
  const IV_Speed = document.getElementsByName('IV_Speed')[0].value;
  const EV_HP = document.getElementsByName('EV_HP')[0].value;
  const EV_Attack = document.getElementsByName('EV_Attack')[0].value;
  const EV_Defense = document.getElementsByName('EV_Defense')[0].value;
  const EV_Sp_Attack = document.getElementsByName('EV_Sp_Attack')[0].value;
  const EV_Sp_Defense = document.getElementsByName('EV_Sp_Defense')[0].value;
  const EV_Speed = document.getElementsByName('EV_Speed')[0].value;

  Form_Data.append('Recipient', Recipient);
  Form_Data.append('Creation_Location', Creation_Location);
  Form_Data.append('Level', Level);
  Form_Data.append('Frozen', Frozen);
  Form_Data.append('Gender', Gender);
  Form_Data.append('Type', Type);
  Form_Data.append('Nature', Nature);
  Form_Data.append('Ability', Ability);
  Form_Data.append('IV_HP', IV_HP);
  Form_Data.append('IV_Attack', IV_Attack);
  Form_Data.append('IV_Defense', IV_Defense);
  Form_Data.append('IV_Sp_Attack', IV_Sp_Attack);
  Form_Data.append('IV_Sp_Defense', IV_Sp_Defense);
  Form_Data.append('IV_Speed', IV_Speed);
  Form_Data.append('EV_HP', EV_HP);
  Form_Data.append('EV_Attack', EV_Attack);
  Form_Data.append('EV_Defense', EV_Defense);
  Form_Data.append('EV_Sp_Attack', EV_Sp_Attack);
  Form_Data.append('EV_Sp_Defense', EV_Sp_Defense);
  Form_Data.append('EV_Speed', EV_Speed);

  SendRequest('spawn_pokemon', Form_Data)
    .then((Spawn_Pokemon) => {
      const Spawn_Pokemon_Data = JSON.parse(Spawn_Pokemon);

      document.getElementById('Spawn_Pokemon_AJAX').className = Spawn_Pokemon_Data.Success ? 'success' : 'error';
      document.getElementById('Spawn_Pokemon_AJAX').innerHTML = Spawn_Pokemon_Data.Message;

      document.getElementById('Spawn_Pokemon_Table').innerHTML = Spawn_Pokemon_Data.Spawn_Pokemon_Table;
    })
    .catch((Error) => console.error('[ECRPG] An error occurred while spawning the specified Pokemon:', Error));
}
