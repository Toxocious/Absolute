<?php
	require '../../required/session.php';
	
	if ( isset($User_Data) )
	{
		if ( isset($_POST['id']) )
		{
			$User_ID = $Purify->Cleanse($_POST['id']);
			$User = $User_Class->FetchUserData($User_ID);

			try
			{
				$Item_Query = $PDO->prepare("SELECT * FROM `items` WHERE `Owner_Current` = ? AND `Quantity` > 0");
				$Item_Query->execute([$User_ID]);
				$Item_Query->setFetchMode(PDO::FETCH_ASSOC);
				$Items = $Item_Query->fetchAll();
			}
			catch ( PDOException $e )
			{
				HandleError( $e->getMessage() );
			}

			echo "<div style='height: 190px; padding: 5px;'>";
			if ( count($Items) === 0 )
			{
				echo "<div style='padding: 85px 5px;'>There are no items in this user's bag.</div>";
			}
			else
			{
				foreach( $Items as $Key => $Value )
				{
					echo "
						<img class='spricon' src='images/Items/" . $Value['Item_Name'] . ".png' title='" . number_format($Value['Quantity']) . " Owned' onclick='Action({$User['ID']}, \"Add\", \"Item\", {$Value['Item_ID']})' />
					";
				}
			}			
			echo "</div>";
		}
		else
		{
			echo "An error has occurred.";
		}
	}
	else
	{
		echo "To use this feature, you must be logged in.";
	}