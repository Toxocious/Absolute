<?php
	require '../../required/session.php';

	$Item_Types = ['Battle', 'Berry', 'General', 'Held', 'Key', 'Machine', 'Medicine', 'Pokeball'];
	$Item_Type = 'Held';

	try
	{
		$Fetch_Items = $PDO->prepare("SELECT * FROM `items_owned` WHERE `Owner_Current` = ? AND `Equipped_To` > 0");
		$Fetch_Items->execute([$User_Data['id']]);
		$Fetch_Items->setFetchMode(PDO::FETCH_ASSOC);
		$Items = $Fetch_Items->fetchAll();

		$Fetch_Items = $PDO->prepare("SELECT * FROM `items_owned` WHERE `Owner_Current` = ? AND `Item_Type` = ?");
		$Fetch_Items->execute([$User_Data['id'], $Item_Type]);
		$Fetch_Items->setFetchMode(PDO::FETCH_ASSOC);
		$Items = $Fetch_Items->fetchAll();
	}
	catch ( PDOException $e )
	{
		HandleError( $e->getMessage() );
	}

	//$Item_Types = ['Battle', 'Berry', 'General', 'Held', 'Key', 'Machine', 'Medicine', 'Pokeball'];
	//$Fetch_Box = mysqli_query($con, "SELECT * FROM pokemon WHERE Owner_Current = '" . $User_Data['id'] . "' AND Slot = 7 LIMIT 50");
	//$Check_Equipped = mysqli_query($con, "SELECT * FROM items_owned WHERE Owner_Current = '" . $User_Data['id'] . "' AND Equipped_To > 0");

	//$Item_Type = 'Held';
	//$Get_Items = mysqli_query($con, "SELECT * FROM items_owned WHERE Owner_Current = '" . $User_Data['id'] . "' AND Item_Type = '" . $Item_Type . "'");

	echo "
		<div class='panel' style='margin-bottom: 5px;'>
			<div class='panel-heading'>Inventory</div>
			<div class='panel-body inventory'>
				<div>
					<div style='float: left; padding: 5px; width: calc(100% / 6);' onclick=\"inventoryTab('inventory', 'showtab', 'Battle');\">
						Battle Items
					</div>
					<div style='float: left; padding: 5px; width: calc(100% / 6);' onclick=\"inventoryTab('inventory', 'showtab', 'General');\">
						General Items
					</div>
					<div style='float: left; padding: 5px; width: calc(100% / 6);' onclick=\"inventoryTab('inventory', 'showtab', 'Held');\">
						Hold Items
					</div>
					<div style='float: left; padding: 5px; width: calc(100% / 6);' onclick=\"inventoryTab('inventory', 'showtab', 'Evolutionary');\">
						Evolutionary Items
					</div>
					<div style='float: left; padding: 5px; width: calc(100% / 6);' onclick=\"inventoryTab('inventory', 'showtab', 'Key');\">
						Key Items
					</div>
					<div style='float: left; padding: 5px; width: calc(100% / 6);' onclick=\"inventoryTab('inventory', 'showtab', 'Misc');\">
						Misc. Items
					</div>
				</div>
		";
				
		echo "
			<div id='activeTab'>";
			if ( count($Items) == 0 )
			{
				echo	"<div style='padding: 2px;'>There are no items in your inventory.</div>";
			}
			else
			{
				foreach ($Items as $key => $Item)
				{
					echo  "<div class='item_cont' onclick='selectItem(\"inventory\", \"item_show\", " . $Item['id'] . ");'>";
					echo    "<div style='float: left;'>";
					echo      "<img src='images/Items/" . $Item['Item_Name'] . ".png' />";
					echo    "</div>";
					echo    "<b>{$Item['Item_Name']}</b><br />";
					echo    "x" . number_format($Item['Quantity']);
					echo  "</div>";
				}
			}
		echo "</div>";

		echo "</div>
			</div>

			<div class='panel' style='float: left; margin-right: 0.5%; width: 49.75%;'>
				<div class='panel-heading'>Attached Items</div>
				<div class='panel-body attacheditems' style='padding: 0px 3px 3px;'>";

				if ( count($Items) == 0 )
				{
					echo "<div style='padding: 5px;'>None of your Pokemon have an item equipped.</div>";
				}
				else
				{
					while ( $Query = mysqli_fetch_assoc($Items) )
					{
						$Items_Ref = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM items_owned WHERE id = '" . $Query['id'] . "' AND Owner_Current = '" . $User_Data['id'] . "'"));
						$Pokemon = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM pokemon WHERE ID = '" . $Items_Ref['Equipped_To'] . "'"));

						if ( $Pokemon['Type'] !== "Normal" ) $Pokemon['Type'] = $Pokemon['Type'];
						else                                 $Pokemon['Type'] = '';
						
						echo "
							<div class='panel' style='float: left; margin-top: 3px; width: 49.75%;'>
								<div class='panel-heading'>{$Pokemon['Type']}{$Pokemon['Name']}</div>
								<!--<div class='panel-body'>their icon plus the icon of w/e item they have equipped plus a remove item button</div>-->
								<div class='panel-body'>
									<div style='float: left; padding-top: 2px;'>
						";

						showImage('icon', $Pokemon['ID'], 'pokemon', 'blank');

						echo "</div>
									<div style='float: left; padding-top: 3px;'>
										<img src='images/Items/{$Items_Ref['Item_Name']}.png' />
									</div>
									<div style='float: left; height: 30px; padding-top: 7px; text-align: center; width: calc(100% - 70px);'>
										<a href='javascript:void(0);' onclick='removeItem()'>Remove Item</a>
									</div>
								</div>
							</div>
						";
					}
				}

		echo "
				</div>
			</div>

			<div class='panel' style='float: left; width: 49.75%;'>
				<div class='panel-heading'>Selected Item Data</div>
				<div class='panel-body' id='dataDiv'>
					<div style='padding: 5px;'>Please select an item to use it.</div>
				</div>
			</div>
		";