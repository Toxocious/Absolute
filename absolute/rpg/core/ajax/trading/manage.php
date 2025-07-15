<?php
	require_once '../../required/session.php';

	/**
	 * Handle both accepting trades, as well as declining/deleting trades.
	 */
	if ( isset($_POST['Trade_ID']) && isset($_POST['Action']) )
	{
		$Trade_ID = Purify($_POST['Trade_ID']);
		$Action = Purify($_POST['Action']);

		try
		{
			$Trade_Query = $PDO->prepare("SELECT * FROM `trades` WHERE `ID` = ? AND (`Sender` = ? OR `Recipient` = ?) AND `Status` = ?");
			$Trade_Query->execute([ $Trade_ID, $User_Data['ID'], $User_Data['ID'], 'Pending' ]);
			$Trade_Query->setFetchMode(PDO::FETCH_ASSOC);
			$Trade_Content = $Trade_Query->fetchAll();

			$Update_Status = $PDO->prepare("UPDATE `trades` SET `Status` = ? WHERE `ID` = ? LIMIT 1");
			$Update_Status->execute([ $Action, $Trade_ID ]);
		}
		catch( PDOException $e )
		{
			HandleError($e);
		}

		echo "
			<div class='success'>
				You have successfully " . strtolower($Action) . " trade #{$Trade_ID}.
			</div>
		";

		/**
		 * Process the logic required to accept the trade.
		 */
		if ( $Action == 'Accepted' )
		{
			$Sender = $User_Class->FetchUserData($Trade_Content[0]['Sender']);
			$Recipient = $User_Class->FetchUserData($Trade_Content[0]['Recipient']);

			/**
			 * Process the sender's half of the trade.
			 */
			if ( !empty($Trade_Content[0]['Sender_Pokemon']) )
			{
				$Sender_Pokemon = explode(',', $Trade_Content[0]['Sender_Pokemon']);
				foreach( $Sender_Pokemon as $Key => $Pokemon_1 )
				{
					$Pokemon_Data = GetPokemonData($Pokemon_1);

					try
					{
						$Update_Location = $PDO->prepare("UPDATE `pokemon` SET `Location` = 'Box', `Owner_Current` = ?  WHERE `ID` = ? LIMIT 1");
						$Update_Location->execute([ $Recipient['ID'], $Pokemon_Data['ID'] ]);
					}
					catch( PDOException $e )
					{
						HandleError($e);
					}
				}
			}

			if ( !empty($Trade_Content[0]['Sender_Items']) )
			{
				$Sender_Items = explode(',', $Trade_Content[0]['Sender_Items']);
				foreach ( $Sender_Items as $Key => $Item )
				{
					// row-id-quantity-owner
					$Item_Params = explode('-', $Item);
					$Item_Data = $Item_Class->FetchOwnedItem($Trade_Content[0]['Sender'], $Item_Params[1]);

					// $User_ID, $Item_ID, $Quantity, $Subtract = false
					$Update_Sender_Items = $Item_Class->SpawnItem( $Sender['ID'], $Item_Data['ID'], $Item_Params[2], true );
					$Update_Recipient_Items = $Item_Class->SpawnItem( $Recipient['ID'], $Item_Data['ID'], $Item_Params[2] );
				}
			}

			if ( !empty($Trade_Content[0]['Sender_Currency']) )
			{
				$Sender_Currency = explode(',', $Trade_Content[0]['Sender_Currency']);
				foreach ( $Sender_Currency as $Key => $Currency )
				{
					$Currency_Info = explode('-', $Currency);
					$Currency_Data = $Constants->Currency[$Currency_Info[0]];

					try
					{
						$Update_Sender_Currency = $PDO->prepare("UPDATE `users` SET `{$Currency_Data['Value']}` = `{$Currency_Data['Value']}` - ? WHERE `id` = ? LIMIT 1");
						$Update_Sender_Currency->execute([ $Currency_Info[1], $Sender['ID'] ]);

						$Update_Recipient_Currency = $PDO->prepare("UPDATE `users` SET `{$Currency_Data['Value']}` = `{$Currency_Data['Value']}` + ? WHERE `id` = ? LIMIT 1");
						$Update_Recipient_Currency->execute([ $Currency_Info[1], $Recipient['ID'] ]);
					}
					catch( PDOException $e )
					{
						HandleError($e);
					}
				}
			}

			/**
			 * Process the Recipient's half of the trade.
			 */
			if ( !empty($Trade_Content[0]['Recipient_Pokemon']) )
			{
				$Recipient_Pokemon = explode(',', $Trade_Content[0]['Recipient_Pokemon']);
				foreach( $Recipient_Pokemon as $Key => $Pokemon_2 )
				{
					$Pokemon_Data = GetPokemonData($Pokemon_2);

					try
					{
						$Update_Location = $PDO->prepare("UPDATE `pokemon` SET `Location` = 'Box', `Owner_Current` = ? WHERE `ID` = ? LIMIT 1");
						$Update_Location->execute([ $Sender['ID'], $Pokemon_Data['ID'] ]);
					}
					catch( PDOException $e )
					{
						HandleError($e);
					}
				}
			}

			if ( !empty($Trade_Content[0]['Recipient_Items']) )
			{
				$Recipient_Items = explode(',', $Trade_Content[0]['Recipient_Items']);
				foreach ( $Recipient_Items as $Key => $Item )
				{
					// row-id-quantity-owner
					$Item_Params = explode('-', $Item);
					$Item_Data = $Item_Class->FetchOwnedItem($Trade_Content[0]['Recipient'], $Item_Params[1]);

					// $User_ID, $Item_ID, $Quantity, $Subtract = false
					$Update_Recipient_Items = $Item_Class->SpawnItem( $Recipient['ID'], $Item_Data['ID'], $Item_Params[2], true );
					$Update_Sender_Items = $Item_Class->SpawnItem( $Sender['ID'], $Item_Data['ID'], $Item_Params[2] );
				}
			}

			if ( !empty($Trade_Content[0]['Recipient_Currency']) )
			{
				$Recipient_Currency = explode(',', $Trade_Content[0]['Recipient_Currency']);
				foreach ( $Recipient_Currency as $Key => $Currency )
				{
					$Currency_Info = explode('-', $Currency);
					$Currency_Data = $Constants->Currency[$Currency_Info[0]];

					try
					{
						$Update_Recipient_Currency = $PDO->prepare("UPDATE `users` SET `{$Currency_Data['Value']}` = `{$Currency_Data['Value']}` - ? WHERE `id` = ? LIMIT 1");
						$Update_Recipient_Currency->execute([ $Currency_Info[1], $Recipient['ID'] ]);

						$Update_Sender_Currency = $PDO->prepare("UPDATE `users` SET `{$Currency_Data['Value']}` = `{$Currency_Data['Value']}` + ? WHERE `id` = ? LIMIT 1");
						$Update_Sender_Currency->execute([ $Currency_Info[1], $Sender['ID'] ]);
					}
					catch( PDOException $e )
					{
						HandleError($e);
					}
				}
			}
		}

		/**
		 * Process the logic required to delete the trade.
		 */
		else if ( $Action == 'Declined' || $Action == 'Deleted' )
		{
			/**
			 * Process the sender's half of the trade.
			 */
			if ( !empty($Trade_Content[0]['Sender_Pokemon']) )
			{
				$Sender_Pokemon = explode(',', $Trade_Content[0]['Sender_Pokemon']);
				foreach( $Sender_Pokemon as $Key => $Pokemon_1 )
				{
					$Pokemon_Data = GetPokemonData($Pokemon_1);

					try
					{
						$Update_Location = $PDO->prepare("UPDATE `pokemon` SET `Location` = 'Box' WHERE `ID` = ? LIMIT 1");
						$Update_Location->execute([ $Pokemon_Data['ID'] ]);
					}
					catch( PDOException $e )
					{
						HandleError($e);
					}
				}
			}

			/**
			 * Process the Recipient's half of the trade.
			 */
			if ( !empty($Trade_Content[0]['Recipient_Pokemon']) )
			{
				$Recipient_Pokemon = explode(',', $Trade_Content[0]['Recipient_Pokemon']);
				foreach( $Recipient_Pokemon as $Key => $Pokemon_2 )
				{
					$Pokemon_Data = GetPokemonData($Pokemon_2);

					try
					{
						$Update_Location = $PDO->prepare("UPDATE `pokemon` SET `Location` = 'Box' WHERE `ID` = ? LIMIT 1");
						$Update_Location->execute([ $Pokemon_Data['ID'] ]);
					}
					catch( PDOException $e )
					{
						HandleError($e);
					}
				}
			}
		}
?>

<div style='flex-basis: 49%; margin: 5px 3px;'>
<div class='description'>Enter another user's ID to begin a trade with them.</div>

<table class='border-gradient' style='width: 100%;'>
	<thead>
		<tr>
			<th colspan='1'>
				Create A Trade
			</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td>
				<input type='text' placeholder='User ID' id='recipientID' style='text-align: center; width: 200px;'/>
			</td>
		</tr>
		<tr>
			<td>
				<button onclick='TradePrepare();' style='width: 200px;'>
					Begin A Trade
				</button>
			</td>
		</tr>
	</tbody>
</table>
</div>

<div style='flex-basis: 49%; margin: 5px 3px;'>
<div class='description'>All pending trades that involve you are listed below.</div>

<?php
	try
	{
		$Pending_Query = $PDO->prepare("SELECT `ID`, `Sender`, `Recipient`, `Status` FROM `trades` WHERE (`Sender` = ? OR `Recipient` = ?) AND `Status` = ?");
		$Pending_Query->execute([ $User_Data['ID'], $User_Data['ID'], 'Pending' ]);
		$Pending_Query->setFetchMode(PDO::FETCH_ASSOC);
		$Pending_Trades = $Pending_Query->fetchAll();
	}
	catch( PDOException $e )
	{
		HandleError($e);
	}

	if ( count($Pending_Trades) === 0 )
	{
		$Trade_Text = "
			<tr>
				<td colspan='3' style='padding: 7px;'>
					You do not currently have any pending trades.
				</td>
			</tr>
		";
	}
	else
	{
		$Trade_Text = "
			<tbody>
				<td colspan='1' style='padding: 7px;'>
					<b>Trade ID</b>
				</td>
				<td colspan='1' style='padding: 7px;'>
					<b>Sender</b>
				</td>
				<td colspan='1' style='padding: 7px;'>
					<b>Recipient</b>
				</td>
			</tbody>
		";

		foreach( $Pending_Trades as $Key => $Value )
		{
			$Sender = $User_Class->FetchUserData($Value['Sender']);
			$Sender_Username = $User_Class->DisplayUserName($Sender['ID']);

			$Recipient = $User_Class->FetchUserData($Value['Recipient']);
			$Recipient_Username = $User_Class->DisplayUserName($Recipient['ID']);

			$Trade_Text .= "
				<tr>
					<td style='padding: 6px;'>
						<a href='javascript:void(0);' onclick='TradeView({$Value['ID']});'>#" . number_format($Value['ID']) . "</a>
					</td>
					<td style='padding: 6px;'>
						<a href='" . DOMAIN_ROOT . "/profile.php?id={$Sender['ID']}'>{$Sender_Username}</a>
					</td>
					<td style='padding: 6px;'>
						<a href='" . DOMAIN_ROOT . "/profile.php?id={$Recipient['ID']}'>{$Recipient_Username}</a>
					</td>
				</tr>
			";
		}
	}
?>

<table class='border-gradient' style='width: 100%;'>
	<thead>
		<tr>
			<th colspan='3'>
				Pending Trades
			</th>
		</tr>
	</thead>
	<tbody>
		<?= $Trade_Text; ?>
	</tbody>
</table>
</div>


<?php
	 }
	 else
	 {
		 echo "An error has occurred while trying to accept or delete this trade.";
	 }
