<?php
  namespace TmxParser;

  class TmxTileset extends Parser
  {
    public $firstgid;
    public $source;
    public $tiles;

    public function ProcessTileset()
    {
      $Tileset_File = file_get_contents($this->source);
      $Tileset_Obj = new \SimpleXMLElement($Tileset_File);

      $this->GetTiledObjectFromXmlElement($Tileset_Obj, $this);

      foreach ( $Tileset_Obj->tile as $Current_Tile )
      {
        $Tile = new TmxTile($Current_Tile);
        $this->tiles[] = $Tile;
      }

      unset($Tileset_Obj);
    }
  }
