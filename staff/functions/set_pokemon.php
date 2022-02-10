<?php
  /**
   * Display a table of obtainable Pokemon in the specified database table.
   *
   * @param $Obtainable_Location
   */
  function ShowObtainablePokemonTable
  (
    $Database_Table
  )
  {
    global $PDO, $Poke_Class;

    switch ( $Database_Table )
    {
      case 'map_encounters':
        $Obtainable_Location_Name = 'Map Encounters';
        $Obtainable_Pokemon_Locations_Query = "SELECT DISTINCT(`Map_Name`), `Obtained_Text` FROM `map_encounters` ORDER BY `Obtained_Text` ASC";
        $Obtainable_Pokemon_Query = "SELECT * FROM `map_encounters` WHERE `Map_Name` = ?";
        break;

      case 'shop_pokemon':
        $Obtainable_Location_Name = 'Shop Pok&eacute;mon';
        $Obtainable_Pokemon_Locations_Query = "SELECT DISTINCT(`Obtained_Place`), `Obtained_Text` FROM `shop_pokemon` ORDER BY `Obtained_Text` ASC";
        $Obtainable_Pokemon_Query = "SELECT * FROM `shop_pokemon` WHERE `Obtained_Place` = ?";
        break;
    }

    try
    {
      $Get_Obtainable_Pokemon_Locations = $PDO->prepare($Obtainable_Pokemon_Locations_Query);
      $Get_Obtainable_Pokemon_Locations->execute([ ]);
      $Get_Obtainable_Pokemon_Locations->setFetchMode(PDO::FETCH_ASSOC);
      $Obtainable_Pokemon_Locations = $Get_Obtainable_Pokemon_Locations->fetchAll();
    }
    catch ( PDOException $e )
    {
      HandleError($e);
    }

    $Obtainable_Pokemon_Table_Text = "
      <div style='width: 100%;'>
        <h2>{$Obtainable_Location_Name}</h2>
      </div>
    ";

    if ( empty($Obtainable_Pokemon_Locations) )
    {
      return "
        {$Obtainable_Pokemon_Table_Text}

        <table class='border-gradient' style='width: 600px;'>
          <thead>
            <tr>
              <th colspan='2'>
                `{$Database_Table}`
              </th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td colspan='2'>
                No Pok&eacute;mon are set in the '{$Database_Table}' database table.
              </td>
            </tr>
          </tbody>
        </table>
      ";
    }

    foreach ( $Obtainable_Pokemon_Locations as $Pokemon_Location )
    {
      switch ( $Database_Table )
      {
        case 'map_encounters':
          $Database_Field_Identifier = $Pokemon_Location['Map_Name'];
          break;

        case 'shop_pokemon':
          $Database_Field_Identifier = $Pokemon_Location['Obtained_Place'];
          break;
      }

      try
      {
        $Get_Obtainable_Pokemon = $PDO->prepare($Obtainable_Pokemon_Query);
        $Get_Obtainable_Pokemon->execute([ $Database_Field_Identifier ]);
        $Get_Obtainable_Pokemon->setFetchMode(PDO::FETCH_ASSOC);
        $Obtainable_Pokemon = $Get_Obtainable_Pokemon->fetchAll();
      }
      catch ( PDOException $e )
      {
        HandleError($e);
      }

      if ( empty($Obtainable_Pokemon) )
      {
        $Obtainable_Pokemon_Table_Text .= "
          <table class='border-gradient' style='width: 600px;'>
            <thead>
              <tr>
                <th colspan='2'>
                  {$Database_Field_Identifier}
                </th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td colspan='2' style='padding: 10px;'>
                  No Pok&eacute;mon are set here.
                </td>
              </tr>
            </tbody>
          </table>
        ";
      }
      else
      {
        $Obtainable_Pokemon_Table_Row_Text = '';

        foreach ( $Obtainable_Pokemon as $Pokemon )
        {
          $Pokemon_Type = $Pokemon['Type'] ?? 'Normal';
          $Pokemon_Icon = $Poke_Class->FetchImages($Pokemon['Pokedex_ID'], $Pokemon['Alt_ID'], $Pokemon_Type);
          $Obtainable_Pokemon_Table_Row_Text .= "<img src='{$Pokemon_Icon['Icon']}' pokedex_id='{$Pokemon['Pokedex_ID']}' alt_id='{$Pokemon['Alt_ID']}' type='{$Pokemon_Type}' />";
        }

        $Obtainable_Pokemon_Table_Text .= "
          <table class='border-gradient' style='width: 750px;'>
            <tbody>
              <tr>
                <td colspan='1' style='width: 150px;'>
                  <h3>{$Pokemon_Location['Obtained_Text']}</h3>
                  <button onclick='ShowObtainablePokemonByLocation(\"{$Database_Table}\", \"{$Database_Field_Identifier}\");'>
                    Edit Pok&eacute;mon
                  </button>
                </td>
                <td colspan='2'>
                  {$Obtainable_Pokemon_Table_Row_Text}
                </td>
              </tr>
            </tbody>
          </table>
        ";
      }
    }

    return $Obtainable_Pokemon_Table_Text;
  }

  /**
   * Display a table of obtainable Pokemon in the specified table and area.
   *
   * @param $Database_Table
   * @param $Obtainable_Location
   */
  function ShowAreaObtainablePokemon
  (
    $Database_Table,
    $Obtainable_Location
  )
  {
    $Database_Table = Purify($Database_Table);
    $Obtainable_Location = Purify($Obtainable_Location);

    switch ( $Database_Table )
    {
      case 'map_encounters':
        return GetObtainableMapPokemon($Database_Table, $Obtainable_Location);
        break;

      case 'shop_pokemon':
        return GetObtainableShopPokemon($Database_Table, $Obtainable_Location);
        break;
    }
  }

  /**
   * Get all obtainable Pokemon from the specified map.
   *
   * @param $Database_Table
   * @param $Obtainable_Location
   */
  function GetObtainableMapPokemon
  (
    $Database_Table,
    $Obtainable_Location
  )
  {
    global $PDO, $Poke_Class;

    try
    {
      $Get_Map_Areas = $PDO->prepare("
        SELECT DISTINCT(`Zone`)
        FROM `map_encounters`
        WHERE `Map_Name` = ?
      ");
      $Get_Map_Areas->execute([
        $Obtainable_Location
      ]);
      $Get_Map_Areas->setFetchMode(PDO::FETCH_ASSOC);
      $Map_Areas = $Get_Map_Areas->fetchAll();
    }
    catch ( PDOException $e )
    {
      HandleError($e);
    }

    $Map_Areas_Table_Text = "
      <div style='width: 100%;'>
        <h2>Map Encounters</h2>
        <h3>{$Obtainable_Location}</h3>
        <button onclick='ShowPokemonCreationTable(\"{$Database_Table}\", \"{$Obtainable_Location}\");'>
          Add New Pok&eacute;mon
        </button>
      </div>
    ";

    if ( empty($Map_Areas) )
    {
      return "
        {$Map_Areas_Table_Text}

        <table class='border-gradient' style='width: 600px;'>
          <tbody>
            <tr>
              <td colspan='1'>
                No Pok&eacute;mon are set to appear on the maps.
              </td>
            </tr>
          </tbody>
        </table>
      ";
    }

    $Area_Tables = '';
    foreach ( $Map_Areas as $Area )
    {
      try
      {
        $Get_Area_Pokemon = $PDO->prepare("
          SELECT *
          FROM `map_encounters`
          WHERE `Map_Name` = ? AND (`Zone` = ? OR `ZONE` IS NULL)
        ");
        $Get_Area_Pokemon->execute([
          $Obtainable_Location,
          $Area['Zone']
        ]);
        $Get_Area_Pokemon->setFetchMode(PDO::FETCH_ASSOC);
        $Area_Pokemon = $Get_Area_Pokemon->fetchAll();
      }
      catch ( PDOException $e )
      {
        HandleError($e);
      }

      if ( empty($Area_Pokemon) || empty($Area['Zone']) )
        continue;

      $Total_Weight = 0;
      foreach ( $Area_Pokemon as $Pokemon )
      {
        if ( !empty($Pokemon['Weight']) )
        {
          $Total_Weight += $Pokemon['Weight'];
        }
      }

      $Area_Table_Row_Text = '';
      foreach ( $Area_Pokemon as $Pokemon )
      {
        $Pokemon_Type = $Pokemon['Type'] ?? 'Normal';
        $Pokedex_Data = $Poke_Class->FetchPokedexData($Pokemon['Pokedex_ID'], $Pokemon['Alt_ID'], $Pokemon_Type);

        $Global_Encounter_Text = '';
        if ( empty($Pokemon['Zone']) )
          $Global_Encounter_Text = '<i>This Pok&eacute;mon is a global encounter</i>';

        $Area_Table_Row_Text .= "
          <tr>
            <td colspan='1' style='width: 50px;'>
              <img src='{$Pokedex_Data['Icon']}' pokedex_id='{$Pokedex_Data['Pokedex_ID']}' alt_id='{$Pokedex_Data['Alt_ID']}' type='{$Pokemon_Type}' />
            </td>
            <td colspan='1' style='width: 250px;'>
              <a href='javascript:void(0);' onclick='EditSetPokemon(\"map_encounters\", {$Pokemon['ID']});'>
                <b style='font-size: 16px;'>
                  {$Pokedex_Data['Display_Name']}
                </b>
              </a>
              {$Global_Encounter_Text}
            </td>
            <td colspan='1' style='width: 300px;'>
              <b>Encounter Odds:</b> " . number_format(($Pokemon['Weight'] / $Total_Weight) * 100, 2) . "%
              <br />
              <b>Map Exp. Yield:</b> {$Pokemon['Min_Exp_Yield']} - {$Pokemon['Max_Exp_Yield']}
              <br />
              <b>Level Range:</b> {$Pokemon['Min_Level']} - {$Pokemon['Max_Level']}
            </td>
          </tr>
        ";
      }

      $Area_Tables .= "
        <table class='border-gradient' style='width: 600px;'>
          <thead>
            <tr>
              <th colspan='4'>
                Zone #{$Area['Zone']}
              </th>
            </tr>
          </thead>
          <tbody>
            {$Area_Table_Row_Text}
          </tbody>
        </table>
      ";
    }

    return "
      {$Map_Areas_Table_Text}

      {$Area_Tables}
    ";
  }

  /**
   * Get all obtainable Pokemon from the specified shop.
   *
   * @param $Database_Table
   * @param $Obtainable_Location
   */
  function GetObtainableShopPokemon
  (
    $Database_Table,
    $Obtainable_Location
  )
  {
    global $PDO, $Poke_Class;

    try
    {
      $Get_Shop_Pokemon = $PDO->prepare("
        SELECT *
        FROM `shop_pokemon`
        WHERE `Obtained_Place` = ?
      ");
      $Get_Shop_Pokemon->execute([
        $Obtainable_Location
      ]);
      $Get_Shop_Pokemon->setFetchMode(PDO::FETCH_ASSOC);
      $Shop_Pokemon = $Get_Shop_Pokemon->fetchAll();
    }
    catch ( PDOException $e )
    {
      HandleError($e);
    }

    $Shop_Pokemon_Table_Text = "
      <div style='width: 100%;'>
        <h2>Shop Pok&eacute;mon</h2>
        <h3>{$Obtainable_Location}</h3>
        <button onclick='ShowPokemonCreationTable(\"{$Database_Table}\", \"{$Obtainable_Location}\");'>
          Add New Pok&eacute;mon
        </button>
      </div>
    ";

    if ( empty($Shop_Pokemon) )
    {
      return "
        {$Shop_Pokemon_Table_Text}

        <table class='border-gradient' style='width: 600px;'>
          <tbody>
            <tr>
              <td colspan='1' style='padding: 10px;'>
                No Pok&eacute;mon are set to appear in the {$Obtainable_Location}.
              </td>
            </tr>
          </tbody>
        </table>
      ";
    }

    $Shop_Table_Row_Text = '';
    foreach ( $Shop_Pokemon as $Pokemon )
    {
      $Pokemon_Type = $Pokemon['Type'] ?? 'Normal';
      $Pokedex_Data = $Poke_Class->FetchPokedexData($Pokemon['Pokedex_ID'], $Pokemon['Alt_ID'], $Pokemon_Type);

      $Price_List = json_decode($Pokemon['Prices'], true);

      $Pokemon_Cost_Text = '';
      foreach ( $Price_List[0] as $Currency => $Amount )
      {
        $Pokemon_Cost_Text .= "
          <div style='align-items: center; display: flex; gap: 10px; justify-content: center; width: 100%;'>
            <div>
              <img src='" . DOMAIN_SPRITES . "/Assets/{$Currency}.png' />
            </div>
            <div>
              " . number_format($Amount) . "
            </div>
          </div>
        ";
      }

      $Shop_Table_Row_Text .= "
        <tr>
          <td colspan='1' style='width: 50px;'>
            <img src='{$Pokedex_Data['Icon']}' pokedex_id='{$Pokedex_Data['Pokedex_ID']}' alt_id='{$Pokedex_Data['Alt_ID']}' type='{$Pokemon_Type}' />
          </td>
          <td colspan='1' style='width: 250px;'>
            <a href='javascript:void(0);' onclick='EditSetPokemon(\"shop_pokemon\", {$Pokemon['ID']});'>
              <b style='font-size: 16px;'>
                {$Pokedex_Data['Display_Name']}
              </b>
            </a>
          </td>
          <td colspan='1' style='width: 300px;'>
            {$Pokemon_Cost_Text}
          </td>
        </tr>
      ";
    }

    return "
      {$Shop_Pokemon_Table_Text}

      <table class='border-gradient' style='width: 600px;'>
        <tbody>
          {$Shop_Table_Row_Text}
        </tbody>
      </table>
    ";
  }

  /**
   * Show an editable table for the specified Pokemon.
   *
   * @param $Database_Table
   * @param $Obtainable_Pokemon_Database_ID
   */
  function ShowPokemonEditTable
  (
    $Database_Table,
    $Obtainable_Pokemon_Database_ID
  )
  {
    global $PDO, $Poke_Class;

    switch ( $Database_Table )
    {
      case 'map_encounters':
        $Table_Name = 'Map Encounters';
        $Pokemon_Entry_Query = "SELECT * FROM `map_encounters` WHERE `ID` = ? LIMIT 1";
        break;

      case 'shop_pokemon':
        $Table_Name = 'Shop Pok&eacute;mon';
        $Pokemon_Entry_Query = "SELECT * FROM `shop_pokemon` WHERE `ID` = ? LIMIT 1";
        break;
    }

    try
    {
      $Get_Pokemon_Entry = $PDO->prepare($Pokemon_Entry_Query);
      $Get_Pokemon_Entry->execute([
        $Obtainable_Pokemon_Database_ID
      ]);
      $Get_Pokemon_Entry->setFetchMode(PDO::FETCH_ASSOC);
      $Pokemon_Entry = $Get_Pokemon_Entry->fetch();
    }
    catch ( PDOException $e )
    {
      HandleError($e);
    }

    if ( empty($Pokemon_Entry) )
    {
      return "
        <table class='border-gradient' style='width: 600px;'>
          <tbody>
            <tr>
              <td colspan='1' style='padding: 10px;'>
                The Pok&eacute;mon that you selected to edit doesn't exist.
              </td>
            </tr>
          </tbody>
        </table>
      ";
    }

    $Pokedex_Info = $Poke_Class->FetchPokedexData($Pokemon_Entry['Pokedex_ID'], $Pokemon_Entry['Alt_ID'], $Pokemon_Entry['Type'] ?? 'Normal');

    switch ( $Database_Table )
    {
      case 'map_encounters':
        $Additional_Table_Rows = "
          <tr>
            <td colspan='2' style='width: 50%;'>
              <b>Encounter Weight / Odds</b>
            </td>
            <td colspan='2' style='width: 50%;'>
              <input type='number' name='Encounter_Weight' value='{$Pokemon_Entry['Weight']}' style='width: 100px;' />
            </td>
          </tr>

          <tr>
            <td colspan='1' style='width: 25%;'>
              <b>Minimum Level</b>
            </td>
            <td colspan='1' style='width: 25%;'>
              <input type='number' name='Min_Level' value='{$Pokemon_Entry['Min_Level']}' style='width: 100px;' />
            </td>
            <td colspan='1' style='width: 25%;'>
              <b>Maximum Level</b>
            </td>
            <td colspan='1' style='width: 25%;'>
              <input type='number' name='Max_Level' value='{$Pokemon_Entry['Max_Level']}' style='width: 100px;' />
            </td>
          </tr>

          <tr>
            <td colspan='1' style='width: 25%;'>
              <b>Minimum Map Exp.</b>
            </td>
            <td colspan='1' style='width: 25%;'>
              <input type='number' name='Min_Map_Exp' value='{$Pokemon_Entry['Min_Exp_Yield']}' style='width: 100px;' />
            </td>
            <td colspan='1' style='width: 25%;'>
              <b>Maximum Map Exp.</b>
            </td>
            <td colspan='1' style='width: 25%;'>
              <input type='number' name='Max_Map_Exp' value='{$Pokemon_Entry['Max_Exp_Yield']}' style='width: 100px;' />
            </td>
          </tr>
        ";
        break;

      case 'shop_pokemon':
        $Price_List = json_decode($Pokemon_Entry['Prices'], true);

        $Pokemon_Cost_Text = '';

        $Available_Currencies = ['Money', 'Abso_Coins'];
        foreach ( $Available_Currencies as $Currency )
        {
          $Amount = 0;
          if ( !empty($Price_List[0][$Currency]) )
            $Amount = $Price_List[0][$Currency];

          $Pokemon_Cost_Text .= "
            <tr>
              <td colspan='2'>
                <img src='" . DOMAIN_SPRITES . "/Assets/{$Currency}.png' />
              </td>
              <td colspan='2'>
                <input type='number' name='{$Currency}_Cost' value='{$Amount}' />
              </td>
            </tr>
          ";
        }

        $Type_Text = '';
        $Type_Options = ['Normal', 'Shiny'];
        foreach ( $Type_Options as $Type )
        {
          $Type_Text .= "
            <option value='{$Type}' " . ($Pokemon_Entry['Type'] === $Type ? 'selected' : '') . ">
              {$Type}
            </option>
          ";
        }

        $Additional_Table_Rows = "
          <tr>
            <td colspan='2' style='width: 50%;'>
              <b>Pok&eacute;mon Type</b>
            </td>
            <td colspan='2' style='width: 50%;'>
              <select name='Pokemon_Type'>
                {$Type_Text}
              </select>
            </td>
          </tr>

          <tr>
            <td colspan='2' style='width: 50%;'>
              <b>Pok&eacute;mon Remaining</b>
            </td>
            <td colspan='2' style='width: 50%;'>
              <input type='number' name='Pokemon_Remaining' value='{$Pokemon_Entry['Remaining']}' />
            </td>
          </tr>

          {$Pokemon_Cost_Text}
        ";
        break;
    }

    $Active_Text = '';
    $Active_Options = ['Yes', 'No'];
    foreach ( $Active_Options as $Active )
    {
      $Active_Text .= "
        <option value='{$Active}' " . ($Pokemon_Entry['Active'] === $Active ? 'selected' : '') . ">
          {$Active}
        </option>
      ";
    }

    try
    {
      $Fetch_Pokedex = $PDO->prepare("
        SELECT `ID`, `Pokedex_ID`, `Alt_ID`, `Pokemon`, `Forme`
        FROM `pokedex`
        ORDER BY `Pokedex_ID` ASC, `Alt_ID` ASC
      ");
      $Fetch_Pokedex->execute();
      $Fetch_Pokedex->setFetchMode(PDO::FETCH_ASSOC);
      $Pokedex = $Fetch_Pokedex->fetchAll();
    }
    catch ( PDOException $e )
    {
      HandleError( $e->getMessage() );
    }

    $Pokedex_Dropdown_List = '';
    foreach ( $Pokedex as $Pokedex_Entry )
    {
      $Pokemon_Display_Name = $Pokedex_Entry['Pokemon'];
      if ( !empty($Pokedex_Entry['Forme']) )
        $Pokemon_Display_Name .= " {$Pokedex_Entry['Forme']}";

      $Pokemon_Display_Name .= " - #{$Pokedex_Entry['Pokedex_ID']}";
      if ( !empty($Pokedex_Entry['Alt_ID']) && $Pokedex_Entry['Alt_ID'] > 0 )
        $Pokemon_Display_Name .= ".{$Pokedex_Entry['Alt_ID']}";

      $Active_Selection = $Pokemon_Entry['Pokedex_ID'] === $Pokedex_Entry['Pokedex_ID'] && $Pokemon_Entry['Alt_ID'] === $Pokedex_Entry['Alt_ID'];

      $Pokedex_Dropdown_List .= "
        <option value='{$Pokedex_Entry['ID']}' " . ($Active_Selection ? 'selected' : '') . ">
          {$Pokemon_Display_Name}
        </option>
      ";
    }

    return "
      <div style='width: 100%;'>
        <h2>{$Table_Name}</h2>
        <h3>Editing Pok&eacute;mon</h3>
      </div>

      <table class='border-gradient' style='width: 600px;'>
        <thead>
          <tr>
            <th colspan='4' style='width: 100%;'>
              Configure `{$Table_Name}` Pok&eacute;mon #{$Pokemon_Entry['ID']}
            </th>
          </tr>
        </thead>

        <tbody>
          <tr>
            <td colspan='4'>
              <img src='{$Pokedex_Info['Sprite']}' />
              <br />
              <b>{$Pokedex_Info['Display_Name']}</b>
            </td>
          </tr>
        </tbody>

        <thead>
          <tr>
            <th colspan='4'>
              Configuration Options
            </th>
          </tr>
        </thead>

        <tbody>
          <tr>
            <td colspan='2' style='width: 50%;'>
              <b>Active</b>
            </td>
            <td colspan='2' style='width: 50%;'>
              <select name='Is_Pokemon_Active'>
                {$Active_Text}
              </select>
            </td>
          </tr>

          <tr>
            <td colspan='2' style='width: 50%;'>
              <b>Pok&eacute;mon Species</b>
            </td>
            <td colspan='2' style='width: 50%;'>
              <select name='Pokemon_Species'>
                {$Pokedex_Dropdown_List}
              </select>
            </td>
          </tr>

          <tr>
            <td colspan='2' style='width: 50%;'>
              <b>Obtained Text</b>
            </td>
            <td colspan='2' style='width: 50%;'>
              <input type='text' name='Obtained_Text' value='{$Pokemon_Entry['Obtained_Text']}' />
            </td>
          </tr>

          {$Additional_Table_Rows}
        </tbody>

        <tbody>
          <tr>
            <td colspan='4'>
              <button onclick='FinalizePokemonEdit(\"{$Database_Table}\", {$Pokemon_Entry['ID']});'>
                Update Pok&eacute;mon
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    ";
  }

  /**
   * Finalize the edited Pokemon.
   *
   * @param $Database_Table
   * @param $Obtainable_Pokemon_Database_ID
   * @param $Database_Table
   * @param $Pokemon_Database_ID
   * @param $Pokemon_Active
   * @param $Pokemon_Dex_ID
   * @param $Obtained_Text
   * @param $Encounter_Weight
   * @param $Min_Level
   * @param $Max_Level
   * @param $Min_Map_Exp
   * @param $Max_Map_Exp
   * @param $Pokemon_Type
   * @param $Pokemon_Remaining
   * @param $Money_Cost
   * @param $Abso_Coins_Cost
   */
  function FinalizePokemonEdit
  (
    $Database_Table,
    $Obtainable_Pokemon_Database_ID,
    $Pokemon_Active,
    $Pokemon_Dex_ID,
    $Obtained_Text,
    $Encounter_Weight,
    $Min_Level,
    $Max_Level,
    $Min_Map_Exp,
    $Max_Map_Exp,
    $Pokemon_Type,
    $Pokemon_Remaining,
    $Money_Cost,
    $Abso_Coins_Cost
  )
  {
    global $PDO, $Poke_Class;

    $Pokedex_Info = $Poke_Class->FetchPokedexData(null, null, null, $Pokemon_Dex_ID);
    $Pokemon_Active = $Pokemon_Active == 'Yes' ? 1 : 0;

    switch ( $Database_Table )
    {
      case 'map_encounters':
        $Update_Query = "
          UPDATE `map_encounters`
          SET `Pokedex_ID` = ?, `Alt_ID` = ?, `Obtained_Text` = ?, `Weight` = ?, `Min_Level` = ?, `Max_Level` = ?, `Min_Exp_Yield` = ?, `Max_Exp_Yield` = ?, `Active` = ?
          WHERE `ID` = ?
          LIMIT 1
        ";
        $Update_Query_Params = [
          $Pokedex_Info['Pokedex_ID'],
          $Pokedex_Info['Alt_ID'],
          $Obtained_Text,
          $Encounter_Weight,
          $Min_Level,
          $Max_Level,
          $Min_Map_Exp,
          $Max_Map_Exp,
          $Pokemon_Active,
          $Obtainable_Pokemon_Database_ID
        ];
        break;

      case 'shop_pokemon':
        $Pokemon_Price_JSON = json_encode([
          'Money' => $Money_Cost,
          'Abso_Coins' => $Abso_Coins_Cost
        ]);

        $Update_Query = "
          UPDATE `shop_pokemon`
          SET `Pokedex_ID` = ?, `Alt_ID` = ?, `Obtained_Text` = ?, `Active` = ?, `Type` = ?, `Remaining` = ?, `Prices` = ?
          WHERE `ID` = ?
          LIMIT 1
        ";
        $Update_Query_Params = [
          $Pokedex_Info['Pokedex_ID'],
          $Pokedex_Info['Alt_ID'],
          $Obtained_Text,
          $Pokemon_Active,
          $Pokemon_Type,
          $Pokemon_Remaining,
          "[{$Pokemon_Price_JSON}]",
          $Obtainable_Pokemon_Database_ID
        ];
        break;
    }

    try
    {
      $PDO->beginTransaction();

      $Update_Pokemon_Entry = $PDO->prepare($Update_Query);
      $Update_Pokemon_Entry->execute($Update_Query_Params);

      $PDO->commit();
    }
    catch ( PDOException $e )
    {
      $PDO->rollBack();

      HandleError($e);
    }

    return [
      'Success' => true,
      'Message' => 'You have updated this Pok&eacute;mon',
      'Finalized_Edit_Table' => ShowPokemonEditTable($Database_Table, $Obtainable_Pokemon_Database_ID),
    ];
  }

  /**
   * Show a table that allows customization and creation of a new Pokemon.
   *
   * @param $Database_Table
   * @param $Obtainable_Location
   */
  function ShowPokemonCreationTable
  (
    $Database_Table,
    $Obtainable_Location
  )
  {
    global $PDO;

    $Location_Name = ucwords(str_replace('_', ' ', $Obtainable_Location));

    switch ( $Database_Table )
    {
      case 'map_encounters':
        $Table_Name = 'Map Encounters';
        $Additional_Table_Rows = "
          <tr>
            <td colspan='2' style='width: 50%;'>
              <b>Encounter Weight / Odds</b>
            </td>
            <td colspan='2' style='width: 50%;'>
              <input type='number' name='Encounter_Weight' value='' style='width: 100px;' />
            </td>
          </tr>

          <tr>
            <td colspan='2' style='width: 50%;'>
              <b>Encounter Zone</b>
            </td>
            <td colspan='2' style='width: 50%;'>
              <input type='number' name='Encounter_Zone' value='' style='width: 100px;' />
            </td>
          </tr>

          <tr>
            <td colspan='1' style='width: 25%;'>
              <b>Minimum Level</b>
            </td>
            <td colspan='1' style='width: 25%;'>
              <input type='number' name='Min_Level' value='' style='width: 100px;' />
            </td>
            <td colspan='1' style='width: 25%;'>
              <b>Maximum Level</b>
            </td>
            <td colspan='1' style='width: 25%;'>
              <input type='number' name='Max_Level' value='' style='width: 100px;' />
            </td>
          </tr>

          <tr>
            <td colspan='1' style='width: 25%;'>
              <b>Minimum Map Exp.</b>
            </td>
            <td colspan='1' style='width: 25%;'>
              <input type='number' name='Min_Map_Exp' value='' style='width: 100px;' />
            </td>
            <td colspan='1' style='width: 25%;'>
              <b>Maximum Map Exp.</b>
            </td>
            <td colspan='1' style='width: 25%;'>
              <input type='number' name='Max_Map_Exp' value='' style='width: 100px;' />
            </td>
          </tr>
        ";
        break;

      case 'shop_pokemon':
        $Table_Name = 'Shop Pok&eacute;mon';
        $Pokemon_Cost_Text = '';

        $Available_Currencies = ['Money', 'Abso_Coins'];
        foreach ( $Available_Currencies as $Currency )
        {
          $Pokemon_Cost_Text .= "
            <tr>
              <td colspan='2'>
                <img src='" . DOMAIN_SPRITES . "/Assets/{$Currency}.png' />
              </td>
              <td colspan='2'>
                <input type='number' name='{$Currency}_Cost' value='' />
              </td>
            </tr>
          ";
        }

        $Type_Text = '';
        $Type_Options = ['Normal', 'Shiny'];
        foreach ( $Type_Options as $Type )
        {
          $Type_Text .= "
            <option value='{$Type}'>
              {$Type}
            </option>
          ";
        }

        $Additional_Table_Rows = "
          <tr>
            <td colspan='2' style='width: 50%;'>
              <b>Pok&eacute;mon Type</b>
            </td>
            <td colspan='2' style='width: 50%;'>
              <select name='Pokemon_Type'>
                {$Type_Text}
              </select>
            </td>
          </tr>

          <tr>
            <td colspan='2' style='width: 50%;'>
              <b>Pok&eacute;mon Remaining</b>
            </td>
            <td colspan='2' style='width: 50%;'>
              <input type='number' name='Pokemon_Remaining' value='' />
            </td>
          </tr>

          {$Pokemon_Cost_Text}
        ";
        break;
    }

    $Active_Text = '';
    $Active_Options = ['Yes', 'No'];
    foreach ( $Active_Options as $Active )
    {
      $Active_Text .= "
        <option value='{$Active}'>
          {$Active}
        </option>
      ";
    }

    try
    {
      $Fetch_Pokedex = $PDO->prepare("
        SELECT `ID`, `Pokedex_ID`, `Alt_ID`, `Pokemon`, `Forme`
        FROM `pokedex`
        ORDER BY `Pokedex_ID` ASC, `Alt_ID` ASC
      ");
      $Fetch_Pokedex->execute();
      $Fetch_Pokedex->setFetchMode(PDO::FETCH_ASSOC);
      $Pokedex = $Fetch_Pokedex->fetchAll();
    }
    catch ( PDOException $e )
    {
      HandleError($e);
    }

    $Pokedex_Dropdown_List = '';
    foreach ( $Pokedex as $Pokedex_Entry )
    {
      $Pokemon_Display_Name = $Pokedex_Entry['Pokemon'];
      if ( !empty($Pokedex_Entry['Forme']) )
        $Pokemon_Display_Name .= " {$Pokedex_Entry['Forme']}";

      $Pokemon_Display_Name .= " - #{$Pokedex_Entry['Pokedex_ID']}";
      if ( !empty($Pokedex_Entry['Alt_ID']) && $Pokedex_Entry['Alt_ID'] > 0 )
        $Pokemon_Display_Name .= ".{$Pokedex_Entry['Alt_ID']}";

      $Pokedex_Dropdown_List .= "
        <option value='{$Pokedex_Entry['ID']}'>
          {$Pokemon_Display_Name}
        </option>
      ";
    }

    return "
      <div style='width: 100%;'>
        <h2>{$Table_Name}</h2>
        <h3>Adding Pok&eacute;mon</h3>
      </div>

      <table class='border-gradient' style='width: 600px;'>
        <thead>
          <tr>
            <th colspan='4' style='width: 100%;'>
              {$Location_Name}
            </th>
          </tr>
        </thead>

        <tbody>
          <tr>
            <td colspan='2' style='width: 50%;'>
              <b>Active</b>
            </td>
            <td colspan='2' style='width: 50%;'>
              <select name='Is_Pokemon_Active'>
                {$Active_Text}
              </select>
            </td>
          </tr>

          <tr>
            <td colspan='2' style='width: 50%;'>
              <b>Pok&eacute;mon Species</b>
            </td>
            <td colspan='2' style='width: 50%;'>
              <select name='Pokemon_Species'>
                {$Pokedex_Dropdown_List}
              </select>
            </td>
          </tr>

          <tr>
            <td colspan='2' style='width: 50%;'>
              <b>Obtained Text</b>
            </td>
            <td colspan='2' style='width: 50%;'>
              <input type='text' name='Obtained_Text' value='{$Location_Name}' />
            </td>
          </tr>

          {$Additional_Table_Rows}
        </tbody>

        <tbody>
          <tr>
            <td colspan='4'>
              <button onclick='FinalizePokemonCreation(\"{$Database_Table}\", \"{$Obtainable_Location}\");'>
                Create Pok&eacute;mon
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    ";
  }

  /**
   * Finalize creating a new Pokemon for an area.
   *
   * @param $Database_Table
   * @param $Obtainable_Pokemon_Database_ID
   * @param $Database_Table
   * @param $Pokemon_Database_ID
   * @param $Pokemon_Active
   * @param $Pokemon_Dex_ID
   * @param $Obtained_Text
   * @param $Encounter_Weight
   * @param $Min_Level
   * @param $Max_Level
   * @param $Min_Map_Exp
   * @param $Max_Map_Exp
   * @param $Pokemon_Type
   * @param $Pokemon_Remaining
   * @param $Money_Cost
   * @param $Abso_Coins_Cost
   */
  function FinalizePokemonCreation
  (
    $Database_Table,
    $Obtainable_Pokemon_Database_ID,
    $Pokemon_Active,
    $Pokemon_Dex_ID,
    $Obtained_Text,
    $Encounter_Weight,
    $Encounter_Zone,
    $Min_Level,
    $Max_Level,
    $Min_Map_Exp,
    $Max_Map_Exp,
    $Pokemon_Type,
    $Pokemon_Remaining,
    $Money_Cost,
    $Abso_Coins_Cost
  )
  {
    global $PDO;

    $Pokemon_Active = $Pokemon_Active == 'Yes' ? 1 : 0;

    switch ( $Database_Table )
    {
      case 'map_encounters':
        $Creation_Query = "INSERT INTO `map_encounters` (`Map_Name`, `Pokedex_ID`, `Alt_ID`, `Min_Level`, `Max_Level`, `Min_Exp_Yield`, `Max_Exp_Yield`, `Weight`, `Zone`, `Active`, `Obtained_Text`) VALUES ( ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ? )";
        $Creation_Params = [$Obtainable_Pokemon_Database_ID, $Pokemon_Dex_ID, 0, $Min_Level, $Max_Level, $Min_Map_Exp, $Max_Map_Exp, $Encounter_Weight, $Encounter_Zone, $Pokemon_Active, $Obtained_Text];
        break;

      case 'shop_pokemon':
        $Pokemon_Prices = json_encode([ 'Money' => $Money_Cost, 'Abso_Coins' => $Abso_Coins_Cost ]);

        $Creation_Query = "INSERT INTO `shop_pokemon` (`Obtained_Place`, `Obtained_Text`, `Pokedex_ID`, `Alt_ID`, `Type`, `Active`, `Remaining`, `Prices`) VALUES ( ?, ?, ?, ?, ?, ?, ?, ? )";
        $Creation_Params = [$Obtainable_Pokemon_Database_ID, $Obtained_Text, $Pokemon_Dex_ID, 0, $Pokemon_Type, $Pokemon_Active, $Pokemon_Remaining, "[{$Pokemon_Prices}]"];
        break;
    }

    try
    {
      $PDO->beginTransaction();

      $Create_Pokemon = $PDO->prepare($Creation_Query);
      $Create_Pokemon->execute($Creation_Params);

      $PDO->commit();
    }
    catch ( PDOException $e )
    {
      $PDO->rollBack();

      HandleError($e);
    }

    return [
      'Success' => true,
      'Message' => "You have successfully added a Pok&eacute;mon to {$Obtained_Text}."
    ];
  }
