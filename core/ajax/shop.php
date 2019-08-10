<?php
	require '../required/session.php';
	
	/**
	 * The user is attempting to purchase a Pokemon.
	 */
	if ( isset($_POST['id']) && isset($_POST['shop']) )
	{
		/**
		 * Switch/Case for all shops.
		 */
		$Shop = Purify($_POST['shop']);
		switch($Shop)
		{
			case 'pokemon':
				$Shop = "pokemon_shop";
				$Description = "Welcome to the Pokemon Shop.";
				$Shiny_Odds = $Constants->Shiny_Odds[$Shop];
				break;
		}

		/**
		 * Fetch the Pokemon from the database.
		 */
		$DB_ID = Purify($_POST['id']);
		try
		{
			$Fetch_Pokemon = $PDO->prepare("SELECT * FROM `obtainable_pokemon` WHERE `ID` = ? AND `Obtained_Place` = ? LIMIT 1");
			$Fetch_Pokemon->execute([ $DB_ID, $Shop ]);
			$Fetch_Pokemon->setFetchMode(PDO::FETCH_ASSOC);
			$Pokemon = $Fetch_Pokemon->fetch();
		}
		catch ( PDOException $e )
		{
			HandleError( $e->getMessage() );
		}

		/**
		 * Error checking.
		 */
		if ( !isset($Pokemon) || $Pokemon['Active'] != 'Yes' || count($Pokemon) == 0 || !$Pokemon )
		{
			echo "The selected Pokemon couldn't be found.";
			exit;
		}
		else
		{
			$Price_Array = GeneratePrice($Pokemon['Price']);
			foreach( $Price_Array as $Key => $Currency )
			{
				if ( ($User_Data[$Currency['Value']] - $Currency['Amount']) < 0 )
				{
					echo "You need an additional" . number_format($Currency['Amount'] - $User_Data[$Currency['Value']]) . " " . $Currency['Name'] . " to purchase this Pokemon.<br />";
					exit;
				}

				if ( !isset($Constants->Currency[$Currency['Value']]) )
				{
					echo "An error has occured with this Pokemon's price. Please report this to an Admin.<br />";
					exit;
				}	
			}

			foreach ( $Price_Array as $Key => $Currency )
			{
				try
				{
					$Update = $PDO->prepare("UPDATE `users` SET `" . $Currency['Name'] . "` = `" . $Currency['Name'] . "` - ? WHERE `id` = ? LIMIT 1");
					$Update->execute([ $Currency['Amount'], $User_Data['id'] ]);
				}
				catch ( PDOException $e )
				{
					HandleError( $e->getMessage() );
				}
			}

			/**
			 * Determine if the Pokemon will be shiny or not.
			 */
			if ( $Pokemon['Type'] == "Normal" )
			{
				if ( mt_rand(1, $Shiny_Odds) === 1 )
				{
					$Pokemon['Type'] = 'Shiny';
					echo "<script type='text/javascript'>setTimeout(function() { alert('You purchased a Shiny Pokemon.'); }, 69);</script>";
				}
			}

			/**
			 * Determine if the Pokemon will be (?) or not.
			 */
			if ( mt_rand(1, 420) === 1 )
			{
				$Pokemon['Gender'] = '(?)';
				echo "<script type='text/javascript'>setTimeout(function() { alert('You purchased an ungendered Pokemon.'); }, 69);</script>";
			}
			else
			{
				$Pokemon['Gender'] = $Poke_Class->GenerateGender($Pokemon['Pokedex_ID'], $Pokemon['Alt_ID']);
			}

			/**
			 * Create the Pokemon, and show it, it's gender, it's stats, and it's IV's to the user.
			 */
			$Pokemon_Created = $Poke_Class->CreatePokemon( $Pokemon['Pokedex_ID'], $Pokemon['Alt_ID'], 5, $Pokemon['Type'], $Pokemon['Gender'], $Pokemon['Obtained_Text'], null, null, $User_Data['id'] );
			$Total_Stat = array_sum($Pokemon_Created['Stats']);
			$Total_IV = array_sum($Pokemon_Created['IVs']);

			echo "
				<div style='float: left; width: 25%;'>
					<img src='" . $Pokemon_Created['Sprite'] . "' />
				</div>
				<div style='float: left; padding-top: 20px; width: 25%;'>
					<b>{$Pokemon_Created['Name']}</b><br />
					<b>Level:</b> 5<br />
					<b>Gender:</b> {$Pokemon_Created['Gender']}
				</div>
				<div>
					<table class='standard' style='float: left; margin: 12px 0px 5px 5px; width: 49%;'>
						<thead>
							<tr>
								<th> </th>
								<th>HP</th>
								<th>Att</th>
								<th>Def</th>
								<th>SpA</th>
								<th>SpD</th>
								<th>Spe</th>
								<th>Total</th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td><b>Base</b></td>
								<td>" . number_format($Pokemon_Created['Stats'][0]) . "</td>
								<td>" . number_format($Pokemon_Created['Stats'][1]) . "</td>
								<td>" . number_format($Pokemon_Created['Stats'][2]) . "</td>
								<td>" . number_format($Pokemon_Created['Stats'][3]) . "</td>
								<td>" . number_format($Pokemon_Created['Stats'][4]) . "</td>
								<td>" . number_format($Pokemon_Created['Stats'][5]) . "</td>
								<td>" . number_format($Total_Stat) . "</td>
							</tr>
							<tr>
								<td><b>IV's</b></td>
								<td>" . number_format($Pokemon_Created['IVs'][0]) . "</td>
								<td>" . number_format($Pokemon_Created['IVs'][1]) . "</td>
								<td>" . number_format($Pokemon_Created['IVs'][2]) . "</td>
								<td>" . number_format($Pokemon_Created['IVs'][3]) . "</td>
								<td>" . number_format($Pokemon_Created['IVs'][4]) . "</td>
								<td>" . number_format($Pokemon_Created['IVs'][5]) . "</td>
								<td>" . number_format($Total_IV) . "</td>
							</tr>
						</tbody>
					</table>
				</div>
			";
		}
	}
	else
	{
		echo "An error has occurred.";
	}
?>