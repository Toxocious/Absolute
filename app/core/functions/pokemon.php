<?php
  /**
   * Calculate the value of the specified stat.
   *
   * @param $Stat_Name
   * @param $Base_Stat_Value
   * @param $Pokemon_Level
   * @param $Pokemon_Stat_IV
   * @param $Pokemon_Stat_EV
   * @param $Pokemon_Nature
   */
  function CalculateStat
  (
    $Stat_Name,
    $Base_Stat_Value,
    $Pokemon_Level,
    $Pokemon_Stat_IV,
    $Pokemon_Stat_EV,
    $Pokemon_Nature
  )
  {
    $Pokemon_Level = $Pokemon_Level < 1 ? 1 : $Pokemon_Level;
    $Pokemon_Stat_IV = $Pokemon_Stat_IV > 31 ? 31 : $Pokemon_Stat_IV;
    $Pokemon_Stat_EV = $Pokemon_Stat_EV > 252 ? 252 : $Pokemon_Stat_EV;

    if ( $Stat_Name == 'HP' )
    {
      if ( $Base_Stat_Value == 1 )
        return 1;

      return floor((((2 * $Base_Stat_Value + $Pokemon_Stat_IV + ($Pokemon_Stat_EV / 4)) * $Pokemon_Level) / 100) + $Pokemon_Level + 10);
    }
    else
    {
      $Nature_Data = Natures()[$Pokemon_Nature];

      if ( $Nature_Data['Plus'] == $Stat_Name )
        $Nature_Bonus = 1.1;
      else if ( $Nature_Data['Minus'] == $Stat_Name )
        $Nature_Bonus = 0.9;
      else
        $Nature_Bonus = 1;

      return floor(((((2 * $Base_Stat_Value + $Pokemon_Stat_IV + ($Pokemon_Stat_EV / 4)) * $Pokemon_Level) / 100) + 5) * $Nature_Bonus);
    }
  }

  /**
   * Spawn in a copy of the specified species of Pokemon.
   *
   * @param $Pokedex_ID
   * @param $Alt_ID
   * @param $Level
   * @param $Type
   * @param $Gender
   * @param $Obtained_At
   * @param $Location
   * @param $Slot
   * @param $Owner_ID
   * @param $Nature
   * @param $IVs
   * @param $EVs
   */
  function CreatePokemon
  (
    $Owner_ID,
    $Pokedex_ID,
    $Alt_ID,
    $Level = 5,
    $Type = "Normal",
    $Gender = null,
    $Obtained_At = "Unknown",
    $Nature = null,
    $IVs = null,
    $EVs = null
  )
  {
    global $PDO;

    $Pokemon = GetPokedexData($Pokedex_ID, $Alt_ID, $Type);
    if ( !$Pokemon )
      return false;

    if ( !is_numeric($Level) )
      $Level = 5;

    if ( $Type != "Normal" )
      $Display_Name = $Type . $Pokemon['Name'];
    else
      $Display_Name = $Pokemon['Name'];

    if ( empty($Gender) )
      $Gender = GenerateGender($Pokemon['Pokedex_ID'], $Pokemon['Alt_ID']);

    $Ability = GenerateAbility($Pokemon['Pokedex_ID'], $Pokemon['Alt_ID']);

    $Poke_Images = GetSprites($Pokedex_ID, $Alt_ID, $Type);

    try
    {
      $Query_Party = $PDO->prepare("
        SELECT DISTINCT(`Slot`)
        FROM `pokemon`
        WHERE `Owner_Current` = ? AND (Slot = 1 OR Slot = 2 OR Slot = 3 OR Slot = 4 OR Slot = 5 OR Slot = 6) AND `Location` = 'Roster'
        LIMIT 6
      ");
      $Query_Party->execute([
        $Owner_ID
      ]);
      $Query_Party->setFetchMode(PDO::FETCH_ASSOC);
    }
    catch ( PDOException $e )
    {
      HandleError($e);
    }

    $Slots_Used = [0, 0, 0, 0, 0, 0, 0];
    while ( $Party = $Query_Party->fetch() )
      $Slots_Used[$Party['Slot']] = 1;

    $First_Empty_Slot = array_search(0, $Slots_Used);
    if ( $First_Empty_Slot === false )
    {
      $Location = 'Box';
      $Slot = 7;
    }
    else
    {
      $Location = 'Roster';
      $Slot = $First_Empty_Slot;
    }

    $Experience = FetchExperience($Level, 'Pokemon');

    if ( empty($IVs) )
    {
      $IVs = mt_rand(0, 31) . "," . mt_rand(0, 31) . "," . mt_rand(0, 31) . "," . mt_rand(0, 31) . "," . mt_rand(0, 31) . "," . mt_rand(0, 31);
    }

    if ( empty($EVs) )
    {
      $EVs = "0,0,0,0,0,0";
    }

    if ( empty($Nature) )
    {
      $Nature = GenerateNature();
    }

    try
    {
      $PDO->beginTransaction();

      $Pokemon_Create = $PDO->prepare("
        INSERT INTO `pokemon` (
          `Pokedex_ID`,
          `Alt_ID`,
          `Name`,
          `Forme`,
          `Type`,
          `Experience`,
          `Location`,
          `Slot`,
          `Owner_Current`,
          `Owner_Original`,
          `Gender`,
          `IVs`,
          `EVs`,
          `Nature`,
          `Creation_Date`,
          `Creation_Location`,
          `Ability`
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
      ");
      $Pokemon_Create->execute([
        $Pokedex_ID, $Alt_ID, $Pokemon['Name'], $Pokemon['Forme'],
        $Type, $Experience, $Location, $Slot, $Owner_ID, $Owner_ID, $Gender,
        $IVs, $EVs, $Nature, time(), $Obtained_At, $Ability
      ]);
      $Poke_DB_ID = $PDO->lastInsertId();

      $PDO->commit();
    }
    catch ( PDOException $e )
    {
      $PDO->rollBack();

      HandleError($e);
    }

    return [
      'Name' => $Pokemon['Name'],
      'Forme' => $Pokemon['Forme'],
      'Display_Name' => $Display_Name,
      'Exp' => $Experience,
      'Gender' => $Gender,
      'Location' => $Location,
      'Slot' => $Slot,
      'PokeID' => $Poke_DB_ID,
      'Stats' => $Pokemon['Base_Stats'],
      'IVs' => explode(',', $IVs),
      'EVs' => explode(',', $EVs),
      'Nature' => $Nature,
      'Ability' => $Ability,
      'Sprite' => $Poke_Images['Sprite'],
      'Icon' => $Poke_Images['Icon'],
    ];
  }

  /**
   * Get all abilities of the specified Pokemon.
   *
   * @param $Pokedex_ID
   * @param $Alt_ID
   */
  function GetAbilities
  (
    $Pokedex_ID,
    $Alt_ID
  )
  {
    global $PDO;

    try
    {
      $Fetch_Abilities = $PDO->prepare("
        SELECT `Ability_1`, `Ability_2`, `Hidden_Ability`
        FROM `pokedex`
        WHERE `Pokedex_ID` = ? AND `Alt_ID` = ?
        LIMIT 1
      ");
      $Fetch_Abilities->execute([
        $Pokedex_ID,
        $Alt_ID
      ]);
      $Fetch_Abilities->setFetchMode(PDO::FETCH_ASSOC);
      $Abilities = $Fetch_Abilities->fetch();
    }
    catch ( PDOException $e )
    {
      HandleError($e);
    }

    if ( !$Abilities )
      return false;

    return $Abilities;
  }

  /**
   * Get the base stats of a Pokemon species.
   *
   * @param $Pokedex_ID
   * @param $Alt_ID
   */
  function GetBaseStats
  (
    $Pokedex_ID,
    $Alt_ID
  )
  {
    global $PDO;

    try
    {
      $Fetch_Stats = $PDO->prepare("
        SELECT `HP`, `Attack`, `Defense`, `SpAttack`, `SpDefense`, `Speed`
        FROM `pokedex`
        WHERE `Pokedex_ID` = ? AND `Alt_ID` = ?
        LIMIT 1
      ");
      $Fetch_Stats->execute([
        $Pokedex_ID,
        $Alt_ID
      ]);
      $Fetch_Stats->setFetchMode(PDO::FETCH_ASSOC);
      $Stats = $Fetch_Stats->fetch();
    }
    catch ( PDOException $e )
    {
      HandleError($e);
    }

    if ( !$Stats )
      return false;

    return $Stats;
  }

  /**
     * Get the current stats of the specified Pokemon.
     *
     * @param int $Pokemon_ID
     */
    function GetCurrentStats
    (
      int $Pokemon_ID
    )
    {
      global $PDO;

      try
      {
        $Get_Pokemon_Data = $PDO->prepare("
          SELECT `Pokedex_ID`, `Alt_ID`, `Nature`, `Type`, `EVs`, `IVs`, `Experience`
          FROM `pokemon`
          WHERE `ID` = ?
          LIMIT 1
        ");
        $Get_Pokemon_Data->execute([
          $Pokemon_ID
        ]);
        $Get_Pokemon_Data->setFetchMode(PDO::FETCH_ASSOC);
        $Pokemon = $Get_Pokemon_Data->fetch();
      }
      catch ( PDOException $e )
      {
        HandleError($e);
      }

      if ( !isset($Pokemon) )
        return false;

      switch($Pokemon['Type'])
      {
        case 'Normal':
          $StatBonus = 0;
          break;

        case 'Shiny':
          $StatBonus = 5;
          break;

        case 'Sunset':
          $StatBonus = 10;
          break;

        default:
          $StatBonus = 0;
          break;
      }

      $Base_Stats = GetBaseStats($Pokemon['Pokedex_ID'], $Pokemon['Alt_ID']);
      $Level = FetchLevel($Pokemon['Experience'], 'Pokemon');
      $EVs = explode(',', $Pokemon['EVs']);
      $IVs = explode(',', $Pokemon['IVs']);

      $Stats = [
        CalculateStat('HP', floor($Base_Stats['HP'] + $StatBonus), $Level, $IVs[0], $EVs[0], $Pokemon['Nature']),
        CalculateStat('Attack', floor($Base_Stats['Attack'] + $StatBonus), $Level, $IVs[1], $EVs[1], $Pokemon['Nature']),
        CalculateStat('Defense', floor($Base_Stats['Defense'] + $StatBonus), $Level, $IVs[2], $EVs[2], $Pokemon['Nature']),
        CalculateStat('SpAttack', floor($Base_Stats['SpAttack'] + $StatBonus), $Level, $IVs[3], $EVs[3], $Pokemon['Nature']),
        CalculateStat('SpDefense', floor($Base_Stats['SpDefense'] + $StatBonus), $Level, $IVs[4], $EVs[4], $Pokemon['Nature']),
        CalculateStat('Speed', floor($Base_Stats['Speed'] + $StatBonus), $Level, $IVs[5], $EVs[5], $Pokemon['Nature']),
      ];

      return $Stats;
    }

  /**
   * Get database information on the specified move.
   *
   * @param $Move_ID
   */
  function GetMoveData
  (
    $Move_ID
  )
  {
    global $PDO;

    try
    {
      $Fetch_Move_Data = $PDO->prepare("
        SELECT *
        FROM `moves`
        WHERE `ID` = ?
        LIMIT 1
      ");
      $Fetch_Move_Data->execute([
        $Move_ID
      ]);
      $Fetch_Move_Data->setFetchMode(PDO::FETCH_ASSOC);
      $Move_Data = $Fetch_Move_Data->fetch();
    }
    catch ( PDOException $e )
    {
      HandleError($e);
    }

    if ( !$Move_Data )
      return false;

    return [
      "ID" => $Move_Data['ID'],
      "Name" => $Move_Data['Name'],
      "Type" => $Move_Data['Move_Type'],
      "Category" => $Move_Data['Category'],
      "Power" => $Move_Data['Power'],
      "Accuracy" => $Move_Data['Accuracy'],
      "Priority" => $Move_Data['Priority'],
      "PP" => $Move_Data['PP'],
      "Effect_Short" => $Move_Data['Effect_Short'],
    ];
  }

  /**
   * Get pokedex data of the specified Pokemon species.
   *
   * @param $Pokedex_ID
   * @param $Alt_ID
   * @param $Type
   */
  function GetPokedexData
  (
    $Pokedex_ID = null,
    $Alt_ID = 0,
    $Type = "Normal"
  )
  {
    global $PDO;

    try
    {
      $Get_Pokedex_Data = $PDO->prepare("
        SELECT *
        FROM `pokedex`
        WHERE `Pokedex_ID` = ? AND `Alt_ID` = ?
        LIMIT 1
      ");
      $Get_Pokedex_Data->execute([
        $Pokedex_ID,
        $Alt_ID
      ]);
      $Get_Pokedex_Data->setFetchMode(PDO::FETCH_ASSOC);
      $Pokedex_Data = $Get_Pokedex_Data->fetch();
    }
    catch ( PDOException $e )
    {
      HandleError($e);
    }

    if ( !$Pokedex_Data )
      return false;

    $BaseStats = [
      $Pokedex_Data['HP'],
      $Pokedex_Data['Attack'],
      $Pokedex_Data['Defense'],
      $Pokedex_Data['SpAttack'],
      $Pokedex_Data['SpDefense'],
      $Pokedex_Data['Speed'],
    ];

    $Type_Display = '';
    if ( $Type != 'Normal' )
      $Type_Display = $Type;

    $Name = $Pokedex_Data['Pokemon'];

    if ( empty($Pokedex_Data['Forme']) )
      $Display_Name = $Type_Display . $Pokedex_Data['Pokemon'];
    else
      $Display_Name = $Type_Display . $Pokedex_Data['Pokemon'] . " " . $Pokedex_Data['Forme'];

    $Poke_Images = GetSprites($Pokedex_Data['Pokedex_ID'], $Pokedex_Data['Alt_ID'], $Type);

    return [
      "ID" => $Pokedex_Data['ID'],
      "Pokedex_ID" => $Pokedex_Data['Pokedex_ID'],
      "Alt_ID" => $Pokedex_Data['Alt_ID'],
      "Name" => $Name,
      "Forme" => $Pokedex_Data['Forme'],
      "Display_Name" => $Display_Name,
      "Type_Primary" => $Pokedex_Data['Type_Primary'],
      "Type_Secondary" => $Pokedex_Data['Type_Secondary'],
      "Base_Stats" => $BaseStats,
      'Exp_Yield' => $Pokedex_Data['Exp_Yield'],
      'Height' => $Pokedex_Data['Height'],
      'Weight' => $Pokedex_Data['Weight'],
      "Sprite" => $Poke_Images['Sprite'],
      "Icon" => $Poke_Images['Icon'],
    ];
  }

  /**
   * Get an exhaustive amount of data pertaining to the specified Pokemon.
   *
   * @param $Pokemon_ID
   */
  function GetPokemonData
  (
    $Pokemon_ID
  )
  {
    global $PDO;

    try
    {
      $Get_Pokemon_Data = $PDO->prepare("
        SELECT *
        FROM `pokemon`
        WHERE `ID` = ?
        LIMIT 1
      ");
      $Get_Pokemon_Data->execute([
        $Pokemon_ID
      ]);
      $Get_Pokemon_Data->setFetchMode(PDO::FETCH_ASSOC);
      $Pokemon_Data = $Get_Pokemon_Data->fetch();

      $Get_Pokemon_Evolution_Count = $PDO->prepare("
        SELECT COUNT(*)
        FROM `evolution_data`
        WHERE `poke_id` = ? AND `alt_id` = ?
        LIMIT 1
      ");
      $Get_Pokemon_Evolution_Count->execute([
        $Pokemon_Data['Pokedex_ID'],
        $Pokemon_Data['Alt_ID']
      ]);
      $Get_Pokemon_Evolution_Count->setFetchMode(PDO::FETCH_ASSOC);
      $Can_Evolve = $Get_Pokemon_Evolution_Count->fetch();

      $Get_Held_Item_Data = $PDO->prepare("
        SELECT `Item_ID`, `Item_Name`
        FROM `item_dex`
        WHERE `Item_ID` = ?
        LIMIT 1
      ");
      $Get_Held_Item_Data->execute([
        $Pokemon_Data['Item']
      ]);
      $Get_Held_Item_Data->setFetchMode(PDO::FETCH_ASSOC);
      $Item_Data = $Get_Held_Item_Data->fetch();
    }
    catch ( PDOException $e )
    {
      HandleError($e);
    }

    if ( !$Pokemon_Data )
      return false;

    $Pokedex_Data = GetPokedexData($Pokemon_Data['Pokedex_ID'], $Pokemon_Data['Alt_ID']);

    switch($Pokemon_Data['Gender'])
    {
      case 'Female':
        $Gender = 'Female';
        $Gender_Short = 'F';
        break;

      case 'Male':
        $Gender = 'Male';
        $Gender_Short = 'M';
        break;

      case 'Genderless':
        $Gender = 'Genderless';
        $Gender_Short = 'G';
        break;

      case '?':
      case '(?)':
        $Gender = '(?)';
        $Gender_Short = '(?)';
        break;

      default:
        $Gender = "(?)";
        $Gender_Short = "(?)";
        break;
    }

    switch($Pokemon_Data['Type'])
    {
      case 'Normal':
        $StatBonus = 0;
        break;

      case 'Shiny':
        $StatBonus = 5;
        break;

      case 'Sunset':
        $StatBonus = 10;
        break;

      default:
        $StatBonus = 0;
        break;
    }

    $EVs = explode(',', $Pokemon_Data['EVs']);
    $IVs = explode(',', $Pokemon_Data['IVs']);
    $Level = FetchLevel($Pokemon_Data['Experience'], 'Pokemon');
    $Experience = $Pokemon_Data['Experience'];

    $Stats = GetCurrentStats($Pokemon_ID);

    if ( $Pokemon_Data['Type'] !== 'Normal' )
      $Display_Name = $Pokemon_Data['Type'] . $Pokemon_Data['Name'];
    else
      $Display_Name = $Pokemon_Data['Name'];

    if ( $Pokemon_Data['Forme'] )
      $Display_Name .= " {$Pokemon_Data['Forme']}";

    $Poke_Images = GetSprites($Pokemon_Data['Pokedex_ID'], $Pokemon_Data['Alt_ID'], $Pokemon_Data['Type']);

    return [
      'ID' => $Pokemon_Data['ID'],
      'Pokedex_ID' => $Pokemon_Data['Pokedex_ID'],
      'Alt_ID' => $Pokemon_Data['Alt_ID'],
      'Nickname' => $Pokemon_Data['Nickname'],
      'Display_Name' => $Display_Name,
      'Name' => $Pokemon_Data['Name'],
      'Type' => $Pokemon_Data['Type'],
      'Location' => $Pokemon_Data['Location'],
      'Slot' => $Pokemon_Data['Slot'],
      'Item' => (!empty($Item_Data) ? $Item_Data['Item_Name'] : null),
      'Item_ID' => (!empty($Item_Data) ? $Item_Data['Item_ID'] : null),
      'Item_Icon' => (!empty($Item_Data) ? DOMAIN_SPRITES . '/Items/' . $Item_Data['Item_Name'] . '.png' : null),
      'Gender' => $Gender,
      'Gender_Short' => $Gender_Short,
      'Gender_Icon' => DOMAIN_SPRITES . '/Assets/' . $Gender . '.svg',
      'Level' => number_format($Level),
      'Level_Raw' => $Level,
      'Experience' => number_format($Experience),
      'Experience_Raw' => $Experience,
      'Height' => ($Pokedex_Data['Height'] / 10),
      'Weight' => ($Pokedex_Data['Weight'] / 10),
      'Type_Primary' => $Pokedex_Data['Type_Primary'],
      'Type_Secondary' => $Pokedex_Data['Type_Secondary'],
      'Ability' => $Pokemon_Data['Ability'],
      'Nature' => $Pokemon_Data['Nature'],
      'Stats' => $Stats,
      'IVs' => $IVs,
      'EVs' => $EVs,
      'Move_1' => $Pokemon_Data['Move_1'],
      'Move_2' => $Pokemon_Data['Move_2'],
      'Move_3' => $Pokemon_Data['Move_3'],
      'Move_4' => $Pokemon_Data['Move_4'],
      'Frozen' => $Pokemon_Data['Frozen'],
      'Happiness' => $Pokemon_Data['Happiness'],
      'Exp_Yield' => $Pokedex_Data['Exp_Yield'],
      'Can_Evolve' => ($Can_Evolve === 0 ? false : true),
      'Owner_Current' => $Pokemon_Data['Owner_Current'],
      'Owner_Original' => $Pokemon_Data['Owner_Original'],
      'Trade_Interest' => $Pokemon_Data['Trade_Interest'],
      'Challenge_Status' => $Pokemon_Data['Challenge_Status'],
      'Biography' => $Pokemon_Data['Biography'],
      'Creation_Date' => date('M j, Y (g:i A)', $Pokemon_Data['Creation_Date']),
      'Creation_Location' => $Pokemon_Data['Creation_Location'],
      'Sprite' => $Poke_Images['Sprite'],
      'Icon' => $Poke_Images['Icon'],
    ];
  }

  /**
   * Get sprite URLs for the specified Pokemon's icon and sprite.
   *
   * @param $Pokedex_ID
   * @param $Alt_ID
   * @param $Type
   */
  function GetSprites
  (
    $Pokedex_ID,
    $Alt_ID = 0,
    $Type = 'Normal'
  )
  {
    global $PDO;
    global $Dir_Root;

    try
    {
      $Get_Pokedex_Data = $PDO->prepare("
        SELECT `Pokedex_ID`, `Alt_ID`, `Forme`
        FROM `pokedex`
        WHERE `Pokedex_ID` = ? AND `Alt_ID` = ?
        LIMIT 1
      ");
      $Get_Pokedex_Data->execute([
        $Pokedex_ID,
        $Alt_ID
      ]);
      $Get_Pokedex_Data->setFetchMode(PDO::FETCH_ASSOC);
      $Pokedex_Data = $Get_Pokedex_Data->fetch();
    }
    catch ( PDOException $e )
    {
      HandleError($e);
    }

    if ( !$Pokedex_Data )
      return false;

    $Pokedex_ID = str_pad($Pokedex_Data['Pokedex_ID'], 3, "0", STR_PAD_LEFT);
    $Pokemon_Forme = isset($Pokemon_Forme) ? strtolower(preg_replace('/(^\s*\()|(\)\s*$)/', '', $Pokedex_Data['Forme'])) : null;

    switch($Pokemon_Forme)
    {
      case 'mega x':
        $Pokemon_Forme = '-x-mega';
        break;
      case 'mega y':
        $Pokemon_Forme = '-y-mega';
        break;
      case 'gigantamax':
        $Pokemon_Forme = '-gmax';
        break;
      case 'dynamax':
        $Pokemon_Forme = '-dmax';
        break;
      case 'female':
        $Pokemon_Forme = '-f';
        break;
      case 'male':
        $Pokemon_Forme = '-m';
        break;
      case NULL:
        break;
      default:
        $Pokemon_Forme = "-{$Pokemon_Forme}";
        break;
    }

    $Sprite = DOMAIN_SPRITES . "/Pokemon/Sprites/{$Type}/{$Pokedex_ID}{$Pokemon_Forme}.png";
    $Relative_Sprite = str_replace(DOMAIN_SPRITES, $Dir_Root . '/images', $Sprite);
    if ( !file_exists($Relative_Sprite) )
    {
      $Sprite = DOMAIN_SPRITES . "/Pokemon/Sprites/Normal/{$Pokedex_ID}{$Pokemon_Forme}.png";
    }

    $Icon = DOMAIN_SPRITES . "/Pokemon/Icons/{$Type}/{$Pokedex_ID}{$Pokemon_Forme}.png";
    $Relative_Icon = str_replace(DOMAIN_SPRITES, $Dir_Root . '/images', $Icon);
    if ( !file_exists($Relative_Icon) )
    {
      $Icon = DOMAIN_SPRITES . "/Pokemon/Icons/Normal/{$Pokedex_ID}{$Pokemon_Forme}.png";
    }

    return [
      'Icon' => $Icon,
      'Sprite' => $Sprite,
    ];
  }

  /**
   * Generate an ability for the specified Pokemon.
   *
   * @param $Pokedex_ID
   * @param $Alt_ID
   */
  function GenerateAbility
  (
    $Pokedex_ID,
    $Alt_ID
  )
  {
    $Abilities = GetAbilities($Pokedex_ID, $Alt_ID);

    if ( !$Abilities )
      return false;

    if ( $Abilities['Hidden_Ability'] && mt_rand(1, 50) == 1 )
      return $Abilities['Hidden_Ability'];

    if ( empty($Abilities['Ability_2']) )
      return $Abilities['Ability_1'];

    if ( mt_rand(1, 2) == 1 )
      return $Abilities['Ability_1'];

    return $Abilities['Ability_2'];
  }

  /**
   * Generate a random gender for the specified Pokemon species.
   *
   * @param $Pokedex_ID
   * @param $Alt_ID
   */
  function GenerateGender
  (
    $Pokedex_ID,
    $Alt_ID = 0
  )
  {
    global $PDO;

    try
    {
      $FetchPokedex = $PDO->prepare("
        SELECT `Female`, `Male`, `Genderless`
        FROM `pokedex`
        WHERE `Pokedex_ID` = ? AND `Alt_ID` = ?
        LIMIT 1
      ");
      $FetchPokedex->execute([
        $Pokedex_ID,
        $Alt_ID
      ]);
      $FetchPokedex->setFetchMode(PDO::FETCH_ASSOC);
      $Pokemon = $FetchPokedex->fetch();
    }
    catch ( PDOException $e )
    {
      HandleError($e);
    }

    if ( !$Pokemon )
      return false;

    $Weighter = new Weighter();
    foreach ( ['Female', 'Male', 'Genderless'] as $Gender )
    {
      $Weighter->AddObject($Gender, $Pokemon[$Gender]);
    }
    $Gender = $Weighter->GetObject();

    return $Gender;
  }

  /**
   * Generate a random nature.
   */
  function GenerateNature()
  {
    $Nature_Keys = array_keys(Natures());
    $Nature = $Nature_Keys[mt_rand(0, count($Nature_Keys) - 1)];

    return $Nature;
  }

  /**
   * Move the specified Pokemon to the specified slot.
   * Defaults to the user's box.
   *
   * @param $Pokemon_ID
   * @param $Slot
   */
  function MovePokemon
  (
    $Pokemon_ID,
    $Slot = 7
  )
  {
    global $PDO, $User_Data;

    try
    {
      $Get_Selected_Pokemon = $PDO->prepare("
        SELECT `ID`, `Owner_Current`
        FROM `pokemon`
        WHERE `ID` = ?
        LIMIT 1
      ");
      $Get_Selected_Pokemon->execute([
        $Pokemon_ID
      ]);
      $Get_Selected_Pokemon->setFetchMode(PDO::FETCH_ASSOC);
      $Selected_Pokemon = $Get_Selected_Pokemon->fetch();
    }
    catch ( PDOException $e )
    {
      HandleError($e);
    }

    if ( !$Selected_Pokemon )
    {
      return [
        'Message' => 'This Pok&eacute;mon does not exist.',
        'Type' => 'error',
      ];
    }

    if ( $Selected_Pokemon['Owner_Current'] != $User_Data['ID'] )
    {
      return [
        'Message' => 'This Pok&eacute;mon does not belong to you.',
        'Type' => 'error',
      ];
    }

    if ( !in_array($Slot, [1, 2, 3, 4, 5, 6, 7]) )
      $Slot = 7;

    try
    {
      $Get_User_Roster = $PDO->prepare("
        SELECT DISTINCT(`Slot`)
        FROM `pokemon`
        WHERE `Owner_Current` = ? AND `Location` = 'Roster' AND `Slot` <= 6
        ORDER BY `Slot` ASC
        LIMIT 6
      ");
      $Get_User_Roster->execute([
        $User_Data['ID']
      ]);
      $Get_User_Roster->setFetchMode(PDO::FETCH_ASSOC);
      $User_Roster = $Get_User_Roster->fetchAll();
    }
    catch ( PDOException $e )
    {
      HandleError($e);
    }

    $Poke_Data = GetPokemonData($Pokemon_ID);

    if ( $Slot == 7 )
    {
      try
      {
        $PDO->beginTransaction();

        $Update_Roster = $PDO->prepare("
          UPDATE `pokemon`
          SET `Location` = 'Box', `Slot` = ?
          WHERE `ID` = ?
          LIMIT 1
        ");
        $Update_Roster->execute([
          $Slot,
          $Poke_Data['ID']
        ]);

        $PDO->commit();
      }
      catch ( PDOException $e )
      {
        $PDO->rollBack();

        HandleError($e);
      }

      $Move_Message = "<b>{$Poke_Data['Display_Name']}</b> has been sent to your box.";
    }
    else
    {
      if ( isset($Roster[$Slot - 1]) )
      {
        try
        {
          $PDO->beginTransaction();

          $Update_Roster = $PDO->prepare("
            UPDATE `pokemon`
            SET `Location` = 'Roster', `Slot` = ?
            WHERE `ID` = ?
            LIMIT 1
          ");
          $Update_Roster->execute([
            $Slot,
            $Poke_Data['ID']
          ]);

          $Update_Roster = $PDO->prepare("
            UPDATE `pokemon`
            SET `Location` = ?, `Slot` = ?
            WHERE `ID` = ?
            LIMIT 1
          ");
          $Update_Roster->execute([
            $Poke_Data['Location'],
            $Poke_Data['Slot'],
            $Roster[$Slot - 1]['ID']
          ]);

          $PDO->commit();
        }
        catch ( PDOException $e )
        {
          $PDO->rollBack();

          HandleError($e);
        }

        $Move_Message = "<b>{$Poke_Data['Display_Name']}</b> has been added to your roster.";
      }
      else
      {
        try
        {
          $PDO->beginTransaction();

          $Update_Roster = $PDO->prepare("
            UPDATE `pokemon`
            SET `Location` = 'Roster', `Slot` = ?
            WHERE `ID` = ?
            LIMIT 1
          ");
          $Update_Roster->execute([
            count($User_Roster) + 1,
            $Poke_Data['ID']
          ]);

          $PDO->commit();
        }
        catch (PDOException $e)
        {
          $PDO->rollBack();

          HandleError($e);
        }

        $Move_Message = "<b>{$Poke_Data['Display_Name']}</b> has been added to your roster.";
      }
    }

    return [
      'Message' => $Move_Message,
      'Type' => 'success',
    ];
  }

  /**
   * Returns an array of all natures and the stats that they modify.
   */
  function Natures()
  {
    return [
      'Adamant' => [
        'Plus' => 'Attack',
        'Minus' => 'SpAttack'
      ],
      'Brave' => [
        'Plus' => 'Attack',
        'Minus' => 'Speed'
      ],
      'Lonely' => [
        'Plus' => 'Attack',
        'Minus' => 'Defense'
      ],
      'Naughty' => [
        'Plus' => 'Attack',
        'Minus' => 'SpDefense'
      ],

      'Bold' => [
        'Plus' => 'Defense',
        'Minus' => 'Attack'
      ],
      'Impish' => [
        'Plus' => 'Defense',
        'Minus' => 'SpAttack'
      ],
      'Lax' => [
        'Plus' => 'Defense',
        'Minus' => 'SpDefense'
      ],
      'Relaxed' => [
        'Plus' => 'Defense',
        'Minus' => 'Speed'
      ],

      'Modest' => [
        'Plus' => 'SpAttack',
        'Minus' => 'Attack'
      ],
      'Mild' => [
        'Plus' => 'SpAttack',
        'Minus' => 'Defense'
      ],
      'Quiet' => [
        'Plus' => 'SpAttack',
        'Minus' => 'SpDefense'
      ],
      'Rash' => [
        'Plus' => 'SpAttack',
        'Minus' => 'Speed'
      ],

      'Calm' => [
        'Plus' => 'SpDefense',
        'Minus' => 'Attack'
      ],
      'Careful' => [
        'Plus' => 'SpDefense',
        'Minus' => 'SpAttack'
      ],
      'Gentle' => [
        'Plus' => 'SpDefense',
        'Minus' => 'Defense'
      ],
      'Sassy' => [
        'Plus' => 'SpDefense',
        'Minus' => 'Speed'
      ],

      'Hasty' => [
        'Plus' => 'Speed',
        'Minus' => 'Defense'
      ],
      'Jolly' => [
        'Plus' => 'Speed',
        'Minus' => 'SpAttack'
      ],
      'Naive' => [
        'Plus' => 'Speed',
        'Minus' => 'SpDefense'
      ],
      'Timid' => [
        'Plus' => 'Speed',
        'Minus' => 'Attack'
      ],

      'Bashful' => [
        'Plus' => null,
        'Minus' => null
      ],
      'Docile' => [
        'Plus' => null,
        'Minus' => null
      ],
      'Hardy' => [
        'Plus' => null,
        'Minus' => null
      ],
      'Quirky' => [
        'Plus' => null,
        'Minus' => null
      ],
      'Serious' => [
        'Plus' => null,
        'Minus' => null
      ],
    ];
  }

  /**
   * Handles the release a Pokemon.
   * This deletes a Pokemon from the `pokemon` database table and creates a copy of it in the `released` database table.
   *
   * @param $Pokemon_ID
   * @param $Staff_Panel_Deletion
   */
  function ReleasePokemon
  (
    $Pokemon_ID,
    $Staff_Panel_Deletion = false
  )
  {
    global $PDO, $User_Data;

    $Pokemon = GetPokemonData($Pokemon_ID);

    if ( !$Pokemon )
    {
      return [
        'Type' => 'error',
        'Message' => 'This Pok&eacute;mon does not exist.',
      ];
    }

    if
    (
      $Pokemon['Owner_Current'] != $User_Data['ID'] &&
      !$Staff_Panel_Deletion
    )
    {
      return [
        'Type' => 'error',
        'Message' => 'You may not release a Pok&eacute;mon that does not belong to you.',
      ];
    }

    try
    {
      $PDO->beginTransaction();

      $Release_Pokemon = $PDO->prepare("
        INSERT INTO `released` (
          ID, Pokedex_ID, Alt_ID, Name, Forme, Type, Location, Slot, Item, Owner_Current, Owner_Original, Gender, Experience, IVs, EVs, Nature, Happiness, Trade_Interest, Challenge_Status, Frozen, Ability, Move_1, Move_2, Move_3, Move_4, Nickname, Biography, Creation_Date, Creation_Location
        )
        SELECT ID, Pokedex_ID, Alt_ID, Name, Forme, Type, Location, Slot, Item, Owner_Current, Owner_Original, Gender, Experience, IVs, EVs, Nature, Happiness, Trade_Interest, Challenge_Status, Frozen, Ability, Move_1, Move_2, Move_3, Move_4, Nickname, Biography, Creation_Date, Creation_Location
        FROM `pokemon`
        WHERE ID = ?;
      ");
      $Release_Pokemon->execute([
        $Pokemon_ID
      ]);

      $Delete_Pokemon = $PDO->prepare("
        DELETE FROM `pokemon`
        WHERE ID = ?;
      ");
      $Delete_Pokemon->execute([
        $Pokemon_ID
      ]);

      $PDO->commit();
    }
    catch ( PDOException $e )
    {
      $PDO->rollBack();

      HandleError($e);
    }

    return [
      'Type' => 'success',
      'Message' => "You have successfully released {$Pokemon['Display_Name']}.",
    ];
  }
