<?php
	require '../../required/session.php';

	function Unequip($Poke_ID, $User_ID, $Method = null)
	{
		global $PDO;
		global $Poke_Class;
		global $Item_Class;
		
		$Owner_ID = Purify($User_ID);
		$Pokemon_ID = Purify($Poke_ID);

		$Pokemon = $Poke_Class->FetchPokemonData($Pokemon_ID);
		$Item_Data = $Item_Class->FetchOwnedItem($Owner_ID, $Pokemon['Item_ID']);

		if ( $Pokemon == "Error" )
		{
			echo "<div class='error' style='margin-bottom: 5px;'>This Pokemon could not be found.</div>";
		}
		else if ( $Pokemon['Owner_Current'] != $Owner_ID )
		{
			echo "<div class='error' style='margin-bottom: 5px;'>You don't own this Pokemon.</div>";
		}
		else if ( $Pokemon['Item_ID'] == null || $Pokemon['Item_ID'] == 0 )
		{
			echo "<div class='error' style='margin-bottom: 5px;'>This Pokemon doesn't have an item equipped.</div>";
		}
		else if ( $Pokemon['Location'] == "Trade" )
		{
			echo "<div class='error' style='margin-bottom: 5px;'>This Pokemon is in a trade.</div>";
		}
		else
		{
			try
			{
				$Update_Pokemon = $PDO->prepare("UPDATE `pokemon` SET `Item` = 0 WHERE `ID` = ?");
				$Update_Pokemon->execute([ $Pokemon['ID'] ]);

				$Update_Item = $PDO->prepare("UPDATE `items` SET `Quantity` = `Quantity` + 1 WHERE `Owner_Current` = ? AND `Item_ID` = ?");
				$Update_Item->execute([ $Owner_ID, $Item_Data['ID'] ]);
			}
			catch ( PDOException $e )
			{
				HandleError( $e->getMessage() );
			}

			if ( $Method == 'Detach' )
			{
				echo "
					<div class='success' style='margin-bottom: 5px;'>You have detached your <b>{$Item_Data['Name']}</b> from <b>{$Pokemon['Display_Name']}</b>.</div>
				"; 
			}
		}
	}

	/**
	 * Attach the given item to the user's Pokemon.
	 */
	if ( isset($_POST['request']) && isset($_POST['category']) && isset($_POST['id']) && isset($_POST['pokeid']) && $_POST['request'] == 'attach' )
	{
		$Item_ID = Purify($_POST['id']);
		$Pokemon_ID = Purify($_POST['pokeid']);

		$Item_Data = $Item_Class->FetchOwnedItem($User_Data['id'], $Item_ID);
		$Poke_Data = $Poke_Class->FetchPokemonData($Pokemon_ID);

		if ( $Item_Data['Quantity'] >= 1 )
		{
			try
			{
				$Update_Pokemon = $PDO->prepare("UPDATE `pokemon` SET `Item` = ? WHERE `ID` = ?");
				$Update_Pokemon->execute([ $Item_Data['ID'], $Poke_Data['ID'] ]);

				$Update_Item = $PDO->prepare("UPDATE `items` SET `Quantity` = `Quantity` - 1 WHERE `Owner_Current` = ? AND `Item_ID` = ?");
				$Update_Item->execute([ $User_Data['id'], $Item_Data['ID'] ]);
			}
			catch ( PDOException $e )
			{
				HandleError( $e->getMessage() );
			}

			echo "
				<div class='success' style='margin-bottom: 5px;'>You have attached your <b>{$Item_Data['Name']}</b> to your <b>{$Poke_Data['Display_Name']}</b>.</div>
			";
		}
		else
		{
			echo "
				<div class='error' style='margin-bottom: 5px;'>You need at least one of these in order to equip it to your Pokemon.</div>
			";
		}
	}

	/**
	 * Detach the item(s) of the user's given Pokemon.
	 */
	if ( isset($_POST['request']) )
	{
		$Request = Purify($_POST['request']);

		if ( $Request == 'detach' && isset($_POST['pokeid']) )
		{
			$Poke_ID = Purify($_POST['pokeid']);
			Unequip($Poke_ID, $User_Data['id'], 'Detach');
		}

		if ( $Request == 'detachall' )
		{
			try
			{
				$Fetch_Equipped = $PDO->prepare("SELECT * FROM `pokemon` WHERE `Item` != 0 AND `Owner_Current` = ?");
				$Fetch_Equipped->execute([$User_Data['id']]);
				$Fetch_Equipped->setFetchMode(PDO::FETCH_ASSOC);
				$Items = $Fetch_Equipped->fetchAll();
			}
			catch( PDOException $e )
			{
				HandleError( $e->getMessage() );
			}

			foreach( $Items as $Key => $Value )
			{
				Unequip($Value['ID'], $User_Data['id']);
			}

			if ( count($Items) == 0 )
			{
				echo "
					<div class='description' style='margin-bottom: 5px;'>None of your Pokemon are holding items.</div>
				";
			}
			else
			{
				echo "
					<div class='description' style='margin-bottom: 5px;'>All items that your Pokemon were holding have been detached.</div>
				";
			}
		}
	}

	/**
	 * Display the data of a specific item.
	 */
	if ( isset($_POST['request']) && Purify($_POST['request']) == 'item_data' )
	{
		$Item_ID = Purify($_POST['id']);
		$Item_Data = $Item_Class->FetchItemData($Item_ID);

		echo "
			<div style='float: left; width: 50px;'>
				<img src='images/Items/{$Item_Data['Name']}.png' />
			</div>
			<div style='float: left; padding: 3px; width: calc(100% - 50px);'>
				<b>{$Item_Data['Name']}</b><br />
				{$Item_Data['Description']}
			</div>

			<hr />

			<b>Attach Item To:</b><br />
		";

		for ( $i = 0; $i <= 5; $i++ )
		{
			if ( isset($Roster[$i]['ID'])  )
			{
				$Roster_Slot[$i] = $Poke_Class->FetchPokemonData($Roster[$i]['ID']);

				if ( $Roster_Slot[$i]['Item'] == null )
				{
					echo "
						<img class='spricon' src='{$Roster_Slot[$i]['Icon']}' onclick=\"itemHandler('attach', '{$Item_Data['Category']}', {$Item_Data['ID']}, {$Roster_Slot[$i]['ID']});\" />
					";
				}
				else
				{
					echo "
						<img class='spricon' src='{$Roster_Slot[$i]['Icon']}' style='filter: grayscale(100%);' />
					";
				}				
			}
		}

		exit();
	}

	/**
	 * Display the items that the user owns in a specific item category.
	 */
	if ( isset($_POST['request']) && $_POST['request'] == 'item_tab' && isset($_POST['category']) )
	{
		$Category = Purify($_POST['category']);

		try
		{
			$Fetch_Items = $PDO->prepare("SELECT * FROM `items` WHERE `Owner_Current` = ? AND `Item_Type` = ? AND `Quantity` > 0");
			$Fetch_Items->execute([$User_Data['id'], $Category]);
			$Fetch_Items->setFetchMode(PDO::FETCH_ASSOC);
			$Items = $Fetch_Items->fetchAll();
		}
		catch ( PDOException $e )
		{
			HandleError( $e->getMessage() );
		}

		if ( count($Items) == 0 )
		{
			echo	"<div style='padding: 40px 5px 5px;'>There are no items in this inventory category.</div>";
		}
		else
		{
			foreach ($Items as $key => $Item)
			{
				$Item_Data = $Item_Class->FetchItemData($Item['Item_ID']);

				echo  "<div class='item_cont' onclick=\"itemHandler('item_data', '{$Item_Data['Category']}', {$Item['Item_ID']});\">";
				echo    "<div style='float: left;'>";
				echo      "<img src='images/Items/" . $Item['Item_Name'] . ".png' />";
				echo    "</div>";
				echo    "<b>{$Item['Item_Name']}</b><br />";
				echo    "x" . number_format($Item['Quantity']);
				echo  "</div>";
			}
		}

		exit();
	}

	/**
	 * Primary inventory code below.
	 */
	$Item_Categories = ['Battle Item', 'General Item', 'Held Item', 'Key Item', 'Medicine', 'Pokeballs'];
	$Item_Types = ['Battle', 'Berry', 'General', 'Held', 'Key', 'Machine', 'Medicine', 'Pokeball'];
	$Item_Type = 'Held Item';

	try
	{
		$Fetch_Equipped = $PDO->prepare("SELECT * FROM `pokemon` WHERE `Item` != 0 AND `Owner_Current` = ?");
		$Fetch_Equipped->execute([$User_Data['id']]);
		$Fetch_Equipped->setFetchMode(PDO::FETCH_ASSOC);
		$Equipped_Pokes = $Fetch_Equipped->fetchAll();

		$Fetch_Items = $PDO->prepare("SELECT * FROM `items` WHERE `Owner_Current` = ? AND `Item_Type` = ? AND `Quantity` > 0");
		$Fetch_Items->execute([$User_Data['id'], $Item_Type]);
		$Fetch_Items->setFetchMode(PDO::FETCH_ASSOC);
		$Items = $Fetch_Items->fetchAll();
	}
	catch ( PDOException $e )
	{
		HandleError( $e->getMessage() );
	}

	echo "
		<div class='panel' style='margin-bottom: 5px;'>
			<div class='panel-heading'>Inventory</div>
			<div class='panel-body inventory'>
				<div>
	";

	foreach ( $Item_Categories as $Category )
	{
		echo "
			<div style='float: left; padding: 5px; width: calc(100% / 6);' onclick=\"itemHandler('item_tab', '$Category');\">
				$Category
			</div>
		";
	}
				
	echo "
			</div>
			<div id='activeTab'>
	";

		if ( count($Items) == 0 )
		{
			echo	"<div style='padding: 2px;'>There are no items in your inventory.</div>";
		}
		else
		{
			foreach ($Items as $key => $Item)
			{
				$Item_Data = $Item_Class->FetchItemData($Item['Item_ID']);
				
				echo  "<div class='item_cont' onclick=\"itemHandler('item_data', '{$Item_Data['Category']}', {$Item['Item_ID']});\">";
				echo    "<div style='float: left;'>";
				echo      "<img src='images/Items/" . $Item_Data['Name'] . ".png' />";
				echo    "</div>";
				echo    "<b>{$Item_Data['Name']}</b><br />";
				echo    "x" . number_format($Item['Quantity']);
				echo  "</div>";
			}
		}

	echo "
		</div>
		
			</div>
		</div>

		<div class='panel' style='float: left; margin-right: 0.5%; width: 49.75%;'>
			<div class='panel-heading'>
				Attached Items
				<div style='float: right; padding-right: 5px;'>
					<a href='javascript:void(0);' onclick=\"itemHandler('detachall');\">Detach All</a>
				</div>
			</div>
			<div class='panel-body' style='padding: 5px 5px 0px 5px;'>";

			if ( count($Equipped_Pokes) == 0 )
			{
				echo "<div style='padding-bottom: 5px;'>None of your Pokemon have an item equipped.</div>";
			}
			else
			{
				foreach ($Equipped_Pokes as $Key => $Value)
				{
					$Item_Data = $Item_Class->FetchItemData($Value['Item']);
					$Poke_Data = $Poke_Class->FetchPokemonData($Value['ID']);

					echo "
						<div class='panel held_item' style='width: 100%;'>
							<div class='panel-heading'>{$Poke_Data['Display_Name']}</div>
							<div class='panel-body'>
								<div style='float: left;'>
									<img src='{$Poke_Data['Icon']}' />
									<img src='images/Items/{$Item_Data['Name']}.png' />
								</div>
								<div style='float: left;'>
									{$Item_Data['Name']}
								</div>
								<div>
									<a href='javascript:void(0);' onclick=\"itemHandler('detach', null, null, {$Poke_Data['ID']});\">Unequip</a>
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
			<div class='panel-body' id='itemData'>
				<div style='padding: 5px;'>Please select an item to use it.</div>
			</div>
		</div>
	";