<?php
	require '../../functions/main_functions.php';

	if ( isset($_POST['username']) && isset($_POST['password']) )
	{
		if ( $_POST['username'] != '' && $_POST['password'] != '' )
		{
			global $PDO;

			$username = Text($_POST['username'])->in();
			$password = Text($_POST['password'])->in();
			$ip = $_SERVER["REMOTE_ADDR"];

			try {
				$Login_Query = $PDO->prepare("SELECT `id`, `Username`, `Password`, `Password_Salt` FROM members WHERE `Username` = ? OR `id` = ? LIMIT 1");
				$Login_Query->execute([$username, $username]);
				$Login_Query->setFetchMode(PDO::FETCH_ASSOC);
				$Login_User = $Login_Query->fetch();
			} catch (PDOException $e) {
				throw $e;
			}

			$salt = '5rrx4YP64TIuxqclMLaV1elGheNxJJRggMxzQjv5gQeFl84NFgXvR3NxcHuOc31SSZBTzUFEt0mYQ4Oo';
			$pass = hash_hmac('sha512', $password.$Login_User['Password_Salt'], $salt);

			if ( $Login_User['Password'] != $pass )
			{
?>

				<div class='description' style='border: 2px solid #7f0000; background: #190000; margin-bottom: 3px; width: 100%;'>
					You have entered an incorrect username or password.<br />
					<?php
						//var_dump($Login_User);
						//echo "<br /><br />login_user[password] - pass wen hashed -><br />" . $Login_User['Password'] . " - " . $pass;
						//echo "<br /><br />pass wen hashed -><br />" . $pass;
						//echo "<br /><br />Password + login_user['password_salt'] -><br />" . $password.$Login_User['Password_Salt'] . "<br/><br/>";
					?>
				</div>

				<div class='description' style='background: #334364; margin-bottom: 3px; width: 70%;'>
					Fill in the form below if you wish to login to Absolute.
				</div>

				<div class='description' style='background: #334364; width: 50%;'>
					<form action="#" method="POST">
						<b>Username/ID</b><br />
						<input type='text' name='username' placeholder='Username/ID' style='text-align: center;' />
						<br />
						<b>Password</b><br />
						<input type='password' name='password' placeholder='Password' style='text-align: center;' />
						<br />
						<input type='submit' name='login' value='Login to Absolute' style='margin-left: -3px; width: 180px;' />
					</form>
				</div>

				<script type='text/javascript'>
					$('form').submit(function(e) {
						e.preventDefault();
						$.ajax({
							type: "POST",
							url: 'core/ajax/index/login.php',
							data: { username: $('input[name="username"]').val(), password: $('input[name="password"]').val() },
							success: function(data)
							{
								window.location.href = 'news.php';
								console.log(data);
							},
							error: function(data)
							{
								$('#index').html(data);
								console.log(data);
							}
						});
					});
				</script>

<?php
			}
			else
			{
				//echo "Successfully being logged in!";
				//echo "<br /><br />pass wen hashed -><br />" . $pass;
				//echo "<br /><br />Password + Userpass['salt'] -><br />" . $password.$Login_User['Password_Salt'] . "<br/><br/>";
				session_start();
				$_SESSION['login_user'] = $Login_User['id'];
			}
		}
		else
		{
?>

			<div class='description' style='border: 2px solid #7f0000; background: #190000; margin-bottom: 3px; width: 70%;'>
				You didn't fill out all of the required fields.
			</div>

			<div class='description' style='background: #334364; margin-bottom: 3px; width: 70%;'>
				Fill in the form below if you wish to login to Absolute.
			</div>

			<div class='description' style='background: #334364; width: 50%;'>
				<form action="#" method="POST">
					<b>Username/ID</b><br />
					<input type='text' name='username' placeholder='Username/ID' style='text-align: center;' />
					<br />
					<b>Password</b><br />
					<input type='password' name='password' placeholder='Password' style='text-align: center;' />
					<br />
					<input type='submit' name='login' value='Login to Absolute' style='margin-left: -3px; width: 180px;' />
				</form>
			</div>

			<script type='text/javascript'>
				$('form').submit(function(e) {
					e.preventDefault();
					$.ajax({
						type: "POST",
						url: 'core/ajax/index/login.php',
						data: { username: $('input[name="username"]').val(), password: $('input[name="password"]').val() },
						success: function(data)
						{
							window.location.href = 'news.php';
							console.log(data);
						},
						error: function(data)
						{
							$('#index').html(data);
							console.log(data);
						}
					});
				});
			</script>

<?php
		}
	}