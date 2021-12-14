<?php
  class Encounter
  {

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
