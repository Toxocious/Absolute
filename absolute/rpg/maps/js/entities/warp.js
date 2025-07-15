class Warp extends Phaser.Scene
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
}
