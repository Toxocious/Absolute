<?php
	require_once 'core/required/layout_top.php';

	if ( isset($_GET['Logout']) )
	{
		session_start();
		$params = session_get_cookie_params();
		setcookie(session_name(), '', time() - 42000,
			$params["path"], $params["domain"],
			$params["secure"], $params["httponly"]
		);
		session_destroy();
		unset($_SESSION);
		header("Location: login.php");
	}

	if ( isset($_SESSION['abso_user']) )
	{
		echo "
			<div class='content'>
				<div class='head'>Login</div>
				<div class='box'>
					You're already logged in to Absolute.
				</div>
			</div>
		";
		
		require_once 'core/required/layout_bottom.php';
		exit();
	}

	if ( isset($_POST['username']) && isset($_POST['password']) )
	{
		$Username = Text($_POST['username'])->in();
		$Password = Text($_POST['password'])->in();
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

		if ( !isset($User_Info['Username']) )
		{
			$Oops = "<div class='description' style='border: 2px solid #7f0000; background: #190000; margin-bottom: 3px; width: 70%;'>The account that you tried logging into doesn't exist.</div>";
		}
		else
		{
			$Pass_Salt = '5rrx4YP64TIuxqclMLaV1elGheNxJJRggMxzQjv5gQeFl84NFgXvR3NxcHuOc31SSZBTzUFEt0mYQ4Oo';
			$Pass_Hash = hash_hmac('sha512', $Password.$User_Info['Password_Salt'], $Pass_Salt);
			
			if ( $User_Info['Password'] != $Pass_Hash )
			{
				$Oops = "<div class='description' style='border: 2px solid #7f0000; background: #190000; margin-bottom: 3px; width: 70%;'>You've entered an incorrect username or password.<br />Please try again.</div>";
			}

			if ( !isset($Oops) )
			{
				$_SESSION['abso_user'] = $User_Info['id'];
      	header("Location: index.php");
			}
		}
	}
?>

<div class='content' style='margin: 5px; width: calc(100% - 10px);'>
	<div class='head'>Login</div>
	<div class='box pokecenter'>
		<div class='nav'>
			<div><a href='index.php' style='display: block;'>Home</a></div>
			<div><a href='login.php' style='display: block;'>Login</a></div>
			<div><a href='register.php' style='display: block;'>Register</a></div>
			<div><a href='discord.php' style='display: block;'>Discord</a></div>
		</div>

		<?= ( isset($Oops) ) ? $Oops : ''; ?>

		<div class='description' style='background: #334364; margin-bottom: 3px; width: 70%;'>
			Fill in the form below if you wish to login to Absolute.
		</div>

		<div class='description' style='background: #334364; width: 50%;'>
			<form method="POST">
				<b>Username/ID</b><br />
				<input autofocus type='text' name='username' placeholder='Username/ID' style='text-align: center;' />
				<br />
				<b>Password</b><br />
				<input type='password' name='password' placeholder='Password' style='text-align: center;' />
				<br />
				<input type='submit' name='action' value='Login to Absolute' style='margin-left: -3px; width: 180px;' />
			</form>
		</div>
	</div>
</div>

<?php
	require_once 'core/required/layout_bottom.php';