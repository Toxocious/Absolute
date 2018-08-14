<?php
	require '../session.php';
	
	# Cleaner way to access the user's data.
	$User_Data = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM members WHERE id = '" . $row['id'] . "'"));
	
	# Verify that a request has been sent.
	if ( isset($_POST['request']) ) {
		# Verify that the user has the proper rank credentials.
		if ( $User_Data['Rank'] == 420 ) {
			# If the request has been set to 'pokemon' ->
			if ( $_POST['request'] === 'pokemon' ) {
				# Get the Pokemon's information.
				$Pokemon_Data = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM pokemon WHERE id = '" . $_POST['id'] . "'"));
				$Pokemon_Type = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM types WHERE id = '" . $Pokemon_Data['Type'] . "'"));
				$Pokemon_Dex = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM pokedex WHERE id = '" . $Pokemon_Data['Pokedex_ID'] . "'"));
				$Pokemon_Owner = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM members WHERE id = '" . $Pokemon_Data['Owner_ID'] . "'"));
				
				echo	"<form action='pokemon_editor.php?id=" . $Pokemon_Data['id'] . "' method='post'>";
				echo		"<div class='pokemon_container'>";
				echo			"<div class='display_title'>";
										if ( $Pokemon_Type['Name'] != "Normal" ) {
											echo	$Pokemon_Type['Name'] . $Pokemon_Dex['Name'];
										} else {
											echo	$Pokemon_Dex['Name'];
										}
				echo			"</div>";
				echo			"<img src='images/Pokemon/" . $Pokemon_Type['Name'] . "/" . $Pokemon_Data['Pokedex_ID'] . ".png' /><br />";
				echo			"<div class='display_subtitle'>Level</div>";
				echo			"<div class='display_content'>";
				echo				number_format($Pokemon_Data['Level']);
				echo			"</div>";
				echo			"<div class='display_subtitle'>Experience</div>";
				echo			"<div class='display_content'>";
				echo				number_format($Pokemon_Data['Experience']);
				echo			"</div>";
				echo			"<div class='display_subtitle'>Owner</div>";
				echo			"<div class='display_content'>";
				echo				"<a href='profile.php?id=" . $Pokemon_Owner['id'] . "'>" . $Pokemon_Owner['Username'] . "</a>";
				echo			"</div>";
				echo		"</div>";
				
				echo		"<div class='pokemon_stats'>";
				echo			"<div>";
				echo				"<div class='stat'>";
				echo					"<b>HP EV</b><br />";
				echo					"<input type='text' name='hp' value='" . $Pokemon_Data['EV_HP'] . "' maxlength='3' />";
				echo				"</div>";
				echo				"<div class='stat'>";
				echo					"<b>Att EV</b><br />";
				echo					"<input type='text' name='attack' value='" . $Pokemon_Data['EV_Attack'] . "' maxlength='3' />";
				echo				"</div>";
				echo				"<div class='stat'>";
				echo					"<b>Def EV</b><br />";
				echo					"<input type='text' name='defense' value='" . $Pokemon_Data['EV_Defense'] . "' maxlength='3' />";
				echo				"</div>";
				echo				"<div class='stat'>";
				echo					"<b>Sp.Att EV</b><br />";
				echo					"<input type='text' name='spattack' value='" . $Pokemon_Data['EV_Special_Attack'] . "' maxlength='3' />";
				echo				"</div>";
				echo				"<div class='stat'>";
				echo					"<b>Sp.Def EV</b><br />";
				echo					"<input type='text' name='spdefense' value='" . $Pokemon_Data['EV_Special_Defense'] . "' maxlength='3' />";
				echo				"</div>";
				echo				"<div class='stat'>";
				echo					"<b>Speed EV</b><br />";
				echo					"<input type='text' name='speed' value='" . $Pokemon_Data['EV_Speed'] . "' maxlength='3' />";
				echo				"</div>";
				echo			"</div>";
				
				echo			"<div>";
				echo				"<div class='move'>";
				echo					"<b>Move #1</b><br />";
				echo					"<select name='move1'>";
				echo						"<option>Select A Move</option>";
													for ( $i = 1; $i < 720; $i++ ) {
														$Move_Data = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM moves WHERE id = '" . $i . "' ORDER BY Name ASC"));
														
														echo	"<option value='" . $i . "'>";
														echo		$Move_Data['Name'];
														echo	"</option>";
													}
				echo					"</select>";
				echo				"</div>";
				echo				"<div class='move'>";
				echo					"<b>Move #2</b><br />";
				echo					"<select name='move2'>";
				echo						"<option>Select A Move</option>";
													for ( $i = 1; $i < 720; $i++ ) {
														$Move_Data = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM moves WHERE id = '" . $i . "' ORDER BY Name ASC"));
														
														echo	"<option value='" . $i . "'>";
														echo		$Move_Data['Name'];
														echo	"</option>";
													}
				echo					"</select>";
				echo				"</div>";
				echo				"<div class='move'>";
				echo					"<b>Move #3</b><br />";
				echo					"<select name='move3'>";
				echo						"<option>Select A Move</option>";
													for ( $i = 1; $i < 720; $i++ ) {
														$Move_Data = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM moves WHERE id = '" . $i . "' ORDER BY Name ASC"));
														
														echo	"<option value='" . $i . "'>";
														echo		$Move_Data['Name'];
														echo	"</option>";
													}
				echo					"</select>";
				echo				"</div>";
				echo				"<div class='move'>";
				echo					"<b>Move #4</b><br />";
				echo					"<select name='move4'>";
				echo						"<option>Select A Move</option>";
													for ( $i = 1; $i < 720; $i++ ) {
														$Move_Data = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM moves WHERE id = '" . $i . "' ORDER BY Name ASC"));
														
														echo	"<option value='" . $i . "'>";
														echo		$Move_Data['Name'];
														echo	"</option>";
													}
				echo					"</select>";
				echo				"</div>";
				echo			"</div>";
				
				echo			"<div>";
				echo				"<div class='bio'>";
				echo					"<b>Biography</b><br />";
				echo					"<textarea type='text' name='bio' style='height: 90px; resize: none; width: 100%'>" . $Pokemon_Data['Bio'] . "</textarea>";
				echo				"</div>";
				echo			"</div>";
				echo		"</div>";
				
				echo		"<input type='submit' name='updatePokemon' value='Update Pokemon' style='border-color: #40A0DC; margin: 0px auto 3px; width: 99%' />";
				echo	"</form>";
			}
		}
		# They don't have the proper rank credentials.
		else {
			echo	"You are not authorized to use this feature.";
		}
	}
?>