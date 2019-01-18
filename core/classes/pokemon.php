<?php
	class Pokemon
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

				$FetchPokedex = $PDO->prepare("SELECT * FROM `pokedex` WHERE `Pokedex_ID` = ? AND `Alt_ID` = ? LIMIT 1");
				$FetchPokedex->execute([$Pokemon['ID'], $Pokemon['Alt_ID']]);
				$FetchPokedex->setFetchMode(PDO::FETCH_ASSOC);
				$Pokedex = $FetchPokedex->fetch();

				$FetchItem = $PDO->prepare("SELECT * FROM `item_dex` WHERE `Item_ID` = ? LIMIT 1");
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

				if ( !isset($Pokemon) )
				{
					return "Error";
				}
			}
			catch ( PDOException $e )
			{
				HandleError( $e->getMessage() );
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
			$Moves = explode(',', $Pokemon['Moves']);
			$Level = FetchLevel($Pokemon['Experience'], 'Pokemon');
			$Experience = number_format($Pokemon['Experience']);

			$BaseStats = [
				round($Pokedex['hp'] + $StatBonus),
				round($Pokedex['attack'] + $StatBonus),
				round($Pokedex['defense'] + $StatBonus),
				round($Pokedex['spatk'] + $StatBonus),
				round($Pokedex['spdef'] + $StatBonus),
				round($Pokedex['speed'] + $StatBonus),
			];

			$Stats = [
				$this->CalcStats("HP", $BaseStats[0], $Level, $IVs[0], $EVs[0], $Pokemon['Nature']),
				$this->CalcStats("Attack", $BaseStats[1], $Level, $IVs[1], $EVs[1], $Pokemon['Nature']),
				$this->CalcStats("Defense", $BaseStats[2], $Level, $IVs[2], $EVs[2], $Pokemon['Nature']),
				$this->CalcStats("SpAtk", $BaseStats[3], $Level, $IVs[3], $EVs[3], $Pokemon['Nature']),
				$this->CalcStats("SpDef", $BaseStats[4], $Level, $IVs[4], $EVs[4], $Pokemon['Nature']),
				$this->CalcStats("Speed", $BaseStats[5], $Level, $IVs[5], $EVs[5], $Pokemon['Nature']),
			];
			
			if ( $Pokemon['Alt_ID'] != 0)
			{
				$Sprite = "/images/Pokemon/Sprites/" . $Pokemon['Type'] . "/" . str_pad($Pokemon['Pokedex_ID'], 3, "0", STR_PAD_LEFT) . "." . $Pokemon['Alt_ID'] . ".png";
				$Icon = "/images/Pokemon/Icons/". $Pokemon['Type'] . "/" . str_pad($Pokemon['Pokedex_ID'], 3, "0", STR_PAD_LEFT) . "." . $Pokemon['Alt_ID'] . ".png";
			}
			else
			{
				$Sprite = "/images/Pokemon/Sprites/" . $Pokemon['Type'] . "/" . str_pad($Pokemon['Pokedex_ID'], 3, "0", STR_PAD_LEFT) . ".png";
				$Icon = "/images/Pokemon/Icons/". $Pokemon['Type'] . "/" . str_pad($Pokemon['Pokedex_ID'], 3, "0", STR_PAD_LEFT) . ".png";
			}

			if ( $Pokemon['Type'] !== 'Normal' )
			{
				$Display_Name = $Pokemon['Type'] . $Pokemon['Name'];
			}
			else
			{
				$Display_Name = $Pokemon['Name'];
			}

			return [
				"ID" => number_format($Pokemon['ID']),
				"Pokedex_ID" => $Pokemon['Pokedex_ID'],
				"Alt_ID" => $Pokemon['Alt_ID'],
				"Nickname" => $Pokemon['Nickname'],
				"Display_Name" => $Display_Name,
				"Name" => $Pokemon['Name'],
				"Type" => $Pokemon['Type'],
				"Location" => $Pokemon['Location'],
				"Slot" => $Pokemon['Slot'],
				"Item" => $Item['Item_Name'],
				"Item_ID" => $Item['Item_ID'],
				"Item_Icon" => Domain(1) . "/images/Items/" . $Item['Item_Name'] . ".png",
				"Gender" => $Gender,
				"GenderShort" => $GenderShort,
				"Gender_Icon" => Domain(1) . "/images/Assets/" . $Gender . ".svg",
				"Level" => number_format($Level),
				"Experience" => $Experience,
				"Nature" => $Pokemon['Nature'],
				"BaseStats" => $BaseStats,
      	"Stats" => $Stats,
				"IVs" => $IVs,
				"EVs" => $EVs,
				"Move_1" => $Moves[0],
				"Move_2" => $Moves[1],
				"Move_3" => $Moves[2],
				"Move_4" => $Moves[3],
				"Happiness" => $Pokemon['Happiness'],
				"Owner_Current" => $Pokemon['Owner_Current'],
				"Owner_Current_Username" => $Current_Owner['Username'],
				"Owner_Original" => $Pokemon['Owner_Original'],
				"Owner_Original_Username" => $Original_Owner['Username'],
				"Trade_Interest" => $Pokemon['Trade_Interest'],
				"Challenge_Status" => $Pokemon['Challenge_Status'],
				"Biography" => $Pokemon['Biography'],
				"Creation_Date" => date("F j, Y (g:i A)", $Pokemon['Creation_Date']),
				"Creation_Location" => $Pokemon['Creation_Location'],
				"Sprite" => Domain(1) . $Sprite,
				"Icon" => Domain(1) . $Icon,
			];
		}

		/**
		 * Fetch any Pokemon's Pokedex data, given their Pokedex ID.
		 */
		public function FetchPokedexData($Pokedex_ID, $Alt_ID = 0)
		{
			global $PDO;

			try
			{
				$FetchPokedex = $PDO->prepare("SELECT * FROM `pokedex` WHERE `Pokedex_ID` = ? AND `Alt_ID` = ? LIMIT 1");
				$FetchPokedex->execute([$Pokedex_ID, $Alt_ID]);
				$FetchPokedex->setFetchMode(PDO::FETCH_ASSOC);
				$Pokedex = $FetchPokedex->fetch();

				if ( !isset($Pokedex) )
				{
					return "Error";
				}
			}
			catch ( PDOException $e )
			{
				echo $e->getMessage();
			}

			$BaseStats = [
				round($Pokedex['hp']),
				round($Pokedex['attack']),
				round($Pokedex['defense']),
				round($Pokedex['spatk']),
				round($Pokedex['spdef']),
				round($Pokedex['speed']),
			];

			if ( $Pokedex['Alt_ID'] !== 0 )
			{
				$Sprite = "/images/Pokemon/Sprites/Normal/" . str_pad($Pokedex['Pokedex_ID'], 3, "0", STR_PAD_LEFT) . "." . $Pokedex['Alt_ID'] . ".png";
				$Icon = "/images/Pokemon/Icons/Normal/" . str_pad($Pokedex['Pokedex_ID'], 3, "0", STR_PAD_LEFT) . "." . $Pokedex['Alt_ID'] . ".png";
			}
			else
			{
				$Sprite = "/images/Pokemon/Sprites/Normal/" . str_pad($Pokedex['Pokedex_ID'], 3, "0", STR_PAD_LEFT) . ".png";
				$Icon = "/images/Pokemon/Icons/Normal/" . str_pad($Pokedex['Pokedex_ID'], 3, "0", STR_PAD_LEFT) . ".png";
			}

			if ( $Pokedex['alter_poke_name'] !== null )
			{
				$Name = $Pokedex['poke_name'] . " " . $Pokedex['alter_poke_name'];
			}
			else
			{
				$Name = $Pokedex['poke_name'];
			}

			return [
				"ID" => $Pokedex['id'],
				"Pokedex_ID" => $Pokedex['Pokedex_ID'],
				"Alt_ID" => $Pokedex['Alt_ID'],
				"Name" => $Name,
				"Base_Stats" => $BaseStats,
				"Sprite" => Domain(1) . $Sprite,
				"Icon" => Domain(1) . $Icon,
			];
		}

		/**
		 * Move a Pokemon from your box, into your roster, or vice-versa.
		 */
		public function MovePokemon($PokeID, $Slot = 7)
		{
			global $PDO;
			global $User_Data;

			$Poke_Data = $this->FetchPokemonData($PokeID);

			if ( $Poke_Data == "Error" )
			{
				return "<div class='error'>This Pokemon doesn't exist.</div>";
			}

			if ( !in_array($Slot, [1, 2, 3, 4, 5, 6, 7]) )
			{
				return "<div class='error'>You have chosen an invalid slot.</div>";
			}

			if ( $Poke_Data['Owner_Current'] !== $User_Data['id'] )
			{
				return "<div class='error'>You are not the owner of the Pokemon that you have attempted to move.</div>";
			}

			if ( $Slot < 7 )
			{
				$Location = 'Roster';
			}
			else
			{
				$Location = 'Box';
			}

			try
			{
				$Roster_Fetch = $PDO->prepare("SELECT * FROM `pokemon` WHERE `Owner_Current` = ? AND `Location` = 'Roster' AND `Slot` <= 6 ORDER BY `Slot` ASC LIMIT 6");
				$Roster_Fetch->execute([$User_Data['id']]);
				$Roster_Fetch->setFetchMode(PDO::FETCH_ASSOC);
				$Roster = $Roster_Fetch->fetchAll();
			}
			catch ( PDOException $e )
			{
				HandleError( $e->getMessage() );
			}

			if ( $Slot == 7 )
			{
				try
				{
					$Box_Move = $PDO->prepare("UPDATE `pokemon` SET `Location` = 'Box', `Slot` = 7 WHERE `ID` = ? LIMIT 1");
					$Box_Move->execute([$Poke_Data['ID']]);
				}
				catch ( PDOException $e )
				{
					HandleError( $e->getMessage() );
				}
			}
			else
			{
				if ( isset($Roster[$Slot - 1]) )
				{
					try
					{
						$Roster_Move = $PDO->prepare("UPDATE `pokemon` SET `Location` = 'Roster', `Slot` = ? WHERE `ID` = ? LIMIT 1");
      			$Roster_Move->execute([$Slot, $Poke_Data['ID']]);
							
        		$Roster_Remove = $PDO->prepare("UPDATE `pokemon` SET `Location` = 'Roster', `Slot` = ? WHERE `ID` = ? LIMIT 1");
        		$Roster_Remove->execute([$Poke_Data['Slot'], $Roster[$Slot - 1]['ID']]);
					}
					catch ( PDOException $e )
					{
						HandleError( $e->getMessage() );
					}
				}
				else
				{
					try
					{
						$Roster_Move = $PDO->prepare("UPDATE `pokemon` SET `Location` = 'Roster', `Slot` = ? WHERE `ID` = ? LIMIT 1");
						$Roster_Move->execute([count($Roster) + 1, $Poke_Data['ID']]);
					}
					catch (PDOException $e)
					{
						HandleError( $e->getMessage() );
					}
				}
			}

			return true;
		}

		/**
		 * Release a Pokemon via it's `pokemon` DB ID.
		 * Store all released Pokemon in the `released` database table.
		 */
		public function ReleasePokemon($PokeID)
		{

		}

		/**
		 * Spawn a Pokemon into the game.
		 */
		public function CreatePokemon()
		{

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
				HandleError( $e->getMessage() );
			}

			return [
				"ID" => $Move['id'],
				"Name" => $Move['name'],
				"Type" => $Move['type'],
				"Category" => $Move['category'],
				"Power" => $Move['power'],
				"Accuracy" => $Move['accuracy'],
				"Priority" => $Move['priority'],
				"PP" => $Move['pp'],
				"Description" => $Move['desc'],
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
				HandleError( $e->getMessage() );
			}

			return [
				"ID" => $Item['Item_ID'],
				"Name" => $Item['Item_Name'],
				"Category" => $Item['Item_Type'],
				"Description" => $Item['Item_Description'],
			];
		}

		/**
		 * Calculate the stats of a Pokemon depending on it's EV's, IV's, and Nature.
		 * Makes use of the official stat formulas found on Bulbapedia: https://bulbapedia.bulbagarden.net/wiki/Statistic
		 */
		public function CalcStats($Stat, $BaseStat, $Level, $IVs, $EVs, $Nature)
		{
			$Fetch_Nature = array_search($Nature, $this->Natures());
			$Nature_Mult = 1;

			if ($Fetch_Nature >= 0 && $Fetch_Nature <= 3 && $Stat == 'Attack')
			{
				$Nature_Mult = 1.1;
			}	
			if ($Fetch_Nature >= 4 && $Fetch_Nature <= 7 && $Stat == 'Defense')
			{
				$Nature_Mult = 1.1;
			}
			if ($Fetch_Nature >= 8 && $Fetch_Nature <= 11 && $Stat == 'SpAtk')
			{
				$Nature_Mult = 1.1;
			}
			if ($Fetch_Nature >= 12 && $Fetch_Nature <= 15 && $Stat == 'SpDef')
			{
				$Nature_Mult = 1.1;
			}
			if ($Fetch_Nature >= 16 && $Fetch_Nature <= 19 && $Stat == 'Speed')
			{
				$Nature_Mult = 1.1;
			}
			if (($Fetch_Nature == 4 || $Fetch_Nature == 8 || $Fetch_Nature == 12 || $Fetch_Nature == 16) && $Stat == 'Attack')
			{
				$Nature_Mult = 0.9;
			}
			if (($Fetch_Nature == 0 || $Fetch_Nature == 9 || $Fetch_Nature == 13 || $Fetch_Nature == 17) && $Stat == 'Defense')
			{
				$Nature_Mult = 0.9;
			}
			if (($Fetch_Nature == 1 || $Fetch_Nature == 5 || $Fetch_Nature == 14 || $Fetch_Nature == 18) && $Stat == 'SpAtk')
			{
				$Nature_Mult = 0.9;
			}
			if (($Fetch_Nature == 2 || $Fetch_Nature == 6 || $Fetch_Nature == 10 || $Fetch_Nature == 19) && $Stat == 'SpDef')
			{
				$Nature_Mult = 0.9;
			}
			if (($Fetch_Nature == 3 || $Fetch_Nature == 7 || $Fetch_Nature == 11 || $Fetch_Nature == 15) && $Stat == 'Speed')
			{
				$Nature_Mult = 0.9;
			}
			
			if ($Stat == 'HP')
			{
				return floor( ( ( ( 2 * $BaseStat + $IVs + ( $EVs / 4 ) ) * $Level ) / 100 ) + $Level + 10 );
			}
			else
			{
				return floor( ( ( ( $IVs + 2 * $BaseStat + ( $EVs / 4 ) ) * $Level / 100 ) + 5) * $Nature_Mult );
			}
		}

		/**
		 * List of all natures and their stat modifiers.
		 */
		public function Natures()
		{
			return [
				'Lonely',		// +Attack / -Defense
				'Adamant',	// +Attack / -Special Attack
				'Naughty',	// +Attack / -Special Defense
				'Brave',		// +Attack / -Speed
				'Bold',  		// +Defense / -Attack
				'Impish',		// +Defense / -Special Attack
				'Lax',   		// +Defense / -Special Defense
				'Relaxed',	// +Defense / -Speed
				'Modest',		// +Special Attack / -Attack
				'Mild',   	// +Special Attack / -Defense
				'Rash',   	// +Special Attack / -Special Defense
				'Quiet',  	// +Special Attack / -Speed
				'Calm',   	// +Special Defense / -Attack
				'Gentle', 	// +Special Defense / -Defense
				'Careful',	// +Special Defense / -Special Attack
				'Sassy',  	// +Special Defense / -Speed
				'Timid',  	// +Speed / -Attack
				'Hasty',  	// +Speed / -Defense
				'Jolly',  	// +Speed / -Special Attack
				'Naive',  	// +Speed / -Special Defense
				'Bashful',	// Neutral
				'Docile',		// Neutral
				'Hardy', 		// Neutral
				'Quirky',		// Neutral
				'Serious',	// Neutral
			];
		}
	}