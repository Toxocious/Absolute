<?php
  class Encounter extends Player
  {
    const ALERT_POKEDEX_IDS = [ 144, 151, 243, 244, 245, 249, 250, 384, 489, 639, 640, 716, 802, 888, 889 ];
    const ALERT_POKEMON_TYPES = [ 'Shiny' ];

    /**
     * Generate a wild encounter.
     *
     * @param {string} $Player_Map_Name
     * @param {int} $Player_Map_Level
     */
    public static function Generate
    (
      string $Player_Map_Name,
      int $Player_Map_Level,
      string $Encounter_Zone
    )
    {
      $Shiny_Chance = 4192 - $Player_Map_Level;
      if ( $Shiny_Chance < 2096 )
        $Shiny_Chance = 2096;

      $Generated_Encounter = self::GetRandomEncounter($Player_Map_Name, $Encounter_Zone);
      if ( !$Generated_Encounter )
        return false;

      $Encounter_Type = 'Normal';
      if ( mt_rand(1, $Shiny_Chance) === 1 )
        $Encounter_Type = 'Shiny';

      $Pokedex_Data = GetPokedexData($Generated_Encounter['Pokedex_ID'], $Generated_Encounter['Alt_ID'], $Encounter_Type);

      $Page_Alert = null;
      if ( in_array($Encounter_Type, self::ALERT_POKEMON_TYPES) )
      {
        if ( in_array($Generated_Encounter['Pokedex_ID'], self::ALERT_POKEDEX_IDS) )
          $Alert_Dialogue = "You found a wild {$Encounter_Type} {$Pokedex_Data['Display_Name']}!";
        else
          $Alert_Dialogue = "You found a wild {$Encounter_Type} Pok&eacute;mon!";

        $Page_Alert = [
          'Dialogue' => $Alert_Dialogue,
        ];
      }

      $_SESSION['EvoChroniclesRPG']['Maps']['Encounter'] = [
        'Page_Alert' => $Page_Alert,
        'Pokedex_Data' => $Pokedex_Data,
        'Level' => mt_rand($Generated_Encounter['Min_Level'], $Generated_Encounter['Max_Level']),
        'Map_Exp_Yield' => mt_rand($Generated_Encounter['Min_Exp_Yield'], $Generated_Encounter['Max_Exp_Yield']),
        'Gender' => GenerateGender($Generated_Encounter['Pokedex_ID'], $Generated_Encounter['Alt_ID']),
        'Type' => $Encounter_Type,
        'Obtained_Text' => $Generated_Encounter['Obtained_Text'],
        'Generated_On' => time()
      ];

      return $_SESSION['EvoChroniclesRPG']['Maps']['Encounter'];
    }

    /**
     * Get all potential encounters.
     *
     * @param {string} $Player_Map_Name
     */
    public static function GetRandomEncounter
    (
      string $Player_Map_Name,
      int $Encounter_Zone = null
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
        $Encounter_Pool->AddObject($Encounter_Key, $Encounter['Weight']);
      }

      $Get_Random_Encounter = $Encounter_Pool->GetObject();
      if ( $Get_Random_Encounter === false )
        return false;

      return $Possible_Encounters[$Get_Random_Encounter];
    }

    /**
     * Catch the active encounter.
     */
    public static function Catch()
    {
      global $User_Data;

      if ( empty($_SESSION['EvoChroniclesRPG']['Maps']['Encounter']) )
        return false;

      $Encounter_Data = $_SESSION['EvoChroniclesRPG']['Maps']['Encounter'];

      $Player_Instance = Player::GetInstance();

      $New_Steps_Till_Encounter = mt_rand(2, 21);
      $Player_Instance->SetStepsTillEncounter($New_Steps_Till_Encounter);
      $Get_Steps_Till_Encounter = $Player_Instance->GetStepsTillEncounter();

      $Player_Instance->UpdateMapExperience($_SESSION['EvoChroniclesRPG']['Maps']['Encounter']['Map_Exp_Yield']);
      User::UpdateStat($User_Data['ID'], 'Map_Exp_Earned', $_SESSION['EvoChroniclesRPG']['Maps']['Encounter']['Map_Exp_Yield']);
      User::UpdateStat($User_Data['ID'], 'Map_Pokemon_Caught', 1);

      $Spawn_Pokemon = CreatePokemon(
        $User_Data['ID'],
        $Encounter_Data['Pokedex_Data']['Pokedex_ID'],
        $Encounter_Data['Pokedex_Data']['Alt_ID'],
        $Encounter_Data['Level'],
        $Encounter_Data['Type'],
        $Encounter_Data['Gender'],
        $Encounter_Data['Obtained_Text'],
      );

      $Catch_Text = "
        You caught a wild {$Spawn_Pokemon['Display_Name']} (Level: " . number_format($Encounter_Data['Level']) . ")
        <br />
        <img src='{$Spawn_Pokemon['Sprite']}' />
        <br />
        +<b>" . number_format($_SESSION['EvoChroniclesRPG']['Maps']['Encounter']['Map_Exp_Yield']) . " Map Exp.</b>
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

      self::LogEncounter();

      unset($_SESSION['EvoChroniclesRPG']['Maps']['Encounter']);

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

      if ( empty($_SESSION['EvoChroniclesRPG']['Maps']['Encounter']) )
        return false;

      $Player_Instance = Player::GetInstance();

      $New_Steps_Till_Encounter = mt_rand(2, 21);
      $Player_Instance->SetStepsTillEncounter($New_Steps_Till_Encounter);
      $Get_Steps_Till_Encounter = $Player_Instance->GetStepsTillEncounter();

      $Player_Instance->UpdateMapExperience($_SESSION['EvoChroniclesRPG']['Maps']['Encounter']['Map_Exp_Yield']);
      User::UpdateStat($User_Data['ID'], 'Map_Exp_Earned', $_SESSION['EvoChroniclesRPG']['Maps']['Encounter']['Map_Exp_Yield']);
      User::UpdateStat($User_Data['ID'], 'Map_Pokemon_Released', 1);

      $Release_Text = "
        You caught and released a(n) {$_SESSION['EvoChroniclesRPG']['Maps']['Encounter']['Pokedex_Data']['Display_Name']}!
        <br /><br />
        +" . number_format($_SESSION['EvoChroniclesRPG']['Maps']['Encounter']['Map_Exp_Yield']) . " Map Exp.
      ";

      self::LogEncounter();

      unset($_SESSION['EvoChroniclesRPG']['Maps']['Encounter']);

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

      if ( empty($_SESSION['EvoChroniclesRPG']['Maps']['Encounter']) )
        return false;

      $Player_Instance = Player::GetInstance();

      $New_Steps_Till_Encounter = mt_rand(2, 21);
      $Player_Instance->SetStepsTillEncounter($New_Steps_Till_Encounter);
      $Get_Steps_Till_Encounter = $Player_Instance->GetStepsTillEncounter();

      User::UpdateStat($User_Data['ID'], 'Map_Pokemon_Fled_From', 1);

      $Run_Text = "You ran away from the wild {$_SESSION['EvoChroniclesRPG']['Maps']['Encounter']['Pokedex_Data']['Display_Name']}.";

      unset($_SESSION['EvoChroniclesRPG']['Maps']['Encounter']);

      return [
        'Run_Text' => $Run_Text,
        'Steps_Till_Next_Encounter' => $Get_Steps_Till_Encounter
      ];
    }

    /**
     * Log the encounter to the database.
     */
    public static function LogEncounter()
    {
      global $PDO, $User_Data;

      try
      {
        $PDO->beginTransaction();

        $Log_Map_Encounter = $PDO->prepare("
          INSERT INTO `map_logs` (
            `Map_Name`,
            `Pokemon_Pokedex_ID`,
            `Pokemon_Alt_ID`,
            `Pokemon_Type`,
            `Pokemon_Level`,
            `Pokemon_Gender`,
            `Encountered_On`,
            `Caught_By`,
            `Time_Caught`
          ) VALUES ( ?, ?, ?, ?, ?, ?, ?, ?, ? )
        ");
        $Log_Map_Encounter->execute([
          $_SESSION['EvoChroniclesRPG']['Maps']['Encounter']['Obtained_Text'],
          $_SESSION['EvoChroniclesRPG']['Maps']['Encounter']['Pokedex_Data']['Pokedex_ID'],
          $_SESSION['EvoChroniclesRPG']['Maps']['Encounter']['Pokedex_Data']['Alt_ID'],
          $_SESSION['EvoChroniclesRPG']['Maps']['Encounter']['Type'],
          $_SESSION['EvoChroniclesRPG']['Maps']['Encounter']['Level'],
          $_SESSION['EvoChroniclesRPG']['Maps']['Encounter']['Gender'],
          $_SESSION['EvoChroniclesRPG']['Maps']['Encounter']['Generated_On'],
          $User_Data['ID'],
          time()
        ]);

        $PDO->commit();
      }
      catch ( \PDOException $e )
      {
        $PDO->rollBack();

        HandleError($e);
      }
    }
  }
