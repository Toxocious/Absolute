<?php
	require_once '../../../required/session.php';

	try
	{
		$Pending_Query = $PDO->prepare("SELECT `ID`, `Sender`, `Recipient`, `Status` FROM `trades` WHERE (`Sender` = ? OR `Recipient` = ?) AND `Status` != ? ORDER BY `ID` DESC LIMIT 10");
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
				<td colspan='4'>
					You have never participated in a trade.
				</td>
			</tr>
		";
	}
	else
	{
		$Trade_Text = '';

		foreach( $Pending_Trades as $Key => $Value )
		{
			$Sender = $User_Class->FetchUserData($Value['Sender']);
			$Sender_Username = $User_Class->DisplayUserName($Sender['ID']);
			$Recipient = $User_Class->FetchUserData($Value['Recipient']);
			$Recipient_Username = $User_Class->DisplayUserName($Recipient['ID']);

			switch( $Value['Status'] )
			{
				case 'Accepted':
					$Color = "#00ff00";
					break;
				case 'Declined':
					$Color = "#ff0000";
					break;
				case 'Deleted':
					$Color = "#999";
					break;
			}

			$Trade_Text .= "
				<tr>
					<td>
						<a href='javascript:void(0);' onclick='TradeView({$Value['ID']});'>
							#" . number_format($Value['ID']) . "
						</a>
					</td>
					<td>
						<a href='" . DOMAIN_ROOT . "/profile.php?id={$Sender['ID']}'>
							{$Sender_Username}
						</a>
					</td>
					<td>
						<a href='" . DOMAIN_ROOT . "/profile.php?id={$Recipient['ID']}'>
							{$Recipient_Username}
						</a>
					</td>
					<td style='color: {$Color};'>
						{$Value['Status']}
					</td>
				</tr>
			";
		}

		$Trade_Text .= "
				</tbody>
			</table>
		";
	}
?>

<div class='description'>
	Below, you'll see your ten most recent trades.
</div>

<table class='border-gradient' style='margin-bottom: 5px; width: 450px;'>
	<thead>
		<tr>
			<th colspan='4'>
				Trade History
			</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td style='width: 25%;'>
				<b>
					Trade #
				</b>
			</td>
			<td style='width: 25%;'>
				<b>
					Sender
				</b>
			</td>
			<td style='width: 25%;'>
				<b>
					Recipient
				</b>
			</td>
			<td style='width: 25%;'>
				<b>
					Status
				</b>
			</td>
		</tr>
	</tbody>
	<tbody>
		<?= $Trade_Text; ?>
	</tbody>
</table>
