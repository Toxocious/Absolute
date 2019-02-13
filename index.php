<?php
	require 'core/required/layout_top.php';

	if ( isset($_SESSION['abso_user']) )
	{
?>

<div class='content'>
	<div class='head'>Index</div>
	<div class='box'>
		Welcome back to Absolute, <?= $User_Data['Username']; ?>.
		<br /><br />
		<i>misc user statistics here and stuff</i>
	</div>
</div>

<?php
	}
	else
	{
		$Last_Active = strtotime("-24 hours", time());
		try
		{
			$Online_Query = $PDO->query("SELECT COUNT(`id`) FROM `users` WHERE `last_active` > $Last_Active");
			$Online_Count = $Online_Query->fetchColumn();

			$Fetch_User_Count = $PDO->query("SELECT COUNT(`id`) FROM `users`");
      $User_Count = $Fetch_User_Count->fetchColumn();
	
			$Fetch_Pokemon_Count = $PDO->query("SELECT COUNT(`ID`) FROM `pokemon`");
      $Pokemon_Count = $Fetch_Pokemon_Count->fetchColumn();
		} 
		catch ( PDOException $e )
		{
			HandleError( $e->getMessage() );
		}
?>

<div class='content' style='margin: 5px; width: calc(100% - 10px);'>
	<div class='head'>Index</div>
	<div class='box'>
		<div class='nav'>
			<div><a href='index.php' style='display: block;'>Home</a></div>
			<div><a href='login.php' style='display: block;'>Login</a></div>
			<div><a href='register.php' style='display: block;'>Register</a></div>
			<div><a href='discord.php' style='display: block;'>Discord</a></div>
		</div>

		<div class='description' style='background: #334364; margin-bottom: 3px; width: 70%;'>
			Of the <b><?= number_format($User_Count); ?></b> registered users on Absolute, <b><?= number_format($Online_Count); ?></b> of them have been online today!<br />
			The Pokemon Absolute is home to <b><?= number_format($Pokemon_Count); ?></b> Pokemon!
		</div>

		<div style='text-align: left; width: 100%;'>
			<img src='https://vignette.wikia.nocookie.net/nintendo/images/b/b2/Professor_Sycamore_%28Pok%C3%A9mon_X_and_Y%29.png/revision/latest?cb=20131102213329&path-prefix=en' style='height: 345px; transform: scaleX(-1); width: 230px;' />

			<div style='float: right; text-align: center; width: calc(100% - 230px);'>
				Welcome to Absolute text blurb goes here.<br />
				<br />
				What is Lorem Ipsum?<br />
				Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.
				<br /><br />
				Why do we use it?<br />
				It is a long established fact that a reader will be distracted by the readable content of a page when looking at its layout. The point of using Lorem Ipsum is that it has a more-or-less normal distribution of letters, as opposed to using 'Content here, content here', making it look like readable English. Many desktop publishing packages and web page editors now use Lorem Ipsum as their default model text, and a search for 'lorem ipsum' will uncover many web sites still in their infancy. Various versions have evolved over the years, sometimes by accident, sometimes on purpose (injected humour and the like).
			</div>
		</div>
	</div>
</div>

<?php
	}

	require 'core/required/layout_bottom.php';