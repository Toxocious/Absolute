<?php
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
			$Shiny_Odds = $Constants->Shiny_Odds[$Shop];
			break;
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

		<div class='notice' style='display: none; height: 116px; margin-bottom: 5px;'></div>

		<div class='description' style='margin-bottom: 5px;'><?= $Description; ?></div>

		<div class='row'>
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
						<div class='panel' style='float: left; margin-bottom: 3px; margin-right: 3px; width: calc(100% / 4 - 3px);'>
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
				$('.box .notice').html(data);
				$('.box .notice').css('display', 'block');
			},
			error: function(data)
			{
				$('.box .notice').html(data);
				$('.box .notice').css('display', 'block');
			}
		});
	}
</script>

<?php
	require 'core/required/layout_bottom.php';