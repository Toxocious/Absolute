<?php
	require 'session.php';
?>

<!DOCTYPE html>
<html>
	<head>
		<title><?= $Current_Page['Name']; ?> :: The Pokemon Absolute</title>
		<link href='<?= Domain(1); ?>/images/Icons/4 - Shiny Sunset/Mega/359-mega.png' rel='shortcut icon'>

		<link href='<?= Domain(1); ?>/css/required.css' rel='stylesheet'>
		<link href='<?= Domain(1); ?>/css/default.css' rel='stylesheet'>

		<script type='text/javascript' src='<?= Domain(1); ?>/js/libraries.js'></script>
		<script type='text/javascript' src='<?= Domain(1); ?>/js/popup.js'></script>
		<script type='text/javascript' src='<?= Domain(1); ?>/js/main.js'></script>
		<?php
			/**
			 * Adds snowstorm.js if the current date is sometime in December.
			 */
			if ( isBetweenDates('2018-12-01', '2018-12-31') )
			{
				echo "<script type='text/javascript' src='" . Domain(1) . "/js/snowstorm.js'></script>";	
			}
		?>

		<script type='text/javascript' src='<?= Domain(1); ?>/js/absochat.js'></script>
		<script type='text/javascript'>
			ABSOLUTE_PORT = "3000";
			DOMAIN = "<?= Domain(1); ?>";

			$(function()
			{
				Absolute.user = {
					block_string: [],
					postcode: "<?= $User_Data['Auth_Code']; ?>",
					user_name: "<?= $User_Data['Username']; ?>",
					user_id: <?= $User_Data['id']; ?>, 
					chat_size: "0" ,
					auto_caps: "no",
				}

				Absolute.enable();

				$('#chatMessage').keydown(function(e)
				{
					if (e.keyCode == 13)
					{
						let text = $('#chatMessage').val().trim();
						if (text != '' && Absolute.isConnected())
						{
							socket.emit("input",
							{
								text: text,
								username: "<?= $User_Data['Username']; ?>" //Need to authenticate like before
							});
							$('#chatMessage').val('').trigger('input');
						}

						return false;
					}
				});

				<?php
					if ( $User_Data['Power'] >= 3 )
					{
				?>
					Absolute.isStaff = true;

					Absolute.staff = {
						kick: function( uID )
						{
							socket.emit("chaterpie-kick-user", uID, $('#chatMessage').val());
							$('#chatMessage').val('');

							Absolute.changeWindow('chat');
							return false;
						},

						ban: function( uID, time ) {
							if (!confirm("Do you really want to ban user #"+uID+"?"))
							{
								return;
							}

							var banTime = time ? time : $('#messageBanTimeStaff').val();
							socket.emit("chaterpie-ban-user", uID, $('#chatMessage').val(), banTime);
							$('#chatMessage').val('');

							Absolute.changeWindow('chat');
							return false;
						},

						quickBan: function( uID )
						{
							if ( confirm("Do you really want to ban user #"+uID+" for 5 minutes?") )
							{
								Absolute.staff.ban(uID, 300);
							}
						}
					};
				<?php
					}
				?>
			});
		</script>
	</head>

	<body>
		<div class='banner'>
			<a href= <?= (isset($_SESSION['abso_user'])) ? Domain(1) . '/news.php' : Domain(1) . '/index.php' ?>>
				<img src='<?= Domain(1); ?>/images/Assets/banner.png' />
			</a>
		</div>

