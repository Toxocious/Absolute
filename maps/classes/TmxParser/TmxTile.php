<?php
  namespace TmxParser;

  class TmxTile extends Parser
  {
    public $id;
    public $properties;

    public function __construct
    (
      $Tile_Object
    )
    {
      $this->GetTiledObjectFromXmlElement($Tile_Object, $this);

      if ( !empty($Tile_Object->properties) )
      {
        foreach ( $Tile_Object->properties->property as $Property_Object )
        {
          $Property = new TmxProperty;
          $this->GetTiledObjectFromXmlElement($Property_Object, $Property);
          $this->properties[$Property->name] = $Property->value;

          unset($Property);
        }
      }
    }

    /**
     * Check if the tile has a given property.
     *
     * @param {string} $Property_Name
     */
    public function HasProperty
    (
      string $Property_Name
    )
    {
      foreach ( $this->properties as $Property )
        if ( $Property->name == $Property_Name )
          return true;

      return false;
    }
  }
