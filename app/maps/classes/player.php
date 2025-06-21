<?php
  class Player extends Map
  {
    private static $Instance;
    public $Map_ID;

    public function __construct()
    {
      if ( empty($_SESSION['EvoChroniclesRPG']['Maps']) )
      {
        $_SESSION['EvoChroniclesRPG']['Maps'] = [];
        $this->LoadLastMap();
      }
    }

    /**
     * Fetch the player's current instance.
     */
    public static function GetInstance()
    {
      if ( empty(self::$Instance) )
        self::$Instance = new Player();

      return self::$Instance;
    }

    /**
     * Verify the player's interaction with a tile.
     *
     * @param {int} $x
     * @param {int} $y
     * @param {int} $z
     */
    public function CheckInteraction
    (
      int $x = null,
      int $y = null,
      int $z = null
    )
    {
      $Map_Objects = $_SESSION['EvoChroniclesRPG']['Maps']['Objects'];
      if ( empty($Map_Objects) )
        return false;

      $Check_Tile_Object = MapObject::GetObjectAtTile($Map_Objects, $x, $y, $z);
      if ( empty($Check_Tile_Object) )
        return false;

      $Is_Player_By_Tile = $this->IsNextToTile($x, $y, $z);
      if ( empty($Is_Player_By_Tile) )
        return false;

      return true;
    }

    /**
     * Process warping the user.
     */
    public function ProcessWarp
    (
      int $Tile_X,
      int $Tile_Y,
      int $Tile_Z,
      bool $Is_Warp_Tile = false
    )
    {
      if ( empty($Tile_X) || empty($Tile_Y) || empty($Tile_Z) || !$Is_Warp_Tile )
        return false;

      if ( !$this->IsNextToTile($Tile_X, $Tile_Y + 1, $Tile_Z) )
        return false;

      $Map_Objects = $_SESSION['EvoChroniclesRPG']['Maps']['Objects'];
      $Get_Warp_Object = MapObject::GetObjectAtTile($Map_Objects, $Tile_X, $Tile_Y, $Tile_Z, 'warp');
      if ( !$Get_Warp_Object )
        return false;

      $Get_Designated_Warp_Map = MapObject::CheckPropertyByName($Get_Warp_Object->properties, 'warpTo');
      if ( !$Get_Designated_Warp_Map )
        return false;

      $this->SetMap($Get_Designated_Warp_Map->value);
      $this->SetPosition();

      return $this->GetMap();
    }

    /**
     * Check if the player is next to a given tile.
     *
     * @param {int} $x
     * @param {int} $y
     * @param {int} $z
     */
    public function IsNextToTile
    (
      int $x = null,
      int $y = null,
      int $z = null
    )
    {
      $Player_Position = $_SESSION['EvoChroniclesRPG']['Maps']['Position'];

      $Adjacent_Coords = [
        [0, -1],
        [1, 0],
        [0, 1],
        [-1, 0],
      ];

      foreach ( ['up', 'right', 'down', 'left'] as $Index => $Direction )
      {
        if
        (
          $Player_Position['Map_X'] == $x + $Adjacent_Coords[$Index][0] &&
          $Player_Position['Map_Y'] == $y + $Adjacent_Coords[$Index][1] &&
          $Player_Position['Map_Z'] == $z
        )
          return true;
      }

      return false;
    }

    /**
     * Fetch the player's current position.
     */
    public function GetPosition()
    {
      global $PDO, $User_Data;

      if ( empty($_SESSION['EvoChroniclesRPG']['Maps']['Position']) )
      {
        try
        {
          $Fetch_Map_Position = $PDO->prepare("
            SELECT `Map_X`, `Map_Y`, `Map_Z`
            FROM `users`
            WHERE `ID` = ?
            LIMIT 1
          ");
          $Fetch_Map_Position->execute([ $User_Data['ID'] ]);
          $Fetch_Map_Position->setFetchMode(PDO::FETCH_ASSOC);
          $Map_Position = $Fetch_Map_Position->fetch();
        }
        catch ( \PDOException $e )
        {
          HandleError($e);
        }

        $_SESSION['EvoChroniclesRPG']['Maps']['Position'] = $Map_Position;
        return $Map_Position;
      }

      return $_SESSION['EvoChroniclesRPG']['Maps']['Position'];
    }

    /**
     * Set the player's position on the map,
     *
     * @param {int} $x
     * @param {int} $y
     * @param {int} $z
     */
    public function SetPosition
    (
      int $x = null,
      int $y = null,
      int $z = null
    )
    {
      global $User_Data, $PDO;

      if ( empty(func_get_args()) )
      {
        $Map = new Map($this->GetMap());
        $Map->GetMapObjects();
        $Spawn_Coords = $Map->GetSpawnCoords();

        return $this->SetPosition($Spawn_Coords['x'], $Spawn_Coords['y'], $Spawn_Coords['z']);
      }

      if
      (
        $User_Data['Map_Position']['Map_X'] == $x &&
        $User_Data['Map_Position']['Map_Y'] == $y &&
        $User_Data['Map_Position']['Map_Z'] == $z
      )
      {
        return true;
      }

      $_SESSION['EvoChroniclesRPG']['Maps']['Position'] = [
        'Map_X' => $x,
        'Map_Y' => $y,
        'Map_Z' => $z,
      ];

      try
      {
        $PDO->beginTransaction();

        $Set_Position = $PDO->prepare("
          UPDATE `users`
          SET `Map_X` = ?, `Map_Y` = ?, `Map_Z` = ?
          WHERE `ID` = ?
          LIMIT 1
        ");
        $Set_Position->execute([ $x, $y, $z, $User_Data['ID'] ]);

        $PDO->commit();
      }
      catch ( \PDOException $e )
      {
        $PDO->rollBack();
        HandleError($e);
      }

      return true;
    }

    /**
     * Get the amount of steps until the player's next wild encounter,
     */
    public function GetStepsTillEncounter()
    {
      global $User_Data;

      if ( empty($_SESSION['EvoChroniclesRPG']['Maps']['Map_Steps_To_Encounter']) )
        return $User_Data['Map_Steps_To_Encounter'];

      return $_SESSION['EvoChroniclesRPG']['Maps']['Map_Steps_To_Encounter'];
    }

    /**
     * Set the player's steps until their next wild encounter.
     *
     * @param {int} $Steps
     */
    public function SetStepsTillEncounter
    (
      int $Steps = -1
    )
    {
      global $User_Data, $PDO;

      try
      {
        $PDO->beginTransaction();

        $Update_Steps = $PDO->prepare("
          UPDATE `users`
          SET `Map_Steps_To_Encounter` = `Map_Steps_To_Encounter` + ?
          WHERE `ID` = ?
          LIMIT 1
        ");
        $Update_Steps->execute([ $Steps, $User_Data['ID'] ]);
      }
      catch ( \PDOException $e )
      {
        $PDO->rollBack();
        HandleError($e);
      }

      $PDO->commit();

      $_SESSION['EvoChroniclesRPG']['Maps']['Map_Steps_To_Encounter'] = $User_Data['Map_Steps_To_Encounter'];
      return $_SESSION['EvoChroniclesRPG']['Maps']['Map_Steps_To_Encounter'];
    }

    /**
     * Fetch the player's current map.
     */
    public function GetMap()
    {
      return $_SESSION['EvoChroniclesRPG']['Maps']['Map_ID'];
    }

    /**
     * Set the player's current map.
     *
     * @param {string} $Map_ID
     */
    public function SetMap
    (
      string $Map_ID
    )
    {
      global $User_Data, $PDO;

      $this->Map_ID = $Map_ID;
      $_SESSION['EvoChroniclesRPG']['Maps']['Map_ID'] = $Map_ID;

      if ( !empty($_SESSION['EvoChroniclesRPG']['Maps']['Steps_To_Next_Encounter']) )
        unset($_SESSION['EvoChroniclesRPG']['Maps']['Steps_To_Next_Encounter']);

      if ( $User_Data['Map_ID'] == $Map_ID )
        return true;

      try
      {
        $PDO->beginTransaction();

        $Set_Position = $PDO->prepare("
          UPDATE `users`
          SET `Map_ID` = ?
          WHERE `ID` = ?
          LIMIT 1
        ");
        $Set_Position->execute([ $Map_ID, $User_Data['ID'] ]);

        $PDO->commit();
      }
      catch ( \PDOException $e )
      {
        $PDO->rollBack();
        HandleError($e);
      }

      return true;
    }

    /**
     * Load the map the player was last on, including their position.
     */
    public function LoadLastMap()
    {
      global $User_Data;

      if ( empty($User_Data['Map_ID']) )
      {
        $this->SetPosition();
      }
      else
      {
        $this->SetMap($User_Data['Map_ID']);
        $this->SetPosition($User_Data['Map_Position']['Map_X'], $User_Data['Map_Position']['Map_Y'], $User_Data['Map_Position']['Map_Z']);
      }
    }

    /**
     * Fetch the player's map level and experience.
     */
    public function GetMapLevelAndExp()
    {
      global $User_Data;

      return [
        'Map_Level' => FetchLevel($User_Data['Map_Experience'], 'Map'),
        'Map_Experience' => $User_Data['Map_Experience'],
      ];
    }

    /**
     * Update the user's map experience.
     *
     * @param {int} $Exp_Earned
     */
    public function UpdateMapExperience
    (
      int $Exp_Earned
    )
    {
      global $PDO, $User_Data;

      try
      {
        $PDO->beginTransaction();

        $Update_Map_Exp = $PDO->prepare("
          UPDATE `users`
          SET `Map_Experience` = `Map_Experience` + ?
          WHERE `ID` = ?
          LIMIT 1
        ");
        $Update_Map_Exp->execute([ $Exp_Earned, $User_Data['ID'] ]);

        $PDO->commit();
      }
      catch ( \PDOException $e )
      {
        $PDO->rollBack();
      }
    }
  }
