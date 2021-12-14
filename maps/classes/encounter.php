<?php
  class Encounter
  {
    /**
     * Generate a wild encounter.
     *
     * @param {string} $Player_Map_Name
     * @param {int} $Player_Map_Level
     */
    public static function Generate
    (
      string $Player_Map_Name,
      int $Player_Map_Level
    )
    {
      $Shiny_Chance = 4192 - $Player_Map_Level;
      if ( $Shiny_Chance < 2096 )
        $Shiny_Chance = 2096;

      $Possible_Encounters = self::GetPossibleEncounters($Player_Map_Name);
      if ( !$Possible_Encounters )
        return false;

      return true;
    }

    /**
     * Get all potential encounters.
     *
     * @param {string} $Player_Map_Name
     */
    public static function GetPossibleEncounters
    (
      string $Player_Map_Name
    )
    {
      global $PDO;

      try
      {
        $Fetch_Encounters = $PDO->prepare("
          SELECT *
          FROM `map_encounters`
          WHERE `Map_Name` = ? AND `Active` = 1
        ");
        $Fetch_Encounters->execute([ $Player_Map_Name ]);
        $Fetch_Encounters->setFetchMode(PDO::FETCH_ASSOC);
        $Possible_Encounters = $Fetch_Encounters->fetchAll();
      }
      catch ( \PDOException $e )
      {
        HandleError($e);
      }

      if ( empty($Possible_Encounters) )
        return false;

      $Encounter_Pool = new Weighter();
      foreach ( $Possible_Encounters as $Encounter_Key => $Encounter )
      {
        $Encounter_Pool->add($Encounter_Key, $Encounter['Weight']);
      }

      $Get_Random_Encounter = $Encounter_Pool->get();
      if ( $Get_Random_Encounter === false )
        return false;

      $Selected_Encounter = $Possible_Encounters[$Get_Random_Encounter];
      return $Selected_Encounter;
    }
  }
