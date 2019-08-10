<?php
	require '../../core/required/session.php';
	require '../../core/functions/staff.php';

	/**
	 * Updating the specified Pokemon.
	 */
	if ( isset($_POST['Edit']) )
	{
		$Move_ID = $Purify->Cleanse($_POST['Edit']);
		$Move_Name = $Purify->Cleanse($_POST['Name']);
		$Type = $Purify->Cleanse($_POST['Type']);
		$Category = $Purify->Cleanse($_POST['Category']);
		$Power = $Purify->Cleanse($_POST['Power']);
		$Accuracy = $Purify->Cleanse($_POST['Accuracy']);
		$Priority = $Purify->Cleanse($_POST['Priority']);
		$Crit = $Purify->Cleanse($_POST['Crit']);
		
		try
		{
			$Update = $PDO->prepare("UPDATE `moves` SET `type` = ?, `category` = ?, `power` = ?, `accuracy` = ?, `priority` = ?, `crit` = ? WHERE `id` = ?");
			$Update->execute([ $Type, $Category, $Power, $Accuracy, $Priority, $Crit, $Move_ID ]);
		}
		catch ( PDOException $e )
		{
			HandleError( $e->getMessage() );
		}

		echo "<div class='success'>You have successfully updated the data of <b>{$Move_Name}</b>.</div>";
	}

	/**
	 * Fetching move data.
	 */
	if ( isset($_POST['Fetch']) )
	{
		$Move_ID = $Purify->Cleanse($_POST['Fetch']);

		try
		{
			$Query_Move = $PDO->prepare("SELECT * FROM `moves` WHERE `id` = ? LIMIT 1");
			$Query_Move->execute([ $Move_ID ]);
			$Query_Move->setFetchMode(PDO::FETCH_ASSOC);
			$Move = $Query_Move->fetch();
		}
		catch ( PDOException $e )
		{
			HandleError( $e->getMessage() );
		}
?>

			<form id='EditMove' onsubmit='LoadContent("ajax/edit_moves.php", "AJAX", $("#EditMove").serialize()); return false;'>
				<table>
					<tr>
						<td rowspan='7' style='width: 200px;'>
							<b><?= $Move['name']; ?></b>
						</td>
					</tr>

					<tr>
						<td style='padding-right: 5px;'><b>Type</b></td>
						<td><input type='text' name='Type' value='<?= $Move['type']; ?>' /></td>
					</tr>
					<tr>
						<td style='padding-right: 5px;'><b>Category</b></td>
						<td><input type='text' name='Category' value='<?= $Move['category']; ?>' /></td>
					</tr>
					<tr>
						<td style='padding-right: 5px;'><b>Power</b></td>
						<td><input type='text' name='Power' value='<?= $Move['power']; ?>' /></td>
					</tr>
					<tr>
						<td style='padding-right: 5px;'><b>Accuracy</b></td>
						<td><input type='text' name='Accuracy' value='<?= $Move['accuracy']; ?>' /></td>
					</tr>
					<tr>
						<td style='padding-right: 5px;'><b>Priority</b></td>
						<td><input type='text' name='Priority' value='<?= $Move['priority']; ?>' /></td>
					</tr>
					<tr>
						<td style='padding-right: 5px;'><b>Crit</b></td>
						<td><input type='text' name='Crit' value='<?= $Move['crit']; ?>' /></td>
					</tr>
				</table>

				<input type='hidden' name='Name' value='<?= $Move['name']; ?>' />
				<input type='hidden' name='Edit' value='<?= $Move['id']; ?>' />
				<input type='submit' value='Update Move' />
			</form>

<?php
	}