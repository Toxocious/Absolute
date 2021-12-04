class NPC
{
  constructor(Name, Grid_Engine_ID, Sprite, Properties, Type, Coords)
  {
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
  }
}
