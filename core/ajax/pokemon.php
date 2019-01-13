<?php
	require_once '../required/session.php';

	if ( isset($_GET['id']) )
	{
		$Poke_ID = Purify($_GET['id']);
	}
	else
	{
		$Poke_ID = 0;
	}

	$Pokemon = $PokeClass->FetchPokemonData($Poke_ID);

	if ( $Pokemon == 'Error' )
	{
		echo "This Pokemon doesn't exist.";
		exit();
	}

	$Move_1 = $PokeClass->FetchMoveData($Pokemon['Move_1']);
  $Move_2 = $PokeClass->FetchMoveData($Pokemon['Move_2']);
  $Move_3 = $PokeClass->FetchMoveData($Pokemon['Move_3']);
  $Move_4 = $PokeClass->FetchMoveData($Pokemon['Move_4']);
?>

<!DOCTYPE html>
<html>
	<head>
		<title>Pokemon Statistics :: The Pokemon Absolute</title>
		<link href='<?= Domain(1); ?>/images/Icons/4 - Shiny Sunset/Mega/359-mega.png' rel='shortcut icon'>
		<link href='<?= Domain(1); ?>/css/default.css' rel='stylesheet'>
		<style type='text/css'>
			html, body
			{
				border: none;
				font-family: "Helvetica Neue", "Helvetica", "Arial", "sans-serif";
				font-size: 14px;
        height: 100%;
        margin: 0;
				padding: 0;
				width: 680px;
			}

			.info {
				background: #4A618F;
				font-weight: bold;
				padding: 2px;
				text-align: center;
			}

			table
			{
				background: #4A618F;
				border-color: #4A618F !important;
				border-radius: 4px;

				margin-left: 5px;
				width: 304px;

				display: table;
    		border-collapse: separate;
    		border-spacing: 2px;
			}

			table thead
			{
				background: #4A618F;
				background: -moz-linear-gradient(top, #888 0%, #4A618F 100%, #4A618F 100%);
				background: -webkit-linear-gradient(top, #888 0%, #4A618F 100%, #4A618F 100%);
				background: linear-gradient(to bottom, #888 0%, #4A618F 100%, #4A618F 100%);
				border-bottom: 1px solid #4A618F;
				border-color: #4A618F !important;
			}
			table thead tr
			{
				border-color: #4A618F !important;
			}
			table thead tr th
			{
				border-color: #4A618F !important;
				padding: 3px;
			}
			table thead tr th:nth-child(1)
			{
				border-top-left-radius: 4px;
			}
			table thead tr th:nth-child(4)
			{
				border-top-right-radius: 4px;
			}
			
			table tr td
			{
				background: #253147;
				padding: 2px;
				text-align: center;
			}
		</style>
	</head>
	
	<body>
		<div class='content' style='float: left; margin: 5px 0px 5px 5px; width: 200px;'>
			<div class='head'><?= $Pokemon['Display_Name']; ?></div>
			<div class='box' style='padding: 0px;'>
				<img src='<?= $Pokemon['Sprite']; ?>' />
			</div>
		</div>

		<table style='float: left; margin: 5px 0px 5px 5px; width: 460px;'>
			<thead>
				<tr>
					<th style='width: 25%;'>Stat</th>
					<th style='width: 25%;'>Values</th>
					<th style='width: 25%;'>IV's</th>
					<th style='width: 25%;'>EV's</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td>HP</td>
					<td><?= number_format($Pokemon['Stats'][0]); ?></td>
					<td><?= number_format($Pokemon['IVs'][0]); ?></td>
					<td><?= number_format($Pokemon['EVs'][0]); ?></td>
				</tr>
				<tr>
					<td>Attack</td>
					<td><?= number_format($Pokemon['Stats'][1]); ?></td>
					<td><?= number_format($Pokemon['IVs'][1]); ?></td>
					<td><?= number_format($Pokemon['EVs'][1]); ?></td>
				</tr>
				<tr>
					<td>Defense</td>
					<td><?= number_format($Pokemon['Stats'][2]); ?></td>
					<td><?= number_format($Pokemon['IVs'][2]); ?></td>
					<td><?= number_format($Pokemon['EVs'][2]); ?></td>
				</tr>
				<tr>
					<td>Sp. Attack</td>
					<td><?= number_format($Pokemon['Stats'][3]); ?></td>
					<td><?= number_format($Pokemon['IVs'][3]); ?></td>
					<td><?= number_format($Pokemon['EVs'][3]); ?></td>
				</tr>
				<tr>
					<td>Sp. Defense</td>
					<td><?= number_format($Pokemon['Stats'][4]); ?></td>
					<td><?= number_format($Pokemon['IVs'][4]); ?></td>
					<td><?= number_format($Pokemon['EVs'][4]); ?></td>
				</tr>
				<tr>
					<td>Speed</td>
					<td><?= number_format($Pokemon['Stats'][5]); ?></td>
					<td><?= number_format($Pokemon['IVs'][5]); ?></td>
					<td><?= number_format($Pokemon['EVs'][5]); ?></td>
				</tr>
			</tbody>
		</table>

		<table style='float: left; margin: 0px 0px 5px 5px; width: calc(100% - 10px);'>
			<thead>
				<tr>
					<th colspan='4'>Details</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td style='width: 25%;'><b>Owner</b></td>
					<td style='width: 25%;'><a href='<?= Domain(1); ?>/profile.php?id=<?= $Pokemon['Owner_Current']; ?>'><?= $Pokemon['Owner_Current_Username']; ?></a></td>
					<td style='width: 25%;'><b>Location</b></td>
					<td style='width: 25%;'><?= $Pokemon['Location']; ?></td>
				</tr>
				<tr>
					<td><b>Gender</b></td>
					<td><?= $Pokemon['Gender']; ?></td>
					<td><b>Nature</b></td>
					<td><?= $Pokemon['Nature']; ?></td>
				</tr>
				<tr>
					<td><b>Item</b></td>
					<td>Amulet Coin</td>
					<td><b>Trade Interest</b></td>
					<td><?= $Pokemon['Trade_Interest']; ?></td>
				</tr>
				<tr>
					<td><b>Level</b></td>
					<td><?= $Pokemon['Level']; ?></td>
					<td><b>Experience</b></td>
					<td><?= $Pokemon['Experience']; ?></td>
				</tr>
				<tr>
					<td colspan='2'><i><?= $Move_1['Name']; ?></i></td>
					<td colspan='2'><i><?= $Move_2['Name']; ?></i></td>
				</tr>
				<tr>
					<td colspan='2'><i><?= $Move_3['Name']; ?></i></td>
					<td colspan='2'><i><?= $Move_4['Name']; ?></i></td>
				</tr>
				<tr>
					<td colspan='1'><b>Place Obtained</b></td>
					<td colspan='3'><?= $Pokemon['Creation_Location']; ?></td>
				</tr>
				<tr>
					<td colspan='1'><b>Obtained On</b></td>
					<td colspan='3'><?= $Pokemon['Creation_Date']; ?> by <a href='<?= Domain(1); ?>/profile.php?id=<?= $Pokemon['Original_Owner']; ?>'><?= $Pokemon['Owner_Original_Username']; ?></a></td>
				</tr>
			</tbody>
		</table>
	</body>
</html>