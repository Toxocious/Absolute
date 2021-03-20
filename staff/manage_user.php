<?php
	require_once '../core/required/session.php';
	require_once '../core/functions/staff.php';

	/**
	 * Fetch the user.
	 */
	if ( isset($_POST['fetch']) )
	{
		$User_ID = $Purify->Cleanse($_POST['fetch']);
		$User = $User_Class->FetchUserData($User_ID);

		echo "
			<div class='row'>
				<div class='panel' style='float: left; margin-right: 5px; width: calc(100% / 2 - 2.5px);'>
					<div class='head'>{$User['Username']} (#" . number_format($User['ID']) . ")</div>
					<div class='body' style='padding: 5px;'>
						<div style='float: left; width: 35%;'>
							<img src='../{$User['Avatar']}' />
						</div>
						<div style='float: left; padding-top: 40px; width: 65%;'>
							" . $User_Class->DisplayUserRank($User['ID']) . "
						</div>
					</div>
				</div>

				<div class='panel' style='float: left; width: calc(100% / 2 - 2.5px);'>
					<div class='head'>Options</div>
					<div class='body' style='padding: 5px;'>
						<b>Status</b><br />
						<textarea style='resize: none; width: 100%;' id='status' placeholder='{$User['Status']}'></textarea>
						<br />

						<b>Staff Message</b><br />
						<textarea style='resize: none; width: 100%;' id='staff_message' placeholder='{$User['Staff_Message']}'></textarea>
						<br />

						<button style='width: 80%;' onclick='UpdateUser({$User['ID']});'>Update User</button>
					</div>
				</div>
			</div>
		";

		exit;
	}

	/**
	 * Update the set user parameters.
	 */
	if ( isset($_POST['update']) )
	{
		$User = $Purify->Cleanse($_POST['update'][0]);
		$Status = $Purify->Cleanse($_POST['update'][1]);
		$Staff_Message = $Purify->Cleanse($_POST['update'][2]);

		echo "<div class='success'>This user's Status and Staff Message have been updated.</div>";

		try
		{
			$Update_Query = $PDO->prepare("UPDATE `users` SET `Status` = ?, `Staff_Message` = ? WHERE `id` = ? LIMIT 1");
			$Update_Query->execute([ $Status, $Staff_Message, $User ]);
		}
		catch ( PDOException $e )
		{
			HandleError ( $e->getMessage() );
		}

		exit;
	}
?>

<div class='head'>Manage Users</div>
<div class='body'>
	<div style='padding: 5px'>
		Enter the ID of the User that you want to manage.<br /><br />

		<input type='text' id='user_val' placeholder='ID' style='text-align: center; width: 40%;' />
		<button style='padding: 3px; width: 40%;' onclick='FetchUser();'>Fetch Logs</button>
	</div>

	<div id='AJAX'></div>
</div>

<script type='text/javascript'>
	function FetchUser()
	{
		let user = $('input#user_val').val();

		$.ajax({
			type: 'post',
			url: 'manage_user.php',
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

		$('input#user_val').val('');
	}

	function UpdateUser(user_id)
	{
		let status = '';
		if ( $('textarea#status').val() != '' )
		{
			status = $('textarea#status').val();
		}

		let staff_message = '';
		if ( $('textarea#staff_message').val() != '' )
		{
			staff_message = $('textarea#staff_message').val();
		}

		$.ajax({
			type: 'post',
			url: 'manage_user.php',
			data: { update: [user_id, status, staff_message] },
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
</script>