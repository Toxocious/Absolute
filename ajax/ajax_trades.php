<?php
	require 'session.php';
	
	# Redirect to 'trade_create.php'.
	if ( isset($_POST['createTrade']) ) {
		// Redirect the user to the proper profile.
		header("Location: trade_create.php?id=" . $_POST['tradePartner']);
		die();
	}
	
	# If the user has requested something involving trades.
	if ( isset($_POST['request']) ) {
		# If the user has requested to view a tab in 'trade_central.php'.
		if ( $_POST['request'] === 'tradearea' ) {
			# If the user wants to browse the 'Create A Trade' tab.
			if ( $_POST['id'] === '1' ) {
				echo	"<div class='description' style='margin: 3px auto 5px;'>Enter a player's ID in order to begin a trade with them.</div>";
				echo	"<form action='" . $_SERVER['PHP_SELF'] . "' method='post'>";
				echo		"<input type='text' name='tradePartner' placeholder='Player ID' /><br />";
				echo		"<input type='submit' name='createTrade' value='Create Trade' />";
				echo	"</form>";
			}
			# If the user wants to browse the 'Pending Trades' tab.
			elseif ( $_POST['id'] === '2' ) {
				echo "Panding Trades";
			}
			# If the user wants to browse the 'Trade History' tab.
			elseif ( $_POST['id'] === '3' ) {
				echo "Trade History";
			}
			# Otherwise, display an error.
			else {
				echo "An error has occurred while retrieving the data that you've requested.";
			}
		}
	}
?>