<?php
	require_once $_SERVER['DOCUMENT_ROOT'] . '/core/required/session.php';
    require_once $_SERVER['DOCUMENT_ROOT'] . '/core/required/user_session.php';
?>

<!DOCTYPE html>
<html>
	<head>
		<title><?= $Current_Page['Name']; ?> &mdash; The Pok&eacute;mon Absolute</title>
		<link href='<?= DOMAIN_SPRITES; ?>/Pokemon/Icons/Normal/359-mega.png' rel='shortcut icon'>

        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <meta http-equiv='Content-Type' content='text/html; charset=utf-8' />
        <meta name='robots' content='index' />
        <meta name='description' content='The Pok&eacute;mon Absolute is an exciting and free way to enjoy spending your time. We have a vast community of members of all ages and ethnicities who all enjoy Pok&eacute;mon. Sign up now and begin your own adventure as a Pok&eacute;mon trainer!' />
        <meta property='og:type' content='rpg' />
        <meta property='og:title' content='The Pok&eacute;mon Absolute RPG' />
        <meta property='og:site_name' content='The Pok&eacute;mon Absolute' />
        <meta property='og:image' content='<?= DOMAIN_SPRITES; ?>/Pokemon/Icons/Normal/359-mega.png' />
        <meta property='og:url' content='https://absoluterpg.com' />

		<link type='text/css' rel='stylesheet' href='/themes/css/styles/<?= (isset($User_Data['Theme']) ? $User_Data['Theme'] : 'absol'); ?>.css' />
		<link type='text/css' rel='stylesheet' href='/themes/css/root.css' />
		<link type='text/css' rel='stylesheet' href='/themes/css/structure.css' />
		<link type='text/css' rel='stylesheet' href='/themes/css/theme.css' />

		<?php
            if ( isset($User_Data['ID']) )
            {
                echo "
                    <!-- CSS Dependencies -->
                    <link type='text/css' rel='stylesheet' href='" . DOMAIN_ROOT . "/themes/css/lib/toastify.min.css' />

                    <!-- JS Dependencies -->
                    <script type='text/javascript' src='" . DOMAIN_ROOT . "/js/dependencies/jquery.min.js'></script>
                    <script type='text/javascript' src='" . DOMAIN_ROOT . "/js/dependencies/socket-io.min.js'></script>
                    <script type='text/javascript' src='" . DOMAIN_ROOT . "/js/dependencies/toastify.min.js'></script>

                    <!-- Our custom scripts -->
                    <script type='text/javascript' src='" . DOMAIN_ROOT . "/js/navigation.js' defer></script>
                    <script type='text/javascript' src='" . DOMAIN_ROOT . "/js/PokeView.js' defer></script>
                ";
            }

            if ( date('m') == 12 )
            {
                echo "<script type='text/javascript' src='" . DOMAIN_ROOT . "/js/snowstorm.min.js'></script>";
            }
        ?>
    </head>

	<body>
		<div class='BODY-CONTAINER'>
			<header>
				<?php
					if ( isset($_SESSION['Absolute']) )
					{
				?>

				<div class='user'>
                    <div>
                        <div class="border-gradient hw-100px padding-0px">
                        <div>
                            <img src='<?= $User_Data['Avatar']; ?>' />
                        </div>
                        </div>

                        <div class='border-gradient hover' style='height: 34px;'>
                        <div class='flex wrap' style='height: 24px;'>
                            <a href='<?= DOMAIN_ROOT; ?>/direct_messages.php'>
                            <img src='<?= DOMAIN_SPRITES; ?>/Items/letter.png' />
                            </a>
                        </div>
                        </div>
                    </div>

					<div class="border-gradient hover w-150px padding-5px m-top-m22px">
						<div>
							<a href="<?= DOMAIN_ROOT; ?>/profile.php?id=1">
								<b><?= $User_Class->DisplayUserName($User_Data['ID'], false, false); ?></b>
							</a>
						</div>
					</div>
				</div>

				<div class='stats'>
					<div class='stat border-gradient w-150px'>
						<div>
							<img src='<?= DOMAIN_SPRITES; ?>/Assets/Money.png' />
						</div>
						<div id='user_money'><?= number_format($User_Data['Money']); ?></div>
					</div>

					<div class='stat border-gradient w-150px'>
						<div>
							<img src='<?= DOMAIN_SPRITES; ?>/Assets/Abso_Coins.png' />
						</div>
						<div id='user_abso_coins'><?= number_format($User_Data['Abso_Coins']); ?></div>
					</div>

					<div class='stat border-gradient w-150px'>
						<div><?= $Absolute_Time; ?></div>
					</div>
				</div>

				<div class='roster'>
					<?php
                        if ( $User_Data['Roster'] )
                        {
                            foreach ( $User_Data['Roster'] as $Roster_Pokemon )
                            {
                                $Roster_Pokemon = GetPokemonData($Roster_Pokemon['ID']);

                                echo "
                                    <div class='slot border-gradient hover' onclick='PokemonViewer.open(\"{$Roster_Pokemon['ID']}\")'>
                                        <div>
                                            <img src='{$Roster_Pokemon['Icon']}' />
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

        <div class='social-media'>
            <div>
                <a href='https://github.com/toxocious/absolute' target='_blank' rel='noopener noreferrer' style='color: rgb(250, 250, 250); font-size: 1.5em;'>
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="icon" viewBox="0 0 16 16" style='height: 1em; width: 1em;'>
                        <path d="M8 0C3.58 0 0 3.58 0 8c0 3.54 2.29 6.53 5.47 7.59.4.07.55-.17.55-.38 0-.19-.01-.82-.01-1.49-2.01.37-2.53-.49-2.69-.94-.09-.23-.48-.94-.82-1.13-.28-.15-.68-.52-.01-.53.63-.01 1.08.58 1.23.82.72 1.21 1.87.87 2.33.66.07-.52.28-.87.51-1.07-1.78-.2-3.64-.89-3.64-3.95 0-.87.31-1.59.82-2.15-.08-.2-.36-1.02.08-2.12 0 0 .67-.21 2.2.82.64-.18 1.32-.27 2-.27.68 0 1.36.09 2 .27 1.53-1.04 2.2-.82 2.2-.82.44 1.1.16 1.92.08 2.12.51.56.82 1.27.82 2.15 0 3.07-1.87 3.75-3.65 3.95.29.25.54.73.54 1.48 0 1.07-.01 1.93-.01 2.2 0 .21.15.46.55.38A8.012 8.012 0 0 0 16 8c0-4.42-3.58-8-8-8z"/>
                    </svg>
                </a>
            </div>

            <div>
                <a href='https://discord.gg/SHnvbsS' target='_blank' rel='noopener noreferrer' style='color: rgb(250, 250, 250); font-size: 1.5em;'>
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="icon" viewBox="0 0 16 16" style='height: 1em; width: 1em;'>
                        <path d="M13.545 2.907a13.227 13.227 0 0 0-3.257-1.011.05.05 0 0 0-.052.025c-.141.25-.297.577-.406.833a12.19 12.19 0 0 0-3.658 0 8.258 8.258 0 0 0-.412-.833.051.051 0 0 0-.052-.025c-1.125.194-2.22.534-3.257 1.011a.041.041 0 0 0-.021.018C.356 6.024-.213 9.047.066 12.032c.001.014.01.028.021.037a13.276 13.276 0 0 0 3.995 2.02.05.05 0 0 0 .056-.019c.308-.42.582-.863.818-1.329a.05.05 0 0 0-.01-.059.051.051 0 0 0-.018-.011 8.875 8.875 0 0 1-1.248-.595.05.05 0 0 1-.02-.066.051.051 0 0 1 .015-.019c.084-.063.168-.129.248-.195a.05.05 0 0 1 .051-.007c2.619 1.196 5.454 1.196 8.041 0a.052.052 0 0 1 .053.007c.08.066.164.132.248.195a.051.051 0 0 1-.004.085 8.254 8.254 0 0 1-1.249.594.05.05 0 0 0-.03.03.052.052 0 0 0 .003.041c.24.465.515.909.817 1.329a.05.05 0 0 0 .056.019 13.235 13.235 0 0 0 4.001-2.02.049.049 0 0 0 .021-.037c.334-3.451-.559-6.449-2.366-9.106a.034.034 0 0 0-.02-.019Zm-8.198 7.307c-.789 0-1.438-.724-1.438-1.612 0-.889.637-1.613 1.438-1.613.807 0 1.45.73 1.438 1.613 0 .888-.637 1.612-1.438 1.612Zm5.316 0c-.788 0-1.438-.724-1.438-1.612 0-.889.637-1.613 1.438-1.613.807 0 1.451.73 1.438 1.613 0 .888-.631 1.612-1.438 1.612Z"/>
                    </svg>
                </a>
            </div>
        </div>
    </header>

    <?php
        /**
         * Display the correct navigation bar to the user.
         */
        if ( isset($User_Data) )
        {
            if ( !$User_Data['RPG_Ban'] )
            {
                if ( strpos($Parse_URL['path'], '/staff/') !== false && $User_Data['Is_Staff'] )
                {
                    $Navigation->Render("Staff");
                }
                else
                {
                    $Navigation->Render("Member");
                }
            }
        }

        /**
         * The user does not have an active session.
         */
        if ( !isset($User_Data) )
        {
            if ( $Current_Page['Logged_In'] == 'yes' )
            {
                echo "
                    <div class='panel content' style='margin: 1em auto;'>
                        <div class='head'>Error</div>
                        <div class='body' style='padding: 5px;'>
                        You must be logged in to view this page.
                        <br />
                        <br />
                        <a href='" . DOMAIN_ROOT . "/login.php'><b>Login</b></a> &mdash; <a href='" . DOMAIN_ROOT . "/register.php'><b>Register</b></a>
                        </div>
                    </div>
                ";

                require_once 'layout_bottom.php';
                exit;
            }

            /**
             * Check to see if the page is currently under maintenance.
             */
            if ( $Current_Page['Maintenance'] === 'yes' )
            {
                echo "
                    <div class='panel content' style='margin: 1em auto;'>
                        <div class='head'>Maintenance</div>
                        <div class='body' style='padding: 5px;'>
                        This page is currently undergoing maintenance, please check back later.
                        <br />
                        <br />
                        <a href='javascript:void(0);' onclick='window.history.go(-1); return false;'>
                            Go Back
                        </a>
                        </div>
                    </div>
                ";

                require_once 'layout_bottom.php';
                exit;
            }

            return;
        }
      ?>

      <aside>
        <div class='panel chat' id='AbsoChat'>
          <div class='user_options' id='user_options' style='display: none'></div>
          <div class='body' id='chatContent'></div>
          <?php
                if
                (
                    !$User_Data['RPG_Ban'] &&
                    !$User_Data['Chat_Ban']
                )
                {
          ?>
            <div class="foot">
                <form name="chat_form">
                    <input type="text" name="chatMessage" id="chatMessage" autocomplete="off">
                </form>
            </div>
          <?php
                }
          ?>
        </div>
      </aside>

      <main>
        <?php
            /**
             * Content to display if the user is not currently banned.
             */
            if ( !$User_Data['RPG_Ban'] )
            {
                /**
                 * If the user doesn't have any Pokemon in their roster, display a warning message.
                 */
                if ( !$User_Data['Roster'] )
                {
                    echo "
                        <div class='warning' style='margin: 5px auto 0px'>
                            While you have an empty roster, much of Absolute will be unavailable to you.
                        </div>
                    ";
                }

                /**
                 * Check for any notifications before any further page content gets loaded.
                 */
                $Notification->ShowNotification($User_Data['ID']);
            }

            /**
             * Check to see if the page is currently under maintenance.
             */
            if ( $Current_Page['Maintenance'] === 'yes' )
            {
                if ( $User_Data['Is_Staff'] )
                {
                    echo "
                        <div class='warning' style='margin: 5px auto 0px;'>
                            Despite this page being down for maintenance, you are authorized to be here.
                        </div>
                    ";
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

                    require_once 'layout_bottom.php';
                    return;
                }
            }
