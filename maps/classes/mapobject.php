<?php
  class MapObject extends Map
  {
    /**
     * Get the object at a given tile coordinate.
     *
     * @param {array} $Map_Objects
     * @param {int} $x
     * @param {int} $y
     * @param {int} $z
     */
    public static function GetObjectAtTile
    (
      array $Map_Objects = null,
      int $x = null,
      int $y = null,
      int $z = null
    )
    {
      if ( empty($Map_Objects) )
        return false;

      foreach ( $Map_Objects as $Map_Object_Array )
      {
        foreach ( $Map_Object_Array as $Map_Object )
        {
          if ( !isset($Type) || $Map_Object->type != $Type )
            continue;

          $Get_Layer_Property = self::CheckPropertyByName($Map_Object->properties, 'charLayer');
          if ( empty($Get_Layer_Property) )
            return false;

          if
          (
            $Map_Object->x / 16 == $x &&
            $Map_Object->y / 16 == $y &&
            $Get_Layer_Property->value == "Layer_{$z}"
          )
          {
              return $Map_Object;
          }
        }
      }

      unset($Map_Objects);
      unset($Map_Object);

      return false;
    }

    /**
     * Check if the object has a desired property.
     */
    public static function CheckPropertyByName
    (
      array $Properties,
      string $Property_Name
    )
    {
      if ( empty($Properties) || empty($Property_Name) )
        return false;

      foreach ( $Properties as $Property )
      {
        if ( $Property->name == $Property_Name )
          return $Property;
      }

      return false;
    }
  }
