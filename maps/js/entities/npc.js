class NPC extends Phaser.Scene
{
  constructor(Name, Grid_Engine_ID, Sprite, Properties, Type, Coords, Render_Instance)
  {
    super();

    this.Name = Name;
    this.Grid_Engine_ID = Grid_Engine_ID;
    this.Render_Instance = Render_Instance;
    this.Sprite = Sprite;
    this.properties = Properties;
    this.type = Type;
    this.coords = Coords;
  }

  /**
   * Handle the initial NPC interaction.
   */
  Interact()
  {
    this.GetDialogue();
    if ( this.Dialogue.length > 0 )
    {
      MapGame.Player.In_Dialogue = true;

      this.Dialogue_State = 0;
      this.DisplayDialogue(this.Dialogue_State);
    }
  }

  /**
   * Display the NPC's dialogue.
   */
  DisplayDialogue(Dialogue_State)
  {
    document.getElementById('map_dialogue').innerHTML = `
      <div style='max-width: 230px;'>
        <b>${this.Name}</b>
        <br /><br />
        <i>"${this.Dialogue[Dialogue_State].value}"</i>
      </div>
    `;
  }

  /**
   * Get all lines of dialogue from the NPC.
   */
  GetDialogue()
  {
    this.Dialogue = [];

    for ( const Prop of this.properties )
    {
      if ( Prop.name.includes('dialogue_') )
        this.Dialogue.push(Prop);
    }
  }
}
