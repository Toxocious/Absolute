<?php
	require 'core/required/layout_top.php';

	$Last_Active = time() - 60 * 10;

	try
	{
		$Fetch_Online_Users = $PDO->prepare("SELECT * FROM `users` WHERE `Last_Active` > ?");
		$Fetch_Online_Users->execute([ $Last_Active ]);
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
	<div class='box'>
		<div class='description' style='margin: 0px auto 5px'>
			All users that have been online in the past fifteen minutes are displayed below.
		</div>

		<div class='row'>
			<table class='standard' style='margin: 0 auto; width: 80%;'>
				<thead>
					<th style='width: 5%;'></th>
					<th style='width: 35%;'>Username</th>
					<th style='width: 30%;'>Last Active</th>
					<th style='width: 30%;'>Current Page</th>
				</thead>
				<tbody>
					<?php
						foreach( $Online_Users as $Key => $Value )
						{
							$Render_Username = $UserClass->DisplayUsername($Value['id']);

							echo "
								<tr>
									<td>
										
									</td>
									<td>
										<a href='" . Domain(1) . "/profile.php?id={$Value['id']}'>
											{$Render_Username}
										</a>
									</td>
									<td>
										" . lastseen($Value['Last_Active'], 'week') . "
									</td>
									<td>
										" . $Value['Last_Page'] . "
									</td>
								</tr>
							";
						}
					?>
				</tbody>
			</table>
		</div>
	</div>
</div>

<?php
	require 'core/required/layout_bottom.php';
?>