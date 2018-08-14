<?php
	require '../session.php';

	if ( isset($_POST['shop']) && isset($_POST['type']) )
	{
		# display item shops
		if ( $_POST['type'] == 'items' )
		{
			$Shop_Items = mysqli_query($con, "SELECT * FROM `shops_items` WHERE `Shop_ID` = {$_POST['shop']} ORDER BY `Item_Price` ASC");

			echo 	"<table class='shop'>";
			echo		"<thead>";
			echo			"<td>Icon</td>";
			echo			"<td>Item</td>";
			echo			"<td>Price</td>";
			echo			"<td>Purchase</td>";
			echo		"</thead>";

			while( $Items = mysqli_fetch_assoc($Shop_Items) )
			{
				echo	"<tr>";
				echo		"<td><img src='images/items/{$Items['Item_Name']}.png' /></td>";
				echo		"<td>{$Items['Item_Name']}</td>";
				echo		"<td>$" . number_format($Items['Item_Price']) . "</td>";
				
				if ( $User_Data['Money'] >= $Items['Item_Price'] )
				{
					echo		"<td><button onclick='purchase();' style='margin-top: 5px;'>Purchase</button></td>";
				}
				else
				{
					echo		"<td>You can't afford this.</td>";
				}

				echo	"</tr>";
			}
		}

		# display pokemon shops
		else
		{
			$Shop_Pokemon = mysqli_query($con, "SELECT * FROM `shops` WHERE `Shop_ID` = {$_POST['shop']} ORDER BY `Price` ASC");

			echo 	"<table class='shop'>";
			echo		"<thead>";
			echo			"<td></td>";
			echo			"<td>Pokemon</td>";
			echo			"<td>Price</td>";
			echo			"<td>Purchase</td>";
			echo		"</thead>";

			while( $Pokemon = mysqli_fetch_assoc($Shop_Pokemon) )
			{
				$Pokedex = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM `pokedex` WHERE `ID` = {$Pokemon['Pokedex_ID']}"));

				echo	"<tr>";
				echo		"<td>";
				echo			"<img src='images/Pokemon/{$Pokemon['Type_ID']}/{$Pokemon['Pokedex_ID']}.png' />";
				echo		"</td>";

				echo 		"<td>";
				if ( $Pokemon['Type_ID'] !== "Normal" )
				{
					echo		$Pokemon['Type_ID'] . $Pokedex['Name'];
				}
				else
				{
					echo		$Pokedex['Name'];
				}
				echo		"</td>";

				echo		"<td>$" . number_format($Pokemon['Price']) . "</td>";
				
				if ( $User_Data['Money'] >= $Pokemon['Price'] )
				{
					echo		"<td><button onclick='purchase();'>Purchase</button></td>";
				}
				else
				{
					echo		"<td>You can't afford this.</td>";
				}

				echo	"<tr>";
			}

			echo	"</table>";
		}
	}