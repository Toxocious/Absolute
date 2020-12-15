<?php
	require 'core/required/layout_top.php';

	$Staff_Categories = [
		[
			'Power' => 7,
			'Rank' => 'Administrator',
		],
		[
			'Power' => 6,
			'Rank' => 'Developer',
		],
		[
			'Power' => 5,
			'Rank' => 'Super Moderator',
		],
		[
			'Power' => 4,
			'Rank' => 'Moderator',
		],
		[
			'Power' => 3,
			'Rank' => 'Trial Moderator',
		],
		[
			'Power' => 2,
			'Rank' => 'Chat Moderator',
		],
	];
?>

<div class='panel content'>
	<div class='head'>Staff List</div>
	<div class='body' style='padding: 5px;'>
		<div class='description' style='margin: 0px auto 5px'>
			All members of Absolute's staff team are listed below.<br />
			If you require assistance with something, please don't hesitate to contact one of them.
		</div>

		<div class='row' style='display: flex; flex-direction: row; flex-wrap: wrap; justify-content: center; padding-bottom: 5px;'>
			<?php
				foreach ( $Staff_Categories as $Staff_Category )
				{
					try
					{
						$Fetch_Staff = $PDO->prepare("SELECT `id`, `Username`, `Avatar`, `Rank`, `Last_Active`, `Staff_Message` FROM `users` WHERE `Power` = ? ORDER BY `id` ASC");
						$Fetch_Staff->execute([ $Staff_Category['Power'] ]);
						$Fetch_Staff->setFetchMode(PDO::FETCH_ASSOC);
						$Staff_Members = $Fetch_Staff->fetchAll();
					}
					catch( PDOException $e )
					{
						HandleError( $e->getMessage() );
					}

					if ( !$Staff_Members )
						continue;

					echo "
						<div style='flex-basis: 100%;'>
							<h3 class='" . strtolower($Staff_Category['Rank']) . "'>{$Staff_Category['Rank']}s</h3>
						</div>
					";

					foreach ( $Staff_Members as $User_Key => $User_Val )
					{
						$Staff_Data = $User_Class->FetchUserData($User_Val['id']);
						$Staff_Username = $User_Class->DisplayUsername($User_Val['id'], true, true, true);
	
						echo "
							<table class='border-gradient'  style='flex-basis: 280px; margin: 3px;'>
								<thead>
									<tr>
										<th colspan='3'>
											{$Staff_Data['Username']} (#{$Staff_Data['ID']})
										</th>
									</tr>
								</thead>
								<tbody>
									<tr>
										<td colspan='1' rowspan='3' style='border-radius: 6px; width: 100px;'>
											<img src='{$Staff_Data['Avatar']}' />
										</td>
									</tr>
									<tr>
										<td colspan='2' style='border-radius: 6px 6px 0px 0px;'>
											<b>
												{$Staff_Username}
											</b>
										</td>
									</tr>
									<tr>
										<td colspan='2'>
											" . ($Staff_Data['Staff_Message'] ? $Staff_Data['Staff_Message'] : 'This user has yet to set their staff message.') . "
										</td>
									</tr>
								</tbody>
							</table>
						";
					}

					echo "<br />";
				}
			?>
		</div>
	</div>
</div>

<?php
	require 'core/required/layout_bottom.php';
