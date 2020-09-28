<?php
	require '../../required/session.php';

	if ( isset($_SESSION['Trade']) )
	{
		/**
		 * If both sides of the trade are empty, throw an error.
		 * There's no need to create the trade if no appropriate data is being sent.
		 */
		if
		( 
			empty($_SESSION['Trade']['Sender']['Pokemon']) && empty($_SESSION['Trade']['Sender']['Currency']) && empty($_SESSION['Trade']['Sender']['Items']) && 
			empty($_SESSION['Trade']['Receiver']['Pokemon']) && empty($_SESSION['Trade']['Receiver']['Currency']) && empty($_SESSION['Trade']['Receiver']['Items'])
		)
		{
			echo "<div class='error' style='margin-bottom: 5px;'>Both sides of the trade may not be empty.</div>";
		}
		else
		{
			$Receiver_Data = $User_Class->FetchUserData( $_SESSION['Trade']['Receiver']['User'] );
			$Receiver_Username = $User_Class->DisplayUserName($Receiver_Data['ID']);
			echo "
				<div class='success' style='margin-bottom: 5px;'>
					You have successfully sent a trade to <b>{$Receiver_Username}</b>.
				</div>
			";

			/**
			 * Process the sender's half of the trade.
			 */
			$Sender_Pokemon = '';
			$Sender_Currency = '';
			$Sender_Items = '';
			foreach( $_SESSION['Trade']['Sender']['Pokemon'] as $Key => $Pokemon_1 )
			{
				try
				{
					$Update_Location = $PDO->prepare("UPDATE `pokemon` SET `Location` = 'Trade' WHERE `ID` = ? LIMIT 1");
					$Update_Location->execute([ $Pokemon_1['ID'] ]);
				}
				catch( PDOException $e )
				{
					HandleError( $e->getMessage() );
				}

				$Sender_Pokemon .= $Pokemon_1['ID'] . ",";
			}
			foreach( $_SESSION['Trade']['Sender']['Currency'] as $Key => $Currency_1 )
			{
				$Sender_Currency .= $Currency_1['Currency'] . "-" . $Currency_1['Quantity'] . ",";
			}
			foreach( $_SESSION['Trade']['Sender']['Items'] as $Key => $Items_1 )
			{
				$Sender_Items .= $Items_1['Row'] . "-" . $Items_1['ID'] . "-" . $Items_1['Quantity'] . "-" . $Items_1['Owner'] . ",";
			}

			/**
			 * Process the receiver's half of the trade.
			 */
			$Receiver_Pokemon = '';
			$Receiver_Currency = '';
			$Receiver_Items = '';
			foreach( $_SESSION['Trade']['Receiver']['Pokemon'] as $Key => $Pokemon_2 )
			{
				try
				{
					$Update_Location = $PDO->prepare("UPDATE `pokemon` SET `Location` = 'Trade' WHERE `ID` = ? LIMIT 1");
					$Update_Location->execute([ $Pokemon_2['ID'] ]);
				}
				catch( PDOException $e )
				{
					HandleError( $e->getMessage() );
				}

				$Receiver_Pokemon .= $Pokemon_2['ID'] . ",";
			}
			foreach( $_SESSION['Trade']['Receiver']['Currency'] as $Key => $Currency_2 )
			{
				$Receiver_Currency .= $Currency_2['Currency'] . "-" . $Currency_2['Quantity'] . ",";
			}
			foreach( $_SESSION['Trade']['Receiver']['Items'] as $Key => $Items_2 )
			{
				$Receiver_Items .= $Items_2['Row'] . "-" . $Items_2['ID'] . "-" . $Items_2['Quantity'] . "-" . $Items_2['Owner'] . ",";
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
						`Receiver`,
						`Receiver_Pokemon`,
						`Receiver_Items`,
						`Receiver_Currency`
					)
					VALUES (?, ?, ?, ?, ?, ?, ?, ?)
				");
				$Create_Query->execute([
					$_SESSION['Trade']['Sender']['User'],
					substr($Sender_Pokemon, 0, -1),
					substr($Sender_Items, 0, -1),
					substr($Sender_Currency, 0, -1),
					$_SESSION['Trade']['Receiver']['User'],
					substr($Receiver_Pokemon, 0, -1),
					substr($Receiver_Items, 0, -1),
					substr($Receiver_Currency, 0, -1)
				]);
			}
			catch( PDOException $e )
			{
				HandleError( $e->getMessage() );
			}
		}
	}
	else
	{
		echo "<div class='error' style='margin-bottom: 5px;'>The trade could not be made, as as error has occurred.</div>";
	}
?>

<div class='description' style='margin-bottom: 5px;'>Enter a user's ID to begin a trade with them.</div>
<div class='panel' style='margin-top: 5px;'>
	<div class='head'>Create A Trade</div>
	<div class='body' style='padding: 5px;'>
		<input type='text' placeholder='User ID' id='recipientID' style='text-align: center; width: 200px;'/><br />
		<button onclick='TradePrepare();' style='width: 200px;'>Begin A Trade</button>
	</div>
</div>

<div class='description' style='margin: 5px 0px;'>All ingoing and outgoing trades that involve you are listed below.</div>
<div class='panel' style='margin-top: 5px;'>
	<div class='head'>Pending Trades</div>
	<div class='body' style='padding: 5px;'>
		<?php
			try
			{
				$Pending_Query = $PDO->prepare("SELECT `ID`, `Sender`, `Receiver`, `Status` FROM `trades` WHERE (`Sender` = ? OR `Receiver` = ?) AND `Status` = ?");
				$Pending_Query->execute([ $User_Data['id'], $User_Data['id'], 'Pending' ]);
				$Pending_Query->setFetchMode(PDO::FETCH_ASSOC);
				$Pending_Trades = $Pending_Query->fetchAll();
			}
			catch( PDOException $e )
			{
				HandleError( $e->getMessage() );
			}

			if ( count($Pending_Trades) === 0 )
			{
				echo "<div class='notice'>You do not currently have any pending trades.</div>";
			}
			else
			{
				echo "
					<table class='standard' style='margin: 0px auto; width: calc(100% - 10px);'>
						<thead>
							<th style='width: 25%;'>Trade ID</th>
							<th style='width: 25%;'>Sender</th>
							<th style='width: 25%;'>Recipient</th>
							<th style='width: 25%;'>Status</th>
						</thead>
						<tbody>
				";
				foreach( $Pending_Trades as $Key => $Value )
				{
					$Sender = $User_Class->FetchUserData($Value['Sender']);
					$Sender_Username = $User_Class->DisplayUserName($Sender['ID']);
					$Recipient = $User_Class->FetchUserData($Value['Receiver']);
					$Recipient_Username = $User_Class->DisplayUserName($Recipient['ID']);
					

					echo "
						<tr>
							<td><a href='javascript:void(0);' onclick='TradeView({$Value['ID']});'>#" . number_format($Value['ID']) . "</a></td>
							<td><a href='" . Domain(1) . "/profile.php?id={$Sender['ID']}'>{$Sender_Username}</a></td>
							<td><a href='" . Domain(1) . "/profile.php?id={$Recipient['ID']}'>{$Recipient_Username}</a></td>
							<td style='color: #888;'>{$Value['Status']}</td>
						</tr>
					";
				}
				echo "
						</tbody>
					</table>
				";
			}
		?>
	</div>
</div>