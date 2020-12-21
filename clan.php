<?php
	require 'core/required/layout_top.php';

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
		if ( isset($_GET['clan_id']) )
		{
			$Clan_ID = $Purify->Cleanse($_GET['clan_id']);

			$Clan_Data = $Clan_Class->FetchClanData($Clan_ID);
		}
		else
		{
			$Clan_Data = $Clan_Class->FetchClanData($User_Data['Clan']);
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
					<tbody>
						<tr>
							<td colspan='2' style='height: 200px; width: 200px;'>
								<?= ( $Clan_Data['Avatar'] ? "<img src='{$Clan_Data['Avatar']}' />" : 'This clan has no avatar set.' ); ?>
							</td>
							<td colspan='2' style='height: 200px; width: 200px;'>
								<?= ( $Clan_Data['Signature'] ? $Clan_Data['Signature'] : 'This clan has no signature set.' ); ?>
							</td>
						</tr>
						<tr>
							<td colspan='2'>
								<b>Money</b>
							</td>
							<td colspan='2'>
								$<?= $Clan_Data['Money']; ?>
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
					</tbody>
				</table>

				<br />

				<table class='border-gradient' style='width: 400px;'>
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
								<a href=''>Clan Link</a>
							</td>
							<td colspan='1' style='width: 50%;'>
								<a href=''>Clan Link</a>
							</td>
						</tr>
						<tr>
							<td colspan='1' style='width: 50%;'>
								<a href=''>Clan Link</a>
							</td>
							<td colspan='1' style='width: 50%;'>
								<a href=''>Clan Link</a>
							</td>
						</tr>
						<tr>
							<td colspan='1' style='width: 50%;'>
								<a href=''>Clan Link</a>
							</td>
							<td colspan='1' style='width: 50%;'>
								<a href=''>Clan Link</a>
							</td>
						</tr>
						<tr>
							<td colspan='1' style='width: 50%;'>
								<a href='<?= DOMAIN_ROOT; ?>/core/ajax/clan/leave.php'>Leave Clan</a>
							</td>
							<td colspan='1' style='width: 50%;'>
								<a href=''></a>
							</td>
						</tr>
					</tbody>
				</table>
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
								<b>Clan Exp.</b>
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
										<td>{$Member['Username']}</td>
										<td>{$Member['Clan_Title']}</td>
										<td>{$Member['Clan_Exp']}</td>
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
