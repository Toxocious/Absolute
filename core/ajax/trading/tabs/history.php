<?php
	require '../../../required/session.php';
?>

<div class='description' style='margin-bottom: 5px;'>
	Below, you'll see your ten most recent trades.
</div>

<div class='panel'>
	<div class='panel-heading'>Trade History</div>
	<div class='panel-body' style='padding: 5px;'>
		<?php
			try
			{
				$Pending_Query = $PDO->prepare("SELECT `ID`, `Sender`, `Receiver`, `Status` FROM `trades` WHERE (`Sender` = ? OR `Receiver` = ?) AND `Status` != ?");
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
				echo "<div class='notice'>You have never accepted or declined a trade.</div>";
			}
			else
			{
				echo "
				<table class='standard' style='margin: 0px auto; width: calc(100% - 10px);'>
					<thead>
						<th>Trade ID</td>
						<th>Sender</td>
						<th>Recipient</td>
						<th>Status</td>
					</thead>
					<tbody>
				";
				foreach( $Pending_Trades as $Key => $Value )
				{
					$Sender = $UserClass->FetchUserData($Value['Sender']);
					$Sender_Username = $UserClass->DisplayUserName($Sender['ID']);
					$Recipient = $UserClass->FetchUserData($Value['Receiver']);
					$Recipient_Username = $UserClass->DisplayUserName($Recipient['ID']);
					
					switch( $Value['Status'] )
					{
						case 'Accepted':
							$Color = "#00ff00";
							break;
						case 'Declined':
							$Color = "#ff0000";
							break;
					}

					echo "
						<tr>
							<td><a href='javascript:void(0);' onclick='TradeView({$Value['ID']});'>#" . number_format($Value['ID']) . "</a></td>
							<td><a href='" . Domain(1) . "/profile.php?id={$Sender['ID']}'>{$Sender_Username}</a></td>
							<td><a href='" . Domain(1) . "/profile.php?id={$Recipient['ID']}'>{$Recipient_Username}</a></td>
							<td style='color: {$Color};'>{$Value['Status']}</td>
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