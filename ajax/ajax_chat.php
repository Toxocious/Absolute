<?php
	require '../session.php';

	# Check to see if someone has sent a request to do something.
	if ( isset($_POST['request']) ) {
		$My_Data = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM members WHERE id = '" . $row['id'] . "'"));
		
		# A request has been sent to view a user's chat options.
		if ( $_POST['request'] === 'user_options' ) {
			$User_Data = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM members WHERE id = '" . $_POST['id'] . "'"));
				
			echo	"<div style='margin-left: 5px; position: absolute'>";
			echo		"<a href='javascript:void(0);' onclick='hideUserOptions()'>x</a>";
			echo	"</div>";
			
			if ( $User_Data['Rank'] === '12' ) {
				echo	"<div style='color: #fcbc19; font-weight: bold; padding: 3px; text-align: center'>Chat Moderator</div>";
			}
			if ( $User_Data['Rank'] === '69' ) {
				echo	"<div style='color: #25e1e8; font-weight: bold; padding: 3px; text-align: center'>Global Moderator</div>";
			}
			if ( $User_Data['Rank'] === '420' ) {
				echo	"<div style='color: #bf00ff; font-weight: bold; padding: 3px; text-align: center'>Administrator</div>";
			}
			
			echo	"<div style='padding: 3px; text-align: center'>";
			echo		"<img src='" . $User_Data['Avatar'] . "' />";
			echo	"</div>";
			
			echo	"<div style='text-align: center'>";
			echo		"<a href='profile.php?id=" . $User_Data['id'] . "'><b>" . $User_Data['Username'] . "</b></a>";
			echo	"</div>";
			
			echo	"<div class='index' style='margin-bottom: 4px; padding: 5px'>";
			echo		"<a href='trade_create.php?id='" . $User_Data['id'] . "'>Trade With " . $User_Data['Username'] . "</a><br />";
			echo		"<a href='messages.php?id='" . $User_Data['id'] . "'>Message " . $User_Data['Username'] . "</a>";
							# Add a report user link.
			echo	"</div>";
			
			if ( $User_Data['Rank'] >= '12' ) {
				echo	"<div class='index' style='padding: 5px'>";
				echo		"<input type='text' maxlength='9' id='banDuration' placeholder='Ban Duration (Minutes)' style='text-align: center; width: 100%' />";
				echo		"<input type='text' id='banReason' placeholder='Ban Reason' style='text-align: center; width: 100%' />";
				echo		"<a href='javascript:void(0);' onclick='banUser(" . $User_Data['id'] . ")'>Ban " . $User_Data['Username'] . "</a>";
				echo	"</div>";
			}
		}
		
		# A request has been sent to delete a chat message.
		if ( $_POST['request'] === 'delete' ) {
			# Ensures that only a staff member could truly delete messages.
			if ( $My_Data['Rank'] >= 12 ) {
				$Message_Data = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM chat WHERE Chat_ID = '" . $_POST['id'] . "'"));
				
				mysqli_query($con, "DELETE FROM chat WHERE Chat_ID = '" . $_POST['id'] . "'");
			} else {
				echo	"<script>alert('You do not have permission to delete messages from the chat.');</script>";
			}
		}
		
		# A request has been sent to ban a user from the chat.
		if ( $_POST['request'] === 'ban' ) {
			$banDuration = time() + ($_POST['duration'] * 60);
			
			date_default_timezone_set('America/Los_Angeles');
			$Current_Date = date('M dS, Y g:i:s A');
			$Unbanned_On = date('M dS, Y g:i:s A', $banDuration);
						
			# Update the user's ban status.
			mysqli_query($con, "UPDATE members SET ChatBanned = 1, ChatBan_By = '" . $My_Data['id'] . "', ChatBan_Duration = '" . $banDuration . "', ChatBan_Reason = '" . $_POST['reason'] . "' WHERE id = '" . $_POST['id'] . "'");
			
			# Insert this ban into the `ban_logs` database table.
			mysqli_query($con, "INSERT INTO ban_logs (Ban_Type, Banned_ID, Banned_By, Ban_Reason, Ban_Date, Ban_Expiration) VALUES ('2', '" . $_POST['id'] . "', '" . $My_Data['id'] . "', '" . $_POST['reason'] . "', '" . $Current_Date . "', '" . $Unbanned_On . "')");
		}
	}
?>