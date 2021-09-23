<?php
	require_once 'core/required/layout_top.php';
	
	if ( !isset($_SESSION['abso_user']) )
	{
		$width = " style='margin: 5px; width: calc(100% - 10px);'";
	}
	else
	{
		$width = "";
	}
?>

<div class='panel content'<?= $width; ?>>
	<div class='head'>500 INTERNAL SERVER ERROR</div>
	<div class='body' style='padding: 5px;'>
		Absolute has suffered from internal server errors, and due to this, has crashed.<br /><br />
		
		<a href="javascript:history.go(-1);">Go Back A Page?</a>
	</div>
</div>

<?php
	require_once 'core/required/layout_bottom.php';
?>