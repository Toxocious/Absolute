<?php
	require 'session.php';
	
	# Check for a POST request.
	if ( isset($_POST['id']) ) {
		# Determine which request has been asked for.
		
		# 'Preferences'
		if ( $_POST['id'] === '1' ) {
			echo	"preferences tab";
		}
		
		# 'Security'
		elseif ( $_POST['id'] === '2' ) {
			echo	"security tab";
		}
		
		# 'Avatar'
		elseif ( $_POST['id'] === '3' ) {
			echo	"<div class='row'>";
			
			# If the user has requested an avatar change.
			if ( isset($_POST['request']) && $_POST['request'] === 'avatar' ) {
				# Verify that the user isn't requesting a non-existent avatar.
				if ( $_POST['avatar'] < 1 || $_POST['avatar'] > 352 ) {
					echo	"<div class='error'>You may not request that avatar.</div>";
				}
				
				# They are requesting a real avatar.
				else {
					echo	"<div class='success'>You have successfully changed your avatar.</div>";
				
					mysqli_query($con, "UPDATE members SET Avatar = 'images/Avatars/Sprites/" . $_POST['avatar'] . ".png' WHERE id = '" . $row['id'] . "'");
				}
			}
			
			echo		"<div class='description' style='margin: 0px auto 5px'>Change your avatar below.</div>";
			
			echo		"<div class='col-xs-6'>";
			echo			"<b>Current Avatar</b><br />";
			echo			"<img src='" . $row['Avatar'] . "' />";
			echo		"</div>";
			
			echo		"<div class='col-xs-6'>";
			echo			"<b>Select Avatar</b><br />";
			echo			"<div>";
			echo				"<img src='images/Pokemon/Normal/0.png' id='selectedAvatar' />";
			echo			"</div>";
			
			echo			"<select name='avatar' id='avatar' onchange='changeAvatar()'>";
			echo				"<option>Select An Avatar</option>";
									for ( $i = 1; $i < 353; $i++ ) {
										echo	"<option value='" . $i . "'>Trainer #" . $i . "</option>";
									}
			echo			"</select>";
			
			echo			"<br />";
			echo			"<input type='button' onclick='updateAvatar()' value='Update Avatar' style='margin-bottom: 0px; margin-top: 5px; width: 200px'>";
			echo		"</div>";
			echo	"</div>";
			
			echo	"
				<script type='text/javascript'>
					function changeAvatar() {
						var image = document.getElementById('selectedAvatar');
						var avatar = document.getElementById('avatar');
						image.src = 'images/Avatars/Sprites/' + avatar.value + '.png';
					}
					
					function updateAvatar() {
						var avatar = $('#avatar').val();
						
						$.ajax({
							type: 'post',
							url: 'ajax_settings.php',
							data: { request: 'avatar', id: '3', avatar: avatar },
							success: function(data) {
								$('.row').html(data);
							},
							error: function(data) {
								$('.row').html(data);
							}
						});
					}
				</script>
			";
		}
		
		# The AJAX has been modified.
		else {
			echo	"You are not authorized to view this content.";
		}
	}
?>