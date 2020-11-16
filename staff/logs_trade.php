<?php
	require '../core/required/session.php';
	require '../core/functions/staff.php';

	if ( isset($_GET['user']) )
	{
		$User_Input = $Purify->Cleanse($_GET['user']);

		try
		{
			$Query = $PDO->prepare("SELECT `ID`, `Sender`, `Receiver`, `Status` FROM `trades` WHERE (`Sender` = ? OR `Receiver` = ?)");
			$Query->execute([ $User_Input, $User_Input ]);
			$Query->setFetchMode(PDO::FETCH_ASSOC);
			$Trades = $Query->fetchAll();
		}
		catch( PDOException $e )
		{
			HandleError( $e->getMessage() );
		}

		if ( count($Trades) === 0 )
		{
			echo "<div class='notice' style='margin-top: 5px;'>You do not currently have any pending trades.</div>";
		}
		else
		{
			echo "
				<table class='standard' style='margin: 5px auto 0px; width: 80%;'>
					<thead>
						<th style='width: 25%;'>Trade ID</th>
						<th style='width: 25%;'>Sender</th>
						<th style='width: 25%;'>Recipient</th>
						<th style='width: 25%;'>Status</th>
					</thead>
					<tbody>
			";
			foreach( $Trades as $Key => $Value )
			{
				$Sender = $User_Class->FetchUserData($Value['Sender']);
				$Sender_Username = $User_Class->DisplayUserName($Sender['ID']);
				$Recipient = $User_Class->FetchUserData($Value['Receiver']);
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

				echo "
					<tr>
						<td><a href='javascript:void(0);' onclick='TradeView({$Value['ID']});'>#" . number_format($Value['ID']) . "</a></td>
						<td><a href='" . DOMAIN_ROOT . "/profile.php?id={$Sender['ID']}'>{$Sender_Username}</a></td>
						<td><a href='" . DOMAIN_ROOT . "/profile.php?id={$Recipient['ID']}'>{$Recipient_Username}</a></td>
						<td style='color: {$Color};'>{$Value['Status']}</td>
					</tr>
				";
			}
			echo "
					</tbody>
				</table>
			";
		}

		exit;
	}
?>

<div class='head'>Trade Logs</div>
<div class='body'>
	<div class='panel' style='margin: 0 auto; width: 70%;'>
		<div class='head'>Filter</div>
		<div class='body' style='padding-top: 5px;'>
			<input type='text' id='user_val' placeholder='Username or ID' style='width: 40%;' />
			<button style='padding: 3px; width: 40%;' onclick='FetchLogs();'>Fetch Logs</button>
		</div>
	</div>

	<div id='AJAX'></div>
</div>

<script type='text/javascript'>
	function FetchLogs()
	{
		let user = $('input#user_val').val();

		$.ajax({
			type: 'get',
			url: 'logs_trade.php',
			data: { user: user },
			success: function(data)
			{
				$('#AJAX').html(data);
			},
			error: function(data)
			{
				$('#AJAX').html(data);
			}
		});
	}

	function TradeView(Trade_ID)
	{
		$.ajax({
			type: 'POST',
			url: 'logs_trade.php',
			data: { Trade_ID: Trade_ID },
			success: function(data)
			{
				$('#AJAX').html(data);
			},
			error: function(data)
			{
				$('#AJAX').html(data);
			}
		});
	}
</script>