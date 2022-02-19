<?php
  /**
   * Show an HTML dropdown of all possible pokedex entries.
   */
  function ShowPokedexDropdown()
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
        <select name='pokedex_entries'>
          <option>There are no Pok&eacute;dex entries</option>
        </select>
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
      <select name='pokedex_entries' onchange='ShowPokedexEntry();'>
        <option>Select A Pok&eacute;mon</option>
        {$Dropdown_Entries}
      </select>
    ";
  }

  /**
   * Show an HTML table of editable values for the specified pokedex entry.
   *
   * @param $Pokedex_ID
   */
  function ShowEntryEditTable
  (
    $Pokedex_ID
  )
  {
    global $PDO, $Poke_Class;

    try
    {
      $Get_Pokedex_Entry_Data = $PDO->prepare("
        SELECT *
        FROM `pokedex`
        WHERE `ID` = ?
        LIMIT 1
      ");
      $Get_Pokedex_Entry_Data->execute([ $Pokedex_ID ]);
      $Get_Pokedex_Entry_Data->setFetchMode(PDO::FETCH_ASSOC);
      $Pokedex_Entry_Data = $Get_Pokedex_Entry_Data->fetch();
    }
    catch ( PDOException $e )
    {
      HandleError($e);
    }

    if ( $Pokedex_Entry_Data['Forme'] !== null )
      $Display_Name = $Pokedex_Entry_Data['Pokemon'] . " " . $Pokedex_Entry_Data['Forme'];
    else
      $Display_Name = $Pokedex_Entry_Data['Pokemon'];

    $Pokemon_Sprite = $Poke_Class->FetchImages($Pokedex_Entry_Data['Pokedex_ID'], $Pokedex_Entry_Data['Alt_ID']);

    $Config_Options = [
      'Pok&eacute;mon Species' => [
        'Pok&eacute;mon Name' => [ 'Field_Name' => 'Pokemon', 'Field_Type' => 'Text' ],
        'Pok&eacute;mon Forme' => [ 'Field_Name' => 'Forme', 'Field_Type' => 'Text' ],
      ],
      'Pok&eacute;mon Typings' => [
        'Primary Type' => [ 'Field_Name' => 'Type_Primary', 'Field_Type' => 'Dropdown' ],
        'Secondary Type' => [ 'Field_Name' => 'Type_Secondary', 'Field_Type' => 'Dropdown' ],
      ],
      'Base Stats' => [
        'HP' => [ 'Field_Name' => 'HP', 'Field_Type' => 'Number' ],
        'Attack' => [ 'Field_Name' => 'Attack', 'Field_Type' => 'Number' ],
        'Defense' => [ 'Field_Name' => 'Defense', 'Field_Type' => 'Number' ],
        'Sp. Attack' => [ 'Field_Name' => 'SpAttack', 'Field_Type' => 'Number' ],
        'Sp. Defense' => [ 'Field_Name' => 'SpDefense', 'Field_Type' => 'Number' ],
        'Speed' => [ 'Field_Name' => 'Speed', 'Field_Type' => 'Number' ],
      ],
      'EV Yield' => [
        'HP' => [ 'Field_Name' => 'EV_HP', 'Field_Type' => 'Number' ],
        'Attack' => [ 'Field_Name' => 'EV_Attack', 'Field_Type' => 'Number' ],
        'Defense' => [ 'Field_Name' => 'EV_Defense', 'Field_Type' => 'Number' ],
        'Sp. Attack' => [ 'Field_Name' => 'EV_SpAttack', 'Field_Type' => 'Number' ],
        'Sp. Defense' => [ 'Field_Name' => 'EV_SpDefense', 'Field_Type' => 'Number' ],
        'Speed' => [ 'Field_Name' => 'EV_Speed', 'Field_Type' => 'Number' ],
      ],
      'Gender Odds' => [
        'Female' => [ 'Field_Name' => 'Female', 'Field_Type' => 'Number' ],
        'Male' => [ 'Field_Name' => 'Male', 'Field_Type' => 'Number' ],
        'Genderless' => [ 'Field_Name' => 'Genderless', 'Field_Type' => 'Number' ],
      ],
      'Misc. Info' => [
        'Height' => [ 'Field_Name' => 'Height', 'Field_Type' => 'Number' ],
        'Weight' => [ 'Field_Name' => 'Weight', 'Field_Type' => 'Number' ],
        'Exp. Yield' => [ 'Field_Name' => 'Exp_Yield', 'Field_Type' => 'Number' ],
      ],
      'Categories' => [
        'Baby Pok&eacute;mon' => [ 'Field_Name' => 'Is_Baby', 'Field_Type' => 'Checkbox' ],
        'Mythical Pok&eacute;mon' => [ 'Field_Name' => 'Is_Mythical', 'Field_Type' => 'Checkbox' ],
        'Legendary Pok&eacute;mon' => [ 'Field_Name' => 'Is_Legendary', 'Field_Type' => 'Checkbox' ],
      ],
    ];

    $Config_Table_Rows = '';

    foreach ( $Config_Options as $Option_Category => $Sub_Children )
    {
      $Config_Row_Text = "
        <thead>
          <tr>
            <th colspan='30' style='width: 100%;'>
              <b>{$Option_Category}</b>
            </th>
          </tr>
        </thead>
      ";

      $Child_Options_Text = '';

      $Column_Span = 30 / count($Sub_Children);
      $Width = 100 / count($Sub_Children);

      foreach ( $Sub_Children as $Child_Option => $Child_Fields )
      {
        switch ( $Child_Fields['Field_Type'] )
        {
          case 'Checkbox':
            $Input_HTML = "
              <input
                type='checkbox'
                name='{$Child_Fields['Field_Name']}'
                value='{$Pokedex_Entry_Data[$Child_Fields['Field_Name']]}'
                " . ($Pokedex_Entry_Data[$Child_Fields['Field_Name']] == 'True' ? 'checked' : '') . "
              />
            ";
            break;

          case 'Dropdown':
            $Options = [ 'None', 'Normal', 'Water', 'Fire', 'Grass', 'Electric', 'Ground', 'Flying', 'Fighting', 'Psychic', 'Dark', 'Ghost', 'Bug', 'Rock', 'Ice', 'Steel', 'Dragon', 'Poison', 'Fairy' ];

            $Option_Text = '';
            foreach ( $Options as $Option )
            {
              $Option_Text .= "
                <option value='{$Option}' " . ($Pokedex_Entry_Data[$Child_Fields['Field_Name']] == $Option ? 'selected' : '') . ">
                  {$Option}
                </option>
              ";
            }

            $Input_HTML = "
              <select name='{$Child_Fields['Field_Name']}'>
                {$Option_Text}
              </select>
            ";
            break;

          case 'Number':
            $Input_HTML = "
              <input
                type='number'
                name='{$Child_Fields['Field_Name']}'
                value='{$Pokedex_Entry_Data[$Child_Fields['Field_Name']]}' style='width: 70px;'
              />
            ";
            break;

          case 'Text':
            $Input_HTML = "
              <input
                type='text'
                name='{$Child_Fields['Field_Name']}'
                value='{$Pokedex_Entry_Data[$Child_Fields['Field_Name']]}'
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
            <th colspan='30'>
              Editing Pok&eacute;dex Entry #{$Pokedex_ID}
            </th>
          </tr>
        </thead>

        <tbody>
          <tr>
            <td colspan='30'>
              <img src='{$Pokemon_Sprite['Sprite']}' />
              <br />
              <b>{$Display_Name}</b>
            </td>
          </tr>
        </tbody>

        {$Config_Table_Rows}

        <tbody>
          <tr>
            <td colspan='30' style='padding: 10px;'>
              <button onclick='UpdatePokedexEntry({$Pokedex_ID});'>
                Update Pok&eacute;dex Entry
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    ";
  }

  /**
   * Update the specified Pokedex entry.
   *
   * @param $Pokedex_ID
   * @param $Pokemon
   * @param $Forme
   * @param $Type_Primary
   * @param $Type_Secondary
   * @param $Base_HP
   * @param $Base_Attack
   * @param $Base_Defense
   * @param $Base_Sp_Attack
   * @param $Base_Sp_Defense
   * @param $Base_Speed
   * @param $HP_EV
   * @param $Attack_EV
   * @param $Defense_EV
   * @param $Sp_Attack_EV
   * @param $Sp_Defense_EV
   * @param $Speed_EV
   * @param $Female_Odds
   * @param $Male_Odds
   * @param $Genderless_Odds
   * @param $Height
   * @param $Weight
   * @param $Exp_Yield
   * @param $Is_Baby
   * @param $Is_Mythical
   * @param $Is_Legendary
   */
  function UpdatePokedexEntry
  (
    $Pokedex_ID,
    $Pokemon,
    $Forme,
    $Type_Primary,
    $Type_Secondary,
    $Base_HP,
    $Base_Attack,
    $Base_Defense,
    $Base_Sp_Attack,
    $Base_Sp_Defense,
    $Base_Speed,
    $HP_EV,
    $Attack_EV,
    $Defense_EV,
    $Sp_Attack_EV,
    $Sp_Defense_EV,
    $Speed_EV,
    $Female_Odds,
    $Male_Odds,
    $Genderless_Odds,
    $Height,
    $Weight,
    $Exp_Yield,
    $Is_Baby,
    $Is_Mythical,
    $Is_Legendary
  )
  {
    global $PDO, $User_Data;

    try
    {
      $PDO->beginTransaction();

      $Update_Pokedex_Entry = $PDO->prepare("
        UPDATE `pokedex`
        SET `Pokemon` = ?, `Forme` = ?, `Type_Primary` = ?, `Type_Secondary` = ?, `HP` = ?, `Attack` = ?, `Defense` = ?, `SpAttack` = ?, `SpDefense` = ?, `Speed` = ?, `EV_HP` = ?, `EV_Attack` = ?, `EV_Defense` = ?, `EV_SpAttack` = ?, `EV_SpDefense` = ?, `EV_Speed` = ?, `Female` = ?, `Male` = ?, `Genderless` = ?, `Height` = ?, `Weight` = ?, `Exp_Yield` = ?, `Is_Baby` = ?, `Is_Mythical` = ?, `Is_Legendary` = ?
        WHERE `ID` = ?
        LIMIT 1
      ");
      $Update_Pokedex_Entry->execute([
        $Pokemon,
        $Forme,
        $Type_Primary,
        $Type_Secondary,
        $Base_HP,
        $Base_Attack,
        $Base_Defense,
        $Base_Sp_Attack,
        $Base_Sp_Defense,
        $Base_Speed,
        $HP_EV,
        $Attack_EV,
        $Defense_EV,
        $Sp_Attack_EV,
        $Sp_Defense_EV,
        $Speed_EV,
        $Female_Odds,
        $Male_Odds,
        $Genderless_Odds,
        $Height,
        $Weight,
        $Exp_Yield,
        $Is_Baby,
        $Is_Mythical,
        $Is_Legendary,
        $Pokedex_ID
      ]);

      $PDO->commit();

      LogStaffAction('Pokedex Update', $User_Data['ID']);
    }
    catch ( PDOException $e )
    {
      $PDO->rollBack();

      HandleError($e);
    }

    return [
      'Success' => true,
      'Message' => 'This Pok&eacute;mon\'s pok&eacute;dex entry has been updated.',
    ];
  }
