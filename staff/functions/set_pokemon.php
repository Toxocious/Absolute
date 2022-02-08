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
        <button onclick='AddPokemonToArea(\"{$Database_Table}\", \"{$Obtainable_Location}\");'>
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
          WHERE `Map_Name` = ? AND `Zone` = ?
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

      if ( empty($Area_Pokemon) )
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
        <button onclick='AddPokemonToArea(\"{$Database_Table}\", \"{$Obtainable_Location}\");'>
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
