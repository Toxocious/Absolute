<?php
	require_once '../../required/session.php';

	if ( !isset($_POST['id']) )
  {
    echo "
      <tr>
        <td colspan='21'>
          <b style='color: #f00'>
            An error has occurred while fetching this trainer's inventory
        </td>
      </tr>
    ";

    return;
  }

	$User_ID = $Purify->Cleanse($_POST['id']);
	$User = $User_Class->FetchUserData($User_ID);

	try
	{
		$Item_Query = $PDO->prepare("SELECT `Item_ID` FROM `items` WHERE `Owner_Current` = ? AND `Quantity` > 0 ORDER BY `Item_Name` ASC");
		$Item_Query->execute([ $User_ID ]);
		$Item_Query->setFetchMode(PDO::FETCH_ASSOC);
		$Items = $Item_Query->fetchAll();
	}
	catch ( PDOException $e )
	{
		HandleError($e);
	}

	if ( count($Items) === 0 )
	{
		echo "
			<tr>
				<td colspan='21' style='height: 219px; padding: 10px;'>
					There are no items in this trainer's inventory.
				</td>
			</tr>
		";
	}
	else
	{
		echo "<tr>";
		$Total_Rendered = 0;
		foreach( $Items as $Key => $Value )
		{
			$Key++;
			$Total_Rendered++;
			$Item_Data = $Item_Class->FetchOwnedItem($User_ID, $Value['Item_ID']);

			echo "
				<td colspan='2' onclick='Add_To_Trade({$User['ID']}, \"Add\", \"Item\", {$Value['Item_ID']})'>
					<img src='{$Item_Data['Icon']}' />
				</td>
				<td colspan='5' onclick='Add_To_Trade({$User['ID']}, \"Add\", \"Item\", {$Value['Item_ID']})'>
					{$Item_Data['Name']}
					<br />
					x{$Item_Data['Quantity']}
				</td>
			";

			if ( $Key % 3 === 0 && $Key !== 18 )
				echo "</tr><tr>";
		}

		if ( $Total_Rendered <= 18 )
		{
			$Total_Rendered++;

			for ( $Total_Rendered; $Total_Rendered <= 18; $Total_Rendered++ )
			{
				echo "
					<td colspan='7' style='height: 36px;'></td>
				";

				if ( $Total_Rendered % 3 === 0 && $Total_Rendered % 18 !== 0 )
					echo "</tr><tr>";
			}
		}

		echo "</tr>";
	}
	echo "</div>";
