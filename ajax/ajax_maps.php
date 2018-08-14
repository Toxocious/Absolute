<?php
	require 'session.php';
	
	$Map_Data = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM map WHERE Map_ID = '" . $_POST['id'] . "'"));
?>