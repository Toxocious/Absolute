<?php
	require_once 'core/required/layout_top.php';

	if ( !isset($_SESSION['Absolute']) )
	{
		$Content_Style = "style='margin: 5px; width: calc(100% - 14px)'";
	}
?>

<div class='panel content' <?= $Content_Style; ?>>
	<div class='head'>Discord</div>
	<div class='body pokecenter'>
		<div class='nav'>
			<div><a href='index.php' style='display: block;'>Home</a></div>
			<div><a href='login.php' style='display: block;'>Login</a></div>
			<div><a href='register.php' style='display: block;'>Register</a></div>
			<div><a href='discord.php' style='display: block;'>Discord</a></div>
		</div>

		<div class='description' style='background: #334364; margin-bottom: 5px; padding: 5px 20px; width: 70%;'>
			While you're here, please take a look at Absolute's Discord server.
			A lot goes on here, including announcing updates to Absolute, as well as general conversation between it's users.
		</div>

		<iframe src="https://discordapp.com/widget?id=269182206621122560&theme=dark" width="500" height="500" allowtransparency="true" frameborder="0"></iframe>
	</div>
</div>

<?php
	require_once 'core/required/layout_bottom.php';
