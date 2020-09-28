<?php
	require 'core/required/layout_top.php';
	require 'core/functions/shop.php';

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
			$Shiny_Odds = $Constants->Shiny_Odds[$Shop];
			break;
	}
?>

<div class='panel content'>
	<div class='head'>Shops</div>
	<div class='body'>
		<div class='nav'>
			<div>
				<a href='<?= Domain(1); ?>/shop.php?Shop=pokemon' style='display: block;'>Pokemon</a>
			</div>
		</div>

		<div id='result' style='display: none; height: 116px; margin-bottom: 5px; width: 80%;'></div>

		<div class='description' style='margin-bottom: 5px;'>
			<?= $Description; ?>
		</div>

		<div class='row' style='display: flex; flex-direction: row; flex-wrap: wrap; justify-content: center;'>
			<?php
				try
				{
					$Fetch_Obtainables = $PDO->prepare("SELECT * FROM `obtainable_pokemon` WHERE `Obtained_Place` = ? AND `Active` = 'Yes' ORDER BY `Pokedex_ID` ASC");
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
					// No price has been set; don't list it.
					if ( !$Value['Prices'] )
					{
						continue;
					}

					$Pokemon = $Poke_Class->FetchPokedexData($Value['Pokedex_ID'], $Value['Alt_ID'], $Value['Type']);

					if ( $Value['Type'] !== "Normal" )
					{
						$Pokemon['Name'] = $Value['Type'] . $Pokemon['Name'];
					}

					/**
					 * Fetch the required prices of the object.
					 */
					$Price_Array = FetchPriceList($Value['Prices']);

					/**
					 * Determine if the user is eligible to purchase the object.
					 */
					$Can_Afford = true;
					$Price_String = '';
					foreach ( $Price_Array[0] as $Currency => $Amount )
					{
						// Append to the price string for output later.
						$Price_String .= "<img src='" . Domain(1) . "/images/Assets/{$Currency}.png' /> " . number_format($Amount) . "<br />";

						// Now check to see if the user can afford it.
						if ( $User_Data[$Currency] < $Amount )
						{
							$Can_Afford = false;
							break;
						}
						else
						{
							$Can_Afford = true;
						}
					}

					/**
					 * Generate the button to purchase the Pokemon.
					 */
					if ( $Can_Afford )
					{
						$Purchase_Button = "
							<button onclick='Purchase({$Value['ID']});'>
								Purchase
							</button>
						";
					}
					else
					{
						$Purchase_Button = "
							<button class='disabled'>
								Can't Purchase
							</button>
						";
					}

					echo "
						<div class='panel' style='flex-basis: calc(100% / 4 - 12px); margin: 3px;'>
							<div class='head'>{$Pokemon['Name']}</div>
							<div class='body' style='display: flex; flex-direction: row; flex-wrap: wrap; justify-content: center; padding: 5px;'>
								<div style='flex-basis: 100%; text-align: center;'>
									{$Purchase_Button}
								</div>
								<br />
								<div style='flex-basis: 50%;'>
									<img src='{$Pokemon['Sprite']}' />
								</div>
								<div style='flex-basis: 50%;'>
									{$Price_String}
								</div>
							</div>
						</div>
					";
				}
			?>
		</div>
	</div>
</div>

<script type='text/javascript'>
	function Purchase(id)
	{
		$.ajax({
			type: "POST",
			url: "<?= Domain(1); ?>/core/ajax/shop.php",
			data: { id: id, shop: '<?= substr($Shop, 0, -5); ?>' },
			success: function(data)
			{
				$('#result').html(data);
				$('#result').addClass('success');
				$('#result').css('display', 'block');
			},
			error: function(data)
			{
				$('#result').html(data);
				$('#result').addClass('error');
				$('#result').css('display', 'block');
			}
		});
	}
</script>

<?php
	require 'core/required/layout_bottom.php';