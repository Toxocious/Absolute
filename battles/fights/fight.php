<?php
	/**
	 * Base Fight Class.
	 * Used to determine, as well as construct, necessary data for the types of fights that the user may initiate.
	 */
	Class Fight Extends Battle
	{
		/**
		 * Construct whatever data.
		 */
		public function __construct()
		{
			global $User_Data;

			$this->User_Data = $User_Data;
		}

		/**
		 * Create the $_SESSION data for the attacker.
		 */
		public function Create_Roster_Attacker()
		{
			global $PDO;

			try
			{
				$A_Roster = $PDO->prepare("SELECT `ID` FROM `pokemon` WHERE `Owner_Current` = ? AND `Location` = 'Roster' ORDER BY `Slot` LIMIT 6");
				$A_Roster->execute([ $this->User_Data['id'] ]);
				$A_Roster->setFetchMode(PDO::FETCH_ASSOC);
			}
			catch ( PDOException $e )
			{
				HandleError( $e->getMessage() );
			}

			$_SESSION['Battle']['Attacker'] = [
				'User_ID'		=> $this->User_Data['id'],
				'Username'	=> $this->User_Data['Username'],
			];

			$Total_Pokemon = 0;
			foreach ( $A_Roster as $Key => $Value )
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
						$_SESSION['Battle']['Attacker']['Active'] = $this->Create_Pokemon($Value['ID']);
						$_SESSION['Battle']['Attacker']['Slot_0']	= $this->Create_Pokemon($Value['ID']);

						if ( $_SESSION['Battle']['Attacker']['Active']['Level'] > $this->Level_Limit )
						{
							$_SESSION['Battle']['Attacker']['Active']['Cur_HP'] = 0;
						}

						if ( $_SESSION['Battle']['Attacker']['Slot_0']['Level'] > $this->Level_Limit )
						{
							$_SESSION['Battle']['Attacker']['Slot_0']['Cur_HP'] = 0;
						}
					}
					else
					{
						$_SESSION['Battle']['Attacker']['Slot_' . $Key]	= $this->Create_Pokemon($Value['ID']);

						if ( $_SESSION['Battle']['Attacker']['Slot_' . $Key]['Level'] > $this->Level_Limit )
						{
							$_SESSION['Battle']['Attacker']['Slot_' . $Key]['Cur_HP'] = 0;
						}
					}
				}
			}

			$_SESSION['Battle']['Attacker']['Total_Pokemon'] = $Total_Pokemon;

			if ( $_SESSION['Battle']['Attacker']['Total_Pokemon'] <= 0 )
			{
				return 'Error';
			}

			return 'Success';
		}

		/**
		 * Create the defender's battle session.
		 */
		public function Create_Defender_Roster($User_ID)
		{

		}

		/**
		 * What happens when you defeat a Pokemon.
		 */
		public function Pokemon_Fainted()
		{

		}

		/**
		 * What happens when you end your turn.
		 */
		public function Turn_End()
		{

		}

		/**
		 * What happens when you start your turn.
		 */
		public function Turn_Start()
		{

		}

		/**
		 * What happens when you win a battle.
		 */
		public function Battle_Win()
		{

		}

		/**
		 * What happens when you lose a battle.
		 */
		public function Battle_Lose()
		{

		}
	}