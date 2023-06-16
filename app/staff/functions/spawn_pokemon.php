<?php
  /**
   * Show an HTML dropdown of all possible pokedex entries.
   */
  function ShowSpawnablePokemonDropdown()
  {
    global $PDO;

    try
    {
      $Get_Pokedex_Entries = $PDO->prepare("
        SELECT `ID`, `Pokedex_ID`, `Alt_ID`, `Pokemon`, `Forme`
        FROM `pokedex`
        ORDER BY `Pokedex_ID` ASC, `Alt_ID` ASC
      ");
      $Get_Pokedex_Entries->execute([ ]);
      $Get_Pokedex_Entries->setFetchMode(PDO::FETCH_ASSOC);
      $Pokedex_Entries = $Get_Pokedex_Entries->fetchAll();
    }
    catch ( PDOException $e )
    {
      HandleError($e);
    }

    if ( empty($Pokedex_Entries) )
    {
      return "
        <div style='display: flex; align-items: center; justify-content: center; width: 100%;'>
          <select name='pokedex_entries'>
            <option>There are no Pok&eacute;dex entries</option>
          </select>
        </div>
      ";
    }

    $Dropdown_Entries = '';
    foreach ( $Pokedex_Entries as $Pokemon )
    {
      if ( $Pokemon['Forme'] !== null )
        $Display_Name = $Pokemon['Pokemon'] . " " . $Pokemon['Forme'];
      else
        $Display_Name = $Pokemon['Pokemon'];

      $Dropdown_Entries .= "
        <option value='{$Pokemon['ID']}'>
          {$Display_Name}
        </option>
      ";
    }

    return "
      <div style='display: flex; align-items: center; justify-content: center; width: 100%;'>
        <select name='pokedex_entries' onchange='ShowSpawnPokemonTable();'>
          <option>Select A Pok&eacute;mon</option>
          {$Dropdown_Entries}
        </select>
      </div>
    ";
  }

  /**
   * Show an HTML table for spawning Pokemon.
   *
   * @param $Pokedex_ID
   */
  function ShowSpawnPokemonTable
  (
    $Pokedex_ID
  )
  {
    global $PDO;

    try
    {
      $Get_Pokedex_Info = $PDO->prepare("
        SELECT `Pokedex_ID`, `Alt_ID`
        FROM `pokedex`
        WHERE `ID` = ?
        LIMIT 1
      ");
      $Get_Pokedex_Info->execute([
        $Pokedex_ID
      ]);
      $Get_Pokedex_Info->setFetchMode(PDO::FETCH_ASSOC);
      $Pokedex_Info = $Get_Pokedex_Info->fetch();
    }
    catch ( PDOException $e )
    {
      HandleError($e);
    }

    if ( !$Pokedex_Info )
      return 'This Pok&eacute;mon does not exist.';

    $Pokemon_Info = GetPokedexData($Pokedex_Info['Pokedex_ID'], $Pokedex_Info['Alt_ID']);

    $Config_Options = [
      'Primary Data' => [
        'Recipient' => [ 'Field_Name' => 'Recipient', 'Field_Type' => 'Text' ],
        'Creation Location' => [ 'Field_Name' => 'Creation_Location', 'Field_Type' => 'Text' ],
        'Level' => [ 'Field_Name' => 'Level', 'Field_Type' => 'Number' ],
        'Frozen' => [ 'Field_Name' => 'Frozen', 'Field_Type' => 'Checkbox' ],
      ],
      'Classification' => [
        'Gender' => [ 'Field_Name' => 'Gender', 'Field_Type' => 'Dropdown' ],
        'Type' => [ 'Field_Name' => 'Type', 'Field_Type' => 'Dropdown' ],
        'Nature' => [ 'Field_Name' => 'Nature', 'Field_Type' => 'Dropdown' ],
        'Ability' => [ 'Field_Name' => 'Ability', 'Field_Type' => 'Dropdown' ],
      ],
      'IVs' => [
        '&nbsp;' => [ 'Field_Name' => 'IV_Speed', 'Field_Type' => 'Blank' ],
        'HP' => [ 'Field_Name' => 'IV_HP', 'Field_Type' => 'Number' ],
        'Attack' => [ 'Field_Name' => 'IV_Attack', 'Field_Type' => 'Number' ],
        'Defense' => [ 'Field_Name' => 'IV_Defense', 'Field_Type' => 'Number' ],
        'Sp. Attack' => [ 'Field_Name' => 'IV_Sp_Attack', 'Field_Type' => 'Number' ],
        'Sp. Defense' => [ 'Field_Name' => 'IV_Sp_Defense', 'Field_Type' => 'Number' ],
        'Speed' => [ 'Field_Name' => 'IV_Speed', 'Field_Type' => 'Number' ],
        '&nbsp;&nbsp;' => [ 'Field_Name' => 'IV_Speed', 'Field_Type' => 'Blank' ],
      ],
      'EVs' => [
        '&nbsp;' => [ 'Field_Name' => 'EV_Speed', 'Field_Type' => 'Blank' ],
        'HP' => [ 'Field_Name' => 'EV_HP', 'Field_Type' => 'Number' ],
        'Attack' => [ 'Field_Name' => 'EV_Attack', 'Field_Type' => 'Number' ],
        'Defense' => [ 'Field_Name' => 'EV_Defense', 'Field_Type' => 'Number' ],
        'Sp. Attack' => [ 'Field_Name' => 'EV_Sp_Attack', 'Field_Type' => 'Number' ],
        'Sp. Defense' => [ 'Field_Name' => 'EV_Sp_Defense', 'Field_Type' => 'Number' ],
        'Speed' => [ 'Field_Name' => 'EV_Speed', 'Field_Type' => 'Number' ],
        '&nbsp;&nbsp;' => [ 'Field_Name' => 'EV_Speed', 'Field_Type' => 'Blank' ],
      ],
    ];

    $Config_Table_Rows = '';

    foreach ( $Config_Options as $Option_Category => $Sub_Children )
    {
      $Config_Row_Text = "
        <thead>
          <tr>
            <th colspan='16' style='width: 100%;'>
              <b>{$Option_Category}</b>
            </th>
          </tr>
        </thead>
      ";

      $Child_Options_Text = '';

      $Column_Span = floor(16 / count($Sub_Children));
      $Width = 100 / count($Sub_Children);

      foreach ( $Sub_Children as $Child_Option => $Child_Fields )
      {
        switch ( $Child_Fields['Field_Type'] )
        {
          case 'Blank':
            $Input_HTML = '';
            break;

          case 'Checkbox':
            $Input_HTML = "
              <input
                type='checkbox'
                name='{$Child_Fields['Field_Name']}'
              />
            ";
            break;

          case 'Dropdown':
            switch ( $Child_Fields['Field_Name'] )
            {
              case 'Type':
                $Options = [ 'Normal', 'Shiny' ];
                break;

              case 'Nature':
                $Options = array_keys(Natures());
                break;

              case 'Gender':
                $Options = [ 'Female', 'Male', 'Genderless', 'Ungendered' ];
                break;

              case 'Ability':
                $Options = GetAbilities($Pokemon_Info['Pokedex_ID'], $Pokemon_Info['Alt_ID']);
                break;
            }


            $Option_Text = '';
            foreach ( $Options as $Option )
            {
              if ( !empty($Option) && $Option != '' )
              {
                $Option_Text .= "
                  <option
                    style='width: 180px;'
                    value='{$Option}'
                  >
                    {$Option}
                  </option>
                ";
              }
            }

            $Input_HTML = "
              <select name='{$Child_Fields['Field_Name']}'>
                {$Option_Text}
              </select>
            ";
            break;

          case 'Number':
            switch ( $Child_Fields['Field_Name'] )
            {
              case 'Level':
                $Input_Value = 5;
                break;

              default:
                $Input_Value = 0;
                break;
            }

            $Input_HTML = "
              <input
                type='number'
                name='{$Child_Fields['Field_Name']}'
                value='{$Input_Value}'
                style='width: 80px;'
              />
            ";
            break;

          case 'Text':
            $Input_HTML = "
              <input
                type='text'
                name='{$Child_Fields['Field_Name']}'
              />
            ";
            break;
        }

        $Child_Options_Text .= "
          <td colspan='{$Column_Span}' style='width: calc(100% / {$Width});'>
            <b>{$Child_Option}</b>
            <br />
            {$Input_HTML}
          </td>
        ";
      }

      $Config_Table_Rows .= "
        {$Config_Row_Text}

        <tbody>
          {$Child_Options_Text}
        </tbody>
      ";
    }

    return "
      <table class='border-gradient' style='width: 600px;'>
        <thead>
          <tr>
            <th colspan='16'>
              Pok&eacute;mon Spawner
            </th>
          </tr>
        </thead>

        <tbody>
          <tr>
            <td colspan='16' style='padding: 10px;'>
              <img src='{$Pokemon_Info['Sprite']}' />
              <br />
              <h3>{$Pokemon_Info['Display_Name']}</h3>
            </td>
          </tr>
        </tbody>

        {$Config_Table_Rows}

        <tbody>
          <tr>
            <td colspan='16' style='padding: 10px;'>
              <button onclick='SpawnPokemon({$Pokedex_ID});'>
                Spawn Pok&eacute;mon
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    ";
  }

  /**
   * Spawn the specified pokemon,
   *
   * @param $Pokedex_ID
   * @param $Recipient
   * @param $Creation_Location
   * @param $Level
   * @param $Frozen
   * @param $Gender
   * @param $Type
   * @param $Nature
   * @param $Ability
   * @param $IV_HP
   * @param $IV_Attack
   * @param $IV_Defense
   * @param $IV_Sp_Attack
   * @param $IV_Sp_Defense
   * @param $IV_Speed
   * @param $EV_HP
   * @param $EV_Attack
   * @param $EV_Defense
   * @param $EV_Sp_Attack
   * @param $EV_Sp_Defense
   * @param $EV_Speed
   */
  function SpawnPokemon
  (
    $Pokedex_ID,
    $Recipient,
    $Creation_Location,
    $Level,
    $Frozen,
    $Gender,
    $Type,
    $Nature,
    $Ability,
    $IV_HP,
    $IV_Attack,
    $IV_Defense,
    $IV_Sp_Attack,
    $IV_Sp_Defense,
    $IV_Speed,
    $EV_HP,
    $EV_Attack,
    $EV_Defense,
    $EV_Sp_Attack,
    $EV_Sp_Defense,
    $EV_Speed
  )
  {
    global $PDO, $User_Class, $User_Data;

    $User_Info = $User_Class->FetchUserData($Recipient);
    if ( empty($Recipient) || !$User_Info )
    {
      return [
        'Success' => false,
        'Message' => 'An invalid recipient has been chosen.',
      ];
    }

    try
    {
      $Get_Pokedex_Data = $PDO->prepare("
        SELECT `Pokedex_ID`, `Alt_ID`
        FROM `pokedex`
        WHERE `ID` = ?
        LIMIT 1
      ");
      $Get_Pokedex_Data->execute([
        $Pokedex_ID
      ]);
      $Get_Pokedex_Data->setFetchMode(PDO::FETCH_ASSOC);
      $Pokedex_Data = $Get_Pokedex_Data->fetch();
    }
    catch ( PDOException $e )
    {
      HandleError($e);
    }

    if ( empty($Creation_Location) )
      $Creation_Location = 'Gift';

    if ( empty($Level) || $Level < 0 || !is_nan($Level) )
      $Level = 5;

    $IV_HP = $IV_HP ?? 0;
    $IV_Attack = $IV_Attack ?? 0;
    $IV_Defense = $IV_Defense ?? 0;
    $IV_Sp_Attack = $IV_Sp_Attack ?? 0;
    $IV_Sp_Defense = $IV_Sp_Defense ?? 0;
    $IV_Speed = $IV_Speed ?? 0;

    $EV_HP = $EV_HP ?? 0;
    $EV_Attack = $EV_Attack ?? 0;
    $EV_Defense = $EV_Defense ?? 0;
    $EV_Sp_Attack = $EV_Sp_Attack ?? 0;
    $EV_Sp_Defense = $EV_Sp_Defense ?? 0;
    $EV_Speed = $EV_Speed ?? 0;

    $Gender = GenerateGender($Pokedex_Data['Pokedex_ID'], $Pokedex_Data['Alt_ID']);
    $IVs = join(',', [$IV_HP, $IV_Attack, $IV_Defense, $IV_Sp_Attack, $IV_Sp_Defense, $IV_Speed]);
    $EVs = join(',', [$EV_HP, $EV_Attack, $EV_Defense, $EV_Sp_Attack, $EV_Sp_Defense, $EV_Speed]);

    $Spawn_Pokemon = CreatePokemon(
      $Pokedex_Data['Pokedex_ID'],
      $Pokedex_Data['Alt_ID'],
      $Level,
      $Type,
      $Gender,
      $Creation_Location,
      $User_Info['ID'],
      $Nature,
      $IVs,
      $EVs
    );

    LogStaffAction('Pokemon Spawned', $User_Data['ID']);

    if ( !$Spawn_Pokemon )
    {
      return [
        'Success' => false,
        'Message' => 'An error occurred while spawning in the specified Pok&eacute;mon.',
      ];
    }

    return [
      'Success' => true,
      'Message' => "You have spawned in a {$Spawn_Pokemon['Display_Name']} for {$User_Info['Username']}.",
    ];
  }
