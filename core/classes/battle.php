<?php

	Class Battle
	{
		public $PDO;

		/**
		 * An array containing which battle stats to display on the battle page.
		 */
		public $Stat_Display = [
			'Trainer_Exp' 	=> [ 'Show' => true ],
			'Currency' 			=> [ 'Show' => true ],
			'Total_Battles' => [ 'Show' => true ],
			'Runtime' 			=> [ 'Show' => true ],
		];

		/**
		 * Battle settings that are taken into account during the battle.
		 */
		public $Battle_Settings = [
			'Version'				=> '1',
			'Exp_Trainer' 	=> [ 'Earnable' => true ],
			'Exp_Clan' 			=> [ 'Earnable' => true ],
			'Currency' 			=> [ 'Earnable' => true ],
			'Roster' 				=> [ 'Changeable' => false ],
			'Items' 				=> [ 'Useable' => true ],
			'Limit_Level' 	=> [ 'Min' => 1, 'Max' => -1 ],
			'Limit_Roster' 	=> [ 'Min' => 1, 'Max' => 6 ],
			'Captcha' 			=> [ 'Chance' => 666 ],
		];

		/**
		 * Culmination of all received battle text.
		 */
		public $Battle_Dialog = '';

		/**
		 * Constructor function.
		 */
		public function __contruct()
		{
			global $PDO;
			$this->PDO = $PDO;

			global $User_Data;
			$this->User_Data = $User_Data;
		}

		/**
		 * Determine how much experience that you've earned from the battle.
		 * @params: 
		 * -> type ('trainer' or 'pokemon')
		 */
		public function DetermineExp($Type, $Pokemon)
		{

		}

		/**
		 * Determine how much of a given currency that you've earned from the battle.
		 */
		public function DetermineCurrency()
		{

		}

		/**
		 * Actually apply and deal the proper amount of damage.
		 */
		public function DamageDeal()
		{

		}

		/**
		 * Determine how much damage should be done.
		 */
		public function DamageFormula($Level, $Stats, $Effectiveness, $STAB, $Crit, $Misc)
		{

		}

		/**
		 * Process any necessary battle logs.
		 */
		public function Logify()
		{

		}

		/**
		 * Start the battle, if necessary.
		 */
		public function CreateBattle($Type, $ID)
		{
			global $PDO;
			global $User_Data;
			global $UserClass;

			if ( $Type == 'Trainer' )
			{
				$Foe = $UserClass->FetchUserData($ID);

				if ( $Foe['Roster'] == 0 )
				{
					return "You may not battle a user has no Pokemon in their roster.";
				}
			}

			/**
			 * Retrieve the starting Pokemon in the user's roster.
			 */
			try
			{
				$Fetch_Lead = $PDO->prepare("SELECT `ID` FROM `pokemon` WHERE `Owner_Current` = ? AND `Slot` <= 1 AND `Location` = 'Roster' LIMIT 1");
				$Fetch_Lead->execute([ $User_Data['id'] ]);
				$Fetch_Lead->setFetchMode(PDO::FETCH_ASSOC);
				$Roster_Lead = $Fetch_Lead->fetch();
			}
			catch ( PDOException $e )
			{
				HandleError( $e->getMessage() );
			}

			var_dump($User_Data);

			$_SESSION['Battle'] = [
				"Logs" 					=> null,
				"Battle_ID" 		=> randomSalt(12),
				"Battle_Type" 	=> $Type,
				"Battle_Foe" 		=> $ID,
				"Time_Started" 	=> microtime(true),
				"Clan" 					=> $Clan,
				"Text" 					=> "Please select a move in order to begin the battle.",
				"Roster" 				=> $User_Data['Roster'],
				"Active"				=> $Roster_Lead['ID'],
				"PostCode_M1"		=> randomSalt(12),
				"PostCode_M2"		=> randomSalt(12),
				"PostCode_M3"		=> randomSalt(12),
				"PostCode_M4"		=> randomSalt(12),
				"PostCode_C1"		=> randomSalt(12),
				"PostCode_R1"		=> randomSalt(12),
				"Attacker"			=>
				[
					"Active"			=> [ 'ID' => 1, 'HP_Cur' => 1000, 'HP_Max' => 1000 ],
					"Roster"			=> [],
					"Fainted"			=> [],
				],
				"Defender"			=>
				[
					"Active"			=> [ 'ID' => 1, 'HP_Cur' => 1000, 'HP_Max' => 1000 ],
					"Roster"			=> [],
					"Fainted"			=> [],
				]
			];
		}

		/**
		 * Check to see if the clicked coordinates are within the accepted range.
		 */
		public function CheckCoords($x, $x_min, $x_max, $y, $y_min, $y_max)
		{
			if ( $x >= $x_min && $x <= $x_max && $y >= $y_min && $y <= $y_max )
			{
				return true;
			}
			else
			{
				return false;
			}
		}

		/**
		 * Render attack inputs of the active Pokemon.
		 */
		public function RenderMoves()
		{
			global $PDO;
			global $PokeClass;

			$Pokemon = $PokeClass->FetchPokemonData($_SESSION['Battle']['Active']);

			for ( $i = 1; $i <= 4; $i++ )
			{
				$Spawn_Salt = randomSalt(12);
				$_SESSION['Battle']['PostCode_M' . $i] = $Spawn_Salt;
				
				$Move_Data = $PokeClass->FetchMoveData($Pokemon['Move_' . $i]);
				echo "
					<button style='padding: 5px; width: 20%;' id='Move_{$i}' onclick='Attack($i, {$Move_Data['ID']}, event)' PostCode='{$_SESSION['Battle']['PostCode_M' . $i]}'>
						{$Move_Data['Name']}
					</button>
				";

			}
		}

		/**
		 * Update input postcodes.
		 */
		public function UpdatePostCodes()
		{

			$_SESSION['Battle']['PostCode_C1'] = randomSalt(12);
			$_SESSION['Battle']['PostCode_R1'] = randomSalt(12);

			echo $_SESSION['Battle']['PostCode_C1'] . "<br />";
			echo $_SESSION['Battle']['PostCode_R1'];
		}

		/**
		 * Determine if a Pokemon has dealt a supereffective attack or not.
		 */
		public function DetermineEffectiveness($Move_Type, $Poke_Type)
		{
			$Type_Chart = [
				//  NL  N   F   F   P   G   R   B   G   S   F   W   G   E   P   I   D   D   F
					[ 1,  1,  1,  1,  1,  1,  1,  1,  1,  1,  1,  1,  1,  1,  1,  1,  1,  1,  1 ],	//None
					[ 1,  1,  1,  1,  1,  1, .5,  1,  0, .5,  1,  1,  1,  1,  1,  1,  1,  1,  1 ],	//Normal
					[ 1,  2,  1, .5, .5,  1,  2, .5,  0,  2,  1,  1,  1,  1, .5,  2,  1,  2, .5 ], //Fight
					[ 1,  1,  2,  1,  1,  1, .5,  2,  1, .5,  1,  1,  2, .5,  1,  1,  1,  1,  1 ],	//Flying
					[ 1,  1,  1,  1, .5, .5, .5,  1, .5,  0,  1,  1,  2,  1,  1,  1,  1,  1,  2 ],	//Poison
					[ 1,  1,  1,  0,  2,  1,  2, .5,  1,  2,  2,  1, .5,  2,  1,  1,  1,  1,  1 ],	//Ground
					[ 1,  1, .5,  2,  1, .5,  1,  2,  1, .5,  2,  1,  1,  1,  1,  2,  1,  1,  1 ], //Rock
					[ 1,  1, .5, .5, .5,  1,  1,  1, .5, .5, .5,  1,  2,  1,  2,  1,  1,  2, .5 ], //Bug
					[ 1,  0,  1,  1,  1,  1,  1,  1,  2,  1,  1,  1,  1,  1,  2,  1,  1, .5,  1 ], //Ghost
					[ 1,  1,  1,  1,  1,  1,  2,  1,  1, .5, .5, .5,  1, .5,  1,  2,  1,  1,  2 ], //Steel
					[ 1,  1,  1,  1,  1,  1, .5,  2,  1,  2, .5, .5,  2,  1,  1,  2, .5,  1,  1 ], //Fire
					[ 1,  1,  1,  1,  1,  2,  2,  1,  1,  1,  2, .5, .5,  1,  1,  1, .5,  1,  1 ], //Water
					[ 1,  1,  1, .5, .5,  2,  2, .5,  1, .5, .5,  2, .5,  1,  1,  1, .5,  1,  1 ], //Grass
					[ 1,  1,  1,  2,  1,  0,  1,  1,  1,  1,  1,  2, .5, .5,  1,  1, .5,  1,  1 ], //Electric
					[ 1,  1,  2,  1,  2,  1,  1,  1,  1, .5,  1,  1,  1,  1, .5,  1,  1,  0,  1 ], //Psychic
					[ 1,  1,  1,  2,  1,  2,  1,  1,  1, .5, .5, .5,  2,  1,  1, .5,  2,  1,  1 ], //Ice
					[ 1,  1,  1,  1,  1,  1,  1,  1,  1, .5,  1,  1,  1,  1,  1,  1,  2,  1,  0 ], //Dragon
					[ 1,  1, .5,  1,  1,  1,  1,  1,  2,  1,  1,  1,  1,  1,  2,  1,  1, .5, .5 ], //Dark
					[ 1,  1,  2,  1, .5,  1,  1,  1,  1, .5, .5,  1,  1,  1,  1,  1,  2,  2,  1 ], //Fairy
			];

			$Types = $this->Types();

			$Move_Typing = array_search($Move_Type, $Types);
			$Type_1 = array_search($Poke_Type[0], $Types);
			$Type_2 = array_search($Poke_Type[1], $Types);

			return $Type_Chart[$Move_Typing][$Type_1] * $Type_Chart[$Move_Typing][$Type_2];
		}

		/**
		 * Array of all possible typings that a Pokemon may have.
		 */
		public function Types()
		{
			return [
				"None", "Normal", "Fighting", "Flying", "Poison", "Ground", "Rock", "Bug", "Ghost", "Steel", "Fire", "Water", "Grass", "Electric", "Psychic", "Ice", "Dragon", "Dark", "Fairy",
			];	
		}

		/**
		 * Determine if the user is in compliance with all battle checks.
		 * ~~ BattleDialogue($Text, $Prepend = false, $Line_Break = true)
		 */
		public function BattleCheck($Level, $Roster)
		{
			if ( !$this->WithinLevel($Level) )
			{
				$this->BattleDialogue("Your Pokemon's level is either too high, or too low. Please restart the battle.");
			}

			if ( !$this->VerifyRoster($Roster) )
			{
				$this->BattleDialogue("You have changed your roster. Please restart the battle.");
			}
		}

		/**
		 * Determine if a Pokemon is within the set level limits.
		 */
		public function WithinLevel($Level)
		{
			if
			(
				$Level >= $this->Battle_Settings['Limit_Level']['Min'] &&
				$Level <= $this->Battle_Settings['Limit_Level']['Max']
			)
			{
				return true;
			}
			else
			{
				return false;
			}
		}

		/**
		 * Determine if the user's roster has since changed.
		 */
		public function VerifyRoster()
		{
			global $PDO;
			global $User_Data;

			try
			{
				$Roster_Query = $PDO->prepare("SELECT `ID` FROM `pokemon` WHERE `Owner_Current` = ? AND `Slot` <= 6 AND `Location` = 'Roster' ORDER BY `Slot` ASC LIMIT 6");
				$Roster_Query->execute([ $User_Data['id'] ]);
				$Roster_Query->setFetchMode(PDO::FETCH_ASSOC);
				$curRoster = $Roster_Query->fetchAll();
			}
			catch( PDOException $e )
			{
				HandleError( $e->getMessage() );
			}
			
			/**
			 * Fetch the roster that the user is currently using.
			 * Append the database ID's together to form a string.
			 */
			$Roster_Current = '';
			foreach ( $curRoster as $Key => $Value )
			{
				$Roster_Current .= "{$Value['ID']}";
			}

			/**
			 * Compare the roster strings.
			 * If they're the same, continue on.
			 * Else, stop the battle.
			 */
			if ( $Roster_Current != $_SESSION['Battle']['Roster'] )
			{
				return true;
			}
			else
			{
				return false;
			}
		}

		/**
		 * Add battle dialog to the page.
		 */
		public function BattleDialogue($Text, $Prepend = false, $Line_Break = true)
		{
			if ( $Prepend )
			{
				if ( $Line_Break )
				{
					$_SESSION['Battle']['Text'] = $Text . "<br /><br />" . $_SESSION['Battle']['Text'];
				}
				else
				{
					$_SESSION['Battle']['Text'] = $Text . "<br />" . $_SESSION['Battle']['Text'];
				}
			}
			else
			{
				if ( $Line_Break )
				{
					$_SESSION['Battle']['Text'] .= $Text . "<br />";
				}
				else
				{
					$_SESSION['Battle']['Text'] .= $Text;
				}
			}
		}
	}