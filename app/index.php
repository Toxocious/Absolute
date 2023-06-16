<?php
	require_once 'core/required/layout_top.php';

	if ( isset($_SESSION['Absolute']) )
	{
?>

<div class='panel content'>
	<div class='head'>Index</div>
	<div class='body'>
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
			HandleError($e);
		}
?>

<div class='panel content' style='margin: 5px; width: calc(100% - 14px);'>
	<div class='head'>Index</div>
	<div class='body'>
		<div class='nav'>
			<div><a href='index.php' style='display: block;'>Home</a></div>
			<div><a href='login.php' style='display: block;'>Login</a></div>
			<div><a href='register.php' style='display: block;'>Register</a></div>
			<div><a href='discord.php' style='display: block;'>Discord</a></div>
		</div>

		<div class='description' style='width: 70%;'>
			The Pok&eacute;mon Absolute is home to <b><?= number_format($User_Count); ?></b> trainers and <b><?= number_format($Pokemon_Count); ?></b> Pok&eacute;mon!
		</div>

    <div>
      The Pok&eacute;mon Absolute is an up-to-date multiplayer Pok&eacute;mon RPG, featuring all currently released canonical Pok&eacute;mon
      from the main Pok&eacute;mon games!
      <br /><br />
      Among featuring a plethora of unique gameplay content to explore, we offer content that will appeal to all
      trainers, new and old, including content that Pok&eacute;mon veterans will find nostalgic.
      <br /><br />
      Sign up, catch and train brand new Pok&eacute;mon, and initiate trades with other users so that you can rise to the top!
      <br /><br />
      We have all sorts of Pok&eacute;mon, including Normal and Shiny ones!
      <br />
      <img src='<?= DOMAIN_SPRITES; ?>/Pokemon/Sprites/Normal/359.png' />
      <img src='<?= DOMAIN_SPRITES; ?>/Pokemon/Sprites/Shiny/359.png' />

      <div style='font-size: 12px; margin-top: 20px;'>
        This website is designed and optimized for Chromium based browsers.<br />
        It's recommended to use a Chromium based browser such as Google Chrome or Brave while browsing this website.
      </div>
    </div>
	</div>
</div>

<?php
	}

	require_once 'core/required/layout_bottom.php';
