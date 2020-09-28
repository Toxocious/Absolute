<?php
	require 'core/required/layout_top.php';
	
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
	<div class='head'>404 NOT FOUND</div>
	<div class='body'>
		The page that you are looking for could not be found.<br /><br />
		
		<a href="javascript:history.go(-1);">Go Back A Page?</a>
	</div>
</div>

<?php
	require 'core/required/layout_bottom.php';
?>