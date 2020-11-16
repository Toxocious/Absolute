<?php
	require '../core/required/session.php';
	require '../core/functions/staff.php';

	/**
	 * Processing creating/editing Pokemon.
	 */
	if ( isset($_POST['action']) )
	{
		$Action = $Purify->Cleanse($_POST['action']);

		//var_dump($_POST);

		/**
		 * Performing an edit.
		 */
		if ( $Action == 'edit' )
		{
			$DB_ID = $Purify->Cleanse($_POST['db_id']);

			if ( !isset($DB_ID) )
			{
				die("An invalid Pokemon has been selected. Please try again.");
			}

			$Poke = $Purify->Cleanse($_POST['Poke']);
			$Type = $Purify->Cleanse($_POST['Type']);
			$Active = $Purify->Cleanse($_POST['Active']);
			$Cost = ($_POST['cost']);

			$Cost = GeneratePriceString($Cost);
			if ( $Cost == '' )
			{
    	  $Cost = null;
			}
			
			try
			{
        $Fetch_Pokemon = $PDO->prepare("SELECT * FROM `pokedex` WHERE `id` = ?");
        $Fetch_Pokemon->execute([ $Poke ]);
        $Fetch_Pokemon->setFetchMode(PDO::FETCH_ASSOC);
        $Pokemon = $Fetch_Pokemon->fetch();
			}
			catch ( PDOException $e )
			{
        HandleError( $e->getMessage() );
			}
			
			if ( !isset($Pokemon['id']) )
			{
				die("An invalid Pokemon has been selected. Please try again.");
			}

			try
			{
				$Update = $PDO->prepare("UPDATE `obtainable_pokemon` SET	`Pokedex_ID` = ?, `Alt_ID` = ?,	`Type` = ?,	`Active` = ?,	`Price` = ? WHERE	`ID` = ? LIMIT 1");
				$Update->execute([ $Pokemon['Pokedex_ID'], $Pokemon['Alt_ID'], $Type, $Active, $Cost, $DB_ID ]);
			}
			catch ( PDOException $e )
			{
				HandleError( $e->getMessage() );
			}

			echo "<div class='success'>You have successfully edited a Pokemon in the database.</div>";
		}

		/**
		 * Creating a Pokemon.
		 */
		else if ( $Action == 'create' )
		{
			echo "<div class='success'>You have successfully added a Pokemon to the database.</div>";
		}

		exit;
	}

	/**
	 * $_GET['Location'] has been set; fetch detailed list of obtainable Pokemon in the appropriate location.
	 */
	if ( isset($_POST['Location']) )
	{
		$Location = $Purify->Cleanse($_POST['Location']);

		/**
		 * Fetch the Pokemon at the given location.
		 */
		try
		{
			$Pokemon_Data = $PDO->prepare("SELECT * FROM `obtainable_pokemon` WHERE `Obtained_Place` = ? ORDER BY `ID` ASC");
			$Pokemon_Data->execute([ $Location ]);
			$Pokemon_Data->setFetchMode(PDO::FETCH_ASSOC);
			$Pokemon_List = $Pokemon_Data->fetchAll();
		}
		catch ( PDOException $e )
		{
			HandleError( $e->getMessage() );
		}

		if ( count($Pokemon_List) == 0 )
		{
			die("An error has occurred.");
		}

		echo "
			<table class='standard' style='margin: 0 auto; width: 80%;'>
				<thead>
					<tr style='text-align: center;'>
						<th colspan='4'>
							<b>{$Location}</b>
						</th>
					</tr>
				</thead>
				<tbody>
		";

		foreach ( $Pokemon_List as $Key => $Pokemon )
		{
			$Poke_Data = $Poke_Class->FetchPokedexData( $Pokemon['Pokedex_ID'], $Pokemon['Alt_ID'], $Pokemon['Type'] );

			if ( $Pokemon['Active'] == 'Yes' )
			{
				$Active_Status = "<span style='color: #00ff00;'>Active</span>";
			}
			else
			{
				$Active_Status = "<span style='color: #ff0000;'>Inactive</span>";
			}

			echo "
				<tr>
					<td style='width: 30%;'>
						<img src='{$Poke_Data['Icon']}' />
						{$Poke_Data['Display_Name']}
					</td>

					<td style='text-align: left; width: 30%;'>
			";

			if ( $Pokemon['Price'] != null )
			{
				$Prices = GeneratePrice($Pokemon['Price']);
				if ( count($Prices) != 0 )
				{
					foreach ( $Prices as $key => $Price )
					{
						echo "
							<img src='" . DOMAIN_SPRITES . "/images/Assets/{$Price['Name']}.png' style='height: 30px; width: 30px;' />
							<b>{$Price['Name']}</b>: " . number_format($Price['Amount']) . "
							<br />
						";
					}
				}
			}
			else
			{
				echo "This Pokemon doesn't have a set price.";
			}

			echo "
					</td>

					<td style='width: 10%;'>
						{$Active_Status}
					</td>

					<td style='width: 10%;'>
						<a href='javascript:void(0);' onclick='LoadContent(\"manage_pokemon.php\", \"AJAX\", { Edit: \"{$Pokemon['ID']}\"});'>
							Edit
						</a>
					</td>
				</tr>
			";
		}

		echo "
				</tbody>
			</table>
		";

		exit;
	}

	/**
	 * A Pokemon has been selected to be edited.
	 */
	if ( isset($_POST['Edit']) )
	{
		$DB_ID = $Purify->Cleanse($_POST['Edit']);

		/**
		 * Fetch the selected Pokemon.
		 */
		try
		{
			$Fetch_Pokemon = $PDO->prepare("SELECT * FROM `obtainable_pokemon` WHERE `ID` = ? LIMIT 1");
			$Fetch_Pokemon->execute([ $DB_ID ]);
			$Fetch_Pokemon->setFetchMode(PDO::FETCH_ASSOC);
			$Pokemon = $Fetch_Pokemon->fetch();
		}
		catch ( PDOException $e )
		{
			HandleError( $e->getMessage() );
		}

		$Poke_Data = $Poke_Class->FetchPokedexData( $Pokemon['Pokedex_ID'], $Pokemon['Alt_ID'], $Pokemon['Type'] );

		echo "
			<div class='row'>
				<form id='PokeForm' onsubmit='LoadContent(\"manage_pokemon.php\", \"AJAX\", $(\"#PokeForm\").serialize()); return false;'>
					<input type='hidden' name='action' value='edit' />
					<input type='hidden' name='db_id' value='{$Pokemon['ID']}' />
					<input onclick='' type='submit' value='Submit Form' style='margin-bottom: 5px; padding: 5px; width: 80%;' />

					<table class='standard' style='float: left; margin-left: 35px; margin-right: 5px; width: 400px;'>
						<thead>
							<tr style='text-align: center;'>
								<th colspan='2'>
									<b>Selected Pokemon</b>
								</th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td colspan='2'>
									<img src='{$Poke_Data['Sprite']}' /><br />
									<h4>{$Poke_Data['Display_Name']}</h2>
								</td>
							</tr>

							<tr>
								<td style='width: 50%;'>Pokemon</td>
								<td style='width: 50%;'>
									<select name='Poke'>
										<option value='{$Poke_Data['ID']}'>{$Poke_Data['Name']}</option>
										" . $Poke_Class->RenderDropdown() . "
									</select>
								</td>
							</tr>

							<tr>
								<td>Type</td>
								<td>
									<select name='Type' style='width: 70%;'>
		";

		$Type_List = [ 'Normal', 'Shiny', 'Sunset' ];
		foreach ( $Type_List as $Key => $Type )
		{
			if ( $Pokemon['Type'] == $Type )
			{
				echo "<option selected='selected' value='{$Type}'>{$Type}</option>";
			} else {
				echo "<option value='{$Type}'>{$Type}</option>";
			}
		}

		echo "
									</select>
								</td>
							</tr>

							<tr>
								<td>Active</td>
								<td>
									<select name='Active' style='width: 70%;'>
		";

		$Active_List = [ 'No', 'Yes' ];
		foreach ( $Active_List as $Key => $Active )
		{
			echo "<option value='{$Active}' " . ($Active == $Pokemon['Active'] ? 'selected' : '') . ">{$Active}</option>";
		}

		echo "
									</select>
								</td>
							</tr>
						</tbody>
					</table>

					<table class='standard' style='float: left; width: 400px;'>
						<thead>
							<tr style='text-align: center;'>
								<th colspan='2'>
									<b>Price</b>
								</th>
							</tr>
						</thead>
						<tbody>
		";

		$Price_Array = GeneratePrice($Pokemon['Price']);
		foreach ( $Constants->Currency as $Key => $Currency )
		{
			if ( isset($Price_Array[$Key]) && $Price_Array[$Key]['Amount'] != 0 )
			{
				$Value = $Price_Array[$Key]['Amount'];
			}
			else
			{
				$Value = '';
			}

			echo "
				<tr>
					<td>
						<img src='" . DOMAIN_SPRITES . "/images/Assets/{$Currency['Name']}.png' style='height: 32px; width: 32px;' />
						{$Currency['Name']}
					</td>
					<td>
						<input type='text' style='text-align: center;' size='6' maxlength='12' name='cost[" . $Key . "]' value='{$Value}'>
					</td>
				</tr>
			";
		}

		echo "
						</tbody>
					</table>
				</form>
			</div>
		";

		exit;
	}
