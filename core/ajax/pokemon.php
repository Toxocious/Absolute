<?php
	require_once '../required/session.php';

	if ( isset($_GET['id']) )
		$Poke_ID = $Purify->Cleanse($_GET['id']);
	else
		$Poke_ID = 0;

	$Pokemon = $Poke_Class->FetchPokemonData($Poke_ID);

	if ( !$Pokemon )
	{
		echo "This Pokemon doesn't exist.";
		
		return;
	}

	$Owner_Current_Username = $User_Class->DisplayUsername($Pokemon['Owner_Current'], true, false, true);
	$Owner_Original_Username = $User_Class->DisplayUsername($Pokemon['Owner_Original'], true, false, true);

	if ( !$Pokemon['Item'] )
		$Pokemon['Item'] = "None";

	$Move_1 = $Poke_Class->FetchMoveData($Pokemon['Move_1']);
  $Move_2 = $Poke_Class->FetchMoveData($Pokemon['Move_2']);
  $Move_3 = $Poke_Class->FetchMoveData($Pokemon['Move_3']);
  $Move_4 = $Poke_Class->FetchMoveData($Pokemon['Move_4']);
?>

<!DOCTYPE html>
<html>
	<head>
		<title>Pok&eacute;mon Statistics :: The Pok&eacute;mon Absolute</title>
		<link href='<?= DOMAIN_SPRITES; ?>/images/Pokemon/Icons/Normal/359-mega.png' rel='shortcut icon'>

		<link type='text/css' rel='stylesheet' href='/themes/css/styles/<?= ($User_Data['Theme'] ? $User_Data['Theme'] : 'absol'); ?>.css?<?= time(); ?>' />
		<link type='text/css' rel='stylesheet' href='/themes/css/root.css?<?= time(); ?>' />
		<link type='text/css' rel='stylesheet' href='/themes/css/structure.css?<?= time(); ?>' />
		<link type='text/css' rel='stylesheet' href='/themes/css/theme.css?<?= time(); ?>' />
	</head>
	
	<body>
		<div class='flex' style='flex-direction: row; flex-wrap: wrap; justify-content: center; padding: 5px;'>
			<table class='border-gradient' style='flex-basis: 200px; margin-top: 28px;'>
				<tbody>
					<tr>
						<td colspan='1'>
							<img src='<?= $Pokemon['Sprite']; ?>' />
						</td>
					</tr>
					<tr>
						<td colspan='1'>
							<?= $Pokemon['Display_Name']; ?>
						</td>
					</tr>
				</tbody>
			</table>

			<table class='border-gradient' style='flex-basis: 442px;'>
				<thead>
					<tr>
						<th style='width: 25%;'>Stat</th>
						<th style='width: 25%;'>Base</th>
						<th style='width: 25%;'>IVs</th>
						<th style='width: 25%;'>EVs</th>
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

			<table class='border-gradient' style='flex-basis: 100%; margin: 15px; width: 656px;'>
				<thead>
					<tr>
						<th colspan='4'>Details</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td style='width: 25%;'><b>Owner</b></td>
						<td style='width: 25%;'><?= $Owner_Current_Username; ?></td>
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
						<td><?= $Pokemon['Item']; ?></td>
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
						<td colspan='1'><b>Place Obtained</b></td>
						<td colspan='3'><?= $Pokemon['Creation_Location']; ?></td>
					</tr>
					<tr>
						<td colspan='1'><b>Obtained On</b></td>
						<td colspan='1'>
							<?= $Pokemon['Creation_Date']; ?>
						</td>
						<td colspan='1'>
							<b>Original Owner</b>
						</td>
						<td colspan='1'>
							<?= $Owner_Original_Username; ?>
						</td>
					</tr>
				</tbody>
			</table>

			<table class='border-gradient' style='flex-basis: 100%;'> <!-- width: 460px; -->
				<thead>
					<tr>
						<th colspan='4'>Moves</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td colspan='2'><i><?= $Move_1['Name']; ?></i></td>
						<td colspan='2'><i><?= $Move_2['Name']; ?></i></td>
					</tr>
					<tr>
						<td colspan='2'><i><?= $Move_3['Name']; ?></i></td>
						<td colspan='2'><i><?= $Move_4['Name']; ?></i></td>
					</tr>
				</tbody>
			</table>
		</div>
	</body>
</html>