<?php
	require 'session.php';
	
	$Staff_Panel_Data = $_POST['id'];
	$Staff_Member_Data = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM members WHERE id = '" . $row['id'] . "'"));
	
	# Requesting access to the 'Planned Features' panel.
	if ( $Staff_Panel_Data === '0' ) {
		echo "Planned Features???";
	}
	
	# Requesting access to the 'Chat Moderator' panel.
	else if ( $Staff_Panel_Data === '1' ) {
		# Confirm that the user is of sufficient rank.
		if ( $Staff_Member_Data >= '12' ) {
			echo "
				<div class='row'>
					<div class='col-xs-6'>
						<div class='panel panel-default' style='margin-bottom: 0px'>
							<div class='panel-heading'>Manage Chat Bans</div>
							<div class='panel-body'>
								Manage all active chat bans, as well as any archived chat bans.<br /><br />
								<a href='staff_chatbans.php'>Chat Bans</a>	
							</div>
						</div>
					</div>
					
					<div class='col-xs-6'>
						<div class='panel panel-default' style='margin-bottom: 0px'>
							<div class='panel-heading'>Create News Post</div>
							<div class='panel-body'>
								In the case of an announcement needing to be created, you may do so here.<br /><br />
								<a href='news_create.php'>Create News Post</a>	
							</div>
						</div>
					</div>
				</div>
			";
		} else {
			echo "You are not of sufficient rank to access this feature.";
		}
	}
	
	# Requesting access to the 'Global Moderator' panel.
	else if ( $Staff_Panel_Data === '2' ) {
		# Confirm that the user is of sufficient rank.
		if ( $Staff_Member_Data >= '69' ) {
			echo "
				<div class='row'>
					<div class='col-xs-6'>
						<div class='panel panel-default'>
							<div class='panel-heading'>Manage User Reports</div>
							<div class='panel-body'>
								Manage all active user reports, as well as any archived user reports.<br /><br />
								<a href='staff_reports.php'>User Reports</a>
							</div>
						</div>
					</div>
					
					<div class='col-xs-6'>
						<div class='panel panel-default'>
							<div class='panel-heading'>Manage User Warnings</div>
							<div class='panel-body'>
								Manage all warnings that any user has been given since they've joined.<br /><br />
								<a href='staff_warnings.php'>Warned Users</a>
							</div>
						</div>
					</div>
					
					<div class='col-xs-6'>
						<div class='panel panel-default'>
							<div class='panel-heading'>Report Archive</div>
							<div class='panel-body'>
								An archive of every report that's happened.<br /><br />
								<a href='report_archive.php'>Report Archive</a>
							</div>
						</div>
					</div>
					
					<div class='col-xs-6'>
						<div class='panel panel-default'>
							<div class='panel-heading'>Warning Archive</div>
							<div class='panel-body'>
								An archive of every warning that's been given to users.<br /><br />
								<a href='warning_archive.php'>Warning Archive</a>
							</div>
						</div>
					</div>
					
					<div class='col-xs-6'>
						<div class='panel panel-default'>
							<div class='panel-heading'>Warn A User</div>
							<div class='panel-body'>
								If a user has broken any minor rule(s), warn them here.<br /><br />
								<a href='warn_user.php'>Warn A User</a>
							</div>
						</div>
					</div>
										
					<div class='col-xs-6'>
						<div class='panel panel-default'>
							<div class='panel-heading'>Ban A User</div>
							<div class='panel-body'>
								If a user has broken any rule(s), ban them here.<br /><br />
								<a href='ban_user.php'>Ban A User</a>
							</div>
						</div>
					</div>
					
					<div class='col-xs-6'>
						<div class='panel panel-default'>
							<div class='panel-heading'>Unban A User</div>
							<div class='panel-body'>
								Given the proper situation, unban a user.<br /><br />
								<a href='ban_remove.php'>Unban A User</a>
							</div>
						</div>
					</div>
					
					<div class='col-xs-6'>
						<div class='panel panel-default'>
							<div class='panel-heading'>Manage User Bans</div>
							<div class='panel-body'>
								Manage all active bans, as well as any archived bans.<br /><br />
								<a href='ban_list.php'>Banned Users</a>
							</div>
						</div>
					</div>
					
					<div class='col-xs-6'>
						<div class='panel panel-default' style='margin-bottom: 0px'>
							<div class='panel-heading'>Manage User Logs</div>
							<div class='panel-body'>
								Manage all user logs, ie. battle logs, trade logs, map logs, etc.<br /><br />
								<a href='staff_logs.php'>User Logs</a>
							</div>
						</div>
					</div>
				</div>
			";
		} else {
			echo "You are not of sufficient rank to access this feature.";
		}
	}
	
	# Requesting access to the 'Administrator' panel.
	else if ( $Staff_Panel_Data === '3' ) {
		# Confirm that the user is of sufficient rank.
		if ( $Staff_Member_Data >= '420' ) {
			echo "
				<div class='col-xs-6'>
					<div class='panel panel-default'>
						<div class='panel-heading'>Pokemon Editor</div>
						<div class='panel-body'>
							Need to edit any aspect of a Pokemon? Do so here.<br /><br />
							<a href='pokemon_editor.php'>Edit Pokemon</a>
						</div>
					</div>
				</div>
				
				<div class='col-xs-6'>
					<div class='panel panel-default'>
						<div class='panel-heading'>Pokemon Spawner</div>
						<div class='panel-body'>
							Spawn in any Pokemon of your choosing.<br /><br />
							<a href='pokemon_spawner.php'>Spawn Pokemon</a>
						</div>
					</div>
				</div>
				
				<div class='col-xs-6'>
					<div class='panel panel-default' style='margin-bottom: 0px'>
						<div class='panel-heading'>Promo Changer</div>
						<div class='panel-body'>
							Change the promo.<br /><br />
							<a href='promo_changer.php'>Change Promo</a>
						</div>
					</div>
				</div>
			";
		} else {
			echo "You are not of sufficient rank to access this feature.";
		}
	}
	
	# Requesting access to staff panel options via modifying the HTML or the AJAX function.
	else {
		echo "An error has occurred.";
	}
?>