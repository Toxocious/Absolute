<?php
	require 'global_functions.php';
	require 'session.php';
	include 'title.php';
	
	# Access the user's data easily.
	$User_Data = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM members WHERE id = '" . $row['id'] . "'"));
	
	# Get the full URL of the page that the user is on.
	$Fetch_URL = "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	
	# Set the Timezone for date().
	date_default_timezone_set('America/Los_Angeles');
	$Date = date("M dS, Y g:i:s A");
	$Time = time();

	# Update the `Last_Active` and `Last_Online` data for the user.
	mysqli_query($con, "UPDATE members SET Last_Active = '" . $Time . "', Last_Online = '" . $Date . "' WHERE id = '" . $User_Data['id'] . "'");
	
	# Check to see if the user is banned.
	if ( $User_Data['RPG_Ban'] === '1' && $Fetch_URL !== 'http://localhost/Absolute/banned.php' )
	{
		header('Location: banned.php');
		die();
	}
	
	# Ensure that the user has at least one Pokemon in their roster.
	$Roster = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM pokemon WHERE Slot = '1' AND Owner_Current = '" . $User_Data['id'] . "'"));
	if ( !$Roster && $Fetch_URL !== 'http://localhost/Absolute/pokemon_center.php' )
	{
		header('Location: pokemon_center.php');
		die();
	}
?>

<!DOCTYPE html>
<html>
	<head>
		<title><?php echo $Title; ?> :: The Pokemon Absolute</title>
		<link href='images/Icons/4 - Shiny Sunset/Mega/359-mega.png' rel='shortcut icon'>

		<link href='css/required.css' rel='stylesheet'>
		<link href='css/default.css' rel='stylesheet'>

		<script type='text/javascript' src='js/libraries.js'></script>
		<!--<script type='text/javascript' src='js/absochat.js'></script>-->
		<script type='text/javascript' src='js/main.js'></script>
		<!--<script type='text/javascript' src='js/ajax.js'></script>-->

		<!--
		<script type='text/javascript'>
			$(function () {
				AbsoChat.user = {
					user_id:  '<?php echo $User_Data['id']; ?>',
					username: '<?php echo $User_Data['Username']; ?>',
					postcode: '<?php echo mt_rand(10000000, 99999999); ?>',
					block_string: []
				};

				AbsoChat.enable();
	
				$('#chatMessage').keydown(function(e)
				{
					if ( e.keyCode == 13 )
					{
						var text = $('#chatMessage').val().trim();
						if ( text != '' )
						{
							// p sure this gets sent to ajax/chat_message.php to be stored in json so that it can be called back later or something?
							/**/
							socket.emit('input', 
							{
								username: '<?php echo $User_Data['Username']; ?>', 
								text: text
							});
							/**/

							$('#chatMessage').val('').trigger('input');
							$.post('ajax/chat_message.php', { user_id: AbsoChat.user.user_id, username: AbsoChat.user.username, text: text });

							// debug info below
							$('#test').html("user_id: " + AbsoChat.user.user_id + "<br />username: " + AbsoChat.user.username + "<br />text: " + text);
						}

						return false;
					}
				});

				setTimeout(function() {
					ChatMessage.display(20);
				}, 1000);
      });
		</script>
		-->
	</head>

	<body>
		<div class='overlay'>
			<div class='spinner'></div>
		</div>

		<div class='banner'>
			<a href='news.php'><img src='images/Assets/banner.png' /></a>
			<div style='border-bottom-right-radius: 4px;' id='serverTime'><?php echo $Date; ?></div>
		</div>
		
		<div class='userbar'>
			<div class='user'>
				<?php
					echo	"<a href='profile.php?id={$User_Data['id']}'>";
					echo 		"{$User_Data['Username']} - #{$User_Data['id']}";
					echo	"</a>";
				?>
			</div>

			<div class='notifications' style='display: block; font-size: 14px; height: 30px; padding: 5px 3px 3px 3px;'>
				<a href='testing2.php'>Pokemon Spawner</a>
			</div>

			<div class='messages'>
				<span style='display: block; font-size: 14px; height: 30px; padding: 5px 3px 3px 3px;'>
					<a href='javascript:void(0);' onclick='toggleMessages();'>Messages</a>
				</span>
				<div style='display: none;'></div>
			</div>
			
			<div class='money'>
				$<?php echo number_format($User_Data['Money']); ?>
			</div>
			
			<div class='roster'>
				<?php					
					showRoster($User_Data['id'], 'Userbar', null);
				?>
			</div>
			
			<div class='notification'>
				<?php
					$Get_Warning = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM member_warnings WHERE Warned_ID = '" . $User_Data['id'] . "' ORDER BY id DESC LIMIT 1"));
					
					if ( $Get_Warning['Notification_Expiration'] > $Time )
					{
						echo	"<marquee style='color: #ffa500'><b>Warning:</b> " . $Get_Warning['Warned_Reason'] . "</marquee>";
					}
				?>
			</div>
			
			<script type='text/javascript'>
				$(function() {
					$('.popup.cboxElement').colorbox({ iframe: true, innerWidth: 680, innerHeight: 491 });
				});
			</script>
		</div>

		<?php
			if ( $User_Data['RPG_Ban'] !== '1' ) {
		?>
		<div class='sidebar'>
			<?php
				if ( $User_Data['Rank'] >= 21 ) {
					echo 	"<div class='button'>";
					echo		"<a href='staff_panel.php'>Staff Panel</a>";
					echo	"</div>";
				}
			?>

			<div class='material'>
				<div class='head'>Promotional Pokemon</div>
				<div class='box'>
					<a href='promo.php' style='padding: 5px;'>
						<?php
							$Promo_Data = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM `promo` WHERE `Active` = 'True'"));
							
							showImage('sprite', $Promo_Data['ID'], 'promo', null);
						?>
					</a>
				</div>
			</div>
			
			<div class='chat ps-container ps-theme-default ps-active-y' id='AbsoChat'>
				<div class='head'>
					<img src='images/Assets/options.png' style='cursor: pointer; height: 20px; margin-left: -70px; position: absolute; width: 20px;' onclick='AbsoChat.changeWindow("settings");' />
					Chat
					<img src='images/Assets/options.png' style='cursor: pointer; height: 20px; margin-left: 50px; position: absolute; width: 20px;' onclick='AbsoChat.reset();' />
				</div>
				<div class='user_options' id='user_options' style='display: none'></div>
				<div class='box' id='chatContent'>
					<div style='margin-top: 120px;'>
						<div class='spinner' style='left: 42.5%; position: relative;'></div>
					</div>
				</div>
				<div class='subhead'>
					<?php
						if ( $User_Data['ChatBanned'] != '1' ) {
							echo 	"<form name='chat_form'>";
							echo 		"<input type='text' name='chatMessage' id='chatMessage' autocomplete='off' />";
							echo	"</form>";
						}
					?>
				</div>
			</div>

			<div id='test'></div>
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
							if ( $User_Data['Clan'] != '0' ) {
								echo	"<li><a href='clan.php'>Clan HQ</a></li>";
							} else {
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
		?>