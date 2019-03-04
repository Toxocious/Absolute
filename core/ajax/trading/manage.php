<?php
	require '../../required/session.php';

	/**
	 * Handle both accepting trades, as well as declining/deleting trades.
	 */
	if ( isset($_POST['Trade_ID']) && isset($_POST['Action']) )
	{
		$Trade_ID = $Purify->Cleanse($_POST['Trade_ID']);
		$Action = $Purify->Cleanse($_POST['Action']);

		echo "
			<div class='success' style='margin-bottom: 5px;'>
				You have successfully {$Action}d Trade #$Trade_ID.
			</div>
		";

		/**
		 * Process the logic required to accept the trade.
		 */
		if ( $Action == 'Accept' )
		{

		}

		/**
		 * Process the logic required to delete the trade.
		 */
		else if ( $Action == 'Delete' )
		{
			try
			{
				$Trade_Query = $PDO->prepare("SELECT * FROM `trades` WHERE `ID` = ? AND (`Sender` = ? OR `Receiver` = ?) AND `Status` = ?");
				$Trade_Query->execute([ $Trade_ID, $User_Data['id'], $User_Data['id'], 'Pending' ]);
				$Trade_Query->setFetchMode(PDO::FETCH_ASSOC);
				$Trade_Content = $Trade_Query->fetchAll();

				$Update_Status = $PDO->prepare("UPDATE `trades` SET `Status` = 'Declined' WHERE `ID` = ? LIMIT 1");
				$Update_Status->execute([ $Trade_ID ]);
			}
			catch( PDOException $e )
			{
				HandleError( $e->getMessage() );
			}

			if ( !empty($Trade_Content[0]['Sender_Pokemon']) )
			{
				$Sender_Pokemon = explode(',', $Trade_Content[0]['Sender_Pokemon']);
				foreach( $Sender_Pokemon as $Key => $Pokemon_1 )
				{
					$Pokemon_Data = $PokeClass->FetchPokemonData($Pokemon_1);

					try
					{
						$Update_Location = $PDO->prepare("UPDATE `pokemon` SET `Location` = 'Box' WHERE `ID` = ? LIMIT 1");
						$Update_Location->execute([ $Pokemon_Data['ID'] ]);
					}
					catch( PDOException $e )
					{
						HandleError( $e->getMessage() );
					}
				}
			}

			if ( !empty($Trade_Content[0]['Receiver_Pokemon']) )
			{
				$Receiver_Pokemon = explode(',', $Trade_Content[0]['Receiver_Pokemon']);
				foreach( $Receiver_Pokemon as $Key => $Pokemon_2 )
				{
					$Pokemon_Data = $PokeClass->FetchPokemonData($Pokemon_2);

					try
					{
						$Update_Location = $PDO->prepare("UPDATE `pokemon` SET `Location` = 'Box' WHERE `ID` = ? LIMIT 1");
						$Update_Location->execute([ $Pokemon_Data['ID'] ]);
					}
					catch( PDOException $e )
					{
						HandleError( $e->getMessage() );
					}
				}
			}
		}
?>

<div class='description' style='margin-bottom: 5px;'>Enter a user's ID to begin a trade with them.</div>
<div class='panel' style='margin-top: 5px;'>
	<div class='panel-heading'>Create A Trade</div>
	<div class='panel-body' style='padding: 5px;'>
		<input type='text' placeholder='User ID' id='recipientID' style='text-align: center; width: 200px;'/><br />
		<button onclick='TradePrepare();' style='width: 200px;'>Begin A Trade</button>
	</div>
</div>

<div class='description' style='margin: 5px 0px;'>All ingoing and outgoing trades that involve you are listed below.</div>
<div class='panel' style='margin-top: 5px;'>
	<div class='panel-heading'>Pending Trades</div>
	<div class='panel-body' style='padding: 5px;'>
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
					$Sender = $UserClass->FetchUserData($Value['Sender']);
					$Sender_Username = $UserClass->DisplayUserName($Sender['ID']);
					$Recipient = $UserClass->FetchUserData($Value['Receiver']);
					$Recipient_Username = $UserClass->DisplayUserName($Recipient['ID']);
					

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

<?php
	 }
	 else
	 {
		 echo "An error has occurred while trying to accept or delete this trade.";
	 }