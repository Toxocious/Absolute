<?php
	require '../core/required/session.php';
	require '../core/functions/staff.php';

	/**
	 * Fetch the bans that you're looking for.
	 */
	if ( isset($_GET['Type']) )
	{
		$Type = $Purify->Cleanse($_GET['Type']);

		/**
		 * Fetch the list of bans.
		 */
		try
		{
			$Query_Bans = $PDO->prepare("SELECT * FROM `bans` WHERE `Ban_Type` = ?");
			$Query_Bans->execute([ $Type ]);
			$Query_Bans->setFetchMode(PDO::FETCH_ASSOC);
			$Bans = $Query_Bans->fetchAll();
		}
		catch ( PDOException $e )
		{
			HandleError ( $e->getMessage() );
		}

		/**
		 * List all of the bans.
		 */
		if ( count($Bans) == 0 )
		{
			echo "
				<div class='panel' style='margin-top: 5px;'>
					<div class='head'>{$Type} Bans</div>
					<div class='body' style='padding: 5px;'>
						There are currently no {$Type} bans.
					</div>
				</div>
			";
		}
		else
		{
			foreach ( $Bans as $Key => $Value )
			{
				$Banned_User = $User_Class->FetchUserData($Value['User_ID']);

				echo "
					<div class='panel' style='margin-top: 5px;'>
						<div class='head'>Ban #" . number_format($Value['ID']) . "</div>
						<div class='body navi'>

							<div>
								<div style='float: left; padding: 5px; width: 50%;'>
									<b>Banned On:</b> " . date("F j, Y (g:i A)", $Value['Banned_On']) . "
								</div>
								<div style='float: left; padding: 5px; width: 50%;'>
									<b>Banned Until:</b> " . date("F j, Y (g:i A)", $Value['Ban_Length']) . "
								</div>
							</div>

							<hr />

							<div>
								<div style='float: left; padding: 5px; width: 150px;'>
									<img src='../{$Banned_User['Avatar']}' /><br />
									<a href='../profile.php?id={$Banned_User['ID']}'>
										{$Banned_User['Username']} (#" . number_format($Banned_User['ID']) . ")
									</a>
								</div>
								<div style='float: left; height: 130px; padding: 5px; width: 364px;'>
									<b>Reason</b><hr />
									" . nl2br($Value['Ban_Reason']) . "
								</div>
								<div style='float: left; height: 130px; padding: 5px; width: 360px;'>
									<b>Staff Notes</b><hr />
									" . nl2br($Value['Ban_Notes']) . "
								</div>
							</div>

						</div>
					</div>
				";
			}
		}

		exit;
	}
?>

<div class='head'>Ban List</div>
<div class='body'>

	<div class='panel'>
		<div class='head'>Ban Types</div>
		<div class='body navi'>
			<div>
				<div style='float: left; padding: 2px; width: calc(100% / 2);'>
					<a href='javascript:void(0);' onclick='ShowBans("RPG");' style='display: block;'>
						RPG
					</a>
				</div>
				<div style='float: left; padding: 2px; width: calc(100% / 2);'>
					<a href='javascript:void(0);' onclick='ShowBans("Chat");' style='display: block;'>
						Chat
					</a>
				</div>
			</div>
		</div>
	</div>

	<div id='AJAX'></div>

</div>

<script type='text/javascript'>
	function ShowBans(Type)
	{
		$.ajax({
			type: 'get',
			url: 'ban_list.php',
			data: { Type: Type },
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