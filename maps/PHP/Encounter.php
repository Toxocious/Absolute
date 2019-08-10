<?php
	Class Encounter
	{
		public $Map;
		public $Data;

		public function __construct($Map)
		{
			$this->Map = $Map;
		}

		/**
		 * Display encounter data.
		 */
		public function DisplayEncounter()
		{
			global $Poke_Class;

			$this->Map->OutputAdd('addInterference', 'encounter');

			$this->Pokemon = $Poke_Class->FetchPokedexData($this->Data['Pokedex_ID'], $this->Data['Alt_ID'], $this->Data['Type']);

			$Buttons = "
				<div style='margin-bottom: 3px;'>
					<input type='button' value='Catch' onclick='game.encounter.action(\"catch\", \"" . $this->Data['PostCode'] . "\"); return false;' style='width: 46%;' />
					<input type='button' value='Release' onclick='game.encounter.action(\"release\", \"" . $this->Data['PostCode'] . "\"); return false;' style='width: 46%;' />
				</div>
				<div>
					<input type='button' value='Fight' onclick='game.encounter.action(\"fight\", \"" . $this->Data['PostCode'] . "\"); return false;' style='width: 46%;' />
					<input type='button' value='Run Away' onclick='game.encounter.action(\"run\", \"" . $this->Data['PostCode'] . "\"); return false;' style='width: 46%;' />
				</div>
			";

			if ( $this->Data['Type'] == 'Shiny' )
			{
				$this->Data['Display_Name'] = 'Shiny' . $this->Data['Name'];
			}
			else
			{
				$this->Data['Display_Name'] = $this->Data['Name'];
			}

			$this->Map->TextAdd(
				"A wild {$this->Pokemon['Display_Name']} (Level: " . number_format($this->Data['Level']) . ") has appeared.
				<div style='padding: 3px;'>
					<img src='{$this->Pokemon['Sprite']}' />
				</div>
				{$Buttons}"
			);
		}

		/**
		 * Generate the encounter.
		 */
		public function GenerateEncounter()
		{
			global $PDO;

			$Random = mt_rand(1, 666);

			try
			{
				$Poke_Range = $PDO->prepare("SELECT * FROM `pokedex` WHERE `Pokedex_ID` = ? LIMIT 1");
				$Poke_Range->execute([ $Random ]);
				$Poke_Range->setFetchMode(PDO::FETCH_ASSOC);
				$Potential_Pokes = $Poke_Range->fetchAll();
			}
			catch ( PDOException $e )
			{
				HandleError($e);
			}

			$Weighter = new Weighter();
			foreach ($Potential_Pokes as $Key => $Potential_Poke)
			{
				$Weighter->add($Key, $Potential_Poke['capture_rate']);
			}

			$Selected_Poke = $Weighter->get();

			if ($Selected_Poke === false)
			{
				$this->Map->ThrowError('Encounter Failed', '109');
				return false;
			}

			$this->Data = $Potential_Pokes[$Selected_Poke];
			$this->Data['PostCode'] = RandomSalt(8);
			$this->Data['Level'] = mt_rand(5, 42);
			$this->Data['Type'] = 'Normal';

			/**
			 * Determine if you got lucky and rolled a shiny.
			 */
			if ( $this->IsShiny() )
			{
				$this->Data['Type'] = 'Shiny';
				$this->Data['IsShiny'] = true;
			}

			$this->Save();
			
			return $this->Data;
		}

		/**
		 * Determine if you got lucky and found a shiny Pokemon.
		 */
		public function IsShiny()
		{
			$Random = mt_rand(1, 8192);

			if ( $Random == 69 )
			{
				return true;
			}
			else
			{
				return false;
			}
		}

		public function Save()
		{
			$_SESSION['Maps']['Encounter'] = $this->Data;
		}

		public function Load()
		{
			if (isSet($_SESSION['Maps']['Encounter']))
			{
				$this->Data = $_SESSION['Maps']['Encounter'];

				return true;
			}

			return false;
		}











		public function initEncounter($PokeID, $AltID, $Level, $Type, $SubType, $ObtainedText) {
			$this->data = [];
			$this->data['post_code'] = RandomSalt(5);
			$this->data['poke_id'] = $PokeID;
			$this->data['alt_id'] = $AltID;
			$this->data['level'] = $Level;
			$this->data['type'] = $Type;
			$this->data['subtype'] = $SubType;
			$this->data['obtained_text'] = $ObtainedText;

			return true;
	}
	}