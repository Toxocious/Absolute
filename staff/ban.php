<?php	
	require_once '../core/required/session.php';
	require_once '../core/functions/staff.php';

	/**
	 * Fetch the user's data in preparation for a ban.
	 */
	if ( isset($_POST['fetch']) )
	{
		$User = $Purify->Cleanse($_POST['fetch']);
		$Fetched_User = $User_Class->FetchUserData($User);
		$Display_Username = $User_Class->DisplayUserName($Fetched_User['ID'], false, true);

		echo "
			<br /><hr /><br />

			<div style='float: left;'>
				<img src='../{$Fetched_User['Avatar']}' /><br />
				{$Display_Username}
			</div>

			<div>
				<textarea style='height: 120px; margin-left: 5px; margin-top: -3px; resize: none; width: calc(100% - 105px);'></textarea><br />
				<input type='text' id='ban_duration' placeholder='Duration Of Ban (In Minutes)' style='text-align: center; width: 45%;' />
				<button style='padding: 3px; width: 45%;' onclick='BanUser({$Fetched_User['ID']});'>Ban User</button>
			</div>
		";

		exit;
	}

	/**
	 * Ban the fetched user.
	 */
	if ( isset($_POST['ban']) )
	{
		$Time_Cur = time();
		$User_ID = $Purify->Cleanse($_POST['ban'][0]);
		$Reason = $Purify->Cleanse($_POST['ban'][1]);
		$Length = $Purify->Cleanse($_POST['ban'][2]);
		$Unbanned_On = $Time_Cur + ( $Length * 60 );

		try
		{
			$Ban_Query = $PDO->prepare("UPDATE `users` SET `Rank` = 'Member', `Clan` = 0, `Clan_Exp` = 0, `RPG_Ban` = 'Yes', `RPG_Ban_Data` = ? WHERE `id` = ? LIMIT 1");
			$Ban_Query->execute([ $Unbanned_On.','.$Reason , $User_ID ]);

			$Ban_Log = $PDO->prepare("INSERT INTO `bans` (`User_ID`, `Banned_By`, `Ban_Type`, `Banned_On`, `Ban_Length`, `Ban_Reason`) VALUES (?, ?, ?, ?, ?, ?)");
			$Ban_Log->execute([ $User_ID, $User_Data['ID'], 'RPG', $Time_Cur, $Unbanned_On, $Reason ]);
		}
		catch ( PDOException $e )
		{
			HandleError ( $e->getMessage() );
		}

		$Fetched_User = $User_Class->FetchUserData($User_ID);
		$Display_Username = $User_Class->DisplayUserName($Fetched_User['ID'], false, false);

		echo "
			<br /><hr />

			<div style='padding: 5px 5px 10px;'>
				<img src='../{$Fetched_User['Avatar']}' /><br />
				{$Display_Username} has been banned until " . date("M dS, Y g:i:s A", $Unbanned_On) . ".
			</div>
			<div>
				<b>Reason</b>:<br />
				{$Reason}
			</div>
		";

		exit;
	}
?>

<div class='head'>Ban A User</div>
<div class='body'>
	
	<div class='panel' style='margin: 0 auto; width: 60%;'>
		<div class='head'>Search For A User</div>
		<div class='body' style='padding: 5px;'>	
			<input type='text' placeholder='Username or ID' style='text-align: center; width: 50%;' id='f_user'/><br />
			<button style='width: 50%;' onclick='FetchUser();'>Fetch User</button>
			
			<div id='AJAX'></div>
		</div>
	</div>

</div>

<script type='text/javascript'>
	function FetchUser()
	{
		let user = $('input#f_user').val();

		$.ajax({
			type: 'post',
			url: 'ban.php',
			data: { fetch: user },
			success: function(data)
			{
				$('#AJAX').html(data);
			},
			error: function(data)
			{
				$('#AJAX').html(data);
			}
		});

		$('input#f_user').val('');
	}

	function BanUser(id)
	{
		let reason = $('textarea').val();
		let duration = $('#ban_duration').val();

		$.ajax({
			type: 'post',
			url: 'ban.php',
			data: { ban: [id, reason, duration] },
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