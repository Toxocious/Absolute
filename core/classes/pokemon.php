<?php
	Class Pokemon
	{
		public $PDO;

		/**
		 * Construct and initialize the class.
		 */
		public function __construct()
		{
			global $PDO;
			$this->PDO = $PDO;
		}

		/**
		 * Fetch the complete data of a specific Pokemon via it's `pokemon` DB ID.
		 */
		public function FetchPokemonData($DatabaseID)
		{
			global $PDO;

			try
			{
				$FetchPokemon = $PDO->prepare("SELECT * FROM `pokemon` WHERE `ID` = ? LIMIT 1");
				$FetchPokemon->execute([$DatabaseID]);
				$FetchPokemon->setFetchMode(PDO::FETCH_ASSOC);
				$Pokemon = $FetchPokemon->fetch();

				$FetchPokedex = $PDO->prepare("SELECT `Exp_Yield`, `Type_Primary`, `Type_Secondary`, `HP`, `Attack`, `Defense`, `SpAttack`, `SpDefense`, `Speed`, `Height`, `Weight` FROM `pokedex` WHERE `Pokedex_ID` = ? AND `Alt_ID` = ? LIMIT 1");
				$FetchPokedex->execute([$Pokemon['Pokedex_ID'], $Pokemon['Alt_ID']]);
				$FetchPokedex->setFetchMode(PDO::FETCH_ASSOC);
				$Pokedex = $FetchPokedex->fetch();

        $Fetch_Evos = $PDO->prepare("SELECT COUNT(*) FROM `evolution_data` WHERE `poke_id` = ? AND `alt_id` = ? LIMIT 1");
        $Fetch_Evos->execute([ $Pokemon['Pokedex_ID'], $Pokemon['Alt_ID'] ]);
        $Fetch_Evos->setFetchMode(PDO::FETCH_ASSOC);
        $Can_Evolve = $Fetch_Evos->fetch();

				$FetchItem = $PDO->prepare("SELECT `Item_ID`, `Item_Name` FROM `item_dex` WHERE `Item_ID` = ? LIMIT 1");
				$FetchItem->execute([$Pokemon['Item']]);
				$FetchItem->setFetchMode(PDO::FETCH_ASSOC);
				$Item = $FetchItem->fetch();

				$Fetch_Owner = $PDO->prepare("SELECT `Username` FROM `users` WHERE `id` = ? LIMIT 1");
				$Fetch_Owner->execute([$Pokemon['Owner_Current']]);
				$Fetch_Owner->setFetchMode(PDO::FETCH_ASSOC);
				$Current_Owner = $Fetch_Owner->fetch();

				$Fetch_Original = $PDO->prepare("SELECT `Username` FROM `users` WHERE `id` = ? LIMIT 1");
				$Fetch_Original->execute([$Pokemon['Owner_Original']]);
				$Fetch_Original->setFetchMode(PDO::FETCH_ASSOC);
				$Original_Owner = $Fetch_Original->fetch();
			}
			catch ( PDOException $e )
			{
				HandleError($e);
			}

			if ( !isset($Pokemon) || !$Pokemon )
			{
				return false;
			}

			switch($Pokemon['Gender'])
			{
				case 'Female':
					$Gender = 'Female'; $GenderShort = 'F';
					break;
				case 'Male':
					$Gender = 'Male'; $GenderShort = 'M';
					break;
				case 'Genderless':
					$Gender = 'Genderless'; $GenderShort = 'G';
					break;
				case '?':
				case '(?)':
					$Gender = '(?)'; $GenderShort = '(?)';
					break;
				default:
					$Gender = "(?)"; $GenderShort = "(?)";
					break;
			}

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

			$EVs = explode(',', $Pokemon['EVs']);
			$IVs = explode(',', $Pokemon['IVs']);
			$Level = FetchLevel($Pokemon['Experience'], 'Pokemon');
			$Experience = $Pokemon['Experience'];

			$Stats = [
				$this->CalcStat('HP', floor($Pokedex['HP'] + $StatBonus), $Level, $IVs[0], $EVs[0], $Pokemon['Nature']),
				$this->CalcStat('Attack', floor($Pokedex['Attack'] + $StatBonus), $Level, $IVs[1], $EVs[1], $Pokemon['Nature']),
				$this->CalcStat('Defense', floor($Pokedex['Defense'] + $StatBonus), $Level, $IVs[2], $EVs[2], $Pokemon['Nature']),
				$this->CalcStat('SpAttack', floor($Pokedex['SpAttack'] + $StatBonus), $Level, $IVs[3], $EVs[3], $Pokemon['Nature']),
				$this->CalcStat('SpDefense', floor($Pokedex['SpDefense'] + $StatBonus), $Level, $IVs[4], $EVs[4], $Pokemon['Nature']),
				$this->CalcStat('Speed', floor($Pokedex['Speed'] + $StatBonus), $Level, $IVs[5], $EVs[5], $Pokemon['Nature']),
			];

			if ( $Pokemon['Type'] !== 'Normal' )
				$Display_Name = $Pokemon['Type'] . $Pokemon['Name'];
			else
				$Display_Name = $Pokemon['Name'];

			if ( $Pokemon['Forme'] )
				$Display_Name .= " {$Pokemon['Forme']}";

			$Poke_Images = $this->FetchImages($Pokemon['Pokedex_ID'], $Pokemon['Alt_ID'], $Pokemon['Type']);

			return [
				'ID' => $Pokemon['ID'],
				'Pokedex_ID' => $Pokemon['Pokedex_ID'],
				'Alt_ID' => $Pokemon['Alt_ID'],
				'Nickname' => $Pokemon['Nickname'],
				'Display_Name' => $Display_Name,
				'Name' => $Pokemon['Name'],
				'Type' => $Pokemon['Type'],
				'Location' => $Pokemon['Location'],
				'Slot' => $Pokemon['Slot'],
				'Item' => (!empty($Item) ? $Item['Item_Name'] : null),
				'Item_ID' => (!empty($Item) ? $Item['Item_ID'] : null),
				'Item_Icon' => (!empty($Item) ? DOMAIN_SPRITES . '/Items/' . $Item['Item_Name'] . '.png' : null),
				'Gender' => $Gender,
				'GenderShort' => $GenderShort,
				'Gender_Icon' => DOMAIN_SPRITES . '/Assets/' . $Gender . '.svg',
				'Level' => number_format($Level),
				'Level_Raw' => $Level,
				'Experience' => number_format($Experience),
				'Experience_Raw' => $Experience,
				'Height' => ($Pokedex['Height'] / 10),
				'Weight' => ($Pokedex['Weight'] / 10),
				'Type_Primary' => $Pokedex['Type_Primary'],
				'Type_Secondary' => $Pokedex['Type_Secondary'],
				'Ability' => $Pokemon['Ability'],
				'Nature' => $Pokemon['Nature'],
      	'Stats' => $Stats,
				'IVs' => $IVs,
				'EVs' => $EVs,
				'Move_1' => $Pokemon['Move_1'],
				'Move_2' => $Pokemon['Move_2'],
				'Move_3' => $Pokemon['Move_3'],
				'Move_4' => $Pokemon['Move_4'],
        'Frozen' => $Pokemon['Frozen'],
				'Happiness' => $Pokemon['Happiness'],
        'Exp_Yield' => $Pokedex['Exp_Yield'],
        'Can_Evolve' => ($Can_Evolve === 0 ? false : true),
				'Owner_Current' => $Pokemon['Owner_Current'],
				'Owner_Current_Username' => $Current_Owner['Username'],
				'Owner_Original' => $Pokemon['Owner_Original'],
				'Owner_Original_Username' => $Original_Owner['Username'],
				'Trade_Interest' => $Pokemon['Trade_Interest'],
				'Challenge_Status' => $Pokemon['Challenge_Status'],
				'Biography' => $Pokemon['Biography'],
				'Creation_Date' => date('M j, Y (g:i A)', $Pokemon['Creation_Date']),
				'Creation_Location' => $Pokemon['Creation_Location'],
				'Sprite' => $Poke_Images['Sprite'],
				'Icon' => $Poke_Images['Icon'],
			];
		}

		/**
		 * Fetch any Pokemon's Pokedex data, given their Pokedex ID.
		 */
		public function FetchPokedexData($Pokedex_ID = null, $Alt_ID = 0, $Type = "Normal", $DB_ID = null)
		{
			global $PDO;

			try
			{
				if ( !$Pokedex_ID && $Alt_ID == 0 && $DB_ID )
				{
					$FetchPokedex = $PDO->prepare("SELECT * FROM `pokedex` WHERE `id` = ? LIMIT 1");
					$FetchPokedex->execute([ $DB_ID ]);
				}
				else
				{
					$FetchPokedex = $PDO->prepare("SELECT * FROM `pokedex` WHERE `Pokedex_ID` = ? AND `Alt_ID` = ? LIMIT 1");
					$FetchPokedex->execute([ $Pokedex_ID, $Alt_ID ]);
				}
				$FetchPokedex->setFetchMode(PDO::FETCH_ASSOC);
				$Pokedex = $FetchPokedex->fetch();

				if ( !isset($Pokedex) )
				{
					return "Error";
				}
			}
			catch ( PDOException $e )
			{
				HandleError($e);
			}

			$BaseStats = [
				round($Pokedex['HP']),
				round($Pokedex['Attack']),
				round($Pokedex['Defense']),
				round($Pokedex['SpAttack']),
				round($Pokedex['SpDefense']),
				round($Pokedex['Speed']),
			];

			$Type_Display = '';
			if ( $Type != 'Normal' )
			{
				$Type_Display = $Type;
			}

			$Name = $Pokedex['Pokemon'];

			if ( $Pokedex['Forme'] !== null )
				$Display_Name = $Type_Display . $Pokedex['Pokemon'] . " " . $Pokedex['Forme'];
			else
				$Display_Name = $Type_Display . $Pokedex['Pokemon'];

			$Poke_Images = $this->FetchImages($Pokedex['Pokedex_ID'], $Pokedex['Alt_ID'], $Type);

			return [
				"ID" => $Pokedex['ID'],
				"Pokedex_ID" => $Pokedex['Pokedex_ID'],
				"Alt_ID" => $Pokedex['Alt_ID'],
				"Name" => $Name,
				"Forme" => $Pokedex['Forme'],
				"Display_Name" => $Display_Name,
				"Type_Primary" => $Pokedex['Type_Primary'],
				"Type_Secondary" => $Pokedex['Type_Secondary'],
				"Base_Stats" => $BaseStats,
        'Exp_Yield' => $Pokedex['Exp_Yield'],
        'Height' => $Pokedex['Height'],
        'Weight' => $Pokedex['Weight'],
				"Sprite" => $Poke_Images['Sprite'],
				"Icon" => $Poke_Images['Icon'],
			];
		}

    /**
     * Fetch the current stats of a Pokemon.
     * @param int $Pokemon_ID
     * @param int $Pokedex_ID
     * @param int $Pokedex_Alt_ID
     */
    public function FetchCurrentStats
    (
      int $Pokemon_ID,
      int $Pokedex_ID,
      int $Pokedex_Alt_ID
    )
    {
      global $PDO;

      try
      {
        $FetchPokemon = $PDO->prepare("SELECT `Nature`, `Type`, `EVs`, `IVs`, `Experience` FROM `pokemon` WHERE `ID` = ? LIMIT 1");
        $FetchPokemon->execute([ $Pokemon_ID ]);
        $FetchPokemon->setFetchMode(PDO::FETCH_ASSOC);
        $Pokemon = $FetchPokemon->fetch();

        $FetchPokedex = $PDO->prepare("SELECT `HP`, `Attack`, `Defense`, `SpAttack`, `SpDefense`, `Speed` FROM `pokedex` WHERE `Pokedex_ID` = ? AND `Alt_ID` = ? LIMIT 1");
        $FetchPokedex->execute([ $Pokedex_ID, $Pokedex_Alt_ID ]);
        $FetchPokedex->setFetchMode(PDO::FETCH_ASSOC);
        $Pokedex = $FetchPokedex->fetch();
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

      $Level = FetchLevel($Pokemon['Experience'], 'Pokemon');
      $EVs = explode(',', $Pokemon['EVs']);
      $IVs = explode(',', $Pokemon['IVs']);

      $Stats = [
        $this->CalcStat('HP', floor($Pokedex['HP'] + $StatBonus), $Level, $IVs[0], $EVs[0], $Pokemon['Nature']),
        $this->CalcStat('Attack', floor($Pokedex['Attack'] + $StatBonus), $Level, $IVs[1], $EVs[1], $Pokemon['Nature']),
        $this->CalcStat('Defense', floor($Pokedex['Defense'] + $StatBonus), $Level, $IVs[2], $EVs[2], $Pokemon['Nature']),
        $this->CalcStat('SpAttack', floor($Pokedex['SpAttack'] + $StatBonus), $Level, $IVs[3], $EVs[3], $Pokemon['Nature']),
        $this->CalcStat('SpDefense', floor($Pokedex['SpDefense'] + $StatBonus), $Level, $IVs[4], $EVs[4], $Pokemon['Nature']),
        $this->CalcStat('Speed', floor($Pokedex['Speed'] + $StatBonus), $Level, $IVs[5], $EVs[5], $Pokemon['Nature']),
      ];

      return $Stats;
    }

		/**
		 * Move a Pokemon from your box, into your roster, or vice-versa.
		 */
		public function MovePokemon($Pokemon_ID, $Slot = 7)
		{
			global $PDO;
			global $User_Data;

			if ( !isset($Pokemon_ID) )
			{
				return [
					'Message' => 'The ID of the Pok&eacute;mon that you\'re trying to move isn\'t set.',
					'Type' => 'error',
				];
			}

			$Poke_Data = $this->FetchPokemonData($Pokemon_ID);

			if ( !$Poke_Data )
			{
				return [
					'Message' => 'This Pok&eacute;mon doesn\'t exist.',
					'Type' => 'error',
				];
			}

			if ( !in_array($Slot, [1, 2, 3, 4, 5, 6, 7]) )
			{
				return [
					'Message' => 'You have chosen an invalid slot to move your Pok&eacute;mon to.',
					'Type' => 'error',
				];
			}

			if ( $Poke_Data['Owner_Current'] !== $User_Data['ID'] )
			{
				return [
					'Message' => 'The Pok&eacute;mon that you are trying to move doesn\'t belong to you.',
					'Type' => 'error',
				];
			}

			try
			{
				$Roster_Fetch = $PDO->prepare("SELECT * FROM `pokemon` WHERE `Owner_Current` = ? AND `Location` = 'Roster' AND `Slot` <= 6 ORDER BY `Slot` ASC LIMIT 6");
				$Roster_Fetch->execute([ $User_Data['ID'] ]);
				$Roster_Fetch->setFetchMode(PDO::FETCH_ASSOC);
				$Roster = $Roster_Fetch->fetchAll();
			}
			catch ( PDOException $e )
			{
				HandleError($e);
			}

			if ( $Slot == 7 )
			{
				$Location = 'Box';

				try
				{
					$Box_Move = $PDO->prepare("UPDATE `pokemon` SET `Location` = 'Box', `Slot` = 7 WHERE `ID` = ? LIMIT 1");
					$Box_Move->execute([ $Poke_Data['ID'] ]);
				}
				catch ( PDOException $e )
				{
					HandleError($e);
				}

				return [
					'Message' => "You have moved your <b>{$Poke_Data['Display_Name']}</b> to your box.",
					'Type' => 'success',
				];
			}
			else
			{
				$Location = 'Roster';

				if ( isset($Roster[$Slot - 1]) )
				{
					try
					{
						$Roster_Move = $PDO->prepare("UPDATE `pokemon` SET `Location` = 'Roster', `Slot` = ? WHERE `ID` = ? LIMIT 1");
      			$Roster_Move->execute([ $Slot, $Poke_Data['ID'] ]);

        		$Roster_Remove = $PDO->prepare("UPDATE `pokemon` SET `Location` = ?, `Slot` = ? WHERE `ID` = ? LIMIT 1");
        		$Roster_Remove->execute([ $Poke_Data['Location'], $Poke_Data['Slot'], $Roster[$Slot - 1]['ID'] ]);
					}
					catch ( PDOException $e )
					{
						HandleError($e);
					}
				}
				else
				{
					try
					{
						$Roster_Move = $PDO->prepare("UPDATE `pokemon` SET `Location` = 'Roster', `Slot` = ? WHERE `ID` = ? LIMIT 1");
						$Roster_Move->execute([ count($Roster) + 1, $Poke_Data['ID'] ]);
					}
					catch (PDOException $e)
					{
						HandleError($e);
					}
				}

				return [
					'Message' => "You have moved your <b>{$Poke_Data['Display_Name']}</b> to your roster.",
					'Type' => 'success',
				];
			}
		}

		/**
		 * Release a Pokemon via it's `pokemon` DB ID.
		 * Store all released Pokemon in the `released` database table.
		 */
		public function ReleasePokemon($Pokemon_ID, $User_ID, $Staff_Panel_Deletion = false)
		{
			global $PDO, $User_Data;

			if ( !$Pokemon_ID || !$User_ID )
			{
				return [
					'Type' => 'error',
					'Message' => 'The Pok&eacute;mon/User ID wasn\'t set, please try again.',
				];
			}

			$Pokemon = $this->FetchPokemonData($Pokemon_ID);

			if ( $Pokemon['Owner_Current'] !== $User_Data['ID'] && !$Staff_Panel_Deletion )
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
				$Release_Pokemon->execute([ $Pokemon_ID ]);

        $Delete_Pokemon = $PDO->prepare("
					DELETE FROM `pokemon`
					WHERE ID = ?;
        ");
        $Delete_Pokemon->execute([ $Pokemon_ID ]);

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

		/**
		 * Spawn a Pokemon into the game.
		 */
		public function CreatePokemon
		(
			$Pokedex_ID,
			$Alt_ID,
			$Level = 5,
			$Type = "Normal",
			$Gender = null,
			$Obtained_At = "Unknown",
			$Location,
			$Slot,
			$Owner,
			$Nature = null,
			$IVs = null,
			$EVs = null
		)
		{
			global $PDO;

			$Pokemon = $this->FetchPokedexData($Pokedex_ID, $Alt_ID, $Type);
			if ( !$Pokemon )
				return false;

			/**
			 * Check the variable inputs.
			 */
			if ( !is_numeric($Level) )
			{
				die(
					"Some expected inputs for the CreatePokemon() function weren't valid.<br />" .
					"Please try again."
				);
			}

			/**
			 * Verify that the Pokemon exists in the `pokedex` database table.
			 */
			if ( $Pokemon['ID'] == null )
			{
				die(
					"The Pokemon that was being created does not exist in the database."
				);
			}

			if ( $Type !== "Normal" )
				$Display_Name = $Type . $Pokemon['Name'];
			else
				$Display_Name = $Pokemon['Name'];

			if ( !$Gender )
				$Gender = $this->GenerateGender($Pokemon['ID']);

			$Ability = $this->GenerateAbility($Pokemon['Pokedex_ID'], $Pokemon['Alt_ID']);
			if ( !$Ability )
				$Ability = null;

			try
			{
				$Query_Party = $PDO->prepare("SELECT DISTINCT(`Slot`) FROM `pokemon` WHERE `Owner_Current` = ? AND (Slot = 1 OR Slot = 2 OR Slot = 3 OR Slot = 4 OR Slot = 5 OR Slot = 6) AND `Location` = 'Roster' LIMIT 6");
				$Query_Party->execute([ $Owner ]);
				$Query_Party->setFetchMode(PDO::FETCH_ASSOC);
			}
			catch ( PDOException $e )
			{
				HandleError($e);
			}

			if ( $Location != "Box" )
			{
				$Slots_Used = [0, 0, 0, 0, 0, 0, 0];
				while ( $Party = $Query_Party->fetch() )
				{
					$Slots_Used[$Party['Slot']] = 1;
				}

				if ( $Slots_Used[1] == 0 )
				{
					$Location = "Roster";
					$Slot = 1;
				}
				else if ( $Slots_Used[2] == 0 )
				{
					$Location = "Roster";
					$Slot = 2;
				}
				else if ( $Slots_Used[3] == 0 )
				{
					$Location = "Roster";
					$Slot = 3;
				}
				else if ( $Slots_Used[4] == 0 )
				{
					$Location = "Roster";
					$Slot = 4;
				}
				else if ( $Slots_Used[5] == 0 )
				{
					$Location = "Roster";
					$Slot = 5;
				}
				else if ( $Slots_Used[6] == 0 )
				{
					$Location = "Roster";
					$Slot = 6;
				}
				else
				{
					$Location = "Box";
					$Slot = 7;
				}
			}

			$Experience = FetchExperience($Level, 'Pokemon');

			if ( $IVs == null )
			{
				$IVs = mt_rand(0, 31) . "," . mt_rand(0, 31) . "," . mt_rand(0, 31) . "," . mt_rand(0, 31) . "," . mt_rand(0, 31) . "," . mt_rand(0, 31);
			}

			if ( $EVs == null )
			{
				$EVs = "0,0,0,0,0,0";
			}

			if ( $Nature == null )
			{
        $Nature = $this->GenerateNature();
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
          )
          VALUES
          (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $Pokemon_Create->execute([
          $Pokedex_ID, $Alt_ID, $Pokemon['Name'], $Pokemon['Forme'],
          $Type, $Experience, $Location, $Slot, $Owner, $Owner, $Gender,
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

			// Have to wait until the Pokemon has been created to fetch it's icon and sprite.
			$Poke_Images = $this->FetchImages($Pokedex_ID, $Alt_ID, $Type);

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
     * Generate a random nature.
     */
    public function GenerateNature()
    {
      $Nature_Keys = array_keys($this->Natures());
      $Nature = $Nature_Keys[mt_rand(0, count($Nature_Keys) - 1)];

      return $Nature;
    }

		/**
		 * Fetch a random gender given a Pokemon's gender ratio.
		 */
		public function GenerateGender($DB_ID, $Pokedex_ID = null, $Alt_ID = null)
		{
			global $PDO;

			try
			{
				if ( $DB_ID )
				{
					$FetchPokedex = $PDO->prepare("SELECT `Female`, `Male`, `Genderless` FROM `pokedex` WHERE `id` = ? LIMIT 1");
					$FetchPokedex->execute([ $DB_ID ]);
				}
				else
				{
					$FetchPokedex = $PDO->prepare("SELECT `Female`, `Male`, `Genderless` FROM `pokedex` WHERE `Pokedex_ID` = ? AND `Alt_ID` = ? LIMIT 1");
					$FetchPokedex->execute([ $Pokedex_ID, $Alt_ID ]);
				}

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
			foreach (['Female', 'Male', 'Genderless'] as $Key)
			{
				$Weighter->AddObject($Key, $Pokemon[$Key]);
			}
			$Gender = $Weighter->GetObject();

			return $Gender;
		}

		/**
		 * Generate an ability for the specified Pokemon.
		 * @param int $Pokedex_ID
		 * @param int $Alt_ID
		 */
		public function GenerateAbility
		(
			int $Pokedex_ID,
			int $Alt_ID
		)
		{
			$Abilities = $this->FetchAbilities($Pokedex_ID, $Alt_ID);
			if ( !$Abilities )
				return false;

			if ( $Abilities['Hidden_Ability'] && mt_rand(1, 50) == 1 )
				return $Abilities['Hidden_Ability'];
			else
			{
				if ( !$Abilities['Ability_2'] )
					return $Abilities['Ability_1'];
				else
				{
					if ( mt_rand(1, 2) == 1 )
						return $Abilities['Ability_1'];
					else
						return $Abilities['Ability_2'];
				}
			}
		}

		/**
		 * Function to render a dropdown menu that consists of Pokemon in the `pokedex` database table.
		 */
		public function RenderDropdown()
		{
			global $PDO;

			try
			{
				$Fetch_Pokedex = $PDO->prepare("SELECT `id`, `Name`, `Name_Alter`, `Pokedex_ID` FROM `pokedex` ORDER BY `Pokedex_ID` ASC, `Alt_ID` ASC");
				$Fetch_Pokedex->execute();
				$Fetch_Pokedex->setFetchMode(PDO::FETCH_ASSOC);
			}
			catch ( PDOException $e )
			{
				HandleError($e);
			}

			$List = "<option>-------</option>";
			while ( $Pokemon = $Fetch_Pokedex->fetch() )
			{
				if ( strlen( $Pokemon['Name_Alter'] ) != 1 )
				{
          $Pokemon['Name'] .= " " . $Pokemon['Name_Alter'];
        }

				$List .= "
					<option value='{$Pokemon['id']}'>
						{$Pokemon['Name']} - #" . str_pad($Pokemon['Pokedex_ID'], 3, "0", STR_PAD_LEFT) . "
					</option>
				";
			}

			return $List;
		}

		/**
		 * Fetch the available abilities of a Pokemon.
		 * @param int $Pokedex_ID
		 * @param int $Alt_ID
		 */
		public function FetchAbilities
		(
			int $Pokedex_ID,
			int $Alt_ID
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
				$Fetch_Abilities->execute([ $Pokedex_ID, $Alt_ID ]);
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
		 * Fetch the base stats of a Pokemon.
		 * @param int $Pokedex_ID
		 * @param int $Alt_ID
		 */
		public function FetchBaseStats
		(
			int $Pokedex_ID,
			int $Alt_ID
		)
		{
			global $PDO;

			if ( !$Pokedex_ID || !$Alt_ID )
				return false;

			try
			{
				$Fetch_Stats = $PDO->prepare("
					SELECT `HP`, `Attack`, `Defense`, `SpAttack`, `SpDefense`, `Speed`
					FROM `pokedex`
					WHERE `Pokedex_ID` = ? AND `Alt_ID` = ?
					LIMIT 1
				");
				$Fetch_Stats->execute([ $Pokedex_ID, $Alt_ID ]);
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
		 * Fetch the data of a given move via it's `moves` DB ID.
		 */
		public function FetchMoveData($Move_ID)
		{
			global $PDO;

			try
			{
				$Fetch_Move = $PDO->prepare("SELECT * FROM `moves` WHERE `id` = ?");
				$Fetch_Move->execute([$Move_ID]);
				$Fetch_Move->setFetchMode(PDO::FETCH_ASSOC);
				$Move = $Fetch_Move->fetch();
			}
			catch ( PDOException $e )
			{
				HandleError($e);
			}

			return [
				"ID" => $Move['ID'],
				"Name" => $Move['Name'],
				"Type" => $Move['Move_Type'],
				"Category" => $Move['Category'],
				"Power" => $Move['Power'],
				"Accuracy" => $Move['Accuracy'],
				"Priority" => $Move['Priority'],
				"PP" => $Move['PP'],
				"Effect_Short" => $Move['Effect_Short'],
			];
		}

		/**
		 * Fetch the item data of the item that a Pokemon is holding.
		 */
		public function FetchItemData($Item_ID)
		{
			global $PDO;

			try
			{
				$Fetch_Item = $PDO->prepare("SELECT * FROM `item_dex` WHERE `Item_ID` = ?");
				$Fetch_Item->execute([$Item_ID]);
				$Fetch_Item->setFetchMode(PDO::FETCH_ASSOC);
				$Item = $Fetch_Item->fetch();
			}
			catch ( PDOException $e )
			{
				HandleError($e);
			}

			return [
				"ID" => $Item['Item_ID'],
				"Name" => $Item['Item_Name'],
				"Category" => $Item['Item_Type'],
				"Description" => $Item['Item_Description'],
				"Icon" => DOMAIN_SPRITES . "/Items/" . $Item['Item_ID'] . ".png",
			];
		}

		/**
		 * Given a Pokemon's Pokedex_ID and Alt_ID, determine it's icon and sprite URLs.
		 */
		public function FetchImages($Pokedex_ID, $Alt_ID = 0, $Type = 'Normal')
		{
			global $PDO;
			global $Dir_Root;

			if ( !$Pokedex_ID )
				return false;

			try
			{
				$FetchPokemon = $PDO->prepare("SELECT `Pokedex_ID`, `Alt_ID`, `Forme` FROM `pokedex` WHERE `Pokedex_ID` = ? AND `Alt_ID` = ? LIMIT 1");
				$FetchPokemon->execute([ $Pokedex_ID, $Alt_ID ]);
				$FetchPokemon->setFetchMode(PDO::FETCH_ASSOC);
				$Pokemon = $FetchPokemon->fetch();
			}
			catch ( PDOException $e )
			{
				HandleError($e);
			}

			$Pokedex_ID = str_pad($Pokemon['Pokedex_ID'], 3, "0", STR_PAD_LEFT);
			$Pokemon_Forme = strtolower(preg_replace('/(^\s*\()|(\)\s*$)/', '', $Pokemon['Forme']));

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
		 * Calculate the stats of a Pokemon depending on it's EV's, IV's, and Nature.
		 * Makes use of the official stat formulas found on Bulbapedia: https://bulbapedia.bulbagarden.net/wiki/Statistic
		 */
		public function CalcStat
		(
			string $Stat_Name,
			int $Base_Stat,
			int $Level,
			int $IV,
			int $EV,
			string $Nature
		)
		{
			if
			(
				!isset($Stat_Name) ||
				!isset($Base_Stat) ||
				!isset($Level) ||
				!isset($IV) ||
				!isset($EV) ||
				!isset($Nature)
			)
				return -1;

			if ( $Level < 1 )
				$Level = 1;

			if ( $IV > 31 )
				$IV = 31;

			if ( $EV > 252 )
				$EV = 252;

			if ( $Stat_Name == 'HP' )
			{
				if ( $Base_Stat == 1 )
					return 1;

				return floor((((2 * $Base_Stat + $IV + ($EV / 4)) * $Level) / 100) + $Level + 10);
			}
			else
			{
				$Nature_Data = $this->Natures()[$Nature];

				if ( $Nature_Data['Plus'] == $Stat_Name )
					$Nature_Bonus = 1.1;
				else if ( $Nature_Data['Minus'] == $Stat_Name )
					$Nature_Bonus = 0.9;
				else
					$Nature_Bonus = 1;

				return floor(((((2 * $Base_Stat + $IV + ($EV / 4)) * $Level) / 100) + 5) * $Nature_Bonus);
			}
		}

		/**
		 * List of all natures and their stat modifiers.
		 */
		public function Natures()
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
	}
