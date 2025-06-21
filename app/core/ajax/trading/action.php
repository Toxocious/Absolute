<?php
	require_once '../../required/session.php';

	if ( !isset($_POST['Action']) )
	{
		echo "
			<tr>
				<td colspan='3'>
					Unable to determine the desired trade action.<br />
					Please try again.
				</td>
			</tr>
		";

		return;
	}

	if ( !isset($_POST['Type']) )
	{
		echo "
			<tr>
				<td colspan='3'>
					Unable to determine the desired object type.<br />
					Please try again.
				</td>
			</tr>
		";

		return;
	}

	if ( !isset($_POST['ID']) )
	{
		echo "
			<tr>
				<td colspan='3'>
					Unable to determine the desired sender or recipient ID.<br />
					Please try again.
				</td>
			</tr>
		";

		return;
	}

	if ( !isset($_POST['Data']) )
	{
		echo "
			<tr>
				<td colspan='3'>
					Unable to determine the desired trade data.<br />
					Please try again.
				</td>
			</tr>
		";

		return;
	}


	$Action = Purify($_POST['Action']);
	$Type = Purify($_POST['Type']);
	$Data = Purify($_POST['Data']);
	$User_ID = Purify($_POST['ID']);

	$User = $User_Class->FetchUserData($User_ID);

	/**
	 * Determine if something is getting added/removed from a certain side of the trade.
	 */
	if ( $User['ID'] == $_SESSION['EvoChroniclesRPG']['Trade']['Sender']['User'] )
	{
		$Side = "Sender";
	}
	else
	{
		$Side = "Recipient";
	}

	/**
	 * Add things to the trade.
	 */
	if ( $Action == 'Add' )
	{
		$Already_Included = false;

		/**
		 * Add Pokemon to the trade.
		 */
		if ( $Type == 'Pokemon' )
		{
			$Pokemon = GetPokemonData($Data);

			if ( isset($_SESSION['EvoChroniclesRPG']['Trade'][$Side]['Pokemon']) )
			{
				foreach ( $_SESSION['EvoChroniclesRPG']['Trade'][$Side]['Pokemon'] as $Key => $Value )
				{
					if ( $Value['ID'] == $Pokemon['ID'] )
					{
						$Already_Included = true;
					}
				}
			}

			if ( !isset($Pokemon) || !$Pokemon )
			{
				echo "
					<tr>
						<td colspan='3' style='padding: 10px;'>
							<b style='color: #f00;'>
								This Pok&eacute;mon doesn't exist.
							</b>
						</td>
					</tr>
				";
			}
			else if ( $Pokemon['Owner_Current'] != $User['ID'] )
			{
				echo "
					<tr>
						<td colspan='3' style='padding: 10px;'>
							<b style='color: #f00;'>
								This Pok&eacute;mon doesn't belong to {$User['Username']}.
							</b>
						</td>
					</tr>
				";
			}
			else if ( $Pokemon['Frozen'] )
			{
				echo "
					<tr>
						<td colspan='3' style='padding: 10px;'>
							<b style='color: #f00;'>
								This Pok&eacute;mon is frozen and may not be traded.
							</b>
						</td>
					</tr>
				";
			}
			else if ( $Already_Included )
			{
				echo "
					<tr>
						<td colspan='3' style='padding: 10px;'>
							<b style='color: #f00;'>
								This Pok&eacute;mon is already included in the trade.
							</b>
						</td>
					</tr>
				";
			}
			else
			{
				echo "
					<tr>
						<td colspan='3' style='padding: 10px;'>
							<b style='color: #0f0;'>
								{$Pokemon['Display_Name']} has been added to the trade.
							</b>
						</td>
					</tr>
				";

				$_SESSION['EvoChroniclesRPG']['Trade'][$Side]['Pokemon'][] = [
					'ID' => $Pokemon['ID'],
				];
			}
		}

		/**
		 * Add Items to the trade.
		 */
		if ( $Type == 'Item' )
		{
			/**
			 * Add Tradeable check
			 * if ( in_array($Item['Item_ID'], [untradeable item id's]) ) { echo error }
			 */
			$Item = $Item_Class->FetchOwnedItem($User['ID'], $Data);

			if ( isset( $_SESSION['EvoChroniclesRPG']['Trade'][$Side]['Items']) )
			{
				foreach ( $_SESSION['EvoChroniclesRPG']['Trade'][$Side]['Items'] as $Key => $Value )
				{
					if ( $Value['ID'] == $Item['ID'] )
					{
						$Already_Included = true;
					}
				}
			}

			if ( !isset($Item) || !$Item )
			{
				echo "
					<tr>
						<td colspan='3' style='padding: 10px;'>
							<b style='color: #f00;'>
								The item that you're trying to add, doesn't exist.
							</b>
						</td>
					</tr>
				";
			}
			else if ( $Item['Owner'] != $User['ID'] )
			{
				echo "
					<tr>
						<td colspan='3' style='padding: 10px;'>
							<b style='color: #f00;'>
								This item doesn't belong to {$User['Username']}.
							</b>
						</td>
					</tr>
				";
			}
			else if ( $Already_Included )
			{
				echo "
					<tr>
						<td colspan='3' style='padding: 10px;'>
							<b style='color: #f00;'>
								This item is already included within the trade.
							</b>
						</td>
					</tr>
				";
			}
			else if ( $Item['Quantity'] <= 0 )
			{
				echo "
					<tr>
						<td colspan='3' style='padding: 10px;'>
							<b style='color: #f00;'>
								This user doesn't own enough of this item.
							</b>
						</td>
					</tr>
				";
			}
			else
			{
				echo "
					<tr>
						<td colspan='3' style='padding: 10px;'>
							<b style='color: #0f0;'>
								{$Item['Name']} has been added to the trade.
							</b>
						</td>
					</tr>
				";

				$_SESSION['EvoChroniclesRPG']['Trade'][$Side]['Items'][] = [
					'Row' => $Item['Row'],
					'ID' => $Item['ID'],
					'Quantity' => 1,
					'Owner' => $Item['Owner'],
				];
			}
		}

		/**
		 * Add Currencies to the trade.
		 */
		if ( $Type == 'Currency' )
		{
			$Currency_Data = [
				"Name" => explode('-', $Data['Name'])[2],
				"Amount" => $Data['Amount'],
			];

			if ( isset( $_SESSION['EvoChroniclesRPG']['Trade'][$Side]['Currency']) )
			{
				foreach ( $_SESSION['EvoChroniclesRPG']['Trade'][$Side]['Currency'] as $Key => $Value )
				{
					if ( $Value['Currency'] == $Currency_Data['Name'] )
					{
						$Already_Included = true;
					}
				}
			}

			try
			{
				$Currency_Query = $PDO->prepare("SELECT `Money`, `Abso_Coins` FROM `user_currency` WHERE `User_ID` = ?");
				$Currency_Query->execute([ $User['ID'] ]);
				$Currency_Query->setFetchMode(PDO::FETCH_ASSOC);
				$Currency = $Currency_Query->fetch();
			}
			catch( PDOException $e )
			{
				HandleError($e);
			}

			if ( $Currency_Data['Amount'] <= 0 )
			{
				echo "
					<tr>
						<td colspan='3' style='padding: 10px;'>
							<b style='color: #f00;'>
								Please add currency to the trade at a value of 0 or higher.
							</b>
						</td>
					</tr>
				";
			}
			else if ( $Currency_Data['Amount'] > $Currency[$Currency_Data['Name']] )
			{
				echo "
					<tr>
						<td colspan='3' style='padding: 10px;'>
							<b style='color: #f00;'>
								{$User['Username']} does not have enough {$Currency_Data['Name']} to add to the trade.
							</b>
						</td>
					</tr>
				";
			}
			else if ( $Already_Included )
			{
				echo "
				<tr>
						<td colspan='3' style='padding: 10px;'>
							<b style='color: #f00;'>
								You have already added {$Currency_Data['Name']} to {$User['Username']}'s side of the trade.
							</b>
						</td>
					</tr>
				";
			}
			else
			{
				echo "
					<tr>
						<td colspan='3' style='padding: 10px;'>
							<b style='color: #0f0;'>
								{$Currency_Data['Amount']} {$Currency_Data['Name']} has been added to the trade.
							</b>
						</td>
					</tr>
				";

				$_SESSION['EvoChroniclesRPG']['Trade'][$Side]['Currency'][] = [
					'Currency' => $Currency_Data['Name'],
					'Quantity' => $Currency_Data['Amount'],
				];
			}
		}
	}

	/**
	 * Remove things from the trade.
	 */
	else if ( $Action == 'Remove' )
	{
		/**
		 * Remove Pokemon from the trade.
		 */
		if ( $Type == 'Pokemon' )
		{
			if ( isset( $_SESSION['EvoChroniclesRPG']['Trade'][$Side]['Pokemon']) )
			{
				foreach ( $_SESSION['EvoChroniclesRPG']['Trade'][$Side]['Pokemon'] as $Key => $Pokemon )
				{
					if ( $Pokemon['ID'] == $Data )
					{
						$Poke_Data = GetPokemonData($Pokemon['ID']);

						array_splice(
							$_SESSION['EvoChroniclesRPG']['Trade'][$Side]['Pokemon'],
							$Key,
							1
						);

						echo "
							<tr>
								<td colspan='3' style='padding: 10px;'>
									<b style='color: #0f0;'>
										You have removed the {$Poke_Data['Display_Name']} from this side of the trade.
									</b>
								</td>
							</tr>
						";
					}
				}
			}
		}

		/**
		 * Remove Items from the trade.
		 */
		if ( $Type == 'Item' )
		{
			if ( isset( $_SESSION['EvoChroniclesRPG']['Trade'][$Side]['Items']) )
			{
				foreach ( $_SESSION['EvoChroniclesRPG']['Trade'][$Side]['Items'] as $Key => $Item )
				{
					if ( $Item['ID'] == $Data )
					{
						$Item_Data = $Item_Class->FetchItemData($Item['ID']);

						array_splice(
							$_SESSION['EvoChroniclesRPG']['Trade'][$Side]['Items'],
							$Key,
							1
						);

						echo "
							<tr>
								<td colspan='3' style='padding: 10px;'>
									<b style='color: #0f0;'>
										You have removed the {$Item_Data['Name']} from this side of the trade.
									</b>
								</td>
							</tr>
						";
					}
				}
			}
		}

		/**
		 * Remove Currencies from the trade.
		 */
		if ( $Type == 'Currency' )
		{
			if ( isset( $_SESSION['EvoChroniclesRPG']['Trade'][$Side]['Currency']) )
			{
				foreach ( $_SESSION['EvoChroniclesRPG']['Trade'][$Side]['Currency'] as $Key => $Currencies )
				{
					if ( $Currencies['Currency'] === $Data )
					{
						array_splice(
							$_SESSION['EvoChroniclesRPG']['Trade'][$Side]['Currency'],
							0,
							1
						);

						echo "
							<tr>
								<td colspan='3' style='padding: 10px;'>
									<b style='color: #0f0;'>
										You have removed the {$Data} from this side of the trade.
									</b>
								</td>
							</tr>
						";
					}
				}
			}
		}
	}

	/**
	 * Display the current content of the trade.
	 */
	if ( isset( $_SESSION['EvoChroniclesRPG']['Trade'][$Side]['Currency']) )
	{
		foreach ( $_SESSION['EvoChroniclesRPG']['Trade'][$Side]['Currency'] as $Key => $Currencies )
		{
			echo "
				<tr>
					<td colspan='1' style='width: 76px;'>
						<img src='" . DOMAIN_SPRITES . "/Assets/{$Currencies['Currency']}.png' />
					</td>
					<td colspan='1'>
						" . number_format($Currencies['Quantity']) . "
					</td>
					<td colspan='1' style='width: 76px;'>
						<button
							onclick='Remove_From_Trade({$User_ID}, \"Currency\", \"{$Currencies['Currency']}\");'
							style='width: 70px;'
						>
							Remove
						</button>
					</td>
				</tr>
			";
		}
	}

	if ( isset( $_SESSION['EvoChroniclesRPG']['Trade'][$Side]['Items']) )
	{
		foreach ( $_SESSION['EvoChroniclesRPG']['Trade'][$Side]['Items'] as $Key => $Items )
		{
			$Item_Data = $Item_Class->FetchItemData($Items['ID']);

			echo "
				<tr>
					<td colspan='1' style='width: 76px;'>
						<img src='{$Item_Data['Icon']}' />
					</td>
					<td colspan='1'>
						{$Item_Data['Name']}
						<br />
						x" . number_format($Items['Quantity']) . "
					</td>
					<td colspan='1' style='width: 76px;'>
						<button
							onclick='Remove_From_Trade({$User_ID}, \"Item\", \"{$Item_Data['ID']}\");'
							style='width: 70px;'
						>
							Remove
						</button>
					</td>
				</tr>
			";
		}
	}

	if ( isset( $_SESSION['EvoChroniclesRPG']['Trade'][$Side]['Pokemon']) )
	{
		foreach ( $_SESSION['EvoChroniclesRPG']['Trade'][$Side]['Pokemon'] as $Key => $Pokemon )
		{
			$Pokemon_Data = GetPokemonData($Pokemon['ID']);

			echo "
				<tr>
					<td colspan='1' style='width: 76px;'>
						<img src='{$Pokemon_Data['Icon']}' />
						<img src='{$Pokemon_Data['Gender_Icon']}' style='height: 20px; width: 20px;' />
					</td>
					<td colspan='1'>
						{$Pokemon_Data['Display_Name']} (Level: " . $Pokemon_Data['Level'] . ")
						<br />
						" . ($Pokemon_Data['Item'] ? $Pokemon_Data['Item_Name'] : '') . "
					</td>
					<td colspan='1' style='width: 76px;'>
						<button
							onclick='Remove_From_Trade({$User_ID}, \"Pokemon\", \"{$Pokemon_Data['ID']}\");'
							style='width: 70px;'
						>
							Remove
						</button>
					</td>
				</tr>
			";
		}
	}
