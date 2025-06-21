<?php
	require_once 'core/required/layout_top.php';

	if ( !isset($_SESSION['EvoChroniclesRPG']) )
		$Content_Style = " style='margin: 5px; width: calc(100% - 14px)'";
  else
    $Content_Style = " style='margin: 0px 5px; width: 100%;'";
?>

<div class='panel content' <?= $Content_Style; ?>>
	<div class='head'>Discord</div>
	<div class='body'>
    <?php
      if ( empty($_SESSION['EvoChroniclesRPG']) )
      {
        echo "
          <div class='nav'>
            <div><a href='index.php' style='display: block;'>Home</a></div>
            <div><a href='login.php' style='display: block;'>Login</a></div>
            <div><a href='register.php' style='display: block;'>Register</a></div>
            <div><a href='discord.php' style='display: block;'>Discord</a></div>
          </div>
        ";
      }
    ?>

    <br />
		<div class='description' style='background: #334364; margin-bottom: 5px; padding: 5px 20px; width: 70%;'>
			While you're here, please take a look at Evo-Chronicles RPG's Discord server.
			A lot goes on here, including announcing updates to Evo-Chronicles RPG, as well as general conversation between its users.
		</div>

		<iframe src="https://discordapp.com/widget?id=269182206621122560&theme=dark" height='300' allowtransparency="true" frameborder="0"></iframe>
	</div>
</div>

<?php
	require_once 'core/required/layout_bottom.php';
