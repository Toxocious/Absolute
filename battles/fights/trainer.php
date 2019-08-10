<?php
	Class Trainer Extends Fight
	{
		public $Battle_Fight = 'Trainer';
		public $Level_Limit = -1;

		/**
		 * Start the battle.
		 */
		public function Start()
		{
			unset($_SESSION['Battle']);

			return 'Success';
		}

		/**
		 * Create the defender's roster.
		 */
		public function Create_Roster_Defender($User_ID)
		{
			global $PDO;
			global $Level_Limit;
			
			try
			{
				$Fetch_Defender = $PDO->prepare("SELECT `id`, `RPG_Ban`, `Username` FROM `users` WHERE `id` = ? LIMIT 1");
				$Fetch_Defender->execute([ $User_ID ]);
				$Fetch_Defender->setFetchMode(PDO::FETCH_ASSOC);
				$Defender = $Fetch_Defender->fetch();
			}
			catch ( PDOException $e )
			{
				HandleError( $e->getMessage() );
			}

			if ( !isset($Defender) )
			{
				return "The user that you're trying to battle doesn't exist.";
			}
			else if ( $Defender['RPG_Ban'] != 0 )
			{
				return "The user that you're trying to battle is banned.";
			}

			try
			{
				$D_Roster = $PDO->prepare("SELECT `ID`, `Slot` FROM `pokemon` WHERE `Owner_Current` = ? AND `Location` = 'Roster' ORDER BY `Slot` LIMIT 6");
				$D_Roster->execute([ $Defender['id'] ]);
				$D_Roster->setFetchMode(PDO::FETCH_ASSOC);
			}
			catch ( PDOException $e )
			{
				HandleError( $e->getMessage() );
			}

			$_SESSION['Battle']['Defender'] = [
				'User_ID'		=> $Defender['id'],
				'Username'	=> $Defender['Username'],
			];

			$Total_Pokemon = 0;
			foreach ( $D_Roster as $Key => $Value )
			{
				if ( $Key > $this->Roster_Limit )
				{
					return 'Error';
				}
				else
				{
					$Total_Pokemon++;

					if ( $Key == 0 )
					{
						$_SESSION['Battle']['Defender']['Active'] = $this->Create_Pokemon($Value['ID']);
						$_SESSION['Battle']['Defender']['Slot_0'] = $this->Create_Pokemon($Value['ID']);

						if ( $_SESSION['Battle']['Defender']['Active']['Level'] > $this->Level_Limit  )
						{
							$_SESSION['Battle']['Defender']['Active']['Cur_HP'] = 0;
						}

						if ( $_SESSION['Battle']['Defender']['Slot_0']['Level'] > $this->Level_Limit  )
						{
							$_SESSION['Battle']['Defender']['Slot_0']['Cur_HP'] = 0;
						}
					}
					else
					{
						$_SESSION['Battle']['Defender']['Slot_' . $Key] = $this->Create_Pokemon($Value['ID']);

						if ( $_SESSION['Battle']['Defender']['Slot_' . $Key]['Level'] > $this->Level_Limit )
						{
							$_SESSION['Battle']['Defender']['Slot_' . $Key]['Cur_HP'] = 0;
						}
					}
				}
			}

			$_SESSION['Battle']['Defender']['Total_Pokemon'] = $Total_Pokemon;

			if ( $_SESSION['Battle']['Defender']['Total_Pokemon'] <= 0 )
			{
				return 'Error';
			}

			return 'Success';
		}

		/**
		 * What happens when you win the battle.
		 */
		public function Battle_Win()
		{
			$_SESSION['Battle']['Status']['Restart']['Code'] = RandSalt(21);
			$_SESSION['Battle']['Status']['Restart']['Type'] = $_SESSION['Battle']['Status']['Battle_Fight'];
			$_SESSION['Battle']['Status']['Restart']['ID' ] = $_SESSION['Battle']['Status']['Battle_ID'];

			if ( $_SESSION['Battle']['Defender']['Total_Pokemon'] == 6 )
			{
				//$User_Class->Update_Stat( $this->User_Data['id'], 'Battles_Completed', 1 );

				$this->Dialogue("<b>DEBUG :: You have beaten a trainer that has a full roster.</b>");
			}

			$this->Create_Button('Restart');
		}

		/**
		 * What happens when you lose the battle.
		 */
		public function Battle_Lose()
		{
			$_SESSION['Battle']['Status']['Restart']['Code'] = RandSalt(21);
			$_SESSION['Battle']['Status']['Restart']['Type'] = $_SESSION['Battle']['Status']['Battle_Fight'];
			$_SESSION['Battle']['Status']['Restart']['ID' ] = $_SESSION['Battle']['Status']['Battle_ID'];

			$this->Create_Button('Restart');
		}
	}