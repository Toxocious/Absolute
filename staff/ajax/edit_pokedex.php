<?php
	require '../../core/required/session.php';
	require '../../core/functions/staff.php';

	/**
	 * Updating the specified Pokemon.
	 */
	if ( isset($_POST['Edit']) )
	{
		$DB_ID = $Purify->Cleanse($_POST['Edit']);
		$Name = $Purify->Cleanse($_POST['Name']);
		$Alter_Name = $Purify->Cleanse($_POST['Name_Alter']);
		$HP = $Purify->Cleanse($_POST['hp']);
		$Attack = $Purify->Cleanse($_POST['attack']);
		$Defense = $Purify->Cleanse($_POST['defense']);
		$SpAttack = $Purify->Cleanse($_POST['spattack']);
		$SpDefense = $Purify->Cleanse($_POST['spdefense']);
		$Speed = $Purify->Cleanse($_POST['speed']);

		$Pokedex = $Poke_Class->FetchPokedexData(null, null, "Normal", $DB_ID);

		try
		{
			$Update = $PDO->prepare("UPDATE `pokedex` SET `Name` = ?, `Name_Alter` = ?, `HP` = ?, `Attack` = ?, `Defense` = ?, `SpAttack` = ?, `SpDefense` = ?, `Speed` = ? WHERE `id` = ?");
			$Update->execute([ $Name, $Alter_Name, $HP, $Attack, $Defense, $SpAttack, $SpDefense, $Speed, $DB_ID ]);
		}
		catch ( PDOException $e )
		{
			HandleError( $e->getMessage() );
		}

		echo "
			<div class='success'>
				You have successfully updated the Pokedex data for<br />
				<img src='{$Pokedex['Sprite']}' /><br />
				<b>{$Pokedex['Display_Name']}</b>
			</div>
			<br />
			Please select a Pokemon from the Pokedex.
		";
	}

	/**
	 * Attempting to retrieve a specific Pokemon's information.
	 */
	if ( isset($_GET['Pokemon']) )
	{
		$DB_ID = $Purify->Cleanse($_GET['Pokemon']);
		$Pokedex = $Poke_Class->FetchPokedexData(null, null, "Normal", $DB_ID);
?>

<form id='EditDex' onsubmit='LoadContent("ajax/edit_pokedex.php", "AJAX", $("#EditDex").serialize()); return false;'>
	<table>
		<tr>
			<td rowspan='9' style='padding: 0px 10px; width: 180px;'>
				<img src='<?= $Pokedex['Sprite']; ?>' /><br />
				<b><?= $Pokedex['Display_Name']; ?></b><br />
				(DB #<?= $DB_ID; ?>)
			</td>
		</tr>

		<tr>
			<td style='padding-right: 5px; text-align: right;'><b>Name</b></td>
			<td><input type='text' name='Name' value='<?= $Pokedex['Name'] ?>' /></td>
		</tr>
		<tr>
			<td style='padding-right: 5px; text-align: right;'><b>Name_Alter</b></td>
			<td><input type='text' name='Name_Alter' value='<?= $Pokedex['Name_Alter'] ?>' /></td>
		</tr>

		<tr>
			<td style='padding-right: 5px; text-align: right;'><b>HP</b></td>
			<td><input type='text' name='hp' value='<?= $Pokedex['Base_Stats'][0] ?>' /></td>
		</tr>
		<tr>
			<td style='padding-right: 5px; text-align: right;'><b>Attack</b></td>
			<td><input type='text' name='attack' value='<?= $Pokedex['Base_Stats'][1] ?>' /></td>
		</tr>
		<tr>
			<td style='padding-right: 5px; text-align: right;'><b>Defense</b></td>
			<td><input type='text' name='defense' value='<?= $Pokedex['Base_Stats'][2] ?>' /></td>
		</tr>
		<tr>
			<td style='padding-right: 5px; text-align: right;'><b>Sp. Attack</b></td>
			<td><input type='text' name='spattack' value='<?= $Pokedex['Base_Stats'][3] ?>' /></td>
		</tr>
		<tr>
			<td style='padding-right: 5px; text-align: right;'><b>Sp. Defense</b></td>
			<td><input type='text' name='spdefense' value='<?= $Pokedex['Base_Stats'][4] ?>' /></td>
		</tr>
		<tr>
			<td style='padding-right: 5px; text-align: right;'><b>Speed</b></td>
			<td><input type='text' name='speed' value='<?= $Pokedex['Base_Stats'][5] ?>' /></td>
		</tr>
	</table>

	<input type='hidden' name='Edit' value='<?= $DB_ID; ?>' />
	<input type='submit' value='Update Pokedex Entry' style='padding: 3px; width: 100%;' />
</form>

<style>
	input { width: 120px; }
</style>

<?php
	}