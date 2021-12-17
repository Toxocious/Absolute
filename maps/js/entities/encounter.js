class Encounter extends Phaser.Scene
{
  constructor(Name, Grid_Engine_ID, Properties, Type, Coords, Render_Instance)
  {
    super();

    this.Name = Name;
    this.Grid_Engine_ID = Grid_Engine_ID;
    this.Render_Instance = Render_Instance;
    this.properties = Properties;
    this.type = Type;
    this.coords = Coords;
  }

  /**
   * Display the currently active encounter to the player.
   */
  DisplayEncounter()
  {
    MapGame.Network.SendRequest('Encounter').then((Encounter) => {
      Encounter = JSON.parse(Encounter);

      if ( Encounter.Generated_Encounter.Type !== 'Normal' )
        alert(`You found a wild ${Encounter.Generated_Encounter.Type} Pok&eacute;mon!`);

      document.getElementById('map_dialogue').innerHTML = `
        A wild <b>${Encounter.Generated_Encounter.Pokedex_Data.Display_Name}</b> appeared!
        <br />
        <img src='${Encounter.Generated_Encounter.Pokedex_Data.Sprite}' />
        <br />
        <b>${Encounter.Generated_Encounter.Pokedex_Data.Display_Name}</b>
        ${Encounter.Generated_Encounter.Gender.charAt(0)}
        (Level: ${Encounter.Generated_Encounter.Level.toLocaleString()})

        <br /><br />

        <div class='flex wrap' style='gap: 10px; justify-content: center; max-width: 240px;'>
          <button style='flex-basis: 100px;' onclick='MapGame.Encounter.FightEncounter();'>Fight</button>
          <button style='flex-basis: 100px;' onclick='MapGame.Encounter.CatchEncounter();'>Catch</button>
          <button style='flex-basis: 100px;' onclick='MapGame.Encounter.ReleaseEncounter();'>Release</button>
          <button style='flex-basis: 100px;' onclick='MapGame.Encounter.RunFromEncounter();'>Run</button>
        </div>
      `;
    });
  }

  /**
   * Run away from the active encounter.
   */
  RunFromEncounter()
  {
    if ( !MapGame.Player.In_Encounter )
      return;

    MapGame.Network.SendRequest({
      Action: 'Run',
    }, 'POST').then((Run_Data) => {
      Run_Data = JSON.parse(Run_Data);

      MapGame.Player.In_Encounter = false;
      MapGame.Player.Steps_Till_Encounter = Run_Data.Steps_Until_Next_Encounter;

      document.getElementById('map_dialogue').innerHTML = `You ran away from the wild Pok&eacute;mon.`;
    });
  }
}
