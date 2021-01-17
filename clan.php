<?php
	require_once 'core/required/layout_top.php';

	if ( !isset($_GET['clan_id']) && $User_Data['Clan'] == 0 )
	{
		$Creation_Cost = number_format($Constants->Clan['Creation_Cost']);
?>

<div class='panel content'>
	<div class='head'>Create A Clan</div>
	<div class='body' style='padding: 5px;'>
		<div class='description'>
			You may create a clan at the cost of $<?= $Creation_Cost ?>.
		</div>

		<form method='POST'>
			<input type="text" name="name" placeholder='Clan Name' />
			<br />
			<input type='submit' name='create' value='Create Clan' />
		</form>
	</div>
</div>

<?php
	}
	else
	{
		$Clan_Data = $Clan_Class->FetchClanData($User_Data['Clan']);

		if ( isset($_GET['clan_id']) )
		{
			$Clan_ID = $Purify->Cleanse($_GET['clan_id']);
			$Clan_Data = $Clan_Class->FetchClanData($Clan_ID);
		}

		try
		{
			$Member_Query = $PDO->prepare("SELECT `id` FROM `users` WHERE `Clan` = ? ORDER BY `Clan_Exp` DESC");
			$Member_Query->execute([ $Clan_Data['ID'] ]);
			$Member_Query->setFetchMode(PDO::FETCH_ASSOC);
			$Members = $Member_Query->fetchAll();
		}
		catch ( PDOException $e )
		{
			HandleError($e);
		}
?>

<div class='panel content'>
	<div class='head'>
		<?= $Clan_Data['Name']; ?>'s Clan Home
	</div>
	<div class='body' style='padding: 5px;'>
		<div class='flex'>
			<div style='flex-basis: 50%;'>
				<table class='border-gradient' style='width: 400px;'>
					<thead>
					</thead>
					<tbody>
						<tr>
							<td colspan='2' style='height: 200px; width: 200px;'>
								<?= ( $Clan_Data['Avatar'] ? "<img src='{$Clan_Data['Avatar']}' />" : 'This clan has no avatar set.' ); ?>
							</td>
							<td colspan='2' style='height: 200px; width: 200px;'>
								<?= ( $Clan_Data['Signature'] ? $Clan_Data['Signature'] : 'This clan has no signature set.' ); ?>
							</td>
						</tr>
					</tbody>

					<thead>
						<tr>
							<th colspan='4'>
								Statistics
							</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td colspan='2'>
								<b>Clan Level</b>
							</td>
							<td colspan='2'>
								<?= number_format(FetchLevel($Clan_Data['Experience_Raw'], 'Clan')); ?>
							</td>
						</tr>
						<tr>
							<td colspan='2'>
								<b>Clan Experience</b>
							</td>
							<td colspan='2'>
								<?= $Clan_Data['Experience']; ?>
							</td>
						</tr>
						<tr>
							<td colspan='2'>
								<b>Clan Points</b>
							</td>
							<td colspan='2'>
								<?= $Clan_Data['Clan_Points']; ?>
							</td>
						</tr>
					</tbody>
					
					<thead>
						<tr>
							<th colspan='4'>
								Currencies
							</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<?php
								foreach ( $Constants->Currency as $Currency )
								{
									echo "
										<td colspan='2'>
											<img src='{$Currency['Icon']}' />
										</td>
									";
								}
							?>
						</tr>
						<tr>
							<?php
								foreach ( $Constants->Currency as $Currency )
								{
									echo "
										<td colspan='2'>
											{$Clan_Data[$Currency['Value']]}
										</td>
									";
								}
							?>
						</tr>
					</tbody>
				</table>

			<?php
				if ( $Clan_Data['ID'] === $User_Data['Clan'] )
				{
			?>

				<table class='border-gradient' style='margin-top: 5px; width: 400px;'>
					<thead>
						<tr>
							<th colspan='2'>
								<b>Clan Options</b>
							</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td colspan='1' style='width: 50%;'>
								<a href='<?= DOMAIN_ROOT; ?>/core/ajax/clan/leave.php'>Leave Clan</a>
							</td>
							<td colspan='1' style='width: 50%;'>
								<a href='<?= DOMAIN_ROOT; ?>/core/ajax/clan/donate.php'>Donate to Clan</a>
							</td>
						</tr>
					</tbody>
				</table>

			<?php
					if ( in_array($User_Data['Clan_Rank'], ['Moderator', 'Administrator']) )
					{
			?>

				<table class='border-gradient' style='margin-top: 5px; width: 400px;'>
					<thead>
						<tr>
							<th colspan='2'>
								<b>Moderator Options</b>
							</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td colspan='1' style='width: 50%;'>
								<a href='<?= DOMAIN_ROOT; ?>/core/ajax/clan/manage_members.php'>Manage Members</a>
							</td>
							<td colspan='1' style='width: 50%;'>
								<a href='<?= DOMAIN_ROOT; ?>/core/ajax/clan/manage_clan.php'>Manage Clan</a>
							</td>
						</tr>
						<tr>
							<td colspan='1' style='width: 50%;'>
								<a href='<?= DOMAIN_ROOT; ?>/core/ajax/clan/clan_upgrades.php'>Clan Upgrades</a>
							</td>
							<td colspan='1' style='width: 50%;'>
								<a href='<?= DOMAIN_ROOT; ?>/core/ajax/clan/send_message.php'>Send A Clan Announcement</a>
							</td>
						</tr>
					</tbody>
				</table>

			<?php
					}

					if ( $User_Data['Clan_Rank'] == 'Administrator' )
					{
			?>

				<table class='border-gradient' style='margin-top: 5px; width: 400px;'>
					<thead>
						<tr>
							<th colspan='2'>
								<b>Administrator Options</b>
							</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td colspan='1' style='width: 50%;'>
								<a href='<?= DOMAIN_ROOT; ?>/core/ajax/clan/.php'>Clan Option Link</a>
							</td>
							<td colspan='1' style='width: 50%;'>
								<a href='<?= DOMAIN_ROOT; ?>/core/ajax/clan/.php'>Clan Option Link</a>
							</td>
						</tr>
					</tbody>
				</table>

			<?php
					}
				}
			?>

			</div>

			<div style='flex-basis: 50%;'>
				<table class='border-gradient' style='width: 400px;'>
					<thead>
						<tr>
							<th colspan='3'>
								<b>Clan Members</b>
							</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td colspan='1' style='width: 25%;'>
								<b>Member</b>
							</td>
							<td colspan='1' style='width: 25%;'>
								<b>Clan Title</b>
							</td>
							<td colspan='1' style='width: 25%;'>
								<b>Clan Experience</b>
							</td>
						</tr>
					</tbody>
					<tbody>
						<?php
							foreach ( $Members as $Index => $Member )
							{
								$Member = $User_Class->FetchUserData($Member['id']);
								
								echo "
									<tr>
										<td>
											<a href='" . DOMAIN_ROOT . "/profiles.php?id={$Member['ID']}'>
												<b class='" . strtolower($Member['Clan_Rank']) . "'>
													{$Member['Username']}
												</b>
											</a>
										</td>
										<td>
											{$Member['Clan_Title']}
										</td>
										<td>
											{$Member['Clan_Exp']}
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
</div>

<?php
	}

	require_once 'core/required/layout_bottom.php';
