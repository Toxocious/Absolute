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

		$Pokemon = $Poke_Class->FetchPokemonData($My_Poke);

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

		$Evo_Data = $Poke_Class->FetchPokedexData($Evo_To, $Evo_Alt, $Pokemon['Type']);

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
					You have successfully evolved your {$Pokemon['Display_Name']} into {$Evo_Data['Display_Name']}!
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
	 * Display the available evoltuions of the selected Pokemon.
	 */
	if ( isset($_POST['id']) )
	{
		$Poke_ID = Purify($_POST['id']);
		$Pokemon = $Poke_Class->FetchPokemonData($Poke_ID);
		$Time_Of_Day = (date('G') > 7 && date('G') < 19) ? 'Day' : 'Night';

		try
		{
			$Fetch_Evolutions = $PDO->prepare("SELECT * FROM `evolution_data` WHERE `poke_id` = ? AND `alt_id` = ?");
			$Fetch_Evolutions->execute([ $Pokemon['Pokedex_ID'], $Pokemon['Alt_ID'] ]);
			$Fetch_Evolutions->setFetchMode(PDO::FETCH_ASSOC);
			$Num_Of_Evos = $Fetch_Evolutions->rowCount();
		}
		catch ( PDOException $e )
		{
			HandleError( $e->getMessage() );
		}

		echo "
			<table class='border-gradient' style='margin-bottom: 5px; width: 536px;'>
				<thead>
					<th colspan='7'>Selected Pok&eacute;mon</th>
				</thead>
				<tbody>
					<tr>
						<td style='width: 150px;'>
							<img src='{$Pokemon['Icon']}' />
						</td>
						<td>
							<b>Level</b>
						</td>
						<td>
							<b>Gender</b>
						</td>
						<td>
							<b>Held Item</b>
						</td>
						<td>
							<b>Use Item</b>
						</td>
						<td>
							<b>Time of Day</b>
						</td>
						<td>
							<b>Happiness</b>
						</td>
					</tr>
					<tr>
						<td>
							<b>{$Pokemon['Display_Name']}</b>
						</td>
						<td>
							{$Pokemon['Level']}
						</td>
						<td>
							{$Pokemon['Gender']}
						</td>
						<td>
							" . ($Pokemon['Item'] ? $Pokemon['Item'] : 'No Item') . "
						</td>
						<td>
							N/A
						</td>
						<td>
							{$Time_Of_Day}
						</td>
						<td>
							{$Pokemon['Happiness']}
						</td>
					</tr>
				</tbody>
			</table>

			<table class='border-gradient' style='width: 536px;'>
				<thead>
					<th colspan='7'>Evolutions</th>
				</thead>
				<tbody>
		";

		/**
		 * The Pokemon has no available evolutions.
		 */
		if ( $Num_Of_Evos === 0 )
		{
			echo "
				<tr>
					<td colspan='7' style='padding: 5px;'>
						This Pokemon may not evolve further.
					</td>
				</tr>
			";
		}

		while ( $Evolution = $Fetch_Evolutions->fetch() )
		{
			$Evolution_Data = $Poke_Class->FetchPokedexData($Evolution['to_poke_id'], $Evolution['to_alt_id'], $Pokemon['Type']);
			
			/**
			 * Check to see if you meet all of the evolution requirements.
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
				if ( $Evolution['time'] != $Time )
				{
					$Error = true;
				}
			}
	
			if ( $Error )
			{
				$Evolve_Button = "
					<button class='disabled'>
						Requirements Not Met
					</div>
				";
			}
			else
			{
				$Evolve_Button = "
					<button onclick='evolve({$Pokemon['ID']}, {$Evolution_Data['Pokedex_ID']}, {$Evolution_Data['Alt_ID']});'>
						Evolve into {$Evolution_Data['Display_Name']}!
					</div>
				";
			}

			echo "
				<tr>
					<td style='width: 150px;'>
						<img src='{$Evolution_Data['Icon']}' />
					</td>
					<td>
						<b>Level</b>
					</td>
					<td>
						<b>Gender</b>
					</td>
					<td>
						<b>Held Item</b>
					</td>
					<td>
						<b>Use Item</b>
					</td>
					<td>
						<b>Time of Day</b>
					</td>
					<td>
						<b>Happiness</b>
					</td>
				</tr>
				<tr>
					<td>
						<b>{$Evolution_Data['Display_Name']}</b>
					</td>
					<td>
						" . ($Evolution['level'] > 0 ? $Evolution['level'] : 'N/A') . "
					</td>
					<td>
						" . ($Evolution['gender'] ? ucfirst($Evolution['gender']) : 'N/A') . "
					</td>
					<td>
						" . ($Evolution['held_item'] ? $Evolution['held_item'] : 'N/A') . "
					</td>
					<td>
						" . ($Evolution['item'] ? $Evolution['item'] : 'N/A') . "
					</td>
					<td>
						" . ($Evolution['time'] ? $Evolution['time'] : 'N/A') . "
					</td>
					<td>
						" . ($Evolution['min_happy'] ? $Evolution['min_happy'] : 'N/A') . "
					</td>
				</tr>
			";

			echo "
				<tr>
					<td colspan='7'>
						{$Evolve_Button}
					</td>
				</tr>
			";
		}

		echo "
				<tbody>
			</table>
		";
	}