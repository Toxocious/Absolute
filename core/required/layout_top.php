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
			 * Adds snowstorm.js if the current month is December.
			 */
			if ( date('m') == 12 )
			{
				echo "<script type='text/javascript' src='" . Domain(1) . "/js/snowstorm.js'></script>";	
			}

			if ( isset($_SESSION['abso_user']) )
			{
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
				}

				Absolute.enable();

				$('#chatMessage').keydown(function(e)
				{
					if (e.keyCode == 13)
					{
						let text = $('#chatMessage').val().trim();
						if ( text != '' && Absolute.isConnected() )
						{
							socket.emit("input",
							{
								username: "<?= $User_Data['Username']; ?>",
								text: text,
							});

							$('#chatMessage').val('').trigger('input');
						}

						return false;
					}
				});
			});
		</script>
		<?php
			}
		?>
	</head>

	<body>
		<div class='banner'>
			<?php
				if ( isset($_SESSION['abso_user']) )
				{
			?>
			
			<div class='user'>
				<div class='avatar'>
					<div>
						<img src='<?= $User_Data['Avatar']; ?>' />
					</div>
				</div>

				<div class='username'>
					<div>
						<a href='<?= Domain(1); ?>/profile.php?id=<?= $User_Data['id']; ?>'>
							<b>
								<?php
									$Display_Username = $User_Class->DisplayUserName($User_Data['id'], false, false);
									echo $Display_Username;
								?>
							</b>
						</a>
					</div>
				</div>
			</div>

			<div class='stats'>
				<div class='stat'>
					<div>
						$<?= number_format($User_Data['Money']); ?>
					</div>
				</div>

				<div class='stat'>
					<div>
						<?= $Absolute_Time; ?>
					</div>
				</div>
			</div>

			<div class='roster'>
				<?php
					for ( $i = 0; $i <= 5; $i++ )
					{
						if ( isset($Roster[$i]['ID']) )
						{
							$RosterPoke[$i] = $Poke_Class->FetchPokemonData($Roster[$i]['ID']);
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
							<div class='slot popup cboxElement' href='" . Domain(1) . "/core/ajax/pokemon.php?id={$RosterPoke[$i]['ID']}'>
								<div>
									<img src='{$RosterPoke[$i]['Icon']}' />
								</div>
							</div>
						";
					}
				?>
			</div>

			<?php
				}
			?>
		</div>

<?php
	if ( isset($_SESSION['abso_user']) )
	{
		if ( $User_Data['RPG_Ban'] != 1 )
		{
			if ( strpos($Parse_URL['path'], '/staff/') === false )
			{
				$Navigation->Render("Member");
			}
			else
			{
				$Navigation->Render("Staff");
			}
	?>

		<div class='sidebar'>
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
		}

		/**
		 * Check for any notifications before any further page content gets loaded.
		 */
		$Notification->ShowNotification($User_Data['id']);

		/**
		 * Checkto see if the page is currently under maintenance.
		 */
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