<?php
	require_once '../../core/required/session.php';
	require_once '../../core/functions/staff.php';

	/**
	 * Updating the specified Pokemon.
	 */
	if ( isset($_POST['Edit']) )
	{
		var_dump($_POST);
		$Poke_ID = $Purify->Cleanse($_POST['Edit']);
		$IVs = $Purify->Cleanse($_POST['IV']);
		$IVs = $IVs['HP'] . ',' . $IVs['Att'] . ',' . $IVs['Def'] . ',' . $IVs['SpAtt'] . ',' . $IVs['SpDef'] . ',' . $IVs['Speed'];
		$EVs = $Purify->Cleanse($_POST['EV']);
		$EVs = $EVs['HP'] . ',' . $EVs['Att'] . ',' . $EVs['Def'] . ',' . $EVs['SpAtt'] . ',' . $EVs['SpDef'] . ',' . $EVs['Speed'];
		$Experience = $Purify->Cleanse($_POST['Experience']);

		echo "<div class='success'>You have successfully updated the data of Pokemon #{$Poke_ID}.</div>";

		try
		{
			$Update = $PDO->prepare("UPDATE `pokemon` SET `Experience` = ?, `IVs` = ?, `EVs` = ? WHERE `ID` = ?");
			$Update->execute([ $Experience, $IVs, $EVs, $Poke_ID ]);
		}
		catch ( PDOException $e )
		{
			HandleError( $e->getMessage() );
		}
	}

	/**
	 * Attempting to retrieve a specific Pokemon's information.
	 */
	if ( isset($_GET['Pokemon']) )
	{
		$Poke_ID = $Purify->Cleanse($_GET['Pokemon']);
		$Pokemon = $Poke_Class->FetchPokemonData($Poke_ID);
		$Owner = $User_Class->DisplayUserName($Pokemon['Owner_Current']);
?>

		<form id='EditPoke' onsubmit='LoadContent("ajax/edit_pokemon.php", "AJAX", $("#EditPoke").serialize()); return false;'>
			<table>
				<tbody>
					<tr>
						<td colspan='3' style='width: 50%;'>
							<img src='<?= $Pokemon['Sprite']; ?>' />
						</td>
						<td colspan='3' style='width: 50%;'>
							<b><?= $Pokemon['Display_Name']; ?></b><br />
							<b>Level:</b> <?= $Pokemon['Level']; ?><br />
							<b>Experience:</b> <?= number_format($Pokemon['Experience']); ?><br />
							<b>Owner:</b> <?= $Owner; ?>
						</td>
					</tr>

					<tr>
						<td style='padding: 3px; width: 80px;' colspan='2'></td>
						<td style='padding: 3px; width: 80px;' colspan='2'><b>IVs</b></td>
						<td style='padding: 3px; width: 80px;' colspan='2'><b>EVs</b></td>
					</tr>
					<tr>
						<td style='width: 80px;' colspan='2'><b>HP</b></td>
						<td style='width: 80px;' colspan='2'><input type='text' id='IV_HP' name="IV[HP]" maxlength='2' value='<?= $Pokemon['IVs'][0] ?>' onkeyup="UpdateStat('IV', this)" /></td>
						<td style='width: 80px;' colspan='2'><input type='text' id='EV_HP' name="EV[HP]" maxlength='3' value='<?= $Pokemon['EVs'][0] ?>' onkeyup="UpdateStat('EV', this)" /></td>
					</tr>
					<tr>
						<td style='width: 80px;' colspan='2'><b>Attack</b></td>
						<td style='width: 80px;' colspan='2'><input type='text' id='IV_Att' name="IV[Att]" maxlength='2' value='<?= $Pokemon['IVs'][1] ?>' onkeyup="UpdateStat('IV', this)" /></td>
						<td style='width: 80px;' colspan='2'><input type='text' id='EV_Att' name="EV[Att]" maxlength='3' value='<?= $Pokemon['EVs'][1] ?>' onkeyup="UpdateStat('EV', this)" /></td>
					</tr>
					<tr>
						<td style='width: 80px;' colspan='2'><b>Defense</b></td>
						<td style='width: 80px;' colspan='2'><input type='text' id='IV_Def' name="IV[Def]" maxlength='2' value='<?= $Pokemon['IVs'][2] ?>' onkeyup="UpdateStat('IV', this)" /></td>
						<td style='width: 80px;' colspan='2'><input type='text' id='EV_Def' name="EV[Def]" maxlength='3' value='<?= $Pokemon['EVs'][2] ?>' onkeyup="UpdateStat('EV', this)" /></td>
					</tr>
					<tr>
						<td style='width: 80px;' colspan='2'><b>Sp. Att</b></td>
						<td style='width: 80px;' colspan='2'><input type='text' id='IV_SpAtt' name="IV[SpAtt]" maxlength='2' value='<?= $Pokemon['IVs'][3] ?>' onkeyup="UpdateStat('IV', this)" /></td>
						<td style='width: 80px;' colspan='2'><input type='text' id='EV_SpAtt' name="EV[SpAtt]" maxlength='3' value='<?= $Pokemon['EVs'][3] ?>' onkeyup="UpdateStat('EV', this)" /></td>
					</tr>
					<tr>
						<td style='width: 80px;' colspan='2'><b>Sp. Def</b></td>
						<td style='width: 80px;' colspan='2'><input type='text' id='IV_SpDef' name="IV[SpDef]" maxlength='2' value='<?= $Pokemon['IVs'][4] ?>' onkeyup="UpdateStat('IV', this)" /></td>
						<td style='width: 80px;' colspan='2'><input type='text' id='EV_SpDef' name="EV[SpDef]" maxlength='3' value='<?= $Pokemon['EVs'][4] ?>' onkeyup="UpdateStat('EV', this)" /></td>
					</tr>
					<tr>
						<td style='width: 80px;' colspan='2'><b>Speed</b></td>
						<td style='width: 80px;' colspan='2'><input type='text' id='IV_Speed' name="IV[Speed]" maxlength='2' value='<?= $Pokemon['IVs'][5] ?>' onkeyup="UpdateStat('IV', this)" /></td>
						<td style='width: 80px;' colspan='2'><input type='text' id='EV_Speed' name="EV[Speed]" maxlength='3' value='<?= $Pokemon['EVs'][5] ?>' onkeyup="UpdateStat('EV', this)" /></td>
					</tr>
					<tr>
						<td style='width: 80px;' colspan='2'><b>Experience</b></td>
						<td style='width: 360px;' colspan='4'><input type='text' id='Experience' name="Experience" value='<?= $Pokemon['Experience']; ?>' style='width: 360px;' /></td>
					</tr>
					
				</tbody>
			</table>

			<input type='hidden' name='Edit' value='<?= $Pokemon['ID']; ?>' />
			<input type='submit' value='Update Pokemon' style='padding: 3px; width: 80%;' />
		</form>

<?php
	}