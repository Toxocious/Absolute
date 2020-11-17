<?php
	require_once 'core/required/layout_top.php';

	if ( isset($_SESSION['abso_user']) )
	{
		echo "
			<div class='panel content'>
				<div class='head'>Login</div>
				<div class='body'>
					You're already logged in to Absolute.
				</div>
			</div>
		";
		
		require_once 'core/required/layout_bottom.php';
		exit();
	}

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
      	header("Location: news.php");
			}
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

		<?= ( isset($Oops) ) ? $Oops : ''; ?>

		<div class='description' style='background: #334364; margin-bottom: 3px; width: 70%;'>
			Fill in the form below if you wish to login to Absolute.
		</div>
		<br />

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
	require_once 'core/required/layout_bottom.php';