<?php
	require_once 'session.php';
?>

<!DOCTYPE html>
<html>
	<head>
		<title><?= $Current_Page['Name']; ?> :: The Pokemon Absolute</title>
		<link href='<?= Domain(1); ?>/images/Icons/4 - Shiny Sunset/Mega/359-mega.png' rel='shortcut icon'>

		<!--
			** Using a new structure of CSS files.
			** These are to be generated via SASS .scss files.
		-->
		<link type='text/css' rel='stylesheet' href='/themes/css/styles/<?= ($User_Data['Theme'] ? $User_Data['Theme'] : 'absol'); ?>.css?<?= time(); ?>' />
		<link type='text/css' rel='stylesheet' href='/themes/css/root.css?<?= time(); ?>' />
		<link type='text/css' rel='stylesheet' href='/themes/css/structure.css?<?= time(); ?>' />
		<link type='text/css' rel='stylesheet' href='/themes/css/theme.css?<?= time(); ?>' />

		<script type='text/javascript' src='<?= Domain(1); ?>/js/libraries.js'></script>
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
		<script type='text/javascript' src='<?= Domain(1); ?>/js/AbsoChat/Handler.js'></script>
		<script type='text/javascript' src='<?= Domain(1); ?>/js/AbsoChat/absochat.js'></script>
		<script type='text/javascript'>
			$(function()
			{
				Absolute.user = {
					user_id: <?= $User_Data['id']; ?>, 
					postcode: <?= $User_Data['Auth_Code']; ?>,
				}

				Absolute.Enable();

				$('#chatMessage').keydown((e) =>
				{
					if ( e.keyCode == 13 )
					{
						let text = $('#chatMessage').val().trim();
						if ( text != '' && Absolute.user.connected )
						{
							socket.emit('chat-message',
							{
								user: Absolute.user,
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
		<div class='BODY-CONTAINER'>

			<header>
				<?php
					if ( isset($_SESSION['abso_user']) )
					{
				?>
				
				<div class='user'>
					<div class="border-gradient margin-center hw-100px padding-0px">
						<div>
							<img src='<?= Domain(1) . '/' . $User_Data['Avatar']; ?>' />
						</div>
					</div>

					<div class="border-gradient hover w-150px padding-5px m-top-m22px">
						<div>
							<a href="/profile.php?id=1">
								<b><?= $User_Class->DisplayUserName($User_Data['id'], false, false); ?></b>
							</a>
						</div>
					</div>
				</div>

				<div class='stats'>
					<div class='stat border-gradient w-150px'>
						<div>
							<img src='<?= Domain(1); ?>/images/Assets/Money.png' />
						</div>
						<div>$<?= number_format($User_Data['Money']); ?></div>
					</div>

					<div class='stat border-gradient w-150px'>
						<div>
							<img src='<?= Domain(1); ?>/images/Assets/Abso_Coins.png' />
						</div>
						<div><?= number_format($User_Data['Abso_Coins']); ?></div>
					</div>

					<div class='stat border-gradient w-150px'>
						<div><?= $Absolute_Time; ?></div>
					</div>
				</div>

				<div class='roster'>
					<?php
						for ( $i = 0; $i <= 5; $i++ )
						{
							if ( isset($Roster[$i]['ID']) )
							{
								$RosterPoke[$i] = $Poke_Class->FetchPokemonData($Roster[$i]['ID']);
								
								echo "
									<div class='slot popup cboxElement border-gradient hover' href='" . Domain(1) . "/core/ajax/pokemon.php?id={$RosterPoke[$i]['ID']}'>
										<div>
											<img src='{$RosterPoke[$i]['Icon']}' />
										</div>
									</div>
								";
							}
						}
					?>
				</div>

				<?php
					}
				?>
			</header>

			<?php
				/**
				 * Render the nav menu if the user has an active session.
				 */
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
					}
				}
			?>

			<main>

<?php
	if ( !isset($_SESSION['abso_user']) )
	{
		if ( $Current_Page['Logged_In'] == 'yes' )
		{
			echo "
				<div class='panel content' style='margin: 5px; width: calc(100% - 10px);'>
					<div class='head'>Error</div>
					<div class='body'>
						You must be logged in to view this page.
						<br /><br />
						<a href='login.php'><b>Login</b></a> or <a href='register.php'><b>Register</b></a>
					</div>
				</div>
			";

			exit();
		}
	}
	else
	{
		if ( $User_Data['RPG_Ban'] != 1 )
		{
?>

				<div class='sidebar'>
					<div class='panel chat' id='AbsoChat'>
						<div class='head'>
							Chat
						</div>
						<div class='user_options' id='user_options' style='display: none'></div>
						<div class='body' id='chatContent'>
							<div style='margin-top: 150px;'>
								<div class='spinner' style='left: 42.5%; position: relative;'></div>
							</div>
						</div>
						<div class="foot">
							<form name="chat_form">
								<input type="text" name="chatMessage" id="chatMessage" autocomplete="off">
							</form>
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
		 * Check to see if the page is currently under maintenance.
		 */
		if ( $Current_Page['Maintenance'] === 'yes' )
		{
			if ( $User_Data['Power'] >= 7 )
			{
				echo "<div class='maintenance'>Despite this page being down for maintenance, you seem to be authorized to be here.</div>";
			}
			else
			{
				echo "
					<div class='panel content'>
						<div class='head'>Maintenance</div>
						<div class='body'>
							This page is currently undergoing maintenance, please check back later.
						</div>
					</div>
				";

				exit();
			}
		}

		/**
		 * If the user doesn't have any Pokemon in their roster, display a warning message.
		 */
		if ( count($Roster) == 0 )
		{
			echo "
				<div class='maintenance'>
					While you have an empty roster, certain parts of Absolute will be unavailable to you.
				</div>
			";
		}
	}
?>
