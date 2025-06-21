<?php
	require_once '../../required/session.php';

	if ( isset($_SESSION['EvoChroniclesRPG']['Trade']) )
	{
		/**
		 * If both sides of the trade are empty, throw an error.
		 * There's no need to create the trade if no appropriate data is being sent.
		 */
		if
		(
			empty($_SESSION['EvoChroniclesRPG']['Trade']['Sender']['Pokemon']) && empty($_SESSION['EvoChroniclesRPG']['Trade']['Sender']['Currency']) && empty($_SESSION['EvoChroniclesRPG']['Trade']['Sender']['Items']) &&
			empty($_SESSION['EvoChroniclesRPG']['Trade']['Recipient']['Pokemon']) && empty($_SESSION['EvoChroniclesRPG']['Trade']['Recipient']['Currency']) && empty($_SESSION['EvoChroniclesRPG']['Trade']['Recipient']['Items'])
		)
		{
			echo "<div class='error'>Both sides of the trade may not be empty.</div>";
		}
		else
		{
			$Recipient_Data = $User_Class->FetchUserData( $_SESSION['EvoChroniclesRPG']['Trade']['Recipient']['User'] );
			$Recipient_Username = $User_Class->DisplayUserName($Recipient_Data['ID']);

			echo "
				<div class='success'>
					You have successfully sent a trade to <b>{$Recipient_Username}</b>.
				</div>
			";

			/**
			 * Process the sender's half of the trade.
			 */
			$Sender_Pokemon = '';
			$Sender_Currency = '';
			$Sender_Items = '';
			foreach( $_SESSION['EvoChroniclesRPG']['Trade']['Sender']['Pokemon'] as $Key => $Pokemon_1 )
			{
				try
				{
					$Update_Location = $PDO->prepare("UPDATE `pokemon` SET `Location` = 'Trade' WHERE `ID` = ? LIMIT 1");
					$Update_Location->execute([ $Pokemon_1['ID'] ]);
				}
				catch( PDOException $e )
				{
					HandleError($e);
				}

				$Sender_Pokemon .= $Pokemon_1['ID'] . ",";
			}
			foreach( $_SESSION['EvoChroniclesRPG']['Trade']['Sender']['Currency'] as $Key => $Currency_1 )
			{
				$Sender_Currency .= $Currency_1['Currency'] . "-" . $Currency_1['Quantity'] . ",";
			}
			foreach( $_SESSION['EvoChroniclesRPG']['Trade']['Sender']['Items'] as $Key => $Items_1 )
			{
				$Sender_Items .= $Items_1['Row'] . "-" . $Items_1['ID'] . "-" . $Items_1['Quantity'] . "-" . $Items_1['Owner'] . ",";
			}

			/**
			 * Process the Recipient's half of the trade.
			 */
			$Recipient_Pokemon = '';
			$Recipient_Currency = '';
			$Recipient_Items = '';
			foreach( $_SESSION['EvoChroniclesRPG']['Trade']['Recipient']['Pokemon'] as $Key => $Pokemon_2 )
			{
				try
				{
					$Update_Location = $PDO->prepare("UPDATE `pokemon` SET `Location` = 'Trade' WHERE `ID` = ? LIMIT 1");
					$Update_Location->execute([ $Pokemon_2['ID'] ]);
				}
				catch( PDOException $e )
				{
					HandleError($e);
				}

				$Recipient_Pokemon .= $Pokemon_2['ID'] . ",";
			}
			foreach( $_SESSION['EvoChroniclesRPG']['Trade']['Recipient']['Currency'] as $Key => $Currency_2 )
			{
				$Recipient_Currency .= $Currency_2['Currency'] . "-" . $Currency_2['Quantity'] . ",";
			}
			foreach( $_SESSION['EvoChroniclesRPG']['Trade']['Recipient']['Items'] as $Key => $Items_2 )
			{
				$Recipient_Items .= $Items_2['Row'] . "-" . $Items_2['ID'] . "-" . $Items_2['Quantity'] . "-" . $Items_2['Owner'] . ",";
			}

			/**
			 * Create a row in the database table `trades` with the necessary trade information.
			 */
			try
			{
				$Create_Query = $PDO->prepare("
					INSERT INTO `trades` (
						`Sender`,
						`Sender_Pokemon`,
						`Sender_Items`,
						`Sender_Currency`,
						`Recipient`,
						`Recipient_Pokemon`,
						`Recipient_Items`,
						`Recipient_Currency`
					)
					VALUES (?, ?, ?, ?, ?, ?, ?, ?)
				");
				$Create_Query->execute([
					$_SESSION['EvoChroniclesRPG']['Trade']['Sender']['User'],
					substr($Sender_Pokemon, 0, -1),
					substr($Sender_Items, 0, -1),
					substr($Sender_Currency, 0, -1),
					$_SESSION['EvoChroniclesRPG']['Trade']['Recipient']['User'],
					substr($Recipient_Pokemon, 0, -1),
					substr($Recipient_Items, 0, -1),
					substr($Recipient_Currency, 0, -1)
				]);
			}
			catch( PDOException $e )
			{
				HandleError($e);
			}
		}
	}
	else
	{
		echo "<div class='error' style='margin-bottom: 5px;'>The trade could not be made, as as error has occurred.</div>";
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
