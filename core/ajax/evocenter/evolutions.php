<?php
	require '../../required/session.php';

	/**
	 * Check for any AJAX requests.
	 */
	if ( isset($_POST['Request']) )
	{
		$Request = Purify($_POST['Request']);

		/**
		 * Display all available evolutions of a Pokemon.
		 */
		if ( $Request === 'Show_Evos' )
		{
			if ( isset($_POST['Pokemon_ID']) )
			{
				$Pokemon_ID = Purify($_POST['Pokemon_ID']);
				$Pokemon = $Poke_Class->FetchPokemonData($Pokemon_ID);

				if ( !$Pokemon )
				{
					echo "
						<thead>
							<tr>
								<th colspan='7'>
									Selected Pok&eacute;mon
								</th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td colspan='7' style='padding: 5px;'>
									The Pok&eacute;mon that you selected does not exist.
								</td>
							</tr>
						</tbody>
					";

					return;
				}

				try
				{
					$Fetch_Evolutions = $PDO->prepare("SELECT * FROM `evolution_data` WHERE `poke_id` = ? AND `alt_id` = ?");
					$Fetch_Evolutions->execute([ $Pokemon['Pokedex_ID'], $Pokemon['Alt_ID'] ]);
					$Fetch_Evolutions->setFetchMode(PDO::FETCH_ASSOC);

					$Num_Of_Evos = $Fetch_Evolutions->rowCount();
				}
				catch ( PDOException $e )
				{
					HandleError( $e );
				}

				$Time_Of_Day = (date('G') > 7 && date('G') < 19) ? 'Day' : 'Night';

				if ( $Num_Of_Evos === 0 )
				{
					$Evolution_Text = "
						<tr>
							<td colspan='7' style='padding: 5px;'>
								This Pok&eacute;mon may not evolve further.
							</td>
						</tr>
					";
				}
				else
				{
					$Evolution_Text = '';

					while ( $Evolution = $Fetch_Evolutions->fetch() )
					{
						$Evolution_Data = $Poke_Class->FetchPokedexData($Evolution['to_poke_id'], $Evolution['to_alt_id'], $Pokemon['Type']);

						/**
						 * Check to if the Pokemon meets all of the requirements needed to evolve.
						 */
						$Error = false;
						if ( $Evolution['level'] && $Evolution['level'] != 0 && ( $Pokemon['Level'] < $Evolution['level']) )
						{
							$Error = true;
						}

						if ( $Evolution['min_happy'] && $Evolution['min_happy'] > $Pokemon['Happiness'] )
						{
							$Error = true;
						}

						if ( $Evolution['item'] && $Evolution['item'] != $Pokemon['Item'] )
						{
							$Error = true;
						}

						if ( $Evolution['held_item'] && $Evolution['held_item'] != $Pokemon['Item'] )
						{
							$Error = true;
						}

						if ( $Evolution['gender'] && ucfirst($Evolution['gender']) != $Pokemon['Gender'] )
						{
							$Error = true;
						}

						if ( $Evolution['time'] && $Evolution['time'] !== $Time )
						{
							$Error = true;
						}

						/**
						 * Display the appropriate evolution button.
						 */
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
									Evolve into {$Evolution_Data['Display_Name']}
								</div>
							";
						}

						$Evolution_Text .= "
							<tr>
								<td style='width: 150px;'>
									<img src='{$Evolution_Data['Icon']}' />
								</td>
								<td style='width: 100px;'>
									<b>Level</b>
								</td>
								<td style='width: 100px;'>
									<b>Gender</b>
								</td>
								<td style='width: 100px;'>
									<b>Held Item</b>
								</td>
								<td style='width: 100px;'>
									<b>Use Item</b>
								</td>
								<td style='width: 100px;'>
									<b>Time of Day</b>
								</td>
								<td style='width: 100px;'>
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
							<tr>
								<td colspan='7'>
									{$Evolve_Button}
								</td>
							</tr>
						";
					}
				}

				echo "
					<thead>
						<tr>
							<th colspan='7'>
								Selected Pok&eacute;mon
							</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td colspan='1' style='width: 150px;'>
								<img src='{$Pokemon['Icon']}' />
							</td>
							<td colspan='1' style='width: 100px;'>
								<b>Level</b>
							</td>
							<td colspan='1' style='width: 100px;'>
								<b>Gender</b>
							</td>
							<td colspan='1' style='width: 100px;'>
								<b>Held Item</b>
							</td>
							<td colspan='1' style='width: 100px;'>
								<b>Use Item</b>
							</td>
							<td colspan='1' style='width: 100px;'>
								<b>Time of Day</b>
							</td>
							<td colspan='1' style='width: 100px;'>
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

					<thead>
						<tr>
							<th colspan='7'>
								Available Evolutions
							</th>
						</tr>
					</thead>
					<tbody>
						{$Evolution_Text}
					</tbody>
				";
			}
		}
	}




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