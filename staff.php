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
			'Rank' => 'Bot',
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

		<div class='row' style='display: flex; flex-direction: row; flex-wrap: wrap; justify-content: center;'>
			<?php
				foreach ( $Staff_Categories as $Staff_Category )
				{
					try
					{
						$Fetch_Staff = $PDO->prepare("SELECT `ID`, `Username`, `Avatar`, `Rank`, `Last_Active`, `Staff_Message` FROM `users` WHERE `Power` = ? ORDER BY `id` ASC");
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
						$Staff_Data = $User_Class->FetchUserData($User_Val['ID']);
						$Staff_Username = $User_Class->DisplayUsername($User_Val['ID'], true, true, true);
	
						echo "
							<table class='border-gradient'  style='flex-basis: 280px; margin: 3px;'>
								<tbody>
									<tr>
										<td rowspan='2' style='width: 100px;'>
											<img src='{$Staff_Data['Avatar']}' />
										</td>
										<td colspan='2'>
											<b>
											{$Staff_Username}
											</b>
										</td>
									</tr>
									<tr>
										<td colspan='2'>
											<a href='#'>
												Send A Message
											</a>
										</td>
									</tr>
									<tr>
										<td colspan='3' style='padding: 5px;'>
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