<?php
	if ( isset($_SESSION['abso_user']) )
	{
?>

		<div class='userbar'>
			<div class='user'>
				<ul>
					<li class='dropdown' style='height: 30px; width: 198px;'>
						<a href='<?= Domain(1); ?>/profile.php?id=<?= $User_Data['id']; ?>'>
							<?php
								$Display_Username = $UserClass->DisplayUserName($User_Data['id'], false, true);

								echo $Display_Username;
							?>
						</a>
						<ul class='dropdown-content'>
							<li>
								<a href='<?= Domain(1); ?>/settings.php'>Settings & Options</a>
							</li>
							<li>
								<a href='<?= Domain(1); ?>/login.php?Logout'>Logout</a>
							</li>
						</ul>
					</li>
				</ul>
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
							$RosterPoke[$i]['Gender_Icon'] = null;
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

						if ( $RosterPoke[$i]['Gender_Icon'] != null && $RosterPoke[$i]['Gender_Icon'] != 'G' && $RosterPoke[$i]['Gender_Icon'] != "(?)" )
						{
							echo "<img src='{$RosterPoke[$i]['Gender_Icon']}' style='height: 20px; margin: 10px 0px 0px -20px; position: absolute; width: 20px;' />";
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
										<div>" . number_format($RosterPoke[$i]['Experience']) . "</div>
									</div>
								</div>
							</div>
						";
					}
				?>
			</div>

			<div class='money' style='border-left: none; float: left; width: 199px;'>
				<?= $Date; ?>
			</div>
		</div>
		
	<?php
		if ( $User_Data['RPG_Ban'] != 1 )
		{
	?>

		<div class='sidebar'>
			<?php
				if ( $User_Data['Power'] >= 3 )
				{
					if ( $Current_Page['URL'] != '/staff/' )
					{
						echo 	"<div class='button'>";
						echo		"<a href='" . Domain(1) . "/staff'>Staff Panel</a>";
						echo	"</div>";
					}
					else
					{
						echo 	"<div class='button'>";
						echo		"<a href='" . Domain(1) . "/index.php'>Index</a>";
						echo	"</div>";
					}
				}
			?>

			<div class='chat' id='AbsoChat'>
				<div class='head'>
					Chat
				</div>
				<div class='user_options' id='user_options' style='display: none'></div>
				<div class='box' id='chatContent'>
					<div style='margin-top: 150px;'>
						<div class='spinner' style='left: 42.5%; position: relative;'></div>
					</div>
				</div>
				<div class='subhead'>
					<?php
						if ( $User_Data['Chat_Ban'] == 'no' )
						{
							echo 	"<form name='chat_form'>";
							echo 		"<input type='text' name='chatMessage' id='chatMessage' autocomplete='off' />";
							echo	"</form>";
						}
					?>
				</div>
			</div>
		</div>

		<?php
			if ( $Current_Page['URL'] != "/staff/" )
			{
		?>
			<div class='navigation'>
				<ul>
					<li class='dropdown'>
						<a href='javascript:void(0);'>Pokemon</a>
						<ul class='dropdown-content'>
							<li><a href='<?= Domain(1); ?>/pokemon_center.php'>Pokemon Center</a></li>
							<li><a href='<?= Domain(1); ?>/evolution_center.php'>Evolution Center</a></li>
							<!--<li><a href='<?= Domain(1); ?>/lab.php'>Laboratory</a></li>-->
						</ul>
					</li>
					
					<li class='dropdown'>
						<a href='javascript:void(0);'>Economy</a>
						<ul class='dropdown-content'>
							<li><a href='<?= Domain(1); ?>/trades.php'>Trade Center</a></li>
							<li><a href='<?= Domain(1); ?>/trade_interest.php'>Trade Interest</a></li>
							<li><a href='<?= Domain(1); ?>/shop.php'>Shops</a></li>
						</ul>
					</li>
					
					<li class='dropdown'>
						<a href='javascript:void(0);'>Exploration</a>
						<ul class='dropdown-content'>
							<!--<li><a href='<?= Domain(1); ?>/faction.php'>Faction HQ</a></li>-->
							<li><a href='<?= Domain(1); ?>/mining_select.php'>Mining Selection</a></li>
							<li><a href='<?= Domain(1); ?>/map_select.php'>Map Selection</a></li>
						</ul>
					</li>
					
					<li class='dropdown'>
						<a href='javascript:void(0);'>Battle</a>
						<ul class='dropdown-content'>
							<li><a href='<?= Domain(1); ?>/battle_trainer.php'>Battle A Trainer</a></li>
							<li><a href='<?= Domain(1); ?>/battle_challenge.php'>Training Challenge</a></li>
							<li><a href='<?= Domain(1); ?>/battle_regions.php'>Regional Challenges</a></li>
							<li><a href='<?= Domain(1); ?>/battle_frontier.php'>Battle Frontier</a></li>
						</ul>
					</li>
					
					<li class='dropdown'>
						<a href='javascript:void(0);'>Clans</a>
						<ul class='dropdown-content'>
							<li><a href='<?= Domain(1); ?>/clan.php'>Clan Home</a></li>
							<li><a href='<?= Domain(1); ?>/clan_list.php'>Clan Listings</a></li>
							<!--<li><a href='<?= Domain(1); ?>/clan_raids.php'>Clan Raids</a></li>-->
						</ul>
					</li>

					<li class='dropdown'>
						<a href='javascript:void(0);'>Community</a>
						<ul class='dropdown-content'>
							<li><a href='<?= Domain(1); ?>/forums'>Forums</a></li>
							<li><a href='<?= Domain(1); ?>/rankings.php'>Rankings</a></li>
							<li><a href='<?= Domain(1); ?>/messages.php'>Private Messages</a></li>
							<li><a href='<?= Domain(1); ?>/online.php'>Online List</a></li>
							<li><a href='<?= Domain(1); ?>/staff.php'>Staff List</a></li>
						</ul>
					</li>
				</ul>
			</div>
		<?php
			}
			else
			{
		?>

			<div class='navigation'>
				<ul>
					<li class='dropdown'>
						<a href='javascript:void(0);'>Settings</a>
						<ul class='dropdown-content'>
							<li><a href='<?= Domain(1); ?>/staff/settings.php'>Staff Panel Settings</a></li>
							<li><a href='<?= Domain(1); ?>/staff/message.php'>Staff Message</a></li>
							<li><a href='<?= Domain(1); ?>/staff/maintenance.php'>Game Maintenance</a></li>
							<li><a href='<?= Domain(1); ?>/staff/announcement.php'>Game Announcement</a></li>
						</ul>
					</li>
				</ul>
			</div>

		<?php
			}
		}

		if ( $Current_Page['Maintenance'] == 'yes' && $User_Data['Power'] >= 7 )
		{
			echo "<div class='maintenance'>Despite this page being down for maintenance, you seem to be authorized to be here.</div>";
		}
		else if ( $Current_Page['Maintenance'] == 'yes' && $User_Data['Power'] < 7 )
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

		if ( count($Roster) == 0 )
		{
			echo "
				<div class='maintenance'>
					While you have an empty roster, certain parts of Absolute will be unavailable to you.
				</div>
			";
		}
	}
	else
	{
		if ( $Current_Page['Logged_In'] == 'yes' )
		{
			echo "
				<div class='content' style='margin: 5px; width: calc(100% - 10px);'>
					<div class='head'>Error</div>
					<div class='box'>
						You must be logged in to view this page.
					</div>
				</div>
			";

			exit();
		}
	}
?>