<?php
	require '../../required/session.php';

	/**
	 * Check for any AJAX requests.
	 */
	if ( isset($_POST['request']) )
	{
		$Request = Purify($_POST['request']);

		$Valid_Requests = ['attach', 'detach', 'detachall', 'item_data', 'item_tab'];

		/**
		 * Throw an error if the request is invalid.
		 */
		if ( !in_array($Request, $Valid_Requests) )
		{
			return [
				'Error' => 'The sent AJAX request is invalid.',
				'Info' => [
					'Request' => $Request,
					'Valid_Requests' => $Valid_Requests,
					'User_ID' => $User_Data['id'],
					'Time' => time(),
				],
			];
		}

		/**
		 * Attach an item to a Pokemon.
		 */
		if ( $Request === 'attach' )
		{
			/**
			 * Return an error if any of the item category, item id, or pokemon id aren't set.
			 */
			if ( !isset($_POST['id']) || !isset($_POST['pokeid']) )
			{
				echo "
					<div class='error'>
						The sent AJAX request is missing a required data field.
					</div>
				";
			}

			/**
			 * Process attaching the item.
			 */
			else
			{
				$Item_ID = Purify($_POST['id']);
				$Pokemon_ID = Purify($_POST['pokeid']);
		
				$Item_Data = $Item_Class->FetchOwnedItem($User_Data['id'], $Item_ID);
				$Poke_Data = $Poke_Class->FetchPokemonData($Pokemon_ID);

				if ( $Item_Data['Quantity'] < 1 )
				{
					echo "
						<div class='error'>
							You do not own enough of this item to attach it to a Pokemon.
						</div>
					";
				}

				else if ( $Poke_Data['Owner_Current'] !== $User_Data['id'] )
				{
					echo "
						<div class='error'>
							You may not attach items to Pok&eacutemon; that do not belong to you.
						</div>
					";
				}

				else
				{
					$Item_Class->Attach($Item_ID, $Pokemon_ID, $User_Data['id']);

					echo "
						<div class='success'>
							You have successfully attached your {$Item_Data['Name']} to your {$Poke_Data['Display_Name']}.
						</div>
					";
				}
			}
		}

		/**
		 * Detach an item from a Pokemon.
		 */
		else if ( $Request === 'detach' )
		{
			/**
			 * Return an error if the item id or the pokemon's id aren't set.
			 */
			if ( !isset($_POST['pokeid']) )
			{
				echo "
					<div class='error'>
						The sent AJAX request is missing a required data field.
					</div>
				";
			}
			else
			{
				$Pokemon_ID = Purify($_POST['pokeid']);

				$Poke_Data = $Poke_Class->FetchPokemonData($Pokemon_ID);

				/**
				 * Check to see if the requesting user is the owner of the Pokemon.
				 */
				if ( $Poke_Data['Owner_Current'] === $User_Data['id'] )
				{
					$Item_Removal = $Item_Class->Unequip($Pokemon_ID, $User_Data['id']);

					echo "
						<div class='{$Item_Removal['Type']}'>
							{$Item_Removal['Message']}
						</div>
					";
				}
				else
				{
					echo "
						<div class='error'>
							You may not remove items from Pok&eacute;mon that do not belong to you.
						</div>
					";
				}
			}
		}

		/**
		 * Detach all equipped items from your Pokemon.
		 */
		else if ( $Request === 'detachall' )
		{
			try
			{
				$Fetch_Equipped = $PDO->prepare("SELECT * FROM `pokemon` WHERE `Item` != 0 AND `Owner_Current` = ?");
				$Fetch_Equipped->execute([ $User_Data['id'] ]);
				$Fetch_Equipped->setFetchMode(PDO::FETCH_ASSOC);
				$Items = $Fetch_Equipped->fetchAll();
			}
			catch( PDOException $e )
			{
				HandleError( $e->getMessage() );
			}

			foreach( $Items as $Key => $Value )
			{
				$Item_Class->Unequip($Value['ID'], $User_Data['id']);
			}

			if ( count($Items) == 0 )
			{
				echo "
					<div class='warning'>
						None of your Pok&eacute;mon were holding items.
					</div>
				";
			}
			else
			{
				echo "
					<div class='success'>
						All items that your Pok&eacute;mon were holding have been detached.
					</div>
				";
			}
		}

		/**
		 * Display the selected item's information.
		 */
		else if ( $Request === 'item_data' )
		{
			$Item_ID = Purify($_POST['id']);
			$Item_Data = $Item_Class->FetchItemData($Item_ID);

			$Slot_Text = '';
			for ( $i = 0; $i <= 5; $i++ )
			{
				if ( isset($Roster[$i]['ID']) )
				{
					$Roster_Slot[$i] = $Poke_Class->FetchPokemonData($Roster[$i]['ID']);
	
					if ( $Roster_Slot[$i]['Item'] == null )
					{
						$Slot_Text .= "
							<td colspan='1'>
								<img class='spricon' src='{$Roster_Slot[$i]['Icon']}' onclick=\"itemHandler('attach', '{$Item_Data['Category']}', {$Item_Data['ID']}, {$Roster_Slot[$i]['ID']});\" />
							</td>
						";
					}
					else
					{
						$Slot_Text .= "
							<td colspan='1'>
								<img class='spricon' src='{$Roster_Slot[$i]['Icon']}' style='filter: grayscale(100%);' />
							</td>
						";
					}				
				}
				else
				{
					$Roster_Slot[$i]['Icon'] = DOMAIN_SPRITES . "/Pokemon/Sprites/0_mini.png";

					$Slot_Text .= "
						<td colspan='1'>
							<img class='spricon' src='{$Roster_Slot[$i]['Icon']}' style='filter: grayscale(100%);' />
						</td>
					";
				}
			}

			echo "
				<tr>
					<td colspan='3'>
						<img src='{$Item_Data['Icon']}' />
					</td>
					<td colspan='3'>
						<b>{$Item_Data['Name']}</b>
					</td>
				</tr>
				<tr>
					<td colspan='6'>
						{$Item_Data['Description']}
					</td>
				</tr>
				<tr>
					<td colspan='6'>
						<b>Pick a Pok&eacute;mon to equip to:</b>
					</td>
				</tr>
				<tr>
					{$Slot_Text}
				</tr>
			";

			return;
		}

		/**
		 * Display the user's owned items of a given category.
		 */
		else if ( $Request === 'item_tab' )
		{
			if ( !isset($_POST['category']) )
			{
				echo "
					<div class='error'>
						The requested item category was not set.
					</div>
				";

				return;
			}

			$Category = Purify($_POST['category']);

			try
			{
				$Fetch_Items = $PDO->prepare("SELECT * FROM `items` WHERE `Owner_Current` = ? AND `Item_Type` = ? AND `Quantity` > 0 ORDER BY `Item_Name` ASC");
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
				echo "
					<tr>
						<td colspan='6' style='padding: 5px;'>
							This section of your bag is empty.
						</td>
					</tr>
				";
			}
			else
			{
				$Item_Counter = 0;
				$Item_List = '';

				foreach ($Items as $Key => $Item)
				{
					$Item_Data = $Item_Class->FetchItemData($Item['Item_ID']);

					if ( $Item_Counter % 6 === 0 )
					{
						$Item_List .= "
							<tr>
						";
					}

					$Item_List .= "
						<td onclick=\"itemHandler('item_data', '{$Item_Data['Category']}', {$Item['Item_ID']});\">
							<div style='float: left;'>
								<img src='" . DOMAIN_SPRITES . "/Items/{$Item['Item_Name']}.png' />
							</div>
							<b>{$Item_Data['Name']}</b><br />
							Owned: " . number_format($Item['Quantity']) . "
						</td>
					";

					if ( $Item_Counter % 6 === 5 )
					{
						$Item_List .= "
							</tr>
						";
					}

					$Item_Counter++;
				}

				while ( $Item_Counter % 6 !== 0 )
				{
					$Item_List .= "
						<td></td>
					";

					if ( $Item_Counter % 6 === 5 )
					{
						$Item_List .= "
							</tr>
						";
					}

					$Item_Counter++;
				}

				echo "
					{$Item_List}
				";
			}

			return;
		}
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
		$Fetch_Equipped->execute([ $User_Data['id'] ]);
		$Fetch_Equipped->setFetchMode(PDO::FETCH_ASSOC);
		$Equipped_Pokes = $Fetch_Equipped->fetchAll();

		$Fetch_Items = $PDO->prepare("SELECT * FROM `items` WHERE `Owner_Current` = ? AND `Item_Type` = ? AND `Quantity` > 0 ORDER BY `Item_Name` ASC");
		$Fetch_Items->execute([ $User_Data['id'], $Item_Type ]);
		$Fetch_Items->setFetchMode(PDO::FETCH_ASSOC);
		$Items = $Fetch_Items->fetchAll();
	}
	catch ( PDOException $e )
	{
		HandleError( $e->getMessage() );
	}

	/**
	 * Display the currently equipped items of your Pokemon.
	 */
	if ( count($Equipped_Pokes) === 0 )
	{
		$Equipped_Text = "
			<tr>
				<td colspan='3' style='padding: 12px;'>
					None of your Pok&eacute;mon have items attached.
				</td>
			</tr>
		";
	}
	else
	{
		$Equipped_Text = "
			<tr>
				<td colspan='3'>
					<button onclick=\"itemHandler('detachall');\">
						Detach All
					</button>
				</td>
			</tr>
		";

		foreach ( $Equipped_Pokes as $Poke_Key => $Poke_Val )
		{
			$Equipped_Pokemon = $Poke_Class->FetchPokemonData($Poke_Val['ID']);
			$Equipped_Item = $Item_Class->FetchItemData($Poke_Val['Item']);

			$Equipped_Text .= "
				<tr>
					<td colspan='1'>
						<img src='{$Equipped_Pokemon['Icon']}' />
					</td>
					<td colspan='1'>
						<b>{$Equipped_Pokemon['Display_Name']}</b><br />
						<i style='font-size: 12px;'>{$Equipped_Item['Name']}</i>
					</td>
					<td colspan='1'>
						<a href='javascript:void(0);' onclick=\"itemHandler('detach', null, null, {$Equipped_Pokemon['ID']});\">
							Detach
						</a>
					</td>
				</tr>
			";
		}
	}

	/**
	 * Display each of the available item categories.
	 */
	$Category_Text = '';
	foreach ( $Item_Categories as $Category )
	{
		$Category_Image = DOMAIN_SPRITES . "/Assets/bag_" . strtolower(explode(' ', $Category)[0]) . '.png';

		$Category_Text .= "
			<td style='padding: 5px 5px 3px; width: calc(100% / 6);' onclick=\"itemHandler('item_tab', '$Category');\">
				<img src='{$Category_Image}' />
				<br />
				<b>{$Category}</b>
			</td>
		";
	}

	/**
	 * Display the user's items in the specified category.
	 */
	if ( count($Items) == 0 )
	{
		$Inventory_Items = "
			<tr>
				<td colspan='6'>
					There are no items in your inventory.
				</td>
			</tr>
		";
	}
	else
	{
		$Item_Count = 0;
		$Inventory_Items = '';

		foreach ($Items as $Key => $Item)
		{
			$Item_Data = $Item_Class->FetchItemData($Item['Item_ID']);

			if ( $Item_Count % 6 === 0 )
			{
				$Inventory_Items .= "
					<tr>
				";
			}

			$Inventory_Items .= "
				<td style='width: calc(100% / 6);' onclick=\"itemHandler('item_data', '{$Item_Data['Category']}', {$Item['Item_ID']});\">
					<div style='float: left;'>
						<img src='" . DOMAIN_SPRITES . "/Items/{$Item['Item_Name']}.png' />
					</div>
					<b>{$Item_Data['Name']}</b><br />
					Owned: " . number_format($Item['Quantity']) . "
				</td>
			";

			if ( $Item_Count % 6 === 5 )
			{
				$Inventory_Items .= "
					</tr>
				";
			}

			$Item_Count++;
		}

		while ( $Item_Count % 6 !== 0 )
		{
			$Inventory_Items .= "
				<td></td>
			";

			if ( $Item_Count % 6 === 5 )
			{
				$Inventory_Items .= "
					</tr>
				";
			}

			$Item_Count++;
		}
	}

	/**
	 * Display the user's inventory.
	 */
	echo "
		<table class='border-gradient' style='margin-bottom: 5px; flex-basis: 85%;'>
			<thead>
				<tr>
					<th colspan='6'>
						Inventory
					</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					{$Category_Text}
				</tr>
			</tbody>
			<tbody id='activeTab'>
				{$Inventory_Items}
			</tbody>
		</table>

		<table class='border-gradient' style='margin: 5px 5px 5px 7.5%; width: 370px;'>
			<thead>
				<tr>
					<th colspan='3'>
						Equipped Items
					</th>
				</tr>
			</thead>
			<tbody>
				{$Equipped_Text}
			</tbody>
		</table>

		<table class='border-gradient' style='margin: 5px auto 5px 9px; width: 370px;'>
			<thead>
				<tr>
					<th colspan='6'>
						Item Data
					</th>
				</tr>
			</thead>
			<tbody id='itemData'>
				<tr>
					<td colspan='6' style='padding: 12px;'>
						Select an item to view it's information.
					</td>
				</tr>
			</tbody>
		</table>
	";