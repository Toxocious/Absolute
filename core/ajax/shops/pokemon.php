<?php
	require '../../required/session.php';

	if ( isset($_POST['tab']) )
	{
		$Shop = Purify($_POST['tab']);
	}
	else
	{
		exit();
	}

	try
	{
		$Fetch_Obtainables = $PDO->prepare("SELECT * FROM `obtainable_pokemon` WHERE `Obtained_Place` = ? AND `Active` = 'Yes' ORDER BY `Price`, `Pokedex_ID`");
		$Fetch_Obtainables->execute([ $Shop . "_shop" ]);
		$Fetch_Obtainables->setFetchMode(PDO::FETCH_ASSOC);
		$Obtainables = $Fetch_Obtainables->fetchAll();
	}
	catch ( PDOException $e )
	{
		HandleError( $e->getMessage() );
	}

	/**
	 * Fetch the base odds of purchasing a Shiny Pokemon.
	 */
	$Shiny_Odds = $Constants->Shiny_Odds[$Shop . '_shop'];

	/**
	 * Display the relevant content.
	 */
	echo "
		<div class='description' style='margin-bottom: 5px;'>
			Welcome to the Pokemon Shop.
		</div>

		<div class='row' style='margin: 0px;'>
	";

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
				<div class='button'>
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

	echo "
		</div>
	";