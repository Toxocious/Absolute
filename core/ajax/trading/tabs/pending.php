<?php
	require '../../../required/session.php';
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