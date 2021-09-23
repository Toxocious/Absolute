<?php
	require_once 'core/required/layout_top.php';

  /**
   * The user is already logged in; don't process login logic.
   */
  if
  (
    session_status() === PHP_SESSION_ACTIVE &&
    !empty($_SESSION['abso_user'])
  )
  {
    echo "
			<div class='panel content'>
				<div class='head'>Login</div>
				<div class='body' style='padding: 5px;'>
					You're already logged in to Absolute.
				</div>
			</div>
		";

		require_once 'core/required/layout_bottom.php';
		exit;
  }

  /**
   * The user is attempting to log in.
   */
	if ( isset($_POST['username']) && isset($_POST['password']) )
	{
		$Username = $Purify->Cleanse($_POST['username']);
		$Password = $Purify->Cleanse($_POST['password']);
		$IP = $_SERVER["REMOTE_ADDR"];

		try
		{
			$Query_User = $PDO->prepare("SELECT * FROM `users` WHERE `Username` = ? or `id` = ? LIMIT 1");
			$Query_User->execute([ $Username, $Username ]);
			$Query_User->setFetchMode(PDO::FETCH_ASSOC);
			$User_Info = $Query_User->fetch();
		}
		catch ( PDOException $e )
		{
			HandleError( $e->getMessage() );
		}

    if ( empty($User_Info) )
    {
      $Login_Message = [
        'Type' => 'error',
        'Text' => "An account with the ID or Username <b>{$Username}</b> does not exist."
      ];
    }
    else
    {
      $Salt = '5rrx4YP64TIuxqclMLaV1elGheNxJJRggMxzQjv5gQeFl84NFgXvR3NxcHuOc31SSZBTzUFEt0mYQ4Oo';
      $Hashed_Password = hash_hmac('sha512', $Password . $User_Info['Password_Salt'], $Salt);

      if ( $User_Info['Password'] != $Hashed_Password )
      {
        $Login_Message = [
          'Type' => 'error',
          'Text' => "You have entered an incorrect password."
        ];
      }
    }

    if ( empty($Login_Message) )
    {
      $Login_Message = [
        'Type' => 'success',
        'Text' => "
          Welcome, {$Username}.<br />
          Please wait while you are being signed in.<br /><br />
          <a href='" . DOMAIN_ROOT . "/news.php'>Click here if you are not redirected in a few seconds.</a>
        "
      ];

      $_SESSION['abso_user'] = $User_Info['ID'];
    }
	}
?>

<div class='panel content' style='margin: 5px; width: calc(100% - 14px);'>
	<div class='head'>Login</div>
	<div class='body pokecenter'>
		<div class='nav'>
			<div><a href='index.php' style='display: block;'>Home</a></div>
			<div><a href='login.php' style='display: block;'>Login</a></div>
			<div><a href='register.php' style='display: block;'>Register</a></div>
			<div><a href='discord.php' style='display: block;'>Discord</a></div>
		</div>

		<div class='description' style='background: #334364; margin-bottom: 5px; width: 70%;'>
			Fill in the form below if you wish to login to Absolute.
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
				<input type='submit' name='action' value='Login to Absolute' style='margin-left: -3px; width: 180px;' />
			</form>
		</div>
		<br />
	</div>
</div>

<?php
  if ( !empty($Login_Message) && $Login_Message['Type'] == 'success' )
  {
    echo "
      <script type='text/javascript'>
        setTimeout(() => {
          window.location.pathname = 'news.php';
        }, 3000);
      </script>
    ";
  }

	require_once 'core/required/layout_bottom.php';
