<?php
	require 'session.php';

	# IF the ajax var 'request' has been set, do the following..
	if ( isset($_POST['request']) ) {
		# If you're viewing a player's profile, check to see if the $_POST['id'] is set, and deal with the page accordingly.
		if ( isset($_POST['id']) ) {
			# Since 'request' IS set, get the player's ID from the database.
			$Profile_ID = $_POST['id'];
			$Profile_Data = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM members WHERE id = '" . $Profile_ID . "'"));

			# If the 'request' variable has been set to 'roster', retrieve the player's roster information.
			if ( $_POST['request'] === 'roster' ) {
				# Since the 'request' has been set to 'roster', retrieve the user's roster information.
				$Roster_Slots = array();

				# Echo the appropriate 'roster' data to the page.
				echo	"<div class='panel-heading'>" . $Profile_Data['Username'] . "'s Roster</div>";
				echo		"<div class='panel-body' style='border-top: none;'>";

				for ( $i = 1; $i <= 6; $i++ ) {
					$Roster_Data = mysqli_query($con, "SELECT * FROM pokemon WHERE Owner_ID = '" . $Profile_ID . "' AND slot = $i");
					$slots[$i] = mysqli_fetch_assoc($Roster_Data);
					if ($slots[$i]) {
						$type = mysqli_fetch_assoc(mysqli_query($con, "SELECT Name FROM types WHERE ID = " . $slots[$i]['Type']));
						$slots[$i]["Type"] = $type["Name"];
						$name = mysqli_fetch_assoc(mysqli_query($con, "SELECT Name FROM pokedex WHERE ID = " . $slots[$i]["Pokedex_ID"]));
						$slots[$i]["Name"] = $name["Name"];
					} else {
						$slots[$i] = "Empty" ;
					}

					if( $slots[$i] != "Empty" ) {
						echo 	"<div class='roster_slot' style='float: left;'>";
						echo 		"<img src='images/Pokemon/" . $slots[$i]['Type'] . "/" . $slots[$i]['Pokedex_ID'] . ".png' /><br />";

						if ( $slots[$i]['Type'] != "Normal" ) {
							echo $slots[$i]['Type'] . $slots[$i]['Name'] . " (" . $slots[$i]['Gender'] . ")<br />";
							echo "(Level: " . number_format($slots[$i]['Level']) . ")";
						} else {
							echo $slots[$i]['Name'] . " (" . $slots[$i]['Gender'] . ")<br />";
							echo "(Level: " . number_format($slots[$i]['Level']) . ")";
						}
						echo 	"</div>";
					} else {
						echo "<div class='roster_slot' style='float: left; padding: 15px;'>";
						echo		"<img src='images/Assets/pokeball.png' /><br />";
						echo		"Empty";
						echo "</div>";
					}
				}
				echo "</div>";
			}

			# If the 'request' variable has been set to 'roster', retrieve the player's roster information.
			if ( $_POST['request'] === 'box' ) {
				# Since the 'request' has been set to 'box', retrieve the user's boxed pokemon information.
				$Box_Data = mysqli_query($con, "SELECT * FROM pokemon WHERE Owner_ID = '" . $Profile_ID . "' AND slot = 7");

				# Echo the appropriate information.
				echo 	"<div class='panel-heading'>" . $Profile_Data['Username'] . "'s Box</div>";
				echo 		"<div class='panel-body' style='border-top: none;'>";

				while ( $rows = mysqli_fetch_array($Box_Data) ) {
					$Pokedex_Info = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM pokedex WHERE id = '" . $rows['Pokedex_ID'] . "'"));
					$Type_Info = mysqli_fetch_assoc(mysqli_query($con, "SELECT Name FROM types WHERE id = '" . $rows['Type'] . "'"));
					$rows['Name'] = $Pokedex_Info['Name'];
					$rows['Type'] = $Type_Info['Name'];

					echo			"<div class='col-xs-3 box_slot'>";
					echo 				"<img src='images/Icons/" . $rows['Pokedex_ID'] . ".png' /><br />";

					if ( $rows['Type'] != "Normal" ) {
						echo $rows['Type'] . $rows['Name'];
					} else {
						echo $rows['Name'];
					}

					echo			"</div>";
				}

				echo		"</div>";
				echo	"</div>";
			}

			# If the 'request' variable has been set to 'stats', retrieve the player's statistical data.
			if ( $_POST['request'] === 'stats' ) {
				# Retrieve the user's statistical data.
				$Stats_Data = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM members WHERE id = '" . $Profile_ID . "'"));

				# Echo the appropriate information.
				echo 	"<div class='panel-heading'>" . $Profile_Data['Username'] . "'s Statistics</div>";
				echo 		"<div class='panel-body' style='border-top: none;'>";
				echo			"<div class='statistic'>";
				echo				"<b>Trainer Level:</b> " . number_format($Stats_Data['TrainerLevel']) . "<br />";
				echo				"(" . number_format($Stats_Data['TrainerExp']) . " Exp)";
				echo				"<div class='exp_bar'>";
				echo					"<span style='width: 100px'></span>";
				echo				"</div>";
				echo			"</div>";
				echo			"<div class='statistic'>";
				echo				"<b>Mining Level:</b> " . number_format($Stats_Data['TrainerLevel']) . "<br />";
				echo				"(" . number_format($Stats_Data['TrainerExp']) . " Exp)";
				echo				"<div class='exp_bar'>";
				echo					"<span style='width: 100px'></span>";
				echo				"</div>";
				echo			"</div>";
				echo			"<div class='statistic' style='border-bottom: none'>";
				echo				"<b>Test Level:</b> " . number_format($Stats_Data['TrainerLevel']) . "<br />";
				echo				"(" . number_format($Stats_Data['TrainerExp']) . " Exp)";
				echo				"<div class='exp_bar'>";
				echo					"<span style='width: 100px'></span>";
				echo				"</div>";
				echo			"</div>";
				echo		"</div>";
				echo	"</div>";
			}
		} else {
			echo "<div class='error'>An invalid request has been called.</div>";
		}

		# When viewing a Pokemon's statistics via the Pokemon Center.
		if ( $_POST['request'] === 'pokemon_statistics' ) {
			$Pokemon_Data = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM pokemon WHERE id = '" . $_POST['id'] . "'"));
			$Pokemon_Type = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM types WHERE id = '" . $Pokemon_Data['Type'] . "'"));
			$Pokedex_Data = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM pokedex WHERE id = '" . $Pokemon_Data['Pokedex_ID'] . "'"));

			if ( $Pokemon_Type['Name'] != "Normal" ) {
				echo $Pokemon_Type['Name'] . $Pokedex_Data['Name'] . " " . $Pokemon_Data['Gender'] . "<br />";
			} else {
				echo $Pokedex_Data['Name'] . " " . $Pokemon_Data['Gender'] . "<br />";
			}

			echo "Level: " . number_format($Pokemon_Data['Level']) . "<br />";
			echo "<img src='images/Pokemon/" . $Pokemon_Type['Name'] . "/" . $Pokemon_Data['Pokedex_ID'] . ".png' /><br />";

			# changeSlot( pokemon's database id, roster slot )
			echo "[ <a href='#' onclick='changeSlot(" . $_POST['id'] . ", 1)'>1</a> ] ";
			echo "[ <a href='#' onclick='changeSlot(" . $_POST['id'] . ", 2)'>2</a> ] ";
			echo "[ <a href='#' onclick='changeSlot(" . $_POST['id'] . ", 3)'>3</a> ] ";
			echo "[ <a href='#' onclick='changeSlot(" . $_POST['id'] . ", 4)'>4</a> ] ";
			echo "[ <a href='#' onclick='changeSlot(" . $_POST['id'] . ", 5)'>5</a> ] ";
			echo "[ <a href='#' onclick='changeSlot(" . $_POST['id'] . ", 6)'>6</a> ]";
		}

		# Changing slots via the Pokemon Center.
		if ( $_POST['request'] === 'slot_change' ) {
			# Make sure that you're only moving YOUR Pokemon, and not someone else's.
			$Get_Pokemon = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM pokemon WHERE id = '" . $_POST['id'] . "' AND Owner_ID = '" . $row['id'] . "'"));
			$Check_Slot = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM pokemon WHERE Slot = '" . $_POST['slot'] . "' AND Owner_ID = '" . $row['id'] . "'"));

			# Check to see if the requested slot is empty.
			if ( $Check_Slot['id'] === null ) {
				# If the slot IS empty, loop through any previous slots to determine if they are empty as well.
				for ( $i = $_POST['slot']; $i >= 1; $i-- ) {
					$Check_Slots = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM pokemon WHERE Slot = '" . $i . "' AND Owner_ID = '" . $row['id'] . "'"));

					# Looping through all previous slots to find out which ones are empty.
					if ( $Check_Slots['id'] === null ) {
						# Get the very first empty slot.
						$first_empty_slot = $i;
					}
				}
				
				# Update the `pokemon` database table with the updated slot change(s).
				mysqli_query($con, "UPDATE pokemon SET Slot = '" . $first_empty_slot . "' WHERE id = '" . $Get_Pokemon['id'] . "' AND Owner_ID = '" . $row['id'] . "'");
			} else {
				# Update the `pokemon` database table with the updated slot change(s).
				mysqli_query($con, "UPDATE pokemon SET Slot = '" . $_POST['slot'] . "' WHERE id = '" . $Get_Pokemon['id'] . "' AND Owner_ID = '" . $row['id'] . "'");
				mysqli_query($con, "UPDATE pokemon SET Slot = '" . $Get_Pokemon['Slot'] . "' WHERE id = '" . $Check_Slot['id'] . "' AND Owner_ID = '" . $row['id'] . "'");
			}
			
			$Pokemon_Data = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM pokemon WHERE id = '" . $Get_Pokemon['id'] . "' AND Owner_ID = '" . $row['id'] . "'"));
			$Pokedex_Data = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM pokedex WHERE id = '" . $Get_Pokemon['Pokedex_ID'] . "'"));
			$Type_Data = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM types WHERE id = '" . $Get_Pokemon['Type'] . "'"));

			echo 	"<div class='row'>";
			echo  	"<div class='col-xs-12'>";
			echo 			"<div class='description' style='margin-top: 5px; margin-bottom: 0px'>";
									if ( $Type_Data['Name'] != "Normal" )  {
										echo "Your " . $Type_Data['Name'] . $Pokedex_Data['Name'] . " has been moved to Slot " . $_POST['slot'] . ".";
									} else {
										echo "Your " .  $Pokedex_Data['Name'] . " has been moved to Slot " . $_POST['slot'] . ".";
									}
			echo 			"</div>";
			echo 		"</div>";
			echo 	"</div>";

			echo  "<div class='row'>";
			echo 		"<div class='col-xs-12'>";
			echo			"<div class='panel panel-default' style='margin: 5px 0px'>";
			echo 				"<div class='panel-heading'>Roster</div>";
			echo 				"<div class='panel-body' style='padding: 0px'>";
										for ( $i = 1; $i <= 6; $i++ ) {
											$Roster_Data = mysqli_query($con, "SELECT * FROM pokemon WHERE Owner_ID = '" . $row['id'] . "' AND slot = $i");
											$slots[$i] = mysqli_fetch_assoc($Roster_Data);
											if ($slots[$i]) {
												$type = mysqli_fetch_assoc(mysqli_query($con, "SELECT Name FROM types WHERE ID = " . $slots[$i]['Type']));
												$slots[$i]["Type"] = $type["Name"];
												$name = mysqli_fetch_assoc(mysqli_query($con, "SELECT Name FROM pokedex WHERE ID = " . $slots[$i]["Pokedex_ID"]));
												$slots[$i]["Name"] = $name["Name"];
											} else {
												$slots[$i] = "Empty" ;
											}

											if( $slots[$i] != "Empty" ) {
												echo 	"<div class='roster_slot' style='float: left;'>";
												echo 		"<img src='images/Pokemon/" . $slots[$i]['Type'] . "/" . $slots[$i]['Pokedex_ID'] . ".png' /><br />";

												if ( $slots[$i]['Type'] != "Normal" ) {
													echo $slots[$i]['Type'] . $slots[$i]['Name'];
													echo 	"<br />" . $slots[$i]['Gender'];
													echo 	" (Level: " . number_format($slots[$i]['Level']) . ")";
													echo 	"<div class='subhead'>";
													echo "[ <a href='#' onclick='changeSlot(" . $slots[$i]['id'] . ", 1)'>1</a> ] ";
													echo "[ <a href='#' onclick='changeSlot(" . $slots[$i]['id'] . ", 2)'>2</a> ] ";
													echo "[ <a href='#' onclick='changeSlot(" . $slots[$i]['id'] . ", 3)'>3</a> ]<br />";
													echo "[ <a href='#' onclick='changeSlot(" . $slots[$i]['id'] . ", 4)'>4</a> ] ";
													echo "[ <a href='#' onclick='changeSlot(" . $slots[$i]['id'] . ", 5)'>5</a> ] ";
													echo "[ <a href='#' onclick='changeSlot(" . $slots[$i]['id'] . ", 6)'>6</a> ]";
													echo	"</div>";
												} else {
													echo $slots[$i]['Name'];
													echo 	"<br />" . $slots[$i]['Gender'] . "";
													echo 	" (Level: " . number_format($slots[$i]['Level']) . ")";
													echo 	"<div class='subhead'>";
													echo	"[ 1 ]";
													echo 	"</div>";
												}
												echo 	"</div>";
											} else {
												echo "<div class='roster_slot' style='float: left; padding-top: 35px;'>";
												echo		"<img src='images/Assets/pokeball.png' /><br />";
												echo		"Empty";
												echo "</div>";
											}
										}
			echo 				"</div>";
			echo 			"</div>";
			echo 		"</div>";
			echo 	"</div>";

			echo 	"<div class='row'>";
			echo 		"<div class='col-xs-6'>";
			echo 			"<div class='panel panel-default' style='margin-bottom: 0px'>";
			echo 				"<div class='panel-heading'>Boxed Pokemon</div>";
			echo 				"<div class='panel-body boxed_pokemon'>";
										$Query_Box = mysqli_query($con, "SELECT * FROM pokemon WHERE slot = '7' AND Owner_ID = '" . $row['id'] . "'");

										while ( $rows = mysqli_fetch_assoc($Query_Box) ) {
											echo "<img src='images/Icons/" . $rows['Pokedex_ID'] . ".png' onclick='showPokemon(" . $rows['id'] . ")' />";
										}
			echo 				"</div>";
			echo 			"</div>";
			echo 		"</div>";

			echo 		"<div class='col-xs-6'>";
			echo			"<div class='panel panel-default' style='margin-bottom: 0px'>";
			echo				"<div class='panel-heading'>Selected Pokemon</div>";
			echo				"<div class='panel-body' id='selectedPokemon'>";
			echo					"Please select a Pokemon.";
			echo				"</div>";
			echo			"</div>";
			echo 		"</div>";
			echo	"</div>";
		}

		# Rankings.php AJAX
		if ( $_POST['request'] === 'ranks' ) {
			# Show the Pokemon Rankings if the POST variable equals '1'.
			if ( $_POST['id'] === '1' ) {
				echo 	"<div class='row'>";
				echo 		"<div class='col-xs-6'>";
				echo 			"<div class='panel panel-default' style='margin-top: 5px'>";
				echo 				"<div class='panel-heading'>Top Pokemon</div>";
				echo 				"<div class='panel-body top_pokemon'>";
											$Top_Ranking_Data = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM pokemon ORDER BY experience DESC LIMIT 1"));
											$Get_Top_Owner = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM members WHERE id = '" . $Top_Ranking_Data['Owner_ID'] . "'"));
											$Get_Top_Name = mysqli_fetch_assoc(mysqli_query($con, "SELECT Name FROM pokedex WHERE id = '" . $Top_Ranking_Data['Pokedex_ID'] . "'"));
											$Get_Top_Type = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM types WHERE id = '" . $Top_Ranking_Data['Type'] . "'"));
											$Top_Ranking_Data['Type'] = $Get_Top_Type['Name'];
											$Top_Ranking_Data['Name'] = $Get_Top_Name['Name'];
											$Top_Ranking_Data['Owner'] = $Get_Top_Owner['Username'];

											if ( $Top_Ranking_Data['Type'] != "Normal" ) {
												echo "<div>" . $Top_Ranking_Data['Type'] . $Top_Ranking_Data['Name'] . " " . $Top_Ranking_Data['Gender'] . "</div>";
											} else {
												echo "<div>" . $Top_Ranking_Data['Name'] . " " . $Top_Ranking_Data['Gender'] . "</div>";
											}

											echo "<div><b>Level:</b> " . number_format($Top_Ranking_Data['Level']) . "</div>";
											echo "<div><b>Exp:</b> " . number_format($Top_Ranking_Data['Experience']) . "</div>";

											echo  "<div>";
											echo    "<img src='images/Pokemon/" . $Top_Ranking_Data['Type'] . "/" . $Top_Ranking_Data['Pokedex_ID'] . ".png' />";
											echo  "</div>";

											echo  "<div>";
											echo    "<b>Owned By:</b> " . $Top_Ranking_Data['Owner'] . " - #" . $Top_Ranking_Data['Owner_ID'];
											echo  "</div>";
				echo 				"</div>";
				echo 			"</div>";
				echo 		"</div>";

				echo 		"
								<div class='col-xs-6'>
				          <div class='panel panel-default' style='margin-top: 5px'>
				            <div class='panel-heading'>Search For A Pokemon</div>
				            <div class='panel-body'>
				              Select a Pokemon, and the appropriate rankings for that Pokemon will appear.<br /><br />

				              <select>
				                <option>Select A Pokemon</option>
				                <option>Bulbasaur</option>
				              </select>
				            </div>
				          </div>
				        </div>
				      </div>
				";

				echo "
					<div class='row'>
						<div class='col-xs-12'>
							<div class='panel panel-default' style='border-top: 2px solid #00aa00; margin-bottom: 0px;'>
								<div class='panel-body pokemon_rankings'>
									<table class='special' style='width: 100%'>
										<thead>
											<tr>
												<td>Owner</td>
												<td>Pokemon</td>
												<td>Level</td>
												<td>Experience</td>
											</tr>
										</thead>
				";

										$Query_Pokemon = mysqli_Query($con, "SELECT * FROM pokemon ORDER BY Experience DESC LIMIT 12");
										while ( $Query = mysqli_fetch_assoc($Query_Pokemon) ) {
											$Query_Owner = mysqli_fetch_assoc(mysqli_Query($con, "SELECT * FROM members WHERE id = '" . $Query['Owner_ID'] . "'"));
											$Query_Name = mysqli_fetch_assoc(mysqli_Query($con, "SELECT * FROM pokedex WHERE id = '" . $Query['Pokedex_ID'] . "'"));
											$Query_Type = mysqli_fetch_assoc(mysqli_Query($con, "SELECT * FROM types WHERE id = '" . $Query['Type'] . "'"));

											echo 	"<tr>";
											echo		"<td><a href='profile.php?id=" . $Query_Owner['id'] . "'>" . $Query_Owner['Username'] . "</a></td>";
											echo		"<td>";
											echo			"<img src='images/Icons/" . $Query['Pokedex_ID'] . ".png' />";
																if ( $Query_Type['Name'] != "Normal" ) {
																	echo $Query_Type['Name'] . $Query_Name['Name'];
																} else {
																	echo $Query_Name['Name'];
																}
											echo			" (" . $Query['Gender'] . ")";
																if ( $Query['Nickname'] != "" ) {
																	echo "<br /><i>(" . $Query['Nickname'] . ")</i>";
																}
											echo		"</td>";
											echo		"<td>" . number_format($Query['Level']) . "</td>";
											echo		"<td>" . number_format($Query['Experience']) . "</td>";
											echo	"</tr>";
										}

				echo "
											</tbody>
										</table>
									</div>
								</div>
							</div>
						</div>
				";
			}
			elseif ( $_POST['id'] === '2' ) {
				echo "Mining Rankings.";
			} elseif ( $_POST['id'] === '3' ) {
				echo "Misc. Rankings";
			} else {
				echo "$('#ranks').html('<div class='description'>An error has occurred while trying to purchase this Pokemon.<br /> Please contact <a href=\'profile.php?id=1\'>Decay</a> or <a href=\'profile.php?id=2\'>Toxocious</a>.</div>');";
			}
		}
	}
?>
