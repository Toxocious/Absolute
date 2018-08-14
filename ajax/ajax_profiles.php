<?php
	# Require a connection to 'session.php'.
	require '../session.php';
	
	# Verifies that a 'REQUEST' has been sent by the user.
	if ( isset($_POST['request']) ) {
		# Fetch the current URL.
		$Fetch_URL = "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

		# Access the user's database information much more easily.
		$User_Data = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM members WHERE id = '" . $row['id'] . "'"));
		
		# Verify that the $_POST['id'] variable has been set when requesting information from a user's profile.
		if ( isset($_POST['id']) ) {
			# Since the $_POST['id'] variable has been set, begin retrieving the appropriate information.
			$Profile_ID = $_POST['id'];
			$Profile_Data = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM members WHERE id = '" . $Profile_ID . "'"));
			
			# If the $_POST['request'] variable has been set to 'roster', retrieve the appropriate roster information.
			if ( $_POST['request'] === 'roster' ) {
				echo	"
					<div class='nav'>
						<a href='javascript:void(0);' onclick='showProfile(\"roster\", {$Profile_Data['id']})'>Roster</a>
						<a href='javascript:void(0);' onclick='showProfile(\"box\", {$Profile_Data['id']})'>Box</a>
						<a href='javascript:void(0);' onclick='showProfile(\"stats\", {$Profile_Data['id']})'>Stats</a>
					</div>
				";

				echo	"<div class='panel' id='profilePanel'>";
				echo	"<div class='panel-heading' style='border-top-left-radius: 0px;'>" . $Profile_Data['Username'] . "'s Roster</div>";
				echo		"<div class='panel-body' style='border-top: none;'>";

				for ( $i = 1; $i <= 6; $i++ ) {
					$Roster_Data = mysqli_query($con, "SELECT * FROM pokemon WHERE Owner_Current = '" . $Profile_ID . "' AND slot = $i");
					$Slot_Data[$i] = mysqli_fetch_assoc($Roster_Data);

					if ($Slot_Data[$i]) {
						$name = mysqli_fetch_assoc(mysqli_query($con, "SELECT `Name` FROM `pokedex` WHERE `ID` = '" . $Slot_Data[$i]["Pokedex_ID"] . "'"));
						$Slot_Data[$i]["Name"] = $name["Name"];
						$item = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM `items_owned` WHERE `ID` = '" . $Slot_Data[$i]["Item"] . "'"));
						$Slot_Data[$i]["Item"] = $item["Item_Name"];
					} else {
						$Slot_Data[$i] = "Empty" ;
					}

					if ( $Slot_Data[$i] != "Empty" ) {
						echo 	"<div class='roster_slot' style='float: left;'>";
						
						if ( $Slot_Data[$i]['Gender'] === 'Female' ) {
							echo	"<img class='gender' src='images/Assets/female.svg' />";
						}
						elseif ( $Slot_Data[$i]['Gender'] === 'Male' ) {
							echo	"<img class='gender' src='images/Assets/male.svg' />";
						}
						else {
							echo $Slot_Data[$i]['Gender'];
						}
						
						if ( $Slot_Data[$i]['Item'] != '' ) {
							echo		"<img class='item' src='images/Items/" . $Slot_Data[$i]['Item'] . ".png' style='position: absolute; margin-left: -45px; margin-top: 5px;' />";
						}
						
						echo 		"<img src='images/Pokemon/" . $Slot_Data[$i]['Type'] . "/" . $Slot_Data[$i]['Pokedex_ID'] . ".png' /><br />";
						
						if ( $Slot_Data[$i]['Type'] != "Normal" ) {
							echo "<b>" . $Slot_Data[$i]['Type'] . $Slot_Data[$i]['Name'] . "</b><br />";								
						}
						else {
							echo "<b>" . $Slot_Data[$i]['Name'] . "</b><br />";
						}

						echo	"<div class='info'>";
						echo 		"<div><b>Level</b></div>";
						echo 		"<div>" . number_format($Slot_Data[$i]['Level']) . "</div>";
						echo	"</div>";
						echo	"<div class='info'>";
						echo 		"<div><b>Experience</b></div>";
						echo 		"<div>" . number_format($Slot_Data[$i]['Experience']) . "</div>";
						echo	"</div>";

						echo 	"</div>";
					} else {
						echo "<div class='roster_slot' style='float: left; padding: 40px;'>";
						echo		"<img src='images/Assets/pokeball.png' /><br />";
						echo		"Empty";
						echo "</div>";
					}
				}
				echo	"</div>";
				echo "</div>";
			}
			
			# If the $_POST['request'] variable has been set to 'box', retrieve the appropriate roster information.
			if ( $_POST['request'] === 'box' ) {
				$Box_Data = mysqli_query($con, "SELECT * FROM pokemon WHERE Owner_Current = '" . $Profile_ID . "' AND slot = 7");

				echo	"
					<div class='nav'>
						<a href='javascript:void(0);' onclick='showProfile(\"roster\", {$Profile_Data['id']})'>Roster</a>
						<a href='javascript:void(0);' onclick='showProfile(\"box\", {$Profile_Data['id']})'>Box</a>
						<a href='javascript:void(0);' onclick='showProfile(\"stats\", {$Profile_Data['id']})'>Stats</a>
					</div>
				";

				echo	"<div class='panel' id='profilePanel'>";
				echo 		"<div class='panel-heading'>" . $Profile_Data['Username'] . "'s Box</div>";
				echo 		"<div class='panel-body' style='border-top: none;'>";

				if ( mysqli_num_rows($Box_Data) > 0 ) {
					while ( $Poke_Data = mysqli_fetch_array($Box_Data) ) {
						$Pokedex_Info = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM pokedex WHERE id = '" . $Poke_Data['Pokedex_ID'] . "'"));
						$Poke_Data['Name'] = $Pokedex_Info['Name'];

						echo			"<div class='box_slot'>";
						echo 				"<img src='images/Icons/{$Poke_Data['Type']}/{$Poke_Data['Pokedex_ID']}.png' onclick='displayPokemon({$Poke_Data['ID']});' /><br />";
						echo			"</div>";
					}
				} else {
					echo "<div style='padding: 5px;'>This user's box is empty.</div>";
				}

				echo		"</div>";
				echo	"</div>";

				echo	"<div class='panel' style='margin-top: 3px;'>";
				echo		"<div class='panel-heading'>Selected Pokemon</div>";
				echo		"<div class='panel-body' id='selectedPokemon'>";
				echo			"<div style='padding: 8px;'>Please select a Pokemon to view it's stats.</div>";
				echo		"</div>";
				echo	"</div>";
			}

			if ( $_POST['request'] === 'pokemon_stats' )
			{
				$Pokemon_Data = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM `pokemon` WHERE `id` = '" . $_POST['id'] . "'"));
				$Pokemon_Item = mysqli_fetch_assoc(mysqli_query($con, "SELECT `Item_Name` FROM `items_owned` WHERE `Equipped_To` = '" . $Pokemon_Data['ID'] . "'"));
				$Pokedex_Data = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM `pokedex` WHERE `id` = '" . $Pokemon_Data['Pokedex_ID'] . "'"));

				echo	"<div class='row'>";
				echo		"<div class='pokemon_cont' style='width: 210px;'>";
				# Echo the Held Item.
				if ( $Pokemon_Item['Item_Name'] != '' ) {
					echo	"<img class='item' src='images/Items/" . $Pokemon_Item['Item_Name'] . ".png' />";
				}
				
				# Echo the gender icon.
				echo	"<img style='width: 20px; position: absolute; height: 28px;' src='images/Assets/{$Pokemon_Data['Gender']}.svg' />";
				
				# Shiny star!
				if ( $Pokemon_Data['Type'] === 'Shiny' )
				{
					echo	"<img src='images/Assets/shiny_star.png' style='margin-left: 3px; margin-top: 28px; position: absolute;' />";
				}
				
				echo 			"<img src='images/Pokemon/" . $Pokemon_Data['Type'] . "/" . $Pokemon_Data['Pokedex_ID'] . ".png' /><br />";
				echo			"<div style='font-weight: bold;'>";
									if ( $Pokemon_Data['Type'] != "Normal" ) {
										echo $Pokemon_Data['Type'] . $Pokedex_Data['Name'] . "<br />";
									} else {
										echo $Pokedex_Data['Name'] . "<br />";
									}
				echo			"</div>";

				echo			"<div class='info'>";
				echo 				"<div>Level</div>";
				echo				"<div>" . number_format($Pokemon_Data['Level']) . "</div>";
				echo			"</div>";
				echo			"<div class='info'>";
				echo 				"<div>Experience</div>";
				echo				"<div>" . number_format($Pokemon_Data['Experience']) . "</div>";
				echo			"</div>";
				echo		"</div>";

				echo		"<div style='float: right; margin-bottom: 3px; margin-left: 2px; margin-right: 1px; margin-top: -1px; padding-top: 3px; width: 406px'>";
				echo			"<table class='special' style='float: left; width: 200px;'>";
				echo				"<thead>";
				echo					"<td colspan='2'>Individual Values</td>";
				echo				"</thead>";
				echo				"<tbody>";
				echo					"<tr>";
				echo						"<td>HP</td>";
				echo						"<td style='text-align: center'>" . $Pokemon_Data['IV_HP'] . "</td>";
				echo					"</tr>";
				echo					"<tr>";
				echo						"<td>Attack</td>";
				echo						"<td style='text-align: center'>" . $Pokemon_Data['IV_Attack'] . "</td>";
				echo					"</tr>";
				echo					"<tr>";
				echo						"<td>Defense</td>";
				echo						"<td style='text-align: center'>" . $Pokemon_Data['IV_Defense'] . "</td>";
				echo					"</tr>";
				echo					"<tr>";
				echo						"<td>Sp.Att</td>";
				echo						"<td style='text-align: center'>" . $Pokemon_Data['IV_SpAttack'] . "</td>";
				echo					"</tr>";
				echo					"<tr>";
				echo						"<td>Sp.Def</td>";
				echo						"<td style='text-align: center'>" . $Pokemon_Data['IV_SpDefense'] . "</td>";
				echo					"</tr>";
				echo					"<tr>";
				echo						"<td>Speed</td>";
				echo						"<td style='text-align: center'>" . $Pokemon_Data['IV_Speed'] . "</td>";
				echo					"</tr>";
				echo				"</tbody>";
				echo			"</table>";
				
				echo			"<table class='special' style='float: left; margin-left: 5px; width: 200px;'>";
				echo				"<thead>";
				echo					"<td colspan='2'>Effort Values</td>";
				echo				"</thead>";
				echo				"<tbody>";
				echo					"<tr>";
				echo						"<td>HP</td>";
				echo						"<td style='text-align: center'>" . $Pokemon_Data['EV_HP'] . "</td>";
				echo					"</tr>";
				echo					"<tr>";
				echo						"<td>Attack</td>";
				echo						"<td style='text-align: center'>" . $Pokemon_Data['EV_Attack'] . "</td>";
				echo					"</tr>";
				echo					"<tr>";
				echo						"<td>Defense</td>";
				echo						"<td style='text-align: center'>" . $Pokemon_Data['EV_Defense'] . "</td>";
				echo					"</tr>";
				echo					"<tr>";
				echo						"<td>Sp.Att</td>";
				echo						"<td style='text-align: center'>" . $Pokemon_Data['EV_SpAttack'] . "</td>";
				echo					"</tr>";
				echo					"<tr>";
				echo						"<td>Sp.Def</td>";
				echo						"<td style='text-align: center'>" . $Pokemon_Data['EV_SpDefense'] . "</td>";
				echo					"</tr>";
				echo					"<tr>";
				echo						"<td>Speed</td>";
				echo						"<td style='text-align: center'>" . $Pokemon_Data['EV_Speed'] . "</td>";
				echo					"</tr>";
				echo				"</tbody>";
				echo			"</table>";
				echo			"<a href='statistics.php?id={$Pokemon_Data['ID']}'>Complete Statistics</a>";
				echo		"</div>";
			}
			
			# If the $_POST['request'] variable has been set to 'stats', retrieve the appropriate roster information.
			if ( $_POST['request'] === 'stats' ) {
				# Retrieve the user's statistical data.
				$Stats_Data = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM members WHERE id = '" . $Profile_ID . "'"));

				echo	"
					<div class='nav'>
						<a href='javascript:void(0);' onclick='showProfile(\"roster\", {$Profile_Data['id']})'>Roster</a>
						<a href='javascript:void(0);' onclick='showProfile(\"box\", {$Profile_Data['id']})'>Box</a>
						<a href='javascript:void(0);' onclick='showProfile(\"stats\", {$Profile_Data['id']})'>Stats</a>
					</div>
				";

				# Echo the appropriate information.
				echo	"<div class='panel' id='profilePanel'>";
				echo 	"<div class='panel-heading'>" . $Profile_Data['Username'] . "'s Statistics</div>";
				echo 		"<div class='panel-body' style='border-top: none;'>";				
				echo			"<div class='statistic'>";
				echo				"<b>Current Title</b><br />" . $Stats_Data['Title'];
				echo			"</div>";
				
				echo			"<div class='statistic'>";
				echo				"<b>Trainer Level:</b> " . number_format($Stats_Data['TrainerLevel']) . "<br />";
				echo				"" . number_format($Stats_Data['TrainerExp']) . " Exp";
				echo				"<div class='exp_bar'>";
				echo					"<span style='width: 100px'></span>";
				echo				"</div>";
				echo			"</div>";
				
				echo			"<div class='statistic'>";
				echo				"<b>Mining Level:</b> " . number_format($Stats_Data['Mining_Level']) ."<br />";
				echo				"" . number_format($Stats_Data['Mining_Exp']) . " Exp";
				echo				"<div class='exp_bar'>";
				echo					"<span style='width: 100px'></span>";
				echo				"</div>";
				echo			"</div>";
				echo		"</div>";
				echo	"</div>";
				echo	"</div>";
			}
		} else {
			echo	"An invalid command has been issued.";
		}
	}
?>