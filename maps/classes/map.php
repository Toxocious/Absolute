<?php
  // use TMXParser\Parser;

  class Map
  {
    public $Map_Data;
    public $Objects;
    public $Output;
    public $Player;
    public $Map_File;

    public function __construct
    (
      string $Map_ID = null
    )
    {
      if ( !empty($_SESSION['Absolute']['Maps']['Cache']) )
        unset($_SESSION['Absolute']['Maps']['Cache']);

      if ( empty($Map_ID) )
      {
        $this->Player = Player::GetInstance();
        $Map_ID = $this->Player->GetMap();

        $this->Map_File = "../maps/{$Map_ID}.json";
        if ( !file_exists($this->Map_File) )
        {
          $this->Player->SetPosition();
          $this->Map_File = "../maps/{$Map_ID}.json";
        }
      }
      else
      {
        $this->Map_File = "../maps/{$Map_ID}.json";
      }

      /**
       * Try to load from the existing cached map data.
       */
      $this->Map_Data = $this->ParseMap();
    }

    /**
     * Parse the JSON map file.
     */
    public function ParseMap()
    {
      $Map_File_Content = file_get_contents($this->Map_File);
      if ( !$Map_File_Content ) throw new Exception("{$this->Map_File}] Could not get file contents.");

      $this->Map_Data = json_decode($Map_File_Content);
      return $this->Map_Data;
    }

    /**
     * Fetch all objects on the map.
     */
    public function GetObjects()
    {
      $Objects = [];

      if ( !empty($this->Objects) )
      {
        foreach ( $this->Objects as $Current_Object )
        {
          $Object = $Current_Object->Object;

          $Objects[] = [
            'Object_ID' => $Object->properties['object_id']
          ];
        }
      }

      return $Objects;
    }

    /**
     * Fetch the names of all tilesets that need to be loaded.
     */
    public function GetRequiredTilesets()
    {
      $Tilesets = [];

      foreach ( $this->Map_Data->tilesets as $Tileset )
      {
        // "../tilesets/images/halloween.png"
        $Tileset->image = str_replace(['../tilesets/images/', '.png'], '', $Tileset->image);
        $Tilesets[] = $Tileset->image;
      }

      return $Tilesets;
    }

    /**
     * Fetch the spawn coordinates of the map.
     */
    public function GetSpawnCoords()
    {
      // Loop through tilesets
      // If tileset is the 'objects' tileset
      // Look for 'Player_Entity' object
      // If 'Player_Entity' object is found
      // Return the coordinates of the object

      return [
        'x' => 12 * 16,
        'y' => 9 * 16,
        'z' => 1 ];
    }

    /**
     * Render the map.
     */
    public function Render()
    {
      return json_encode($this->Map_Data);
    }


    /**
     * Send initial map load data.
     */
    public function Load()
    {
      global $User_Data;

      return [
        'Character' => $User_Data['Gender'],
        'Map_Name' => $this->Player->GetMap(),
        'Objects' => $this->GetObjects(),
        'Position' => $this->Player->GetPosition(),
        'Tilesets' => $this->GetRequiredTilesets(),
        // 'Map_Data' => $this->Map_Data,
      ];
    }
  }
