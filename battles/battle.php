<?php
	/**
	 * Autoload all necessary fight and module classes.
	 */
	spl_autoload_register(function($Class)
	{
		include_once 'fights/' . strtolower($Class) . '.php';
	});


	Class Battle
	{
		/**
		 * Set up any necessary variables that we'll be using.
		 */
		public $PDO;

		// Keep track of the battle dialogue so we may display it later.
		public $Dialogue = '';

		// Set the maximum allowed level for the battle.
		public $Level_Limit = -1;
		// Set the maximum allowed Pokemon in the user's roster for the battle.
		public $Roster_Limit = 6;

		// Allow the user's Pokemon to earn experience.
		public $Earn_Experience = true;
		// Allow the user to earn money.
		public $Earn_Money = true;
		// Allow the user to earn Clan Exp.
		public $Earn_Clan_Exp = true;
		// Allow the user to use items.
		public $Item_Usage = true;
		// Allow the user to restart the battle.
		public $Allow_Restart = true;

		// Stop the battle if the user's roster changes.
		public $End_On_Change = true;

		// The user's ID that is specified here will be granted a major stat boost on their Pokemon.
		public $Stat_Bonus = -1;

		/**
		 * Objectify the PDO and User_Data variables.
		 */
		public function __construct()
		{
			global $PDO;
			global $User_Data;

			$this->PDO = $PDO;
			$this->User_Data = $User_Data;
		}

		/**
		 * Create the battle.
		 */
		public function Create_Battle($ID)
		{
			$Start = $this->Start($ID);
			if ( $Start != 'Success' )
			{
				return "An error occurred while attempting to start the battle.";
			}

			$Attacker = $this->Create_Roster_Attacker();
			if ( $Attacker != 'Success' )
			{
				return "An error occurred while setting up the attacker's roster.";
			}

			$Defender = $this->Create_Roster_Defender($ID);
			if ( $Defender != 'Success' )
			{
				return "An error occurred while setting up the defender's roster.";
			}

			$_SESSION['Battle']['Status'] = [
				'Logs' => [],
				'Battle_ID' => RandSalt(21),
				'Create_ID' => $ID,
				'Battle_Mode' => '',
				'Battle_Fight' => $this->Battle_Fight,
				'Time_Started' => microtime(true),
				'Weather' => 'Clear',
				'Text' => 'Select an attack to begin the battle.',
				'Roster' => $this->User_Data['Roster'],
				'Continue' => [
					'Code' => '',
					'Type' => '',
					'ID' => '',
				],
				'Restart' => [
					'Code' => '',
					'Type' => '',
					'ID' => '',
				],
				'Turns' => [
					'Total' => 0,
					'Attacker' => 0,
					'Defender' => 0,
				],
			];

			return 'Success';
		}

		/**
		 * Create a Pokemon via it's DB ID.
		 */
		public function Create_Pokemon($ID)
		{
			global $PDO;
			global $Poke_Class;

			$Poke_Data = $Poke_Class->FetchPokemonData($ID);

			if ( $Poke_Data == 'Error' )
			{
				return 'Error';
			}

			$Moves = [];
			for ( $i = 1; $i <= 4; $i++ )
			{
				$Move_Data = $Poke_Class->FetchMoveData( $Poke_Data['Move_' . $i] );

				$Moves[] = [ $Move_Data['ID'] , $Move_Data['Name'], RandSalt(12) ];
			}

			$Status = 'Normal';

			// Att, Def, Sp.Att, Sp.Def, Speed, Accuracy, Evasion
			$Stat_Mods = [
				0, 0, 0, 0, 0, 0, 0
			];

			if ( $this->Stat_Bonus > 0 )
			{
				global $User_Data;

				if ( $User_Data['id'] == $this->Stat_Bonus )
				{
					$Poke_Data['BaseStats'][0] *= 6969;
					$Poke_Data['BaseStats'][1] *= 6969;
					$Poke_Data['BaseStats'][2] *= 6969;
					$Poke_Data['BaseStats'][3] *= 6969;
					$Poke_Data['BaseStats'][4] *= 6969;
					$Poke_Data['BaseStats'][5] *= 6969;
				}
			}

			return [
				'ID' => $Poke_Data['ID'],
				'Poke_ID' => $Poke_Data['Pokedex_ID'],
				'Alt_ID' => $Poke_Data['Alt_ID'],
				'Display_Name' => $Poke_Data['Display_Name'],
				'Level' => $Poke_Data['Level_Raw'],
				'Experience' => $Poke_Data['Experience'],
				'Slot' => $Poke_Data['Slot'],
				'Moves' => $Moves,
				'Base_Stats' => $Poke_Data['BaseStats'],
				'Stats' => $Poke_Data['Stats'],
				'IVs' => $Poke_Data['IVs'],
				'EVs' => $Poke_Data['EVs'],
				//'Ability' => $Poke_Data['Ability'],
				'Nature' => $Poke_Data['Nature'],
				'Happiness' => $Poke_Data['Happiness'],
				'Type' => $Poke_Data['Type'],
				'Primary' => $Poke_Data['Type_Primary'],
				'Secondary' => $Poke_Data['Type_Secondary'],
				'Item' => $Poke_Data['Item_ID'],
				'Gender' => $Poke_Data['Gender'],
				'Sprite' => $Poke_Data['Sprite'],
				'Icon' => $Poke_Data['Icon'],
				'HP_Cur' => $Poke_Data['Stats'][0],
				'HP_Max' => $Poke_Data['Stats'][0],
				'Stat_Mods' => $Stat_Mods,
				'Status' => [
					'Name'		=> $Status,
					'Length'	=> 0,
				],
				'Last_Move' => false,
				'Used_Moves' => [],
				'Turn' => 0,
			];
		}

		/**
		 * Create a fake Pokemon based on it's Pokedex_ID and Alt_ID.
		 */
		public function Create_Fakemon($Pokedex_ID, $Alt_ID)
		{
			$Dialogue = $this->Dialogue("Attempting to create a Pokemon that isn't actually in game.");

			return $Dialogue;
		}

		/**
		 * Handle logging battle data to the database.
		 */
		public function Logify()
		{
			global $PDO;

			if ( isset($_SESSION['Battle']['Status']['Logs']) )
			{
				$Time_Started = $_SESSION['Battle']['Status']['Time_Started'];
				$Time_Ended = microtime(true);
				$Battle_Duration = 1000 * (microtime(true) - $Time_Started);

				$Action = $_SESSION['Battle']['Status']['Logs']['Action'];
				$Postcodes = [ $_SESSION['Battle']['Status']['Logs']['Postcode']['POST'], $_SESSION['Battle']['Status']['Logs']['Postcode']['SESS'] ];
				$Clicks = $_SESSION['Battle']['Status']['Logs']['Coords']['Clicks'];
				$Coords = $_SESSION['Battle']['Status']['Logs']['Coords']['x'] . ',' . $_SESSION['Battle']['Status']['Logs']['Coords']['y'];

				try
				{
					$Insert_Log = $PDO->prepare("
						INSERT INTO `battle_logs`
						(`User_ID`, `Create_ID`, `Battle_ID`, `Battle_Type`, `Battle_Mode`, `Action`, `Postcode_POST`, `Postcode_SESS`, `Clicks`, `Coords` `Time_Started`, `Time_Ended`, `Battle_Duration` `Client_IP`)
						VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
					");
					$Insert_Log->execute([ $this->User_Data['id'], $_SESSION['Battle']['Status']['Create_ID'], $_SESSION['Battle']['Status']['Battle_ID'], $_SESSION['Battle']['Status']['Battle_Fight'], $_SESSION['Battle']['Status']['Battle_Mode'], $Action, $Postcodes[0], $Postcodes[1], $Clicks, $Coords, $Time_Started, $Time_Ended, $Battle_Duration, $_SERVER['REMOTE_ADDR'] ]);
				}
				catch ( PDOException $e )
				{
					HandleError( $e->getMessage() );
				}
			}
		}

		/**
		 * Create battle buttons.
		 */
		public function Create_Button($Button)
		{
			$ID = $_SESSION['Battle']['Status'][$Button]['ID'];
			$Code = $_SESSION['Battle']['Status'][$Button]['Code'];

			$this->Dialogue("<button onclick='Battle.{$Button}({$ID}, {$Code});'>{$Button} Battle</button>", true);
		}

		/**
		 * Handle all battle dialogue.
		 */
		public function Dialogue($Text, $Prepend = false)
		{
			if ( $Prepend )
			{
				$Dialogue = $Text . '<br />' . $_SESSION['Battle']['Dialogue'];
			}
			else
			{
				$Dialogue .= $Text;
			}
		}
	}