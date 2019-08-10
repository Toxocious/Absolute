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
		 */
		public function DetermineExp($Pokemon)
		{

		}

		/**
		 * Determine how much of a given currency that you've earned from the battle.
		 * -- determine quantity of absolute coins earned based on trainer level?
		 * 			floor(total roster level / trainer_level) + trainer level;
		 * -- should probably determine the amount of currency that you get depending on this formula?
		 *      $rand = mt_rand(165, 200) / 200;
		 * 			floor(total roster levels / total roster count * $rand) + 1;
		 */
		public function DetermineRewards()
		{
			global $PDO;
			global $Poke_Class;
			global $User_Data;

			$Abso_Earned = 0;
			$Abso_Chance = mt_rand(1, 10);
			$Money_Earned = mt_rand(69, 420);

			$Poke_Data = $Poke_Class->FetchPokemonData($_SESSION['Battle']['Attacker']['Active']['ID']);
			
			$Additional_Text = "";
			$Earned_Text = 'For winning this battle, you have earned:<br />';
			
			if ( $Poke_Data['Item'] == "Amulet Coin" )
			{
				$Money_Earned *= 2;
				$Additional_Text = " (x2)";
			}

			if ( $Abso_Chance === 4 )
			{
				$Abso_Earned = mt_rand(4, 12);
				$Earned_Text .= "+{$Abso_Earned} Absolute Coins<br />";
			}

			$Earned_Text .= "+ $" . number_format($Money_Earned) . $Additional_Text;

			try
			{
				$Update_Currencies = $PDO->prepare("UPDATE `users` SET `Money` = `Money` + ?, `Abso_Coins` = `Abso_Coins` + ? WHERE `id` = ?");
				$Update_Currencies->execute([ $Money_Earned, $Abso_Earned, $User_Data['id'] ]);
			}
			catch( PDOException $e )
			{
				HandleError( $e->getMessage() );
			}

			return $Earned_Text;
		}

		/**
		 * Determine whether or not your attack was a critical hit.
		 * -- 1 in 7 chance to crit.
		 * -- Crit damage = x1 or x1.5
		 */
		public function CritCheck()
		{
			$Crit = 1;

			if ( mt_rand(1, 7) === 4 )
			{
				$Crit = 1.5;
			}

			return $Crit;
		}

		/**
		 * Actually apply and deal the proper amount of damage.
		 */
		public function DamagePhase($Poke_ID, $Foe_ID, $Move_ID)
		{
			global $PDO;
			global $Poke_Class;

			$Move 			= $Poke_Class->FetchMoveData($Move_ID);
			$Poke 			= $Poke_Class->FetchPokemonData($Poke_ID);
			$Foe				= $Poke_Class->FetchPokemonData($Foe_ID);
			$Poke_Dex		= $Poke_Class->FetchPokedexData($Poke['Pokedex_ID'], $Poke['Alt_ID']);
			$Foe_Dex		= $Poke_Class->FetchPokedexData($Foe['Pokedex_ID'], $Foe['Alt_ID']);
			$Rand 			= mt_rand(1, 4);
			$Rand_Move	= $Foe['Move_' . $Rand];
			$Foe_Move 	= $Poke_Class->FetchMoveData($Rand_Move);

			$Crit								= $this->CritCheck();
			$STAB								= $this->DetermineSTAB($Poke_Dex['Type_Primary'], $Poke_Dex['Type_Secondary'], $Move['Type']);
			$Att_Effectiveness 	= $this->DetermineEffectiveness($Move['Type'], $Foe_Dex['Type_Primary'], $Foe_Dex['Type_Secondary']);
			$Def_Effectiveness 	= $this->DetermineEffectiveness($Move['Type'], $Poke_Dex['Type_Primary'], $Poke_Dex['Type_Secondary']);

			$Att_Stats = [ $Poke['Stats'][1], $Foe['Stats'][2] ];
			$Def_Stats = [ $Foe['Stats'][1], $Poke['Stats'][2] ];

			$Attacker_Damage = $this->DamageCalc( $Move['Power'], 		$Poke['Level'], $Att_Stats, $Att_Effectiveness, $STAB, $Crit );
			$Defender_Damage = $this->DamageCalc( $Foe_Move['Power'], $Foe['Level'], 	$Def_Stats, $Def_Effectiveness, $STAB, $Crit );

			/**
			 * Speed check.
			 */
			if ( $Poke['Stats'][5] >= $Foe['Stats'][5] )
			{
				/**
				 * Do damage now.
				 */
				$_SESSION['Battle']['Defender']['Active']['HP_Cur'] -= $Attacker_Damage;

				/**
				 * If foe has fainted.
				 */
				if ( $_SESSION['Battle']['Defender']['Active']['HP_Cur'] <= 0 )
				{
					$Rewards 		= $this->DetermineRewards();
					//$Experience = $this->DetermineExperience();

					$_SESSION['Battle']['Text'] = "
						{$Poke['Display_Name']} used {$Move['Name']} and has dealt " . number_format($Attacker_Damage) . " damage to {$Foe['Display_Name']}.<br /><br />
						
						The foe has fainted!<br /><br />

						" . $Rewards . "
					";
				}
				/**
				 * Neither Pokemon fainted.
				 */
				else if ( $_SESSION['Battle']['Attacker']['Active']['HP_Cur'] > 0 && $_SESSION['Battle']['Defender']['Active']['HP_Cur'] > 0 )
				{
					$_SESSION['Battle']['Attacker']['Active']['HP_Cur'] -= $Defender_Damage;

					$_SESSION['Battle']['Text'] = "
						You did 'x' amount of damage to the foe!<br /><br />
						The foe did 'y' amount of damage to you!<br /><br />
					";
				}
				/**
				 * Both Pokemon fainted.
				 */
				else if ( $_SESSION['Battle']['Attacker']['Active']['HP_Cur'] <= 0 && $_SESSION['Battle']['Defender']['Active']['HP_Cur'] <= 0 )
				{
					$_SESSION['Battle']['Text'] = "
						The foe has fainted!<br /><br />
						You have fainted!<br /><br />
					";
				}
				/**
				 * You fainted.
				 */
				else
				{
					$_SESSION['Battle']['Text'] = "
						You have fainted!<br />
					";
				}
			}
			else
			{
				/**
				 * Do damage now.
				 */
				$_SESSION['Battle']['Attacker']['Active']['HP_Cur'] -= $Attacker_Damage;

				/**
				 * If foe has fainted.
				 */
				if ( $_SESSION['Battle']['Attacker']['Active']['HP_Cur'] <= 0 )
				{
					$_SESSION['Battle']['Text'] = "
						You have fainted!<br /><br />
					";
				}
				/**
				 * Neither Pokemon fainted.
				 */
				else if ( $_SESSION['Battle']['Defender']['Active']['HP_Cur'] > 0 && $_SESSION['Battle']['Attacker']['Active']['HP_Cur'] > 0 )
				{
					$_SESSION['Battle']['Defender']['Active']['HP_Cur'] -= $Attacker_Damage;

					$_SESSION['Battle']['Text'] = "
						The foe did 'y' amount of damage to you!<br /><br />
						You did 'x' amount of damage to the foe!<br /><br />
					";
				}
				/**
				 * Both Pokemon fainted.
				 */
				else if ( $_SESSION['Battle']['Defender']['Active']['HP_Cur'] <= 0 && $_SESSION['Battle']['Attacker']['Active']['HP_Cur'] <= 0 )
				{
					$_SESSION['Battle']['Text'] = "
						You have fainted!<br /><br />
						The foe has fainted!<br /><br />
					";
				}
				/**
				 * You fainted.
				 */
				else
				{
					$_SESSION['Battle']['Text'] = "
						You have fainted!<br />
					";
				}
			}
			
			//echo "
			//	<b>Move Power</b>:						{$Move['Power']}<br />
			//	<b>Move Type</b>:							{$Move['Type']}<br />
			//	<b>Poke Types</b>:						{$Poke_Dex['Type_Primary']}, {$Poke_Dex['Type_Secondary']}<br />
			//	<b>Poke Level</b>:						{$Poke['Level_Raw']}<br />
			//	<b>Stats (Attacker Att)</b>:	{$Stats[0]}<br />
			//	<b>Stats (Defender Def)</b>:	{$Stats[1]}<br />
			//	<b>Eff</b>:										{$Effectiveness}<br />
			//	<b>STAB</b>:									{$STAB}<br />
			//	<b>Crit</b>:									{$Crit}<br />
			//";
		}

		/**
		 * Determine how much damage should be done.
		 */
		public function DamageCalc($Move_Power, $Level, $Stats, $Effectiveness, $STAB, $Crit, $Misc = 1)
		{
			$Attack = $Stats[0];
			$Defense = $Stats[1];
			$Rand = mt_rand(165, 215) / 200;

			if ( $Move_Power == 0 )
			{
				return 0;
			}
			else
			{
				return @ceil((((((($Level * 2 / 5) + 2) * $Attack * $Move_Power / 50) / $Defense) + 2) * $Crit * .95) * $Effectiveness * $STAB ) + $Attack * $Rand;
			}
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
			global $Poke_Class;
			global $User_Data;
			global $User_Class;

			if ( $Type == 'Trainer' )
			{
				$Foe = $User_Class->FetchUserData($ID);

				if ( $Foe['Roster'] == 0 )
				{
					return "You may not battle a user has no Pokemon in their roster.";
				}
			}

			$Clan = null;

			/**
			 * Retrieve the starting Pokemon in the user's roster.
			 */
			try
			{
				$Fetch_Attacker_Roster = $PDO->prepare("SELECT `ID` FROM `pokemon` WHERE `Owner_Current` = ? AND `Slot` <= 6 AND `Location` = 'Roster' LIMIT 6");
				$Fetch_Attacker_Roster->execute([ $User_Data['id'] ]);
				$Fetch_Attacker_Roster->setFetchMode(PDO::FETCH_ASSOC);
				$Attacker_Roster = $Fetch_Attacker_Roster->fetchAll();
				$Attacker_Data = $Poke_Class->FetchPokemonData($Attacker_Roster[0]['ID']);
				
				$Fetch_Defender_Roster = $PDO->prepare("SELECT `ID` FROM `pokemon` WHERE `Owner_Current` = ? AND `Slot` <= 6 AND `Location` = 'Roster' LIMIT 6");
				$Fetch_Defender_Roster->execute([ $ID ]);
				$Fetch_Defender_Roster->setFetchMode(PDO::FETCH_ASSOC);
				$Defender_Roster = $Fetch_Defender_Roster->fetchAll();
				$Defender_Data = $Poke_Class->FetchPokemonData($Defender_Roster[0]['ID']);
			}
			catch ( PDOException $e )
			{
				HandleError( $e->getMessage() );
			}

			// Fetch the data of the user's active roster.
			$A_Roster = [];
			foreach ( $Attacker_Roster as $Key => $Value )
			{
				// Don't include the first slot, as that's going to be set as the active Pokemon.
				if ( $Key != 0 )
				{
					$A_Poke = $Poke_Class->FetchPokemonData($Value['ID']);

					$A_Roster[] = [
						'ID'			=> $A_Poke['ID'],
						'HP_Cur'	=> $A_Poke['Stats'][0],
						'HP_Max'	=> $A_Poke['Stats'][0],
						'Status'	=> 'None',
					];
				}
			}

			// Fetch the data for the foe's active roster.
			$D_Roster = [];
			foreach ( $Defender_Roster as $Key => $Value )
			{
				// Don't include the first slot, as that's going to be set as the active Pokemon.
				if ( $Key != 0 )
				{
					$D_Poke = $Poke_Class->FetchPokemonData($Value['ID']);

					$D_Roster[] = [
						'ID'			=> $D_Poke['ID'],
						'HP_Cur'	=> $D_Poke['Stats'][0],
						'HP_Max'	=> $D_Poke['Stats'][0],
						'Status'	=> 'None',
					];
				}
			}

			$_SESSION['Battle'] = [
				"Logs" 							=> null,
				"Battle_ID" 				=> RandSalt(12),
				"Battle_Type" 			=> $Type,
				"Battle_Foe" 				=> $ID,
				"Time_Started" 			=> microtime(true),
				"Clan" 							=> $Clan,
				"Text" 							=> "Please select a move in order to begin the battle.",
				"Roster" 						=> $User_Data['Roster'],
				"PostCode_Move_1"		=> RandSalt(12),
				"PostCode_Move_2"		=> RandSalt(12),
				"PostCode_Move_3"		=> RandSalt(12),
				"PostCode_Move_4"		=> RandSalt(12),
				"PostCode_C1"				=> RandSalt(12),
				"PostCode_R1"				=> RandSalt(12),
				"Attacker"					=>
				[
					"Active"					=> [ 'ID' => $Attacker_Data['ID'], 'HP_Cur' => $Attacker_Data['Stats'][0], 'HP_Max' => $Attacker_Data['Stats'][0], 'Status' => 'None' ],
					"Inactive"				=> [ $A_Roster ],
					"Roster"					=> [ $Attacker_Roster ],
					"Fainted"					=> [],
				],
				"Defender"					=>
				[
					"Active"					=> [ 'ID' => $Defender_Data['ID'], 'HP_Cur' => $Defender_Data['Stats'][0], 'HP_Max' => $Defender_Data['Stats'][0], 'Status' => 'None' ],
					"Inactive"				=> [ $D_Roster ],
					"Roster"					=> [ $Defender_Roster ],
					"Fainted"					=> [],
				]
			];

			//echo "ATTACKER<br />";
			//echo "<pre>";var_dump($_SESSION['Battle']['Attacker']);echo "</pre>";
			//echo "<hr />";
			//echo "DEFENDER<br />";
			//echo "<pre>";var_dump($_SESSION['Battle']['Defender']);echo "</pre>";

			//exit;
		}

		/**
		 * Check a myriad of data to accurately figure out if the client is macroing or using an autoclicker.
		 */
		public function MacroCheck($Coords_Valid, $Coords_Sent, $PostCode, $Move, $Clicks)
		{
			/**
			 * Check to see if the user clicked within the accepted coordinate range.
			 */
			$Check_Coords = $this->CheckCoords( $Coords_Sent['x'], $Coords_Valid['X']['Min'], $Coords_Valid['X']['Max'], $Coords_Sent['y'], $Coords_Valid['Y']['Min'], $Coords_Valid['Y']['Max'] );
			if ( $Check_Coords )
			{
				//$Validated_Coords = "Coords are within the accepted range.<br />";
				$Validated_Coords = true;
			}
			else
			{
				//$Validated_Coords = "Coords are outside the accepted range.<br />";
				$Validated_Coords = false;
			}

			/**
			 * Check to see if the submitted post code matches the session post code.
			 */
			if ( $PostCode == $_SESSION['Battle']['PostCode_' . $Move] )
			{
				//$Validated_PostCode = "The submitted postcode matches the session postcode.<br />";
				$Validated_PostCode = true;
			}
			else
			{
				//$Validated_PostCode = "The submitted postcode doesn't match the session postcode.<br />";
				$Validated_PostCode = false;
			}

			/**
			 * A high amount of clicks usually indicates that someone is using a basic autoclicker.
			 */
			if ( $Clicks > 7 )
			{
				//$Validated_Clicks = "A high amount of clicks in between input submits has been detected.";
				$Validated_Clicks = true;
			}
			else
			{
				$Validated_Clicks = false;
			}

			/**
			 * If all were passed, the user is likely in the clear.
			 * ------
			 * Handle some logging stuff here.
			 */
			if ( $Validated_Coords && $Validated_PostCode && !$Validated_Clicks )
			{
				return true;
			}
			else
			{
				return false;
			}
		}

		/**
		 * Check to see if the clicked coordinates are within the accepted range.
		 */
		public function CheckCoords($x, $x_min, $x_max, $y, $y_min, $y_max)
		{
			if
			( 
				$x >= $x_min && $x <= $x_max &&
				$y >= $y_min && $y <= $y_max
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
		 * Render all inputs.
		 */
		public function RenderInputs($Type)
		{
			global $PDO;
			global $Poke_Class;

			$Input_Salt = RandSalt(12);

			/**
			 * Render moves.
			 */
			if ( $Type == 'Moves' )
			{
				$Pokemon = $Poke_Class->FetchPokemonData($_SESSION['Battle']['Attacker']['Active']['ID']);

				for ( $i = 1; $i <= 4; $i++ )
				{
					$_SESSION['Battle']['PostCode_Move_' . $i] = $Input_Salt;
					
					$Move_Data = $Poke_Class->FetchMoveData($Pokemon['Move_' . $i]);

					/**
					 * Grey out moves if either Pokemon has fainted.
					 */
					if ( $_SESSION['Battle']['Attacker']['Active']['HP_Cur'] <= 0 || $_SESSION['Battle']['Defender']['Active']['HP_Cur'] <= 0 )
					{
						$Onclick = '';
						$Style = ' filter: grayscale(100%);';
					}
					else
					{
						$Onclick = "onclick='Input(\"Move_{$i}\", {$Move_Data['ID']}, event)'";
						$Style = '';
					}

					echo "
						<button style='padding: 5px; width: 20%;{$Style}' id='Move_{$i}' {$Onclick} PostCode='{$_SESSION['Battle']['PostCode_Move_' . $i]}'>
							{$Move_Data['Name']}
						</button>
					";
				}
			}
			else
			{
				// if either pokemon has fainted
				if ( $_SESSION['Battle']['Attacker']['Active']['HP_Cur'] <= 0 || $_SESSION['Battle']['Defender']['Active']['HP_Cur'] <= 0 )
				{
					// Remove the user's active Pokemon from the session variable if it has fainted.
					if ( $_SESSION['Battle']['Attacker']['Active']['HP_Cur'] <= 0 && count($_SESSION['Battle']['Attacker']['Inactive']) === 0 )
					{
						$_SESSION['Battle']['Attacker']['Active'] = [ ];
					}
					else if ( $_SESSION['Battle']['Defender']['Active']['HP_Cur'] <= 0 && count($_SESSION['Battle']['Defender']['Inactive']) === 0 )
					{
						$_SESSION['Battle']['Defender']['Active'] = [ ];
					}

					// if neither player has any pokemon left alive
					// restart the battle
					if
					(
						( count($_SESSION['Battle']['Attacker']['Active']) === 0 && count($_SESSION['Battle']['Attacker']['Inactive']) === 0 ) &&
						( count($_SESSION['Battle']['Defender']['Active']) === 0 && count($_SESSION['Battle']['Defender']['Inactive']) === 0 )
					)
					{
						$_SESSION['Battle']['PostCode_R1'] = $Input_Salt;
						
						echo "
							<button style='font-size: 14px; padding: 5px; width: 20%;' id='Restart' onclick='Input(\"Restart\", 1, event)' PostCode='{$_SESSION['Battle']['PostCode_C1']}'>
								Restart Battle
							</button>
							<br /><br />

							The battle ended in a tie.<br /><br />
						";
					}
					// you have no pokemon left, but the opponent does
					else if ( count($_SESSION['Battle']['Attacker']['Inactive']) === 0 && count($_SESSION['Battle']['Defender']['Inactive']) > 0 )
					{
						$_SESSION['Battle']['PostCode_R1'] = $Input_Salt;
						
						echo "
							<button style='font-size: 14px; padding: 5px; width: 20%;' id='Restart' onclick='Input(\"Restart\", 1, event)' PostCode='{$_SESSION['Battle']['PostCode_C1']}'>
								Restart Battle
							</button>
							<br /><br />

							You have lost the battle.
						";
					}
					// you have pokemon left, but the opponent doesn't
					else if ( count($_SESSION['Battle']['Attacker']['Inactive']) > 0 && count($_SESSION['Battle']['Defender']['Inactive']) === 0 )
					{
						$_SESSION['Battle']['PostCode_R1'] = $Input_Salt;

						echo "
							<button style='font-size: 14px; padding: 5px; width: 20%;' id='Restart' onclick='Input(\"Restart\", 1, event)' PostCode='{$_SESSION['Battle']['PostCode_C1']}'>
								Restart Battle
							</button>
							<br />

							You have won the battle.
							<br /><br />
						";
					}
					// both players have pokemon left alive
					else if ( count($_SESSION['Battle']['Attacker']['Inactive']) > 0 && count($_SESSION['Battle']['Defender']['Inactive']) > 0 )
					{
						// move the appropriate pokemon to the 'Fainted' session variable for the user.
						if ( $_SESSION['Battle']['Attacker']['Active']['HP_Cur'] <= 0 )
						{
							$_SESSION['Battle']['Attacker']['Fainted'][] = [
								'ID' => $_SESSION['Battle']['Attacker']['Active']['ID'],
							];

							$_SESSION['Battle']['Attacker']['Active'] = [
								'ID' 			=> $_SESSION['Battle']['Attacker']['Inactive'][0][0]['ID'],
								'HP_Cur'	=> $_SESSION['Battle']['Attacker']['Inactive'][0][0]['HP_Cur'],
								'HP_Max'	=> $_SESSION['Battle']['Attacker']['Inactive'][0][0]['HP_Max'],
							];

							array_splice($_SESSION['Battle']['Attacker']['Inactive'], 0, 1);
						}

						// move the appropriate pokemon to the 'Fainted' session variable for the foe.
						if ( $_SESSION['Battle']['Defender']['Active']['HP_Cur'] <= 0 )
						{

							$_SESSION['Battle']['Defender']['Fainted'][] = [
								'ID' => $_SESSION['Battle']['Defender']['Active']['ID'],
							];

							$_SESSION['Battle']['Defender']['Active'] = [
								'ID' 			=> $_SESSION['Battle']['Defender']['Inactive'][0][0]['ID'],
								'HP_Cur'	=> $_SESSION['Battle']['Defender']['Inactive'][0][0]['HP_Cur'],
								'HP_Max'	=> $_SESSION['Battle']['Defender']['Inactive'][0][0]['HP_Max'],
							];

							array_splice($_SESSION['Battle']['Defender']['Inactive'], 0, 1);
						}

						//echo "<hr />";
						//echo "<pre>";var_dump($_SESSION['Battle']['Attacker']);echo "</pre>";
						//echo "<pre>";var_dump($_SESSION['Battle']['Defender']);echo "</pre>";
						//echo "<hr />";

						$_SESSION['Battle']['PostCode_C1'] = $Input_Salt;
				
						echo "
							<button style='padding: 5px; width: 20%;' id='Continue' onclick='Input(\"Continue\", 1, event)' PostCode='{$_SESSION['Battle']['PostCode_C1']}'>
								Continue Battle
							</button>
							<br /><br />
						";
					}
				}
			}
		}

		/**
		 * Determine if a Pokemon benefits from STAB or not.
		 */
		public function DetermineSTAB($Poke_Type_1, $Poke_Type_2, $Move_Type)
		{
			$STAB = 1;

			if ( $Move_Type == $Poke_Type_1 || $Move_Type == $Poke_Type_2 )
			{
				$STAB = 1.5;
			}

			return $STAB;
		}

		/**
		 * Determine the effectiveness of a move that's been used.
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
					$Battle_Dialog = $Text . "<br /><br />" . $_SESSION['Battle']['Text'];
				}
				else
				{
					$Battle_Dialog = $Text . "<br />" . $_SESSION['Battle']['Text'];
				}
			}
			else
			{
				if ( $Line_Break )
				{
					$Battle_Dialog .= $Text . "<br />";
				}
				else
				{
					$Battle_Dialog .= $Text;
				}
			}

			return $Battle_Dialog;
		}
	}