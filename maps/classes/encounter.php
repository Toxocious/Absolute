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

      $Player_Instance = Player::GetInstance();
      $Encounter_Zone = $Player_Instance->GetEncounterZone();

      $Shiny_Chance = 4192 - $Player_Map_Level;
      if ( $Shiny_Chance < 2096 )
        $Shiny_Chance = 2096;

      $Generated_Encounter = self::GetRandomEncounter($Player_Map_Name, $Encounter_Zone);
      if ( !$Generated_Encounter )
        return false;

      $Encounter_Type = 'Normal';
      if ( mt_rand(1, $Shiny_Chance) === 1 )
        $Encounter_Type = 'Shiny';

      $_SESSION['Absolute']['Maps']['Encounter'] = [
        'Pokedex_Data' => $Poke_Class->FetchPokedexData($Generated_Encounter['Pokedex_ID'], $Generated_Encounter['Alt_ID'], $Encounter_Type),
        'Level' => mt_rand($Generated_Encounter['Min_Level'], $Generated_Encounter['Max_Level']),
        'Map_Exp_Yield' => mt_rand($Generated_Encounter['Min_Exp_Yield'], $Generated_Encounter['Max_Exp_Yield']),
        'Gender' => $Poke_Class->GenerateGender(null, $Generated_Encounter['Pokedex_ID'], $Generated_Encounter['Alt_ID']),
        'Type' => $Encounter_Type,
        'Obtained_Text' => $Generated_Encounter['Obtained_Text'],
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
      string $Player_Map_Name,
      int $Encounter_Zone
    )
    {
      global $PDO;

      try
      {
        $Fetch_Encounters = $PDO->prepare("
          SELECT *
          FROM `map_encounters`
          WHERE `Map_Name` = ? AND `Active` = 1 AND (`Zone` = ? OR `Zone` IS NULL)
        ");
        $Fetch_Encounters->execute([ $Player_Map_Name, $Encounter_Zone ]);
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
     * Catch the active encounter.
     */
    public static function Catch()
    {
      global $Poke_Class, $User_Data;

      if ( empty($_SESSION['Absolute']['Maps']['Encounter']) )
        return false;

      $Encounter_Data = $_SESSION['Absolute']['Maps']['Encounter'];

      $Player_Instance = Player::GetInstance();

      $New_Steps_Till_Encounter = mt_rand(2, 21);
      $Player_Instance->SetStepsTillEncounter($New_Steps_Till_Encounter);
      $Get_Steps_Till_Encounter = $Player_Instance->GetStepsTillEncounter();

      $Player_Instance->UpdateMapExperience($_SESSION['Absolute']['Maps']['Encounter']['Map_Exp_Yield']);
      User::UpdateStat($User_Data['ID'], 'Map_Exp_Earned', $_SESSION['Absolute']['Maps']['Encounter']['Map_Exp_Yield']);
      User::UpdateStat($User_Data['ID'], 'Map_Pokemon_Caught', 1);

      $Generate_Gender = $Poke_Class->GenerateGender($Encounter_Data['Pokedex_Data']['Pokedex_ID'], $Encounter_Data['Pokedex_Data']['Alt_ID']);
      $Spawn_Pokemon = $Poke_Class->CreatePokemon(
        $Encounter_Data['Pokedex_Data']['Pokedex_ID'],
        $Encounter_Data['Pokedex_Data']['Alt_ID'],
        $Encounter_Data['Level'],
        $Encounter_Data['Type'],
        $Generate_Gender,
        $Encounter_Data['Obtained_Text'],
        null,
        null,
        $User_Data['ID']
      );

      $Catch_Text = "
        You caught a wild {$Spawn_Pokemon['Display_Name']} (Level: " . number_format($Encounter_Data['Level']) . ")
        <br />
        <img src='{$Spawn_Pokemon['Sprite']}' />
        <br />
        +<b>" . number_format($_SESSION['Absolute']['Maps']['Encounter']['Map_Exp_Yield']) . " Map Exp.</b>
        <br />
        <table class='border-gradient' style='width: 210px;'>
          <tbody>
            <tr>
              <td>
                <b>HP</b>
              </td>
              <td>{$Spawn_Pokemon['IVs'][0]}</td>
              <td>
                <b>Att</b>
              </td>
              <td>{$Spawn_Pokemon['IVs'][1]}</td>
            </tr>
            <tr>
              <td>
                <b>Def</b>
              </td>
              <td>{$Spawn_Pokemon['IVs'][2]}</td>
              <td>
                <b>Sp.Att</b>
              </td>
              <td>{$Spawn_Pokemon['IVs'][3]}</td>
            </tr>
            <tr>
              <td>
                <b>Sp.Def</b>
              </td>
              <td>{$Spawn_Pokemon['IVs'][4]}</td>
              <td>
                <b>Speed</b>
              </td>
              <td>{$Spawn_Pokemon['IVs'][5]}</td>
            </tr>
            <tr>
              <td colspan='2'>
                <b>{$Spawn_Pokemon['Nature']}</b>
              </td>
              <td>
                <b>Total</b>
              </td>
              <td>" . array_sum($Spawn_Pokemon['IVs']) . "</td>
            </tr>
          </tbody>
        </table>
      ";

      unset($_SESSION['Absolute']['Maps']['Encounter']);

      return [
        'Catch_Text' => $Catch_Text,
        'Steps_Till_Next_Encounter' => $Get_Steps_Till_Encounter,
      ];
    }

    /**
     * Release the active encounter.
     */
    public static function Release()
    {
      global $User_Data;

      if ( empty($_SESSION['Absolute']['Maps']['Encounter']) )
        return false;

      $Player_Instance = Player::GetInstance();

      $New_Steps_Till_Encounter = mt_rand(2, 21);
      $Player_Instance->SetStepsTillEncounter($New_Steps_Till_Encounter);
      $Get_Steps_Till_Encounter = $Player_Instance->GetStepsTillEncounter();

      $Player_Instance->UpdateMapExperience($_SESSION['Absolute']['Maps']['Encounter']['Map_Exp_Yield']);
      User::UpdateStat($User_Data['ID'], 'Map_Exp_Earned', $_SESSION['Absolute']['Maps']['Encounter']['Map_Exp_Yield']);
      User::UpdateStat($User_Data['ID'], 'Map_Pokemon_Released', 1);

      $Release_Text = "
        You caught and released a(n) {$_SESSION['Absolute']['Maps']['Encounter']['Pokedex_Data']['Display_Name']}!
        <br /><br />
        +" . number_format($_SESSION['Absolute']['Maps']['Encounter']['Map_Exp_Yield']) . " Map Exp.
      ";

      unset($_SESSION['Absolute']['Maps']['Encounter']);

      return [
        'Release_Text' => $Release_Text,
        'Steps_Till_Next_Encounter' => $Get_Steps_Till_Encounter,
      ];
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

      $Run_Text = "You ran away from the wild {$_SESSION['Absolute']['Maps']['Encounter']['Pokedex_Data']['Display_Name']}.";

      unset($_SESSION['Absolute']['Maps']['Encounter']);

      return [
        'Run_Text' => $Run_Text,
        'Steps_Till_Next_Encounter' => $Get_Steps_Till_Encounter
      ];
    }
  }
