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
			<img src='images/Assets/Prof_Syc.png' style='height: 345px; transform: scaleX(-1); width: 230px;' />

			<div style='float: right; text-align: center; width: calc(100% - 230px);'>
				Hello, and welcome to the world of Absolute!<br />
				<br />
				Absolute is fan-made Pokemon RPG, with numerous amounts of features, and various pieces of content.
			</div>
		</div>
	</div>
</div>

<?php
	}

	require 'core/required/layout_bottom.php';