<?php
	//require 'core/required/session.php';
	require 'core/required/layout_top.php';

	if ( isset($_GET['shop']) )
	{
		$Shop = Purify($_GET['shop']);
	}
	else
	{
		$Shop = 'pokemon';
	}

	/**
	 * Switch/Case for all shops.
	 */
	switch($Shop)
	{
		case 'pokemon':
			$Shop = "pokemon_shop";
			$Description = "Welcome to the Pokemon Shop.";
			$Currency = "Money";
			$Shiny_Odds = $Constants->Shiny_Odds[$Shop];
			break;
	}

	/**
	 * The user is attempting to purchase a Pokemon.
	 */
	if ( isset($_POST['id']) )
	{
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
		if ( !isset($Pokemon) )
		{
			$Error = "<div class='error' style='margin-bottom: 5px;'>The selected Pokemon couldn't be found.</div>";
		}
		else if ( $Pokemon['Active'] !== 'Yes' )
		{
			$Error = "<div class='error' style='margin-bottom: 5px;'>The selected Pokemon couldn't be found.</div>";
		}
		else
		{
			$Error = '';
			$Price_Array = GeneratePrice($Pokemon['Price']);
			foreach( $Price_Array as $Key => $Currency )
			{
				if ( ($User_Data[$Currency['Value']] - $Currency['Amount']) < 0 )
				{
					$Error .= "You need an additional" . number_format($Currency['Amount'] - $User_Data[$Currency['Value']]) . " " . $Currency['Name'] . " to purchase this Pokemon.<br />";
				}

				if ( !isset($Constants->Currency[$Currency['Value']]) )
				{
					$Error .= "<b>An error has occured with this Pokemon's price. Please report this to an Admin.</b><br />";
				}	
			}

			if ( $Error == "" )
			{
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
					$User_Data[$Currency['Name']] -= $Currency['Amount'];
				}

				$Pokemon_Data = $PokeClass->FetchPokedexData($Pokemon['Pokedex_ID'], $Pokemon['Alt_ID'], $Pokemon['Type']);

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
					$Pokemon['Gender'] = '?';
					echo "<script type='text/javascript'>setTimeout(function() { alert('You purchased an ungendered Pokemon.'); }, 69);</script>";
				}
				else
				{
					$Pokemon['Gender'] = $PokeClass->GenerateGender($Pokemon['Pokedex_ID'], $Pokemon['Alt_ID']);
				}

				/**
				 * Create the Pokemon, and show it, it's gender, it's stats, and it's IV's to the user.
				 */
				$Pokemon_Created = $PokeClass->CreatePokemon( $Pokemon['Pokedex_ID'], $Pokemon['Alt_ID'], 5, $Pokemon['Type'], $Pokemon['Gender'], $Pokemon['Obtained_Text'], null, null, $User_Data['id'] );
				$Total_Stat = array_sum($Pokemon_Created['Stats']);
				$Total_IV = array_sum($Pokemon_Created['IVs']);

				$Success = "
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
	}
?>

<div class='content'>
	<div class='head'>Shops</div>
	<div class='box'>
		<div class='nav'>
			<div><a href='<?= Domain(1); ?>/shop.php?Shop=pokemon' style='display: block;'>Pokemon</a></div>
		</div>

		<?php
			if ( isset($Error) && $Error != '' )
			{
				echo "<div class='error' style='margin-bottom: 5px;'>$Error</div>";
			}

			if ( isset($Success) && $Success != '' )
			{
				echo "<div class='success' style='height: 116px; margin-bottom: 5px;'>$Success</div>";
			}
		?>

		<div class='description' style='margin-bottom: 5px;'><?= $Description; ?></div>

		<?php
			try
			{
				$Fetch_Obtainables = $PDO->prepare("SELECT * FROM `obtainable_pokemon` WHERE `Obtained_Place` = ? AND `Active` = 'Yes' ORDER BY `Price`, `Pokedex_ID`");
				$Fetch_Obtainables->execute([ $Shop ]);
				$Fetch_Obtainables->setFetchMode(PDO::FETCH_ASSOC);
				$Obtainables = $Fetch_Obtainables->fetchAll();
			}
			catch ( PDOException $e )
			{
				HandleError( $e->getMessage() );
			}

			foreach( $Obtainables as $Key => $Value )
			{
				$Pokemon = $PokeClass->FetchPokedexData($Value['Pokedex_ID'], $Value['Alt_ID'], $Value['Type']);

				if ( $Value['Type'] !== "Normal" )
				{
					$Pokemon['Name'] = $Value['Type'] . $Pokemon['Name'];
				}

				/**
				 * Determine the price(s) of a Pokemon.
				 */
				$Can_Afford = true;
				$Price_Array = GeneratePrice($Value['Price']);
				foreach( $Price_Array as $Key => $Currency )
				{
					if ( $User_Data[$Currency['Value']] < $Currency['Amount'] )
					{
						$Can_Afford = false;
					}
					else
					{
						$Can_Afford = true;
					}
				}

				/**
				 * Generate the purchase link.
				 */
				if ( $Can_Afford === true )
				{
					$Purchase_Link = "
						<div class='button' onclick='Purchase({$Value['ID']});'>
							Purchase
						</div>
					";
				}
				else
				{
					$Purchase_Link = "
						<div class='button'>
							You can't afford this.
						</div>
					";
				}

				echo "
					<div class='panel' style='float: left; margin-bottom: 3px; margin-right: 3px; width: calc(100% / 4 - 5px);'>
						<div class='panel-heading'>{$Pokemon['Name']}</div>
						<div class='panel-body shop_panel'>
							{$Purchase_Link}
							<img src='{$Pokemon['Sprite']}' />
							<hr />
				";

				if ( count($Price_Array) !== 0 )
				{
					foreach ( $Price_Array as $Key => $Price_Info )
					{
						echo "<b>" . $Price_Info['Name'] . ":</b> " . number_format($Price_Info['Amount']) . "<br />";
					}
				}

				echo "
						</div>
					</div>
				";
			}
		?>
	</div>
</div>

<script type='text/javascript'>
	function Purchase(id)
	{
		$.ajax({
			type: "POST",
			url: "<?= Domain(1); ?>/shop.php?Shop=<?= substr($Shop, 0, -5); ?>",
			data: { id: id },
			success: function(data)
			{
				$('body').html(data);
			},
			error: function(data)
			{
				$('body').html(data);
			}
		});
	}
</script>

<?php
	require 'core/required/layout_bottom.php';