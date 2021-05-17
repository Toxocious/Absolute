<?php
	require_once 'session.php';
?>

<!DOCTYPE html>
<html>
	<head>
		<title><?= $Current_Page['Name']; ?> &mdash; The Pok&eacute;mon Absolute</title>
		<link href='<?= DOMAIN_SPRITES; ?>/Pokemon/Icons/Normal/359-mega.png' rel='shortcut icon'>

		<link type='text/css' rel='stylesheet' href='/themes/css/styles/<?= ($User_Data['Theme'] ? $User_Data['Theme'] : 'absol'); ?>.css' />
		<link type='text/css' rel='stylesheet' href='/themes/css/root.css' />
		<link type='text/css' rel='stylesheet' href='/themes/css/structure.css' />
		<link type='text/css' rel='stylesheet' href='/themes/css/theme.css' />

		<script type='text/javascript' src='<?= DOMAIN_ROOT; ?>/js/libraries.js'></script>
		<?php
			/**
			 * Adds snowstorm.js if the current month is December.
			 */
			if ( date('m') == 12 )
			{
				//echo "<script type='text/javascript' src='" . DOMAIN_ROOT . "/js/snowstorm.js'></script>";
      }

      /**
       * Include the necessary Absolute Chat scripts.
       */
      if ( isset($_SESSION['abso_user']) )
			{
        echo "
          <script type='text/javascript' src='" . DOMAIN_ROOT . "/js/AbsoChat/absochat.js'></script>
          <script type='text/javascript' src='" . DOMAIN_ROOT . "/js/AbsoChat/Handler.js'></script>
          <script type='text/javascript'>
            $(function()
            {
              Absolute.user = {
                user_id: " . $User_Data['ID'] . ",
                postcode: " . $User_Data['Auth_Code'] . ",
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
        ";
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
          <div>
            <div class="border-gradient hw-100px padding-0px">
              <div>
                <img src='<?= $User_Data['Avatar']; ?>' />
              </div>
            </div>

            <div class='border-gradient hover' style='height: 34px;'>
              <div style='height: 24px;'>
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
						<div>$<?= number_format($User_Data['Money']); ?></div>
					</div>

					<div class='stat border-gradient w-150px'>
						<div>
							<img src='<?= DOMAIN_SPRITES; ?>/Assets/Abso_Coins.png' />
						</div>
						<div><?= number_format($User_Data['Abso_Coins']); ?></div>
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
                $Roster_Pokemon = $Poke_Class->FetchPokemonData($Roster_Pokemon['ID']);

                echo "
                  <div class='slot popup cboxElement border-gradient hover' href='" . DOMAIN_ROOT . "/core/ajax/pokemon.php?id={$Roster_Pokemon['ID']}'>
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
      </header>

      <?php
        /**
         * Display the correct navigation bar to the user.
         */
        if ( isset($User_Data) )
        {
          if ( !$User_Data['Banned_RPG'] )
          {
            if ( strpos($Parse_URL['path'], '/staff/') !== false && $User_Data['Power'] !== 1 )
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
              <main style='width: 100%;'>
                <div class='panel content'>
                  <div class='head'>Error</div>
                  <div class='body' style='padding: 5px;'>
                    You must be logged in to view this page.
                    <br />
                    <br />
                    <a href='" . DOMAIN_ROOT . "/login.php'><b>Login</b></a> or <a href='" . DOMAIN_ROOT . "/register.php'><b>Register</b></a>
                  </div>
                </div>
              </main>
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
              <main style='width: 100%;'>
                <div class='panel content'>
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
              </main>
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
          <div class='body' id='chatContent'>
            <div style='margin-top: 150px;'>
              <div class='spinner' style='left: 42.5%; position: relative;'></div>
            </div>
          </div>
          <?php
            if
            (
              !$User_Data['Banned_RPG'] &&
              !$User_Data['Banned_Chat']
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
          if ( $User_Data['Banned_RPG'] == 0 )
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
            if ( $User_Data['Power'] >= 7 )
            {
              echo "
                <div class='warning' style='margin: 5px auto 0px;'>
                  Despite this page being down for maintenance, you seem to be authorized to be here.
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
