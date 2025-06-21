<?php
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
      if ( !empty($_SESSION['EvoChroniclesRPG']['Maps']['Cache']) )
        unset($_SESSION['EvoChroniclesRPG']['Maps']['Cache']);

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
      if ( !$Map_File_Content )
        throw new Exception("{$this->Map_File}] Could not get file contents.");

      $this->Map_Data = json_decode($Map_File_Content);
      return $this->Map_Data;
    }

    /**
     * Fetch the names of all tilesets that need to be loaded.
     */
    public function GetRequiredTilesets()
    {
      $Tilesets = [];

      foreach ( $this->Map_Data->tilesets as $Tileset )
      {
        $Tileset->image = str_replace(['../tilesets/images/', '.png'], '', $Tileset->image);
        $Tilesets[] = $Tileset->image;
      }

      return $Tilesets;
    }

    /**
     * Fetch all objects on the map.
     */
    public function GetMapObjects()
    {
      if ( !empty($_SESSION['EvoChroniclesRPG']['Maps']['Objects']) )
        unset($_SESSION['EvoChroniclesRPG']['Maps']['Objects']);

      foreach ( $this->Map_Data->layers as $Layer )
      {
        switch ( $Layer->name )
        {
          case 'Objects':
          case 'Warps':
            $_SESSION['EvoChroniclesRPG']['Maps']['Objects'][] = $Layer->objects;
            break;

          default:
            break;
        }
      }

      return $_SESSION['EvoChroniclesRPG']['Maps']['Objects'];
    }

    /**
     * Fetch the spawn coordinates of the map.
     */
    public function GetSpawnCoords()
    {
      if ( empty($_SESSION['EvoChroniclesRPG']['Maps']['Objects']) )
      {
        return [
          'x' => -1,
          'y' => -1,
          'z' => -1
        ];
      }

      $Map_Objects = $_SESSION['EvoChroniclesRPG']['Maps']['Objects'];
      foreach ( $Map_Objects as $Objects )
      {
        foreach ( $Objects as $Object )
        {
          if ( $Object->type != 'Player_Entity' )
            continue;

          $Get_Spawn_Layer = MapObject::CheckPropertyByName($Object->properties, 'charLayer');

          $Spawn_Layer = 1;
          if ( $Get_Spawn_Layer )
            $Spawn_Layer = str_replace('Layer_', '', $Get_Spawn_Layer->value);

          return [
            'x' => floor($Object->x / 16),
            'y' => floor($Object->y / 16) + 1,
            'z' => $Spawn_Layer,
          ];
        }
      }

      return [
        'x' => -1,
        'y' => -1,
        'z' => -1
      ];
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
        'Position' => $this->Player->GetPosition(),
        'Tilesets' => $this->GetRequiredTilesets(),
        'Objects' => $this->GetMapObjects(),
      ];
    }

    /**
     * Fetch all stats to pass to the client.
     */
    public function Stats()
    {
      $Map_Level = $this->Player->GetMapLevelAndExp();

      $Shiny_Chance = 4192 - $Map_Level['Map_Level'];
      if ( $Shiny_Chance < 2096 )
        $Shiny_Chance = 2096;

      return [
        'Map_Name' => $this->Player->GetMap(),
        'Map_Level' => $Map_Level['Map_Level'],
        'Map_Experience' => $Map_Level['Map_Experience'],
        'Map_Experience_To_Level' => FetchExpToNextLevel($Map_Level['Map_Experience'], 'Map', true),
        'Shiny_Odds' => [
          'Text' => "1 / {$Shiny_Chance}",
          'Percent' => (1 / $Shiny_Chance) * 100
        ],
        'Next_Encounter' => $this->Player->GetStepsTillEncounter()
      ];
    }
  }