?>

<div class='head'>Pokemon Manager</div>
<div class='body'>
	<div class='description' style='margin-bottom: 5px;'>
		Here, you may manage all obtainable Pokemon that are in game.<br />
		Click on the name of an area in order to edit the Pokemon within that location.
	</div>
	
	<div id='AJAX'>
		<table class='standard' style='margin: 0 auto; width: 80%;'>
			<thead>
				<tr style='text-align: center;'>
					<th colspan='2'>
						<b>Obtainable Locations</b>
					</th>
				</tr>
			</thead>
			<tbody>
				<?php
					try
					{
						$Query_Pokemon = $PDO->prepare("SELECT DISTINCT(`Obtained_Place`), Obtained_Text FROM `obtainable_pokemon` WHERE id != -1 ORDER BY `Obtained_Text` ASC");
						$Query_Pokemon->execute();
						$Query_Pokemon->setFetchMode(PDO::FETCH_ASSOC);
						$Obtainables = $Query_Pokemon->fetchAll();
					}
					catch ( PDOException $e )
					{
						HandleError( $e->getMessage() );
					}

					foreach ( $Obtainables as $Key => $Place )
					{
						try
						{
							$Pokemon_Data = $PDO->prepare("SELECT * FROM `obtainable_pokemon` WHERE `Obtained_Place` = ? ORDER BY `ID` ASC");
							$Pokemon_Data->execute([ $Place['Obtained_Place'] ]);
							$Pokemon_Data->setFetchMode(PDO::FETCH_ASSOC);
							$Pokemon_List = $Pokemon_Data->fetchAll();
						}
						catch ( PDOException $e )
						{
							HandleError( $e->getMessage() );
						}

						echo "
							<tr>
								<td style='width: 25%;'>
									<a href='javascript:void(0);' onclick='LoadContent(\"manage_pokemon.php\", \"AJAX\", {Location: \"{$Place['Obtained_Place']}\"});'>
										{$Place['Obtained_Text']}
									</a>
								</td>
								<td style='text-align: left; width: 75%;'>
						";
						
						foreach ( $Pokemon_List as $Key => $Pokemon )
						{
							$Poke_Data = $Poke_Class->FetchPokedexData( $Pokemon['Pokedex_ID'], $Pokemon['Alt_ID'], $Pokemon['Type'] );

							echo "
								<img src='{$Poke_Data['Icon']}' />
							";
						}

						echo "
								</td>
							</tr>
						";
					}
				?>
			</tbody>
		</table>
	</div>
</div>

<script type='text/javascript'>

</script>