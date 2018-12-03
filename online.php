<?php
	require 'core/required/layout_top.php';
?>

<div class='content'>
	<div class='head'>Online Users</div>
	<div class='box online_list'>
		<div class='description' style='margin: 0px auto 5px'>
			All users that have been online in the past fifteen minutes are displayed below.
		</div>

		<div class='row'>
			<div class='admin'>Staff</div>
			<?php
				$Online_Staff = mysqli_query($con, "SELECT * FROM members WHERE Rank > 1 ORDER BY Last_Active DESC");

				foreach( $Online_Staff as $Key => $User )
				{
					if ( $User['Rank'] === '420' )
						$User_Rank = "admin";
					else if ( $User['Rank'] === '69' )
						$User_Rank = "gmod";
					else if ( $User['Rank'] === '12' )
						$User_Rank = "cmod";
									
					$Current_Time = time();
					$Calc_Difference = $Current_Time - $User['Last_Active'];
								
					if ( $Calc_Difference / 60 < 15 )
					{
						if ( $User['RPG_Ban'] === '1' )
							$CSS_Background = " style='background: #680000;'";
						else
							$CSS_Background = "";

							echo "
							<div class='online_" . $User_Rank . "'" . $CSS_Background . ">
								<div>
									<div class='" . $User['Rank'] . "'>" . $User['Username'] . "</div>
									<div>" . lastseen($User['Last_Active'], 'week') . "</div>
									<div>" . $User['Last_Page'] . "</div>
								</div>
							</div>
						";
					}
				}
			?>
		</div>

		<div class='row'>
			<div class='member' style='width: 100% !important;'>Members</div>
			<?php
				$Online_Staff = mysqli_query($con, "SELECT * FROM members WHERE Rank = 1 ORDER BY Last_Active DESC");

				foreach( $Online_Staff as $Key => $User )
				{									
					$Current_Time = time();
					$Calc_Difference = $Current_Time - $User['Last_Active'];
								
					if ( $Calc_Difference / 60 < 15 )
					{
						if ( $User['RPG_Ban'] === '1' )
							$CSS_Background = " style='background: #680000;'";
						else
							$CSS_Background = "";

							echo "
							<div class='online_member'" . $CSS_Background . ">
								<div>
									<div>" . $User['Username'] . "</div>
									<div>" . lastseen($User['Last_Active'], 'week') . "</div>
									<div>" . $User['Last_Page'] . "</div>
								</div>
							</div>
						";
					}
				}
			?>
		</div>
	</div>
</div>

<?php
	require 'core/required/layout_bottom.php';
?>