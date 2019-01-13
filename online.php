<?php
	require 'core/required/layout_top.php';

	$Last_Active = time() - 60 * 10;

	try
	{
		$Fetch_Online_Staff = $PDO->prepare("SELECT * FROM `users` WHERE `Power` >= 3 AND `Last_Active` > ?");
		$Fetch_Online_Staff->execute([$Last_Active]);
		$Fetch_Online_Staff->setFetchMode(PDO::FETCH_ASSOC);
		$Online_Staff = $Fetch_Online_Staff->fetchAll();

		$Fetch_Online_Users = $PDO->prepare("SELECT * FROM `users` WHERE `Power` = 1 AND `Last_Active` > ?");
		$Fetch_Online_Users->execute([$Last_Active]);
		$Fetch_Online_Users->setFetchMode(PDO::FETCH_ASSOC);
		$Online_Users = $Fetch_Online_Users->fetchAll();
	}
	catch( PDOException $e )
	{
		HandleError( $e->getMessage() );
	}
?>

<div class='content'>
	<div class='head'>Online Users</div>
	<div class='box online_list'>
		<div class='description' style='margin: 0px auto 5px'>
			All users that have been online in the past fifteen minutes are displayed below.
		</div>

		<div class='row'>
			<div class='admin'>Staff</div>
			<?php
				foreach( $Online_Staff as $Key => $Value )
				{
					echo "
						<div class='panel' style='float: left; margin-right: 5px; width: 200px;'>
							<div class='panel-heading'>
								<div class='{$Value['Rank']}' style='font-size: 14px; text-align: left;'>{$Value['Username']}</div>
								<div style='margin-top: -20px; text-align: right;'>#" . number_format($Value['id']) . "</div>
							</div>
							<div class='panel-body' style='padding: 3px;'>
								<div style='height: 100px;'><img src='{$Value['Avatar']}' /></div>
								<a href='" . Domain(1) . "/profile.php?id={$Value['id']}'><b>{$Value['Username']}</b></a><br />
								";
								$UserClass->DisplayUserRank($Value['id']);
					echo "
							</div>
						</div>
					";
				}
			?>
		</div>

		<div class='row'>
			<div class='member' style='margin-top: 15px; width: 100% !important;'>Members</div>
			<?php
				foreach( $Online_Users as $Key => $Value )
				{
					echo "
						<div class='panel' style='float: left; margin-right: 5px; width: 200px;'>
							<div class='panel-heading'>
								<div class='{$Value['Rank']}' style='font-size: 14px; text-align: left;'>{$Value['Username']}</div>
								<div style='margin-top: -20px; text-align: right;'>#" . number_format($Value['id']) . "</div>
							</div>
							<div class='panel-body' style='padding: 3px;'>
								<div style='height: 100px;'><img src='{$Value['Avatar']}' /></div>
								<a href='" . Domain(1) . "/profile.php?id={$Value['id']}'><b>{$Value['Username']}</b></a>
							</div>
						</div>
					";
				}
			?>
		</div>
	</div>
</div>

<?php
	require 'core/required/layout_bottom.php';
?>