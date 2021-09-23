<?php
	require_once '../core/required/session.php';
	require_once '../core/functions/staff.php';

	if ( isset($_GET['user']) )
	{
		$User_Input = $Purify->Cleanse($_GET['user']);

		try
		{
			$Fetch_Logs = $PDO->prepare("SELECT * FROM `logs` WHERE `User_ID` = ? ORDER BY `ID` DESC LIMIT 2500");
			$Fetch_Logs->execute([ $User_Input ]);
			$Fetch_Logs->setFetchMode(PDO::FETCH_ASSOC);
			$Logs = $Fetch_Logs->fetchAll();
		}
		catch ( PDOException $e )
		{
			HandleError ( $e->getMessage() );
		}

		echo "
			<div class='description' style='margin: 5px 0px;'>Displaying the 2500 most recent logs.</div>
			<table style='margin: 0 auto; width: 80%;'>
				<tr>
					<td><b>User ID</b></td>
					<td><b>Log Type</b></td>
					<td><b>Page</b></td>
					<td><b>Data</b></td>
				</tr>
		";
		foreach ( $Logs as $Key => $Value )
		{
			echo "
				<tr>
					<td>" . number_format($Value['User_ID']) . "</td>
					<td>{$Value['Type']}</td>
					<td>{$Value['Page']}</td>
					<td>{$Value['Data']}</td>
				</tr>
			";
		}
		echo "
			</table>
		";

		exit;
	}
?>

<div class='head'>User Logs</div>
<div class='body'>
	<div class='panel'>
		<div class='head'>Filter</div>
		<div class='body'>
			<input type='text' id='user_val' placeholder='Username or ID' />
			<button onclick='FetchLogs();'>Fetch Logs</button>
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
			url: 'logs_user.php',
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
</script>