<?php
	require '../core/required/layout_top.php';

	if ( $User_Data['Power'] >= 2 )
	{
?>

<div class='content'>
	<div class='head'>Staff Panel</div>
	<div class='box'>
		wip
	</div>
</div>

<?php
	}
	else
	{
?>

<div class='content'>
	<div class='head'>Staff Panel</div>
	<div class='box'>
		You aren't authorized to be here.
	</div>
</div>

<?php
	}

	require '../core/required/layout_bottom.php';
?>