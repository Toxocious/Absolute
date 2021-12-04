class Tile
{
  constructor(x, y, z)
  {
    this.x = x;
    this.y = y;
    this.z = z;
  }

  /**
   * Check if the tile object has a given property.
   */
  DoesObjectHavePropertyOfName(Obj, Property_Name)
  {
    if ( typeof Obj !== 'object' || typeof Property_Name !== 'string' )
      return false;

    for ( const Prop of Obj.properties )
    {
      if ( Prop.name == Property_Name )
        return Prop;
    }

    return false;
  }
}
