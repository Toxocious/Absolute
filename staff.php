<?php
	require 'core/required/layout_top.php';

	try
	{
		$Fetch_Staff = $PDO->prepare("SELECT * FROM `users` WHERE `Power` >= 3 ORDER BY `Power` DESC");
		$Fetch_Staff->execute();
		$Fetch_Staff->setFetchMode(PDO::FETCH_ASSOC);
		$Staff = $Fetch_Staff->fetchAll();
	}
	catch( PDOException $e )
	{
		HandleError( $e->getMessage() );
	}
?>

<div class='content'>
	<div class='head'>Staff List</div>
	<div class='box'>
		<div class='description' style='margin-bottom: 5px;'>
			Every staff member of Absolute can be found below.
		</div>

		<div class='row'>
			<table class='standard' style='margin: 0 auto; width: 80%;'>
				<thead>
					<tr>
						<th style='width: 25%;'>Avatar</th>
						<th style='width: 25%;'>User Info</th>
						<th style='width: 50%;'>Staff Message</th>
					</tr>
				</thead>
				<?php
					foreach( $Staff as $Key => $Value )
					{
						$Rank = $UserClass->DisplayUserRank($Value['id'], 14);

						if ( $Value['Staff_Message'] == null )
						{
							$Value['Staff_Message'] = "<i>This user has yet to set a staff message.</i>";
						}

						echo "
								<tbody>
									<tr>
										<td>
											<img src='{$Value['Avatar']}' />
										</td>
										<td>
											<a href='" . Domain(1) . "/profile.php?id={$Value['id']}'><b>{$Value['Username']}</b></a><br />
											<b>{$Rank}</b>
										</td>
										<td>
											{$Value['Staff_Message']}
										</td>
									</tr>
								</tbody>
						";
					}
				?>
			</table>
		</div>
	</div>
</div>

<?php
	require 'core/required/layout_bottom.php';
?>