<?php
	require_once '../../../required/session.php';
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
			
			$Recipient = $User_Class->FetchUserData($Value['Receiver']);
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
