<?php
	require_once 'core/required/session.php';
  require_once 'core/functions/login.php';

  /**
   * The user is already logged in; don't process login logic.
   */
  if
  (
    session_status() === PHP_SESSION_ACTIVE &&
    !empty($_SESSION['EvoChroniclesRPG'])
  )
  {
    echo "
			<div class='panel content'>
				<div class='head'>Login</div>
				<div class='body' style='padding: 5px;'>
					You're already logged in to Evo-Chronicles RPG.
				</div>
			</div>
		";

		require_once 'core/required/layout_bottom.php';
		exit;
  }

  /**
   * The user is attempting to log in.
   */
	if ( !empty($_POST['username']) && !empty($_POST['password']) )
	{
		$Username = Purify($_POST['username']);
		$Password = Purify($_POST['password']);
		$IP = $_SERVER["REMOTE_ADDR"];

    $Login_Attempt = false;

    $User_Info = CheckUserExistence($Username);

    if ( empty($User_Info) )
    {
      TrackLoginAttempt($Username, $IP, false);

      $Login_Message = [
        'Type' => 'error',
        'Text' => "An account with that ID or Username does not exist."
      ];
    }

    if ( empty($Login_Message) )
    {
      $Password_Check = CheckUserPasswordMatch($User_Info['ID'], $Password);

      if ( !$Password_Check )
      {
        TrackLoginAttempt($User_Info['ID'], $IP, false);

        $Login_Message = [
          'Type' => 'error',
          'Text' => "You have entered an incorrect password."
        ];
      }
    }

    if ( empty($Login_Message) )
    {
      TrackLoginAttempt($User_Info['ID'], $IP, true);

      $_SESSION['EvoChroniclesRPG']['Logged_In_As'] = $User_Info['ID'];
      header('Location: /news.php');
      exit;
    }
	}

  require_once 'core/required/layout_top.php';
?>

<div class='panel content' style='margin: 5px; width: calc(100% - 14px);'>
	<div class='head'>Login</div>
	<div class='body' style='padding-bottom: 5px;'>
		<div class='nav'>
			<div><a href='index.php' style='display: block;'>Home</a></div>
			<div><a href='login.php' style='display: block;'>Login</a></div>
			<div><a href='register.php' style='display: block;'>Register</a></div>
			<div><a href='discord.php' style='display: block;'>Discord</a></div>
		</div>

		<div class='description' style='background: #334364; margin-bottom: 5px; width: 70%;'>
			Fill in the form below if you wish to login to Evo-Chronicles RPG.
		</div>

    <?php
      if ( !empty($Login_Message) )
      {
        echo "
          <div class='{$Login_Message['Type']}'>
            {$Login_Message['Text']}
          </div>
        ";
      }
    ?>

		<div class='description' style='background: #334364; width: 50%;'>
			<form method="POST">
				<b>Username/ID</b><br />
				<input autofocus type='text' name='username' placeholder='Username/ID' style='text-align: center;' />
				<br />
				<b>Password</b><br />
				<input type='password' name='password' placeholder='Password' style='text-align: center;' />
				<br /><br />
				<input type='submit' name='action' value='Login to Evo-Chronicles RPG' style='margin-left: -3px; width: 180px;' />
			</form>
		</div>
	</div>
</div>

<?php
	require_once 'core/required/layout_bottom.php';
