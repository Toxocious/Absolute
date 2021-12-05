class NPC extends Phaser.Scene
{
  constructor(Name, Grid_Engine_ID, Sprite, Properties, Type, Coords)
  {
    super();

    this.Name = Name;
    this.Grid_Engine_ID = Grid_Engine_ID;
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
    console.log(`[NPC | Interaction] Interacting with ${this.Name}`);

    this.GetDialogue();
    if ( this.Dialogue.length > 0 )
      this.DisplayDialogue();
  }

  /**
   * Display the NPC's dialogue.
   */
  DisplayDialogue()
  {
    console.log(`[NPC | Dialogue] Displaying the dialogue of ${this.Name}`);

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
