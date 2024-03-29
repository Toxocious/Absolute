class TileInfo
{
  constructor(x, y, z)
  {
    this.x = x;
    this.y = y;
    this.z = z;
  }

  /**
   * Get the tile's information and check for objects.
   */
  GetTileInfo()
  {
    const Layer_Instance = this.GetLayerInstance();
    if ( !Layer_Instance )
      return false;

    const Tile_Data = Layer_Instance.data[this.x][this.y];
    if ( typeof Tile_Data === 'undefined' )
      return false;

    const Tile_Objects = this.GetObjectOnTile();

    return {
      Data: Tile_Data,
      Layer: Layer_Instance,
      Objects: Tile_Objects
    };
  }

  /**
   * Get the correct layer instance.
   */
  GetLayerInstance()
  {
    for ( const Layer of MapGame.Layers )
      if ( Layer.layer.name === `Layer_${this.z}` )
        return Layer.layer;

    return false;
  }

  /**
   * Get any objects on the tile.
   */
  GetObjectOnTile()
  {
    for ( const Obj of MapGame.Objects )
    {
      if ( Obj.type == 'transition' )
        continue;

      if ( Obj.type == 'encounter' )
        if ( Obj.coords.x == this.x && Obj.coords.y == this.y )
          return Obj;

      const Obj_Layer = this.DoesObjectHavePropertyOfName(Obj, 'charLayer');
      if ( Obj_Layer && Obj_Layer.value.replace('Layer_', '') == this.z && Obj.coords.x == this.x && Obj.coords.y == this.y )
        return Obj;
    }
  }

  /**
   * Check if the tile object has a given property.
   */
  DoesObjectHavePropertyOfName(Obj, Property_Name)
  {
    if
    (
      typeof Obj !== 'object' ||
      typeof Obj.properties === 'undefined' ||
      typeof Property_Name !== 'string'
    )
      return false;

    for ( const Prop of Obj.properties )
      if ( Prop.name == Property_Name )
        return Prop;

    return false;
  }
}
