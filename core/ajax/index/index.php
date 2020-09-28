<?php
	# If the variable is set.
	if ( isset($_POST['id']) ) {
		require 'core/required/db.php';
		
		# Display the most recent news post.
		if ( $_POST['id'] === '1' ) {
			$News_Post = mysqli_query($con, "SELECT * FROM `news` ORDER BY `id` DESC LIMIT 1");
					
			while ( $Query = mysqli_fetch_assoc($News_Post) ) {
				$Poster_Data = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM members WHERE id = '" . $Query['Poster_ID'] . "'"));
				
				echo	"<div class='panel panel-default' style='margin: 5px'>";
				echo		"<div class='head'>";		
				echo			"<div>";
				echo				$Query['News_Title'];
				echo			"</div>";
				echo		"</div>";
						
				echo		"<div class='body' style='padding: 0px'>";
				echo			"<div class='col-xs-3'>";
				echo				"<div style='height: 96px; margin: 0 auto; padding: 5px; width: 96px;'>";
				echo					"<span class='alignment'></span>";
				echo					"<img src='" . $Poster_Data['Avatar'] . "' />";
				echo				"</div>";
				
				if ( $Poster_Data['Rank'] === '12' ) {
					echo				"<b><a class='cmod' href='profile.php?id=" . $Query['Poster_ID'] . "'>" . $Poster_Data['Username'] . "</a></b><br />";
				}
				else if ( $Poster_Data['Rank'] === '69' ) {
					echo				"<b><a class='gmod' href='profile.php?id=" . $Query['Poster_ID'] . "'>" . $Poster_Data['Username'] . "</a></b><br />";
				}
				else {
					echo				"<b><a class='admin' href='profile.php?id=" . $Query['Poster_ID'] . "'>" . $Poster_Data['Username'] . "</a></b><br />";	
				}
				
				echo				"<span>" . $Query['News_Date'] . "</span>";
				echo			"</div>";
						
				echo			"<div class='col-xs-9' style='border-left: 2px solid #4A618F; margin-top: -3px; padding: 5px;'>";
				echo				nl2br($Query['News_Text']);
				echo			"</div>";
				echo		"</div>";
				echo	"</div>";
			}
		}
		
		# Display the login form.
		elseif ( $_POST['id'] === '2' ) {
			echo	"<div class='head' style='border-top-left-radius: 0px'>Error</div>";
			echo	"<div class='body'>";
			echo		"An error has occurred.</div>";
			echo	"</div>";
		}
		
		# Display the registration form.
		elseif ( $_POST['id'] === '3' ) {
?>

<div class='head' style='border-top-left-radius: 0px'>Register</div>
<div class='body'>
	<div id='result'></div>
	
	<form id='registration_form' action="index.php" method="post">
		<div class='row'>
			<div class='col-xs-6'>
				<div class='panel panel-default' style='margin-bottom: 5px;'>
					<div class='head'>Username</div>
					<div class='body' style='padding: 5px'>
						<input type='text' name='Username' placeholder='Username' style='margin-bottom: 0px; text-align: center;' />
					</div>
				</div>
			</div>

			<div class='col-xs-6'>
				<div class='panel panel-default' style='margin-bottom: 5px;'>
					<div class='head'>Gender</div>
					<div class='body' style='padding: 6px'>
						<select name='Gender' style='width: 180px'>
							<option>Select A Gender</option>
							<option value='1'>Female</option>
							<option value='2'>Male</option>
							<option value='3'>Ungendered</option>
						</select>
					</div>
				</div>
			</div>
		</div>

		<div class='row'>
			<div class='col-xs-6'>
				<div class='panel panel-default' style='margin-bottom: 5px;'>
					<div class='head'>Password</div>
					<div class='body' style='padding: 5px'>
						<input type='password' name='Password' placeholder='Password' style='margin-bottom: 0px; text-align: center;' />
					</div>
				</div>
			</div>

			<div class='col-xs-6'>
				<div class='panel panel-default' style='margin-bottom: 5px;'>
					<div class='head'>Confirm Password</div>
					<div class='body' style='padding: 5px'>
						<input type='password' name='Password_Confirm' placeholder='Confirm Password' style='margin-bottom: 0px; text-align: center;' />
					</div>
				</div>
			</div>
		</div>

		<div class='row'>
			<div class='col-xs-6'>
				<div class='panel panel-default'>
					<div class='head'>Choose An Avatar</div>
					<div class='body'>
						<div style='margin-bottom: 17.5px; margin-top: 17.5px;'>
							<img src='images/Avatars/Sprites/1.png' id='selectedAvatar' />
						</div>
						
						<select name='Avatar' id='Avatar' onchange='changeAvatar()'>
							<option>Select An Avatar</option>
							<?php
								for ( $i = 1; $i <= 352; $i++ ) {
									echo "<option value='" . $i . "'>Trainer #" . $i . "</option>";
								}
							?>
						</select>
					</div>
				</div>
			</div>

			<div class='col-xs-6'>
				<div class='panel panel-default'>
					<div class='head'>Choose A Starter</div>
					<div class='body'>
						<div style='margin-bottom: 10px; margin-top: 10px;'>
							<img src='images/Pokemon/Normal/1.png' id='selectedStarter' />
						</div>

						<select name='Starter' id='Starter' onchange='changeStarter()'>
							<option>Select A Starter</option>
							<option value='1'>Bulbasaur</option>
							<option value='4'>Charmander</option>
							<option value='7'>Squirtle</option>
							<option value='152'>Chikorita</option>
							<option value='155'>Cyndaquil</option>
							<option value='158'>Totodile</option>
							<option value='252'>Treecko</option>
							<option value='255'>Torchic</option>
							<option value='258'>Mudkip</option>
							<option value='387'>Turtwig</option>
							<option value='390'>Chimchar</option>
							<option value='393'>Piplup</option>
							<option value='495'>Snivy</option>
							<option value='498'>Tepig</option>
							<option value='501'>Oshawott</option>
							<option value='650'>Chespin</option>
							<option value='653'>Fennekin</option>
							<option value='656'>Froakie</option>
							<option value='722'>Rowlet</option>
							<option value='725'>Litten</option>
							<option value='728'>Popplio</option>
						</select>
					</div>
				</div>
			</div>
		</div>

		<input type='submit' name='register' value='Register!' style='margin-bottom: 5px; margin-top: 8px; width: 50%;' />
	</form>
</div>

<script type='text/javascript'>
	$('form#registration_form').on('submit', function (e) {
		e.preventDefault();

		$.ajax({
			type: 'post',
			url: 'register.php',
			data: $('form#registration_form').serialize(),
			success: function(data) {
				$('#content #result').html(data);
			}
		});
	});

	function changeAvatar() {
		var image = document.getElementById("selectedAvatar");
		var dropd = document.getElementById("Avatar");
		image.src = 'images/Avatars/Sprites/' + dropd.value + '.png';
	}

	function changeStarter() {
		var image = document.getElementById("selectedStarter");
		var dropd = document.getElementById("Starter");
		image.src = 'images/Pokemon/Normal/' + dropd.value + '.png';
	}
</script>

<?php
		}
		
		# Display an error since the user is tampering with the javascript function.
		else {
			echo	"<div class='head'>Error</div>";
			echo	"<div class='body'>";
			echo		"An error has occurred.</div>";
			echo	"</div>";
		}
	}
?>