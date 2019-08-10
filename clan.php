<?php
	require 'core/required/layout_top.php';

	/**
	 * This can really be dealt with in a much better way.. ^-^;
	 * 
	 */
	if ( $User_Data['Style'] === '1' )  
  {
    $Border_Color = "#4A618F";
    $Background_Hover = "#253147";
  }
  else
  {
    $BorderColor = "#fff";
	}

	/**
	 * Attempting to manage some aspect of the clan.
	 */
	if ( isset($_GET['manage']) )
	{
		$Param = Purify($_GET['manage']);

		/**
		 * Manage the clan's upgrades.
		 */
		if ( $Param == "upgrades" )
		{
			echo "
				<div class='content'>
					<div class='head'>Manage Upgrades</div>
					<div class='box'>
						Manage the clan's upgrades.
					</div>
				</div>
			";
		}

		/**
		 * Manage the clan avatar / signature.
		 */
		if ( $Param == "details" )
		{
			echo "
				<div class='content'>
					<div class='head'>Manage Details</div>
					<div class='box'>
						Add, remove, or change the clan's avatar or signature.
					</div>
				</div>
			";
		}

		/**
		 * Manage the clan's members.
		 */
		if ( $Param == "members" )
		{
			if ( isset($_GET['id']) )
			{
				$ID = $Purify->Cleanse($_GET['id']);
				$User = $User_Class->FetchUserData($ID);

				echo "
					<div class='content'>
						<div class='head'>Managing {$User['Username']}</div>
						<div class='box'>
							<div class='row'>
								<div style='float: left; padding-top: 25px; width: calc(100% / 3);'>
									Would you like to kick {$User['Username']} from the clan?<br />
									<input type='submit' name='kick' value='Kick {$User['Username']}!' style='width: 90%;' />
								</div>
								
								<div style='float: left; padding-top: 5px; width: calc(100% / 3);'>
									Would you like to change the Clan Rank of {$User['Username']}?<br />
									<select name='ranks' style='width: 80%;'>
										<option value='Member'>Member</option>
										<option value='Moderator'>Moderator</option>
										<option value='Admin'>Admin</option>
									</select>
									<button onclick='updateTitle();' style='border-top-left-radius: 0px; border-top-right-radius: 0px; border-top: none; width: 80%;'>Update Rank</button>
								</div>

								<div style='float: left; width: calc(100% / 3);'>
									Would you like to update {$User['Username']}'s Clan Title?<br />
									<input type='text' name='title' placeholder='Title' style='border-bottom-left-radius: 0px; border-bottom-right-radius: 0px; margin: 0; text-align: center; width: 80%;' />
									<button onclick='updateTitle();' style='border-top-left-radius: 0px; border-top-right-radius: 0px; border-top: none; width: 80%;'>Update Nickname</button>
								</div>
							</div>
						</div>
					</div>
				";
			}
			else
			{
				try
				{
					$Clan_Query = $PDO->prepare("SELECT * FROM `clans` WHERE `ID` = ?");
					$Clan_Query->execute([ $User_Data['Clan'] ]);
					$Clan_Query->setFetchMode(PDO::FETCH_ASSOC);
					$Clan = $Clan_Query->fetch();

					$Member_Query = $PDO->prepare("SELECT `id`, `Username`, `Clan_Exp`, `Clan_Rank`, `Clan_Title`, `Last_Active`, `Status` FROM `users` WHERE `Clan` = ? ORDER BY `Clan_Exp` DESC");
					$Member_Query->execute([ $Clan['ID'] ]);
					$Member_Query->setFetchMode(PDO::FETCH_ASSOC);
					$Members = $Member_Query->fetchAll();
				}
				catch ( PDOException $e )
				{
					HandleError( $e->getMessage() );
				}

				echo "
					<div class='content'>
						<div class='head'>Manage Members</div>
						<div class='box'>
							<div class='row'>
				";

				foreach( $Members as $Key => $Value )
				{
					echo "
						<div style='float: left; width: calc(100% / 3);'>
							<a href='" . Domain(1) . "/clan.php?manage=members&id={$Value['id']}'>
								{$Value['Username']}
							</a>
						</div>
					";
				}

				echo "
							</div>
						</div>
					</div>
				";
			}
		}
	}

	/**
	 * Process the creation of a clan.
	 */
	if ( isset($_POST['create']) )
	{
		if ( $User_Data['Clan'] == 0 )
		{
			$Clan_Name = Purify($_POST['name']);

			if ( $Clan_Name != '' )
			{
				if ( $User_Data['Money'] >= $Constants->Clan['Creation_Cost'] )
				{
					$Success = "
						You have successfully created the clan <b>{$Clan_Name}</b>!<br />
						<a href='" . Domain(1) . "/clan.php'>Clan Home</a>
					";

					try
					{
						$Create = $PDO->prepare("INSERT INTO `clans` (`Name`) VALUES (?)");
						$Create->execute([ $Clan_Name ]);
						$Clan_ID = $PDO->lastInsertId();

						$Update = $PDO->prepare("UPDATE `users` SET `Money` = `Money` - ?, `Clan` = ? WHERE `id` = ? LIMIT 1");
						$Update->execute([ $Constants->Clan['Creation_Cost'], $Clan_ID, $User_Data['id'] ]);
					}
					catch ( PDOException $e )
					{
						HandleError( $e->getMessage() );
					}
				}
				else
				{
					$Error =  "You can not afford to create a clan.";
				}
			}
			else
			{
				$Error =  "Please enter a name for your clan.";
			}
		}
		else
		{
			$Error = "You're already in a clan.";
		}
	}

	/**
	 * The user isn't in a clan.
	 */
	if ( $User_Data['Clan'] == 0 )
	{
		$Cost = number_format($Constants->Clan['Creation_Cost']);
?>

<div class='content'>
	<div class='head'>Create A Clan</div>
	<div class='box'>
		<?php
			if ( isset($Error) && $Error != '' )
			{
				echo "<div class='error' style='margin-bottom: 5px;'>$Error</div>";
			}

			if ( isset($Success) && $Success != '' )
			{
				echo "<div class='success' style='margin-bottom: 5px;'>$Success</div>";
			}
		?>

		<div class='description' style='margin-bottom: 5px;'>
			You may create a clan at the cost of $<?= $Cost ?>.
		</div>

		<form method='POST'>
			<input type="text" name="name" placeholder='Clan Name' style="border-bottom-left-radius: 0px; border-bottom-right-radius: 0px; margin: 0; text-align: center; width: 30%;" /><br />
			<input type='submit' name='create' value='Create Clan' style="border-top-left-radius: 0px; border-top-right-radius: 0px; border-top: none; width: 30%;" />
		</form>
	</div>
</div>

<?php
	}

	/**
	 * The user is in a clan; display the appropriate information.
	 */
	else
	{
		/**
		 * Fetch the user's clan information.
		 */
		try
		{
			$Clan_Query = $PDO->prepare("SELECT * FROM `clans` WHERE `ID` = ?");
			$Clan_Query->execute([ $User_Data['Clan'] ]);
			$Clan_Query->setFetchMode(PDO::FETCH_ASSOC);
			$Clan = $Clan_Query->fetch();

			$Member_Query = $PDO->prepare("SELECT `id`, `Username`, `Clan_Exp`, `Clan_Rank`, `Clan_Title`, `Last_Active`, `Status` FROM `users` WHERE `Clan` = ? ORDER BY `Clan_Exp` DESC");
			$Member_Query->execute([ $Clan['ID'] ]);
			$Member_Query->setFetchMode(PDO::FETCH_ASSOC);
			$Members = $Member_Query->fetchAll();
		}
		catch ( PDOException $e )
		{
			HandleError( $e->getMessage() );
		}
?>

<div class='content'>
	<div class='head'>Clan Headquarters</div>
	<div class='box'>
		<?php
			if ( $Clan['Signature'] != null )
			{
				echo "
					<div class='description'>"
						. nl2br($Clan['Signature']) . "
					</div>
				";
			}

			if ( $User_Data['Clan_Rank'] != 'Member' )
			{
				echo "
					<div class='panel' style='border-width: 2px; margin-top: 5px;'>
						<div class='panel-heading'>Staff Options</div>
						<div class='panel-body'>
							<div style='border-right: 1px solid {$Border_Color}; float: left; padding: 3px; width: calc(100% / 3);'>
								<a style='display: block;' href='clan.php?manage=members'>Manage Members</a>
							</div>
							<div style='border-right: 1px solid {$Border_Color}; float: left; padding: 3px; width: calc(100% / 3);'>
								<a style='display: block;' href='clan.php?manage=upgrades'>Manage Upgrades</a>
							</div>
							<div style='float: left; padding: 3px; width: calc(100% / 3);'>
								<a style='display: block;' href='clan.php?manage=details'>Manage Avatar/Signature</a>
							</div>
						</div>
					</div>
				";
			}
		?>

		<div class='row'>
			<div class='panel' style='border-width: 2px; float: left; margin-right: 5px; margin-top: 5px; width: 300px;'>
				<div class='panel-heading'><?= $Clan['Name']; ?></div>
				<div class='panel-body'>
					<?php
						if ( $Clan['Avatar'] != null )
						{
							echo "
								<div style='padding: 3px;'>
									<img src='{$Clan['Avatar']}' />
								</div>
								<hr />
							";
						}
					?>

					<div class='info' style='margin-top: 2px;'>
						<div style='padding-top: 0px;'>Level</div>
						<div><?= number_format(FetchLevel($Clan['Experience'], 'Clan')); ?></div>
						<div style='padding-top: 0px;'>Experience</div>
						<div><?= number_format($Clan['Experience']); ?></div>
						<div style='padding-top: 0px;'>Money</div>
						<div><?= number_format($Clan['Money']); ?></div>
					</div>
				</div>
			</div>

			<table class='standard' style='margin: 5px auto 0px; width: calc(100% - 305px);'>
				<thead>
					<tr>
						<th style='width: 5%;'></th>
						<th style='width: 45%;'>Username</th>
						<th style='width: 15%;'>Clan Exp</th>
						<th style='width: 35%;'>Clan Rank</th>
					</tr>
				</thead>
				<tbody>
					<?php
						foreach( $Members as $Key => $Value )
						{
							/**
							 * Determine last_active status.
							 */
							$Calc_Online = time() - $Value['Last_Active'];
							if ( $Calc_Online / 60 < 15 )
              {
								$Status = "online";
              }
              else
              {
                $Status = "offline";
							}
							
							/**
							 * Personal status.
							 */
							if ( $Value['Status'] == null )
							{
								$Value['Status'] = "This user hasn't set their status yet.";
							}

							echo "
								<tr>
									<td onclick='toggleDetails({$Value['id']});'>
										<img src='" . Domain(1) . "/images/Assets/{$Status}.png' />
									</td>
									<td>
										<a href='" . Domain(1) . "/profile.php?id={$Value['id']}'>
											<span class='{$Value['Clan_Rank']}' style='font-size: 14px;'>{$Value['Username']}</span>
										</a>
									</td>
									<td>
										" . number_format($Value['Clan_Exp']) . "
									</td>
									<td>
										{$Value['Clan_Title']}
									</td>
								</tr>
								<tr id='{$Value['id']}' style='display: none;'>
									<td colspan='2'>
										<i>{$Value['Status']}</i>
									</td>
									<td colspan='2'>
										<b>Last Online</b><br />
										" . date("F j, Y (g:i A)", $Value['Last_Active']) . "
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

<script type='text/javascript'>
	$("a.popup.cboxElement").colorbox({ iframe: true, innerWidth: 680, innerHeight: 491 });
	
	function toggleDetails(id)
	{
		if ( $('tr#' + id).css('display') == 'none' )
		{
			$('tr#' + id).css('display', 'table-row');
		}
		else
		{
			$('tr#' + id).css('display', 'none');
		}
	}
</script>

<?php
	}

	require 'core/required/layout_bottom.php';