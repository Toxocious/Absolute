<?php
	require '../../core/required/session.php';
	require '../../core/functions/staff.php';

	/**
	 * Updating the specified item.
	 */
	if ( isset($_POST['Edit']) )
	{
		$Item_ID = $Purify->Cleanse($_POST['Edit']);
		$Name = $Purify->Cleanse($_POST['Name']);
		$Category = $Purify->Cleanse($_POST['Type']);
		$Description = $Purify->Cleanse($_POST['Description']);

		try
		{
			$Update = $PDO->prepare("UPDATE `item_dex` SET `Item_Name` = ?, `Item_Type` = ?, `Item_Description` = ? WHERE `Item_ID` = ?");
			$Update->execute([ $Name, $Category, $Description, $Item_ID ]);
		}
		catch ( PDOException $e )
		{
			HandleError( $e->getMessage() );
		}

		echo "
			<div class='success'>
				You have successfully updated the Itemdex data for<br />
				<img src='/images/Items/{$Name}.png' /><br />
				<b>{$Name}</b>
			</div>
			<br />
			Please select an item from the Itemdex.
		";
	}

	/**
	 * Attempting to retrieve a specific Pokemon's information.
	 */
	if ( isset($_GET['Item']) )
	{
		$Item_ID = $Purify->Cleanse($_GET['Item']);

		try
		{
			$Query_Item = $PDO->prepare("SELECT * FROM `item_dex` WHERE `Item_ID` = ? LIMIT 1");
			$Query_Item->execute([ $Item_ID ]);
			$Query_Item->setFetchMode(PDO::FETCH_ASSOC);
			$Item = $Query_Item->fetch();
		}
		catch ( PDOException $e )
		{
			HandleError( $e->getMessage() );
		}
?>

		<form id='EditDex' onsubmit='LoadContent("ajax/edit_item.php", "AJAX", $("#EditDex").serialize()); return false;'>
			<table>
				<tr>
					<td rowspan='4' style='width: 200px;'>
						<img src='/images/Items/<?= $Item['Item_Name']; ?>.png' /><br />
						<b><?= $Item['Item_Name']; ?></b>
					</td>

					<tr>
						<td style='padding-right: 5px; text-align: right;'><b>Name</b></td>
						<td><input type='text' name='Name' value='<?= $Item['Item_Name']; ?>' /></td>
					</tr>
					<tr>
						<td style='padding-right: 5px; text-align: right;'><b>Category</b></td>
						<td><input type='text' name='Type' value='<?= $Item['Item_Type']; ?>' /></td>
					</tr>
					<tr>
						<td style='padding-right: 5px; text-align: right;'><b>Description</b></td>
						<td><input type='text' name='Description' value='<?= $Item['Item_Description']; ?>' /></td>
					</tr>
				</tr>
			</table>

			<input type='hidden' name='Edit' value='<?= $Item['Item_ID']; ?>' />
			<input type='submit' value='Update Item' style='padding: 3px; width: 100%;' />
		</form>

<?php
	}
?>