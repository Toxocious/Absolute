<?php
	require_once 'session.php';
?>

<!DOCTYPE html>
<html>
	<head>
		<title><?= $Current_Page['Name']; ?> :: The Pokemon Absolute</title>
		<link href='<?= Domain(1); ?>/images/Icons/4 - Shiny Sunset/Mega/359-mega.png' rel='shortcut icon'>

		<link href='<?= Domain(1); ?>/css/required.css' rel='stylesheet'>
		<link href='<?= Domain(1); ?>/css/default.css' rel='stylesheet'>

		<script type='text/javascript' src='<?= Domain(1); ?>/js/libraries.js'></script>
		<script type='text/javascript' src='<?= Domain(1); ?>/js/main.js'></script>
	</head>

	<body>
		<div class='banner'>
			<a href=<?= (isset($_SESSION['abso_user'])) ? Domain(1) . '/news.php' : Domain(1) . '/index.php' ?>>
				<img src='<?= Domain(1); ?>/images/Assets/banner.png' />
			</a>
			<div style='border-bottom-right-radius: 4px;'>
				<?= $Date; ?>
			</div>
			<?php
				if ( $Current_Page['Maintenance'] == 'yes' && $User_Data['Power'] >= 7 )
				{
					echo "<div class='notice'>Despite this page being down for maintenance, you seem to be authorized to be here.</div>";
				}
			?>
		</div>

<?php
	if ( isset($_SESSION['abso_user']) )
	{
?>

		<div class='userbar'>
			<div class='user'>
				<a href='profile.php?id=<?= $User_Data['id']; ?>'>
					<?php
						if ( $User_Data['Rank'] === '420' )
							echo	"<div><span class='admin' style='font-size: 14px;'>" . $User_Data['Username'] . " - #" . $User_Data['id'] . "</span></div>";
						else if ( $User_Data['Rank'] === '69' )
							echo	"<div><span class='gmod' style='font-size: 14px;'>" . $User_Data['Username'] . " - #" . $User_Data['id'] . "</span></div>";
						else if ( $User_Data['Rank'] === '12' )
							echo	"<div><span class='cmod' style='font-size: 14px;'>" . $User_Data['Username'] . " - #" . $User_Data['id'] . "</span></div>";
						else 
							echo  "<div><span class='member' style='font-size: 14px;'>" . $User_Data['Username'] . " - #" . $User_Data['id'] . "</span></div>";
					?>
				</a>
			</div>

			<div class='notifications' style='display: block; font-size: 14px; height: 30px; padding: 5px 3px 3px 3px;'>
				<a href='testing2.php'>Spawn Pokemon</a>
			</div>

			<div class='messages'>
				<span style='display: block; font-size: 14px; height: 30px; padding: 5px 3px 3px 3px;'>
					<a href='javascript:void(0);' onclick='toggleMessages();'><img src='' />Msgs</a>
				</span>
				<div style='display: none;'></div>
			</div>

			<div class='money'>
				$<?= number_format($User_Data['Money']); ?>
			</div>
				
			<div class='roster' id='userbar_roster'>
				<?php
					for ( $i = 0; $i <= 5; $i++ )
					{
						if ( isset($Roster[$i]['ID']) )
						{
							$RosterPoke[$i] = $PokeClass->FetchPokemonData($Roster[$i]['ID']);
						}
						else
						{
							$RosterPoke[$i]['Icon'] = Domain(1) . "/images/Pokemon/0_mini.png";
							$RosterPoke[$i]['Sprite'] = Domain(1) . "/images/Pokemon/0.png";
							$RosterPoke[$i]['Display_Name'] = "Empty";
							$RosterPoke[$i]['Level'] = '0';
							$RosterPoke[$i]['Experience'] = '0';
							$RosterPoke[$i]['Gender'] = null;
							$RosterPoke[$i]['Item'] = null;
						}

						echo "
							<div class='roster_slot' onmouseover='showSlot({$i});' onmouseout='hideSlot({$i});' style='text-align: center; min-width: 40px;'>
								<div class='roster_mini'>
									<img src='{$RosterPoke[$i]['Icon']}' />
								</div>
								<div class='roster_tooltip' id='rosterTooltip{$i}'>
						";

						if ( $RosterPoke[$i]['Gender'] != null )
						{
							echo "<img src='{$RosterPoke[$i]['Gender']}' style='height: 20px; margin: 10px 0px 0px -20px; position: absolute; width: 20px;' />";
						}

						echo "<img src='{$RosterPoke[$i]['Sprite']}' />";

						if ( $RosterPoke[$i]['Item'] != null || $RosterPoke[$i]['Item'] != 0 )
						{
							echo "<img src='{$RosterPoke[$i]['Item_Icon']}' style='margin: 5px 0px 0px -10px; position: absolute;' />";
						}

						echo "
									<div><b>{$RosterPoke[$i]['Display_Name']}</b></div>
									<div class='info'>
										<div>Level</div>
										<div>{$RosterPoke[$i]['Level']}</div>
									</div>
									<div class='info'>
										<div>Experience</div>
										<div>{$RosterPoke[$i]['Experience']}</div>
									</div>
								</div>
							</div>
						";
					}
				?>
			</div>
		</div>
		
	<?php
		if ( $User_Data['RPG_Ban'] != 1 )
		{
	?>

		<div class='sidebar'>
			<?php
				if ( $User_Data['Rank'] >= 21 )
				{
					echo 	"<div class='button'>";
					echo		"<a href='staff'>Staff Panel</a>";
					echo	"</div>";
				}
			?>

			<div class='chat' id='AbsoChat'>
				<div class='head'>
					<img src='<?= Domain(1); ?>/images/Assets/options.png' style='cursor: pointer; height: 20px; margin-left: -70px; position: absolute; width: 20px;' onclick='/*AbsoChat.changeWindow("settings");*/' />
					Chat
					<img src='<?= Domain(1); ?>/images/Assets/options.png' style='cursor: pointer; height: 20px; margin-left: 50px; position: absolute; width: 20px;' onclick='/*AbsoChat.reset();*/' />
				</div>
				<div class='user_options' id='user_options' style='display: none'></div>
				<div class='box' id='chatContent'>
					<div style='margin-top: 150px;'>
						<div class='spinner' style='left: 42.5%; position: relative;'></div>
					</div>
				</div>
				<div class='subhead'>
					<?php
						if ( $User_Data['ChatBanned'] != '1' )
						{
							echo 	"<form name='chat_form'>";
							echo 		"<input type='text' name='chatMessage' id='chatMessage' autocomplete='off' />";
							echo	"</form>";
						}
					?>
				</div>
			</div>
		</div>

		<div class='navigation'>
			<ul>
				<li class='dropdown'>
					<a href='javascript:void(0);'>Pokemon</a>
					<ul class='dropdown-content'>
						<li><a href='pokemon_center.php'>Pokemon Center</a></li>
						<li><a href='evolution_center.php'>Evolution Center</a></li>
						<li><a href='lab.php'>Pokemon Laboratory</a></li>
					</ul>
				</li>
				
				<li class='dropdown'>
					<a href='javascript:void(0);'>Economy</a>
					<ul class='dropdown-content'>
						<li><a href='trade_central.php'>Trade Central</a></li>
						<li><a href='trade_interest.php'>Trade Interest</a></li>
						<li><a href='shop_plaza.php'>Shop Plaza</a></li>
					</ul>
				</li>
				
				<li class='dropdown'>
					<a href='javascript:void(0);'>Exploration</a>
					<ul class='dropdown-content'>
						<?php
							if ( $User_Data['Clan'] != '0' )
							{
								echo	"<li><a href='clan.php'>Clan HQ</a></li>";
							}
							else
							{
								echo	"<li><a href='clan_create.php'>Create A Clan</a></li>";
							}
						?>
						<li><a href='clan_list.php'>Clan Listings</a></li>
						<li><a href='faction.php'>Faction Headquarters</a></li>
						<li><a href='mining_select.php'>Mining Selection</a></li>
						<li><a href='map_select.php'>Map Selection</a></li>
					</ul>
				</li>
				
				<li class='dropdown'>
					<a href='javascript:void(0);'>Battle</a>
					<ul class='dropdown-content'>
						<li><a href='battle_trainer.php'>Battle A Trainer</a></li>
						<li><a href='battle_challenge.php'>Training Challenge</a></li>
						<li><a href='battle_regions.php'>Regional Challenges</a></li>
					</ul>
				</li>
				
				<li class='dropdown'>
					<a href='javascript:void(0);'>Community</a>
					<ul class='dropdown-content'>
						<li><a href='forums.php'>Forums</a></li>
						<li><a href='online.php'>Online List</a></li>
						<li><a href='messages.php'>Private Messages</a></li>
						<li><a href='rankings.php'>Global Rankings</a></li>
						<li><a href='staff_list.php'>Staff List</a></li>
						<li><a href='profile_view.php'>View A Profile</a></li>
					</ul>
				</li>
				
				<li class='dropdown'>
					<a href='javascript:void(0);'>Settings</a>
					<ul class='dropdown-content'>
						<li><a href='achievements.php'>Achievements</a></li>
						<li><a href='settings.php'>Player Settings</a></li>
						<li><a href='logout.php'>Logout</a></li>
					</ul>
				</li>
			</ul>
		</div>

	<?php
		}

		if ( $Current_Page['Maintenance'] == 'yes' && $User_Data['Power'] <= 6 )
		{
			echo "
				<div class='content'>
					<div class='head'>Maintenance</div>
					<div class='box'>
						This page is currently undergoing maintenance.
					</div>
				</div>
			";
			exit();
		}
	}
?>