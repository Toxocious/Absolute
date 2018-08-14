<?php
	require 'layout_top.php';
?>

<div class='content'>
	<div class='head'>Online Users</div>
	<div class='box online_list'>
		<div class='description' style='margin: 3px auto 6px'>
			All users that have been online in the past fifteen minutes are displayed below.
		</div>
		
		<div class='row'>
		
			<?php
				$Online_Users = mysqli_query($con, "SELECT * FROM members ORDER BY Last_Active DESC");
						
				while ( $Query = mysqli_fetch_array($Online_Users) ) {
					$Current_Time = time();
					$Calc_Difference = $Current_Time - $Query['Last_Active'];
							
					if ( $Calc_Difference / 60 < 15 ) {
						echo	"<div class='col-xs-4'>";
										if ( $Query['RPG_Ban'] === '1' ) {
											echo		"<div class='online_user' style='background: #680000'>";
										} else {
											echo		"<div class='online_user'>";
										}
						echo			"<img src='" . $Query['Avatar'] . "' />";
						
						echo			"<div>";
												if ( $Query['RPG_Ban'] === '1' ) {
													echo				"<s><a href='profile.php?id=" . $Query['id'] . "'>" . $Query['Username'] . " - #" . $Query['id'] . "</a></s><br />";
												} else {
													if ( $Query['Rank'] === '12' ) {
														echo	"<a style='color: #fcbc19' href='profile.php?id=" . $Query['id'] . "'>" . $Query['Username'] . " - #" . $Query['id'] . "</a><br />";
													}
													else if ( $Query['Rank'] === '69' ) {
														echo	"<a style='color: #25e1e8' href='profile.php?id=" . $Query['id'] . "'>" . $Query['Username'] . " - #" . $Query['id'] . "</a><br />";
													}
													else if ( $Query['Rank'] === '420' ) {
														echo	"<a style='color: #bf00ff' href='profile.php?id=" . $Query['id'] . "'>" . $Query['Username'] . " - #" . $Query['id'] . "</a><br />";
													}
													else {
														echo	"<a href='profile.php?id=" . $Query['id'] . "'>" . $Query['Username'] . " - #" . $Query['id'] . "</a><br />";
													}
												}
												
												if ( $Calc_Difference < 60 ) {
													echo	$Calc_Difference . "s Ago<br />";
												} else {
													echo	floor($Calc_Difference / 60) . "m Ago<br />";
												}
						echo			"</div>";
						
						echo			"<div>";
						echo				$Query['Last_Page'];
						echo			"</div>";
						echo		"</div>";
						echo	"</div>";
					}
				}
			?>
		
		</div>
	</div>
</div>

<?php
	require 'layout_bottom.php';
?>