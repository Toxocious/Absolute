<?php
  class Player
  {
    private static $Instance;
    public $Map_ID;

    public function __construct()
    {
      if ( empty($_SESSION['Absolute']['Maps']) )
      {
        $_SESSION['Absolute']['Maps'] = [];
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
     * Fetch the player's current position.
     */
    public function GetPosition()
    {
      global $PDO, $User_Data;

      if ( empty($_SESSION['Absolute']['Maps']['Position']) )
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

        return $Map_Position;
      }

      return $_SESSION['Absolute']['Maps']['Position'];
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
        $Spawn_Coords = $Map->GetSpawnCoords();

        return $this->SetPosition($Spawn_Coords['x'], $Spawn_Coords['y'], $Spawn_Coords['z']);
      }

      if
      (
        $User_Data['Map_Position']['x'] == $x &&
        $User_Data['Map_Position']['y'] == $y &&
        $User_Data['Map_Position']['z'] == $z
      )
      {
        return true;
      }

      $_SESSION['Absolute']['Maps']['Position'] = [
        'x' => $x,
        'y' => $y,
        'z' => $z,
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
     * Fetch the player's current map.
     */
    public function GetMap()
    {
      return $_SESSION['Absolute']['Maps']['Map_ID'];
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
      $_SESSION['Absolute']['Maps']['Map_ID'] = $Map_ID;

      if ( !empty($_SESSION['Absolute']['Maps']['Steps_To_Next_Encounter']) )
        unset($_SESSION['Absolute']['Maps']['Steps_To_Next_Encounter']);

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
        $Set_Position->execute([ $Map_ID ]);

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
        $this->SetPosition($User_Data['Map_Position']['x'], $User_Data['Map_Position']['y'], $User_Data['Map_Position']['z']);
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
  }
