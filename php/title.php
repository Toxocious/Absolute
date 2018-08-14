<?php
	$Current_Page = substr($_SERVER['PHP_SELF'], 15);
	
	if ( $Current_Page == 'warn_archive.php' ) {
		$Title = 'Staff Panel';
	}
	
	else if ( $Current_Page == 'warn_user.php' ) {
		$Title = 'Staff Panel';
	}
	
	else if ( $Current_Page == 'staff_warnings.php' ) {
		$Title = 'Staff Panel';
	}
	
	else if ( $Current_Page == 'staff_reports.php' ) {
		$Title = 'Staff Panel';
	}
	
	else if ( $Current_Page == 'staff_panel.php' ) {
		$Title = 'Staff Panel';
	}
	
	else if ( $Current_Page == 'staff_chatbans.php' ) {
		$Title = 'Staff Panel';
	}

	else if ( $Current_Page == 'spawn_pokemon.php' ) {
		$Title = 'Staff Panel';
	}
	
	else if ( $Current_Page == 'trade_interest.php' ) {
		$Title = 'Trade Interest';
	}
	
	else if ( $Current_Page == 'trade_create.php' ) {
		$Title = 'Creating A Trade';
	}
	
	else if ( $Current_Page == 'trade_central.php' ) {
		$Title = 'Trade Central';
	}
	
	else if ( $Current_Page == 'todo_list.php' ) {
		$Title = 'To Do List';
	}

	else if ( $Current_Page == 'testing2.php' ) {
		$Title = 'Testing2';
	}
	
	else if ( $Current_Page == 'testing.php' ) {
		$Title = 'Testing';
	}
	
	else if ( $Current_Page == 'staff_list.php' ) {
		$Title = 'Staff List';
	}
	
	else if ( $Current_Page == 'shop_plaza.php' ) {
		$Title = 'Shop Plaza';
	}
	
	else if ( $Current_Page == 'settings.php' ) {
		$Title = 'Settings';
	}
	
	else if ( $Current_Page == 'report_user.php' ) {
		$Title = 'Report A User';
	}
	
	else if ( $Current_Page == 'report_archive.php' ) {
		$Title = 'Staff Panel';
	}
	
	else if ( $Current_Page == 'rankings.php' ) {
		$Title = 'Rankings';
	}
	
	else if ( $Current_Page == 'promo.php' ) {
		$Title = 'Promo';
	}
	
	else if ( $Current_Page == 'profile.php' ) {
		$Title = 'Profile';
	}
	
	else if ( $Current_Page == 'profile_view.php' ) {
		$Title = 'View A Profile';
	}
	
	else if ( $Current_Page == 'pokemon_spawner.php' ) {
		$Title = 'Staff Panel';
	}
	
	else if ( $Current_Page == 'pokemon_editor.php' ) {
		$Title = 'Staff Panel';
	}
	
	else if ( $Current_Page == 'pokemon_center.php' ) {
		$Title = 'Pokemon Center';
	}
	
	else if ( $Current_Page == 'online.php' ) {
		$Title = 'Online List';
	}
	
	else if ( $Current_Page == 'news_edit.php' ) {
		$Title = 'Staff Panel';
	}
	
	else if ( $Current_Page == 'news_create.php' ) {
		$Title = 'Staff Panel';
	}
	
	else if ( $Current_Page == 'news.php' ) {
		$Title = 'News';
	}
	
	else if ( $Current_Page == 'messages.php' ) {
		$Title = 'Private Messages';
	}
	
	else if ( $Current_Page == 'map_select.php' ) {
		$Title = 'Map Selection';
	}
	
	else if ( $Current_Page == 'map.php' ) {
		$Title = 'Map';
	}

	else if ( $Current_Page == 'lab.php' ) {
		$Title = 'Laboratory';
	}
	
	else if ( $Current_Page == 'forums.php' ) {
		$Title = 'Forums';
	}
	
	else if ( $Current_Page == 'event.php' ) {
		$Title = 'Event';
	}
	
	else if ( $Current_Page == 'evolution_center.php' ) {
		$Title = 'Evolution Center';
	}

	else if ( $Current_Page == 'dashboard.php' ) {
		$Title = 'Dashboard';
	}
	
	else if ( $Current_Page == 'clan_list.php' ) {
		$Title = 'Clan Listings';
	}
	
	else if ( $Current_Page == 'clan.php' ) {
		$Title = 'Clan';
	}
	
	else if ( $Current_Page == 'chat.php' ) {
		$Title = 'Chat';
	}
	
	else if ( $Current_Page == 'battle_trainer.php' ) {
		$Title = 'Battle A Trainer';
	}
	
	else if ( $Current_Page == 'battle_create.php' ) {
		$Title = 'Create A Battle';
	}
	
	else if ( $Current_Page == 'battle_challenge.php' ) {
		$Title = 'Battle Challenge';
	}
	
	else if ( $Current_Page == 'battle.php' ) {
		$Title = 'Battle';
	}
	
	else if ( $Current_Page == 'banned.php' ) {
		$Title = 'Banned';
	}
	
	else if ( $Current_Page == 'ban_user.php' ) {
		$Title = 'Staff Panel';
	}
	
	else if ( $Current_Page == 'ban_remove.php' ) {
		$Title = 'Staff Panel';
	}
	
	else if ( $Current_Page == 'ban_list.php' ) {
		$Title = 'Staff Panel';
	}
	
	else if ( $Current_Page == 'archive_warning.php' ) {
		$Title = 'Staff Panel';
	}
	
	else if ( $Current_Page == 'archive_report.php' ) {
		$Title = 'Staff Panel';
	}

	else
	{
		$Title = '???';
	}
	
	# Update the `Last_Page` data for the user.
	mysqli_query($con, "UPDATE members SET Last_Page = '" . $Title . "' WHERE id = '" . $row['id'] . "'");
?>