<?php
  class Encounter extends Player
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
      global $Poke_Class;

      $Shiny_Chance = 4192 - $Player_Map_Level;
      if ( $Shiny_Chance < 2096 )
        $Shiny_Chance = 2096;

      $Generated_Encounter = self::GetRandomEncounter($Player_Map_Name);
      if ( !$Generated_Encounter )
        return false;

      $Encounter_Type = 'Normal';
      if ( mt_rand(1, $Shiny_Chance) === 1 )
        $Encounter_Type = 'Shiny';

      $_SESSION['Absolute']['Maps']['Encounter'] = [
        'Pokedex_Data' => $Poke_Class->FetchPokedexData($Generated_Encounter['Pokedex_ID'], $Generated_Encounter['Alt_ID'], $Encounter_Type),
        'Level' => mt_rand($Generated_Encounter['Min_Level'], $Generated_Encounter['Max_Level']),
        'Gender' => $Poke_Class->GenerateGender(null, $Generated_Encounter['Pokedex_ID'], $Generated_Encounter['Alt_ID']),
        'Type' => $Encounter_Type,
      ];

      return $_SESSION['Absolute']['Maps']['Encounter'];
    }

    /**
     * Get all potential encounters.
     *
     * @param {string} $Player_Map_Name
     */
    public static function GetRandomEncounter
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

    /**
     * Run away from the active encounter.
     */
    public static function Run()
    {
      global $User_Data;

      if ( empty($_SESSION['Absolute']['Maps']['Encounter']) )
        return false;

      $Player_Instance = Player::GetInstance();

      $New_Steps_Till_Encounter = mt_rand(2, 21);
      $Player_Instance->SetStepsTillEncounter($New_Steps_Till_Encounter);
      $Get_Steps_Till_Encounter = $Player_Instance->GetStepsTillEncounter();

      User::UpdateStat($User_Data['ID'], 'Map_Pokemon_Fled_From', 1);

      unset($_SESSION['Absolute']['Maps']['Encounter']);
      return $Get_Steps_Till_Encounter;
    }
  }
