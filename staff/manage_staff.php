<?php
	require '../core/required/session.php';
	require '../core/functions/staff.php';

	try
	{
		$Query_Staff = $PDO->prepare("SELECT * FROM `users` WHERE `Power` > 1 ORDER BY `Power` DESC");
		$Query_Staff->execute();
		$Query_Staff->setFetchMode(PDO::FETCH_ASSOC);
		$Staff = $Query_Staff->fetchAll();
	}
	catch ( PDOException $e )
	{
		HandleError( $e->getMessage() );
	}
?>

<div class='head'>Staff Manager</div>
<div class='box'>
	<div class='description' style='margin-bottom: 5px;'>
		Click on a current staff member in order to edit their current position or power level.
	</div>

	<div class='row'>
		<div class='panel' style='float: left; margin-right: 5px; width: calc(100% / 2 - 2.5px);'>
			<div class='panel-heading'>Staff List</div>
			<div class='panel-body' style='padding: 5px;'>
				<button onclick='' style='margin-bottom: 5px; width: 95%;'>Add Staff Member</button>

				<table class='standard' style='margin: 0 auto; width: 95%;'>
					<thead>
						<tr>
							<th style='width: calc(100% / 3);'>Avatar</th>
							<th style='width: calc(100% / 3);'>User Info</th>
							<th style='width: calc(100% / 3);'>Edit</th>
						</tr>
					</thead>
					<tbody>
						<?php
							foreach ( $Staff as $Key => $Value )
							{
								$Username = $User_Class->DisplayUsername($Value['id']);
								$Rank = $User_Class->DisplayUserRank($Value['id'], 14);
						?>

									<tr>
										<td>
											<img src='/<?= $Value['Avatar']; ?>' />
										</td>
										<td>
											<a href='/profile.php?id=<?= $Value['id']; ?>'><b><?= $Value['Username']; ?></b></a><br />
											<b><?= $Rank; ?></b><br />
											<b>Power Level:</b> <?= $Value['Power']; ?>
										</td>
										<td>
											<a href='javascript:void(0);' onclick="LoadContent('ajax/manage_staff.php', 'AJAX', { Fetch: <?= $Value['id']; ?>});">Edit Position</a>
										</td>
									</tr>

						<?php
							}
						?>
					</tbody>
				</table>
			</div>
		</div>

		<div class='panel' style='float: left; width: calc(100% / 2 - 2.5px);'>
			<div class='panel-heading'>Selected Staff Member</div>
			<div class='panel-body' id='AJAX' style='padding: 5px;'>
				Please select a staff member.
			</div>
		</div>
	</div>

	
</div>