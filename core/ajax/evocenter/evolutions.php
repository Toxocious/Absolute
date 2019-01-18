<?php
	require '../../required/session.php';

	/**
	 * Handle the evolution of a Pokemon.
	 */
	if ( isset($_POST['evo_id']) && isset($_POST['evo_to']) && isset($_POST['evo_alt']) )
	{
		$My_Poke = Purify($_POST['evo_id']);
		$Evo_To = Purify($_POST['evo_to']);
		$Evo_Alt = Purify($_POST['evo_alt']);

		$Pokemon = $PokeClass->FetchPokemonData($My_Poke);

		try
		{
			$Evolution_Data = $PDO->prepare("SELECT * FROM `evolution_data` WHERE `poke_id` = ?");
			$Evolution_Data->execute([ $Pokemon['Pokedex_ID'] ]);
			$Evolution_Data->setFetchMode(PDO::FETCH_ASSOC);
			$Evolution = $Evolution_Data->fetch();
		}
		catch ( PDOException $e )
		{
			HandleError( $e->getMessage() );
		}

		//var_dump($Evolution);

		$Evo_Data = $PokeClass->FetchPokedexData($Evo_To, $Evo_Alt);

		/**
		 * Double check to ensure that the Pokemon is able to evolve.
		 */
		$Error = false;
		if ( $Evolution['level'] != null && $Evolution['level'] != 0 && ( $Pokemon['Level'] < $Evolution['level']) )
		{
			$Error = true;
		}

		if ( $Evolution['min_happy'] != null && $Evolution['min_happy'] > $Pokemon['Happiness'] )
		{
			$Error = true;
		}

		if ( $Evolution['item'] != null && $Evolution['item'] != $Pokemon['Item'] )
		{
			$Error = true;
		}

		if ( $Evolution['held_item'] != null && $Evolution['held_item'] != $Pokemon['Item'] )
		{
			$Error = true;
		}

		if ( $Evolution['gender'] != null && ucfirst($Evolution['gender']) != $Pokemon['Gender'] )
		{
			$Error = true;
		}

		if ( $Evolution['time'] != null )
		{
			$Time = (date('G') > 7 && date('G') < 19) ? 'day' : 'night';
			if ( $Evolution['time'] != $Time )
			{
				$Error = true;
			}
		}

		/**
			* The Pokemon is truly able to evolve.
			* Process the evolution here.
			*/
		if ( $Error == true )
		{
			echo "
				<div class='error' style='margin-bottom: 5px;'>
					This Pokemon doesn't meet all of the necessary requirements to evolve.
				</div>
			";
		}
		else if ( $Error == false )
		{
			echo "
				<div class='success' style='margin-bottom: 5px;'>
					You have successfully evolved your 'x' into 'y'!
				</div>
			";

			try
			{
        $Update = $PDO->prepare("UPDATE `pokemon` SET `Name` = ?, `Pokedex_ID`= ? , `Alt_ID` = ? WHERE `ID` = ? AND `Owner_Current` = ? LIMIT 1");
        $Update->execute([ $Evo_Data['Name'], $Evo_Data['Pokedex_ID'], $Evo_Data['Alt_ID'], $Pokemon['ID'], $User_Data['id'] ]);
			}
			catch ( PDOException $e )
			{
        handleError($e);
      }
		}
	}

	/**
	 * Handle displaying evolutionary requirements and details of a Pokemon.
	 */
	if ( isset($_POST['id']) )
	{
		$Poke_ID = Purify($_POST['id']);
		$Pokemon = $PokeClass->FetchPokemonData($Poke_ID);

		try
		{
			$Fetch_Evolutions = $PDO->prepare("SELECT * FROM `evolution_data` WHERE `poke_id` = ? AND `alt_id` = ?");
			$Fetch_Evolutions->execute([ $Pokemon['Pokedex_ID'], $Pokemon['Alt_ID'] ]);
			$Fetch_Evolutions->setFetchMode(PDO::FETCH_ASSOC);
		}
		catch ( PDOException $e )
		{
			HandleError( $e->getMessage() );
		}

		$EvoStatus = false;
		while ( $Evolution = $Fetch_Evolutions->fetch() )
		{
			//var_dump($Evolution);

			$EvoStatus = true;

			$Evolution_Data = $PokeClass->FetchPokedexData($Evolution['to_poke_id'], $Evolution['to_alt_id']);

			if ( $Evolution_Data['Alt_ID'] != '0' )
			{
				$Image_ID = str_pad($Evolution_Data['Pokedex_ID'], 3, "0", STR_PAD_LEFT) . "." . $Evolution_Data['Alt_ID'];
			}
			else
			{
				$Image_ID = str_pad($Evolution_Data['Pokedex_ID'], 3, "0", STR_PAD_LEFT);
			}

			if ( $Pokemon['Type'] != "Normal" )
			{
				$Evo_Name = $Pokemon['Type'] . $Evolution_Data['Name'];
			}
			else
			{
				$Evo_Name = $Evolution_Data['Name'];
			}

			/**
			 * Check to see if you meet all of the evolution requirements.
			 */
			$Error = false;
			if ( $Evolution['level'] != null && $Evolution['level'] != 0 && ( $Pokemon['Level'] < $Evolution['level']) )
			{
				$Error = true;
				$Checkmark_Level = "<img src='" . Domain(1) . "images/Assets/offline.png' />";
			}
			else
			{
				$Checkmark_Level = "<img src='" . Domain(1) . "images/Assets/online.png' />";
			}

			if ( $Evolution['min_happy'] != null && $Evolution['min_happy'] > $Pokemon['Happiness'] )
			{
				$Error = true;
				$Checkmark_Happiness = "<img src='" . Domain(1) . "images/Assets/offline.png' />";
			}
			else
			{
				$Checkmark_Happiness = "<img src='" . Domain(1) . "images/Assets/online.png' />";
			}

			if ( $Evolution['item'] != null && $Evolution['item'] != $Pokemon['Item'] )
			{
				$Error = true;
				$Checkmark_Item = "<img src='" . Domain(1) . "images/Assets/offline.png' />";
			}
			else
			{
				$Checkmark_Item = "<img src='" . Domain(1) . "images/Assets/online.png' />";
			}

			if ( $Evolution['held_item'] != null && $Evolution['held_item'] != $Pokemon['Item'] )
			{
				$Error = true;
				$Checkmark_Held_Item = "<img src='" . Domain(1) . "images/Assets/offline.png' />";
			}
			else
			{
				$Checkmark_Held_Item = "<img src='" . Domain(1) . "images/Assets/online.png' />";
			}

			if ( $Evolution['gender'] != null && ucfirst($Evolution['gender']) != $Pokemon['Gender'] )
			{
				$Error = true;
				$Checkmark_Gender = "<img src='" . Domain(1) . "images/Assets/offline.png' />";
			}
			else
			{
				$Checkmark_Gender = "<img src='" . Domain(1) . "images/Assets/online.png' />";
			}

			if ( $Evolution['time'] != null )
			{
				$Time = (date('G') > 7 && date('G') < 19) ? 'day' : 'night';
				if ( $Evolution['time'] != $Time )
				{
					$Error = true;
					$Checkmark_Time = "<img src='" . Domain(1) . "images/Assets/offline.png' />";
				}
				else
				{
					$Checkmark_Time = "<img src='" . Domain(1) . "images/Assets/online.png' />";
				}
			}
			else
			{
				$Checkmark_Time = "<img src='" . Domain(1) . "images/Assets/online.png' />";
			}

	
			if ( $Error == true )
			{
				$Button = "
					<div class='button' style='padding-top: 30px;'>
						You don't meet the requirements to evolve this Pokemon.
					</div>
				";
			}
			else
			{
				$Button = "
					<div class='button' style='padding-top: 50px;' onclick='evolve({$Pokemon['ID']}, {$Evolution_Data['Pokedex_ID']}, {$Evolution_Data['Alt_ID']});'>
						Evolve!
					</div>
				";
			}

			echo "
				<div class='evolution'>
					<div class='image'>
						<img src='" . Domain(1) . "/images/Pokemon/Sprites/{$Pokemon['Type']}/{$Image_ID}.png' />
						<div>
							<b>{$Evo_Name}</b>
						</div>
					</div>
					<div class='requirements'>
						<div><b>Requirements</b></div>
			";

			if ( $Evolution['level'] != null && $Evolution['level'] != 0 )
			{
				echo "
					<div>
						<div>
							Level
						</div>

						<div>
						<img src='" . Domain(1) . "images/Assets/level.png' style='height: 30px; width: 30px;' /><br />
							{$Evolution['level']}
						</div>

						<div>
							$Checkmark_Level
						</div>
					</div>
				";
			}

			if ( $Evolution['gender'] != null )
			{
				echo "
					<div>
						<div>
							Gender
						</div>

						<div>
							<img src='" . Domain(1) . "images/Assets/{$Evolution['gender']}.svg' style='height: 30px; width: 30px;' /><br />
							" . ucfirst($Evolution['gender']) . "
						</div>

						<div>
							$Checkmark_Gender
						</div>
					</div>
				";
			}

			if ( $Evolution['item'] != null )
			{
				echo "
					<div>
						<div>
							Item
						</div>

						<div>
							<img src='" . Domain(1) . "images/Items/{$Evolution['item']}.png' /><br />
							{$Evolution['item']}
						</div>

						<div>
							$Checkmark_Item
						</div>
					</div>
				";
			}

			if ( $Evolution['held_item'] != null )
			{
				echo "
					<div>
						<div>
							Held Item
						</div>

						<div>
							<img src='" . Domain(1) . "images/Items/{$Evolution['held_item']}.png' /><br />
							{$Evolution['held_item']}
						</div>

						<div>
							$Checkmark_Held_Item
						</div>
					</div>
				";
			}

			if ( $Evolution['time'] != null )
			{
				if ( $Evolution['time'] == 'day' )
				{
					$Time = 'sun';
				}
				else
				{
					$Time = 'moon';
				}

				echo "
					<div>
						<div>
							Time
						</div>

						<div>
							<img src='" . Domain(1) . "images/Assets/{$Time}.png' style='height: 30px; width: 30px;' /><br />
							{$Evolution['time']}
						</div>

						<div>
							$Checkmark_Time
						</div>
					</div>
				";
			}

			if ( $Evolution['min_happy'] != null )
			{
				echo "
					<div style='border-right: none;'>
						<div style='width: 100px;'>
							Happiness
						</div>

						<div>
						<img src='" . Domain(1) . "images/Assets/heart.png' style='height: 30px; width: 30px;' /><br />
							{$Evolution['min_happy']}
						</div>

						<div style='width: 100px;'>
							$Checkmark_Happiness
						</div>
					</div>
				";
			}

			echo "
					</div>
					<div class='button'>
						$Button
					</div>
				</div>
			";
		}

		if ( !$EvoStatus )
		{
			echo "<div class='error' style='margin: 5px auto; width: 90%;'><b>{$Pokemon['Display_Name']}</b> can not evolve.</div>";
		}

		exit();
	}
	else
	{
		return "An error has occurred.";
	}