<?php
	require '../../required/session.php';

	$Fetch_Box = mysqli_query($con, "SELECT * FROM pokemon WHERE Owner_Current = '" . $User_Data['id'] . "' AND Slot = 7 LIMIT 50");

	echo "
	<div class='panel' style='float: left; margin-right: 5px; width: calc(50% - 5px);'>
		<div class='panel-heading'>Roster</div>
		<div class='panel-body'>";
			showRoster("{$User_Data['id']}", 'Pokecenter', 'Nickname');
	echo "</div>
	</div>

	<div class='panel' style='float: right; width: 50%;'>
		<div class='panel-heading'>Nickname A Pokemon</div>
		<div class='panel-body' id='dataDiv' style='padding: 3px;'>
			Select the Pokemon that you would like to nickname.
		</div>
	</div>";