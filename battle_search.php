<?php
	require 'core/required/layout_top.php';
?>

<div class='panel content'>
	<div class='head'>Battle A Trainer</div>
	<div class='body' style='padding: 5px;'>
		<div class='description' style='margin-bottom: 5px;'>
			Enter the account ID of the user that you would like to battle.
		</div>

		<form action='battle_create.php'>
			<input type='hidden' name='Battle_Type' value='trainer' />
			<input type='text' name='Foe' placeholder='User ID' /><br />
			<input type='submit' value='Battle!' />
		</form>
	</div>
</div>

<?php
	require 'core/required/layout_bottom.php';
