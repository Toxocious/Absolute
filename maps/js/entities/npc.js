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
      this.DisplayDialogue();
  }

  /**
   * Display the NPC's dialogue.
   */
  DisplayDialogue()
  {
    Scene_Events.emit('NPC_Dialogue', this.Dialogue);
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
