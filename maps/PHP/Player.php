<?php
	/**
	 * Class to handle the player inputs and whatnot.
	 */
	Class Player
	{
		public $Map;
		public $Movement;
		public static $Instance = null;

		/**
		 * Constructor
		 */
		public function __construct()
		{
			/**
			 * If you don't have a map session active, set your map coordinates to the spawn area.
			 */
			if ( !isset($_SESSION['Maps']) )
			{
				$_SESSION['Maps'] = [];
				$this->SetPosition(true);
			}
			/**
			 * Else, you do have an active map session, load your position from the database.
			 */
			else
			{
				$this->LoadPosition('Database');
			}
		}

		/**
		 * Fetch the user's instance.
		 */
		public function FetchInstance()
		{
			if ( self::$Instance == null )
			{
				self::$Instance = new Player();
			}

			return self::$Instance;
		}

		/**
		 * Get the map that the user is on.
		 */
		public function FetchMap()
		{
			global $User_Data;

			if ( isset($_SESSION['Maps']['Map_ID']) )
			{
				return $_SESSION['Maps']['Map_ID'];
			}
			else if ( isset($User_Data['Map_ID']) )
			{
				return $User_Data['Map_ID'];
			}
		}

		/**
		 * Fetch the user's map position.
		 */
		public function FetchPosition()
		{
			global $PDO;
			global $User_Data;

			try
			{
				$Fetch_Pos = $PDO->prepare("SELECT `Map_X`, `Map_Y`, `Map_Z` FROM `users` WHERE `id` = ? LIMIT 1");
				$Fetch_Pos->execute([ $User_Data['ID'] ]);
				$Fetch_Pos->setFetchMode(PDO::FETCH_ASSOC);
				$Position = $Fetch_Pos->fetch();
			}
			catch( PDOException $e )
			{
				HandleError( $e->getMessage() );
			}

			$Position = [
				'x' => $Position['Map_X'],
				'y' => $Position['Map_Y'],
				'z' => $Position['Map_Z'],
			];
			
			//return $_SESSION['Maps']['Position'];
			return $Position;
		}

		/**
		 * Load the user's position.
		 */
		public function LoadPosition()
		{
			global $User_Data;

			if ( $User_Data['Map_ID'] == '' || $User_Data['Map_ID'] != $_SESSION['Maps']['Map_ID'] )
			{
				$this->SetPosition(true);
			}
			else
			{
				$this->SetMap( $User_Data['Map_ID'] );
				$this->SetPosition( false, $User_Data['Map_X'], $User_Data['Map_Y'], $User_Data['Map_Z'] );
			}
		}

		/**
		 * Set the user's map position.
		 */
		public function SetPosition($Spawn = false, $x = false, $y = false, $z = false)
		{
			global $PDO;
			global $User_Data;

			/**
			 * Quick check to make sure all params aren't empty.
			 */
			if ( !$Spawn && !$x && !$y && !$z )
			{
				return false;
			}

			/**
			 * If you're going to set the spawn coordinates to the map's spawn area.
			 */
			if ( $Spawn )
			{
				/**
				 * Update the user's position in the database.
				 */
				try
				{
					$Fetch_Spawn = $PDO->prepare("SELECT `name`, `Spawn_Coords` FROM `maps` WHERE `name` = ? LIMIT 1");
					$Fetch_Spawn->execute([ $_SESSION['Maps']['Map_ID'] ]);
					$Fetch_Spawn->setFetchMode(PDO::FETCH_ASSOC);
					$Spawn_Data = $Fetch_Spawn->fetch();
					$Spawn_Data['Spawn_Coords'] = explode(',', $Spawn_Data['Spawn_Coords']);

					$Update_Pos = $PDO->prepare("UPDATE `users` SET `Map_ID` = ?, `Map_X` = ?, `Map_Y` = ?, `Map_Z` = ? WHERE `id` = ? LIMIT 1");
					$Update_Pos->execute([ $Spawn_Data['name'], $Spawn_Data['Spawn_Coords'][0], $Spawn_Data['Spawn_Coords'][1], $Spawn_Data['Spawn_Coords'][2], $User_Data['ID'] ]);
				}
				catch( PDOException $e )
				{
					HandleError( $e->getMessage() );
				}
			}
			/**
			 * You're not setting the position to the spawn coordinates.
			 */
			else
			{
				$_SESSION['Maps']['Position'] =
				[
					'x' => $x,
					'y' => $y,
					'z' => $z,
				];

				/**
				 * If you're already at that position, do nothing.
				 */
				if
				(
					$User_Data['Map_X'] == $x &&
					$User_Data['Map_Y'] == $y &&
					$User_Data['Map_Z'] == $z
				)
				{
					return true;
				}

				/**
				 * Update the user's position in the database.
				 */
				try
				{
					$Update_Pos = $PDO->prepare("UPDATE `users` SET `Map_X` = ?, `Map_Y` = ?, `Map_Z` = ? WHERE `id` = ? LIMIT 1");
					$Update_Pos->execute([ $x, $y, $z, $User_Data['ID'] ]);
				}
				catch( PDOException $e )
				{
					HandleError( $e->getMessage() );
				}
			}

			return true;
		}

		/**
		 * Set the map that the user is on.
		 */
		public function SetMap($Map_ID)
		{
			global $PDO;
			global $User_Data;

			$_SESSION['Maps']['Map_ID'] = $Map_ID;

			if ( $User_Data['Map_ID'] == $Map_ID )
			{
				return;
			}

			try
			{
				$Update_Map = $PDO->prepare("UPDATE `users` SET `Map_ID` = ? WHERE `id` = ? LIMIT 1");
        $Update_Map->execute([ $Map_ID, $User_Data['ID'] ]);
			}
			catch( PDOException $e )
			{
				HandleError( $e->getMessage() );
			}
		}

		/**
		 * Handle setting and fetching properties.
		 */
		public function HandleProps($Property, $Value = null)
		{
			/**
			 * If value is null, you're fetching the Property.
			 */
			if ( !$Value )
			{
				$_SESSION['Maps']['Properties'][$Property] = $Value;
			}

			/**
			 * If the value isn't null, you're setting the Property.
			 */
			else
			{
				if ( isset($_SESSION['Maps']['Properties'][$Property]) )
				{
					return $_SESSION['Maps']['Properties'][$Property];
				}

				return false;
			}
		}

		/**
		 * Determine if you have hit your next encounter.
		 */
		public function DetermineEncounter()
		{
			/**
			 * Get the encounter rate of the current map.
			 */
			$Encounter_Rate = $Map->Player->getEncounterRate();

			var_dump($Encounter_Rate);
		}
	}