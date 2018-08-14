<?php
	# This file will handle the majority of AJAX requests that are requested by the user's of the RPG.
	# Unless given direct permission, please do not edit or use this file.
	# Created by Jesse Mack, June 2017.
	
	# Require a connection to 'session.php'.
	require '../session.php';

	date_default_timezone_set('America/Los_Angeles');
	$Date = date("M dS, Y g:i:s A");
	
	# Verifies that a 'REQUEST' has been sent by the user.
	if ( isset($_POST['request']) ) {
		# Access the user's database information much more easily.
		$My_Data = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM members WHERE id = '" . $row['id'] . "'"));
		
		# ------------------ START :: PROFILE AJAX REQUESTS ------------------ #
		# Verify that the $_POST['id'] variable has been set when requesting information from a user's profile.
		if ( isset($_POST['id']) ) {
			# Since the $_POST['id'] variable has been set, begin retrieving the appropriate information.
			$Profile_ID = $_POST['id'];
			$Profile_Data = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM members WHERE id = '" . $Profile_ID . "'"));
			
			# If the $_POST['request'] variable has been set to 'roster', retrieve the appropriate roster information.
			if ( $_POST['request'] === 'roster' ) {
				$Roster_Slots = array();

				echo	"<div class='panel-heading'>" . $Profile_Data['Username'] . "'s Roster</div>";
				echo		"<div class='panel-body' style='border-top: none;'>";

				for ( $i = 1; $i <= 6; $i++ ) {
					$Roster_Data = mysqli_query($con, "SELECT * FROM pokemon WHERE Owner_Current = '" . $Profile_ID . "' AND slot = $i");
					$slots[$i] = mysqli_fetch_assoc($Roster_Data);
					if ($slots[$i]) {
						$type = mysqli_fetch_assoc(mysqli_query($con, "SELECT Name FROM types WHERE ID = " . $slots[$i]['Type']));
						$slots[$i]["Type"] = $type["Name"];
						$name = mysqli_fetch_assoc(mysqli_query($con, "SELECT Name FROM pokedex WHERE ID = " . $slots[$i]["Pokedex_ID"]));
						$slots[$i]["Name"] = $name["Name"];
						$item = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM items_owned WHERE ID = " . $slots[$i]["Item"]));
						$slots[$i]["Item"] = $item["Item_Name"];
					} else {
						$slots[$i] = "Empty" ;
					}

					if ( $slots[$i] != "Empty" ) {
						echo 	"<div class='roster_slots' style='float: left;'>";
						echo		"<img src='images/Items/" . $slots[$i]['Item'] . ".png' style='position: absolute; margin-left: -20px; margin-top: 5px;' />";
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
						echo "<div class='roster_slots' style='float: left; padding: 15px;'>";
						echo		"<img src='images/Assets/pokeball.png' /><br />";
						echo		"Empty";
						echo "</div>";
					}
				}
				echo "</div>";
			}
			
			# If the $_POST['request'] variable has been set to 'box', retrieve the appropriate roster information.
			if ( $_POST['request'] === 'box' ) {
				$Box_Data = mysqli_query($con, "SELECT * FROM pokemon WHERE Owner_Current = '" . $Profile_ID . "' AND slot = 7");

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
			
			# If the $_POST['request'] variable has been set to 'stats', retrieve the appropriate roster information.
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
				echo				"<b>Mining Level:</b> " . number_format($Stats_Data['Mining_Level']) . "<br />";
				echo				"(" . number_format($Stats_Data['Mining_Exp']) . " Exp)";
				echo				"<div class='exp_bar'>";
				echo					"<span style='width: 100px'></span>";
				echo				"</div>";
				echo			"</div>";
				echo		"</div>";
				echo	"</div>";
			}
		} else {
			echo	"An invalid command has been issued.";
		}
		# ------------------ END   :: PROFILE AJAX REQUESTS ------------------ #
		
		# ------------------ START :: POKEMON CENTER AJAX REQUESTS ------------------ #
		# If the user has clicked on the "Roster" tab in the Pokemon Center, display the appropriate information.
		if ( $_POST['request'] === 'pokecenter_roster' ) {
			echo	"<div class='col-xs-12'>
							<div class='description' style='display: none; margin-bottom: 0px; margin-top: 5px;'></div>
							<div class='error' style='display: none; margin-bottom: 0px; margin-top: 5px;'></div>
				
							<div class='panel panel-default' style='margin: 5px 0px'>
								<div class='panel-heading'>Roster</div>
								<div class='panel-body' style='padding: 0px'>";
								
								for ( $i = 1; $i <= 6; $i++ ) {
									$Slots_Data[$i] = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM pokemon WHERE Current_Owner = '" . $row['id'] . "' AND slot = $i"));
									
									if ( $Slots_Data[$i] ) {
										$type = mysqli_fetch_assoc(mysqli_query($con, "SELECT Name FROM types WHERE ID = " . $Slots_Data[$i]['Type']));
										$Slots_Data[$i]["Type"] = $type["Name"];
										$name = mysqli_fetch_assoc(mysqli_query($con, "SELECT Name FROM pokedex WHERE ID = " . $Slots_Data[$i]["Pokedex_ID"]));
										$Slots_Data[$i]["Name"] = $name["Name"];
									} else {
										$Slots_Data[$i] = "Empty";
									}

									if ( $Slots_Data[$i] != "Empty" ) {
										$Held_Item = mysqli_fetch_assoc(mysqli_query($con, "SELECT Item_Name FROM items_owned WHERE Equipped_To = '" . $Slots_Data[$i]['id'] . "'"));
									
										echo 	"<div class='roster_slot' style='float: left;'>";
										
										# Echo held item information if necessary.
										if ( $Slots_Data[$i]['Item'] != '0' ) {
											echo	"<img style='position: absolute;' src='images/Items/" . $Held_Item['Item_Name'] . ".png' />";
										}
										
										echo 		"<img src='images/Pokemon/" . $Slots_Data[$i]['Type'] . "/" . $Slots_Data[$i]['Pokedex_ID'] . ".png' /><br />";

										if ( $Slots_Data[$i]['Type'] != "Normal" ) {
											echo $Slots_Data[$i]['Type'] . $Slots_Data[$i]['Name'];
											echo 	"<br />" . $Slots_Data[$i]['Gender'];
											echo 	" (Level: " . number_format($Slots_Data[$i]['Level']) . ")";
											echo 	"<div class='subhead'>";
											
											for ( $x = 1; $x <= 6; $x++ ) {
												$Slots_Data[$x] = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM pokemon WHERE Owner_Current = '" . $row['id'] . "' AND slot = $x"));
												
												if ( $Slots_Data[$x] ) {
													$type = mysqli_fetch_assoc(mysqli_query($con, "SELECT Name FROM types WHERE ID = " . $Slots_Data[$x]['Type']));
													$Slots_Data[$x]["Type"] = $type["Name"];
													$name = mysqli_fetch_assoc(mysqli_query($con, "SELECT Name FROM pokedex WHERE ID = " . $Slots_Data[$x]["Pokedex_ID"]));
													$Slots_Data[$x]["Name"] = $name["Name"];
												} else {
													$Slots_Data[$x] = "Empty";
												}
									
												if ( $Slots_Data[$x] != "Empty" ) {
													if ( $x != 3 ) {
														echo "[ <a href='javascript:void()' onclick='changeSlot(" . $Slots_Data[$i]['id'] . ", $x)'>$x</a> ] ";
													} else {
														echo "[ <a href='javascript:void()' onclick='changeSlot(" . $Slots_Data[$i]['id'] . ", $x)'>$x</a> ]<br />";
													}
												} else {
													echo "[ $x ] ";
												}
											}
											
											echo	"</div>";
										} else {
											echo 	$Slots_Data[$i]['Name'];
											echo 	"<br />" . $Slots_Data[$i]['Gender'] . "";
											echo 	" (Level: " . number_format($Slots_Data[$i]['Level']) . ")";
											echo 	"<div class='subhead'>";
											
											for ( $x = 1; $x <= 6; $x++ ) {
												$Slots_Data[$x] = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM pokemon WHERE Current_Owner = '" . $row['id'] . "' AND slot = $x"));
												
												if ( $Slots_Data[$x] ) {
													$type = mysqli_fetch_assoc(mysqli_query($con, "SELECT Name FROM types WHERE ID = " . $Slots_Data[$x]['Type']));
													$Slots_Data[$x]["Type"] = $type["Name"];
													$name = mysqli_fetch_assoc(mysqli_query($con, "SELECT Name FROM pokedex WHERE ID = " . $Slots_Data[$x]["Pokedex_ID"]));
													$Slots_Data[$x]["Name"] = $name["Name"];
												} else {
													$Slots_Data[$x] = "Empty";
												}
									
												if ( $Slots_Data[$x] != "Empty" ) {
													if ( $x != 3 ) {
														echo "[ <a href='javascript:void()' onclick='changeSlot(" . $Slots_Data[$i]['id'] . ", $x)'>$x</a> ] ";
													} else {
														echo "[ <a href='javascript:void()' onclick='changeSlot(" . $Slots_Data[$i]['id'] . ", $x)'>$x</a> ]<br />";
													}
												} else {
													echo "[ $x ] ";
												}
											}
												
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
							
			echo			"</div>
							</div>
						</div>
						
						<div class='col-xs-6'>
							<div class='panel panel-default' style='margin-bottom: 0px'>
								<div class='panel-heading'>Boxed Pokemon</div>
								<div class='panel-body boxed_pokemon'>";
									$Query_Box = mysqli_query($con, "SELECT * FROM pokemon WHERE slot = '7' AND Owner_Current = '" . $row['id'] . "'");

									while ( $rows = mysqli_fetch_assoc($Query_Box) ) {
										echo "<img src='images/Icons/" . $rows['Pokedex_ID'] . ".png' onclick='showPokemon(" . $rows['id'] . ")' />";
									}
									
									if ( mysqli_num_rows($Query_Box) === 0 ) {
										echo	"There are no Pokemon in your box.";
									}
			echo			"</div>
							</div>
						</div>
						
						<div class='col-xs-6'>
							<div class='panel panel-default' style='margin-bottom: 0px'>
								<div class='panel-heading'>Selected Pokemon</div>
								<div class='panel-body' id='selectedPokemon'>
									Please select a Pokemon.
								</div>
							</div>
						</div>";
		}
		
		# If the $_POST['request'] variable has been set to 'pokemon_statistics', retrieve the appropriate roster information.
		if ( $_POST['request'] === 'pokemon_statistics' ) {
			$Pokemon_Data = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM pokemon WHERE id = '" . $_POST['id'] . "'"));
			$Pokemon_Type = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM types WHERE id = '" . $Pokemon_Data['Type'] . "'"));
			$Pokemon_Item = mysqli_fetch_assoc(mysqli_query($con, "SELECT Item_Name FROM items_owned WHERE Equipped_To = '" . $Pokemon_Data['ID'] . "'"));
			$Pokedex_Data = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM pokedex WHERE id = '" . $Pokemon_Data['Pokedex_ID'] . "'"));
			
			echo	"<div class='row'>";
			echo		"<div style='float: left; width: 250px'>";
								# Echo the Held Item.
								if ( $Pokemon_Item['Item_Name'] != '' ) {
									echo	"<img class='item' src='images/Items/" . $Pokemon_Item['Item_Name'] . ".png' />";
								}
			
								# Echo the gender icon.
								if ( $Pokemon_Data['Gender'] === 'Female' ) {
									echo	"<img class='gender' src='images/Assets/female.svg' />";
								}
								elseif ( $Pokemon_Data['Gender'] === 'Male' ) {
									echo	"<img class='gender' src='images/Assets/male.svg' />";
								}
								else {
									echo $Pokemon_Data['Gender'];
								}
								
			echo 			"<img src='images/Pokemon/" . $Pokemon_Type['Name'] . "/" . $Pokemon_Data['Pokedex_ID'] . ".png' /><br />";
			echo			"<b>";
								if ( $Pokemon_Type['Name'] != "Normal" ) {
									echo $Pokemon_Type['Name'] . $Pokedex_Data['Name'] . "<br />";
								} else {
									echo $Pokedex_Data['Name'] . "<br />";
								}
			echo			"</b>";
			echo 			"<b>Level:</b> " . number_format($Pokemon_Data['Level']);
			
			echo			"<div style='padding-bottom: 5px; padding-top: 5px'>";
			echo				"<b>Select A Slot</b><br />";
			echo 				"[ <a href='#' onclick='changeSlot(" . $_POST['id'] . ", 1)'>1</a> ] ";
			echo 				"[ <a href='#' onclick='changeSlot(" . $_POST['id'] . ", 2)'>2</a> ] ";
			echo 				"[ <a href='#' onclick='changeSlot(" . $_POST['id'] . ", 3)'>3</a> ] ";
			echo 				"[ <a href='#' onclick='changeSlot(" . $_POST['id'] . ", 4)'>4</a> ] ";
			echo 				"[ <a href='#' onclick='changeSlot(" . $_POST['id'] . ", 5)'>5</a> ] ";
			echo 				"[ <a href='#' onclick='changeSlot(" . $_POST['id'] . ", 6)'>6</a> ]";
			echo			"</div>";
			echo		"</div>";
			
			echo		"<div style='float: left; padding-top: 3px; width: 306px'>";
			echo			"<table class='special' style='float: left; width: 148px;'>";
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
			
			echo			"<table class='special' style='float: left; margin-left: 5px; width: 148px;'>";
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
			echo		"</div>";
			echo	"</div>";
		}
		
		# ============== BEGIN :: SLOT CHANGING ============== #
		if ( $_POST['request'] === 'slot_change' ) {
			# Pokemon data for the slot you're moving from.
			$Pokemon_One = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM pokemon WHERE id = '" .  $_POST['id'] . "' AND Owner_Current = '" . $My_Data['id'] . "'"));
			$Pokedex_One = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM pokedex WHERE id = '" . $Pokemon_One['Pokedex_ID'] . "'"));
			$Type_One = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM types WHERE id = '" . $Pokemon_One['Type'] . "'"));
			
			# Pokemon data for the slot you're moving to.
			$Pokemon_Two = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM pokemon WHERE Slot = '" .  $_POST['slot'] . "' AND Owner_Current = '" . $My_Data['id'] . "'"));
			$Pokedex_Two = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM pokedex WHERE id = '" . $Pokemon_Two['Pokedex_ID'] . "'"));
			$Type_Two = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM types WHERE id = '" . $Pokemon_Two['Type'] . "'"));
			
			# Check to see if the slot you're moving the Pokemon to is empty.
			if ( $Pokemon_Two['ID'] === null ) {
				# If the slot IS empty, loop through any previous slots to determine if they are empty as well.
				for ( $i = $_POST['slot']; $i >= 1; $i-- ) {
					$Check_Slots = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM pokemon WHERE Slot = '" . $i . "' AND Owner_Current = '" . $My_Data['id'] . "'"));

					# Looping through all previous slots to find out which ones are empty.
					if ( $Check_Slots['id'] === null ) {
						# Get the very first empty slot.
						$Empty_Slot = $i;
					}
				}
				
				# Update the `pokemon` database table with the updated slot change(s).
				//mysqli_query($con, "UPDATE pokemon SET Slot = '" . $Empty_Slot . "' WHERE id = '" . $Pokemon_One['ID'] . "' AND Owner_Current = '" . $My_Data['id'] . "'");
			} else {
				# Update the `pokemon` database table with the updated slot change(s).
				//mysqli_query($con, "UPDATE pokemon SET Slot = '" . $_POST['slot'] . "' WHERE id = '" . $Pokemon_One['ID'] . "' AND Owner_Current = '" . $My_Data['id'] . "'");
				//mysqli_query($con, "UPDATE pokemon SET Slot = '" . $Pokemon_One['Slot'] . "' WHERE id = '" . $Pokemon_Two['ID'] . "' AND Owner_Current = '" . $My_Data['id'] . "'");
			}
			
			# Echo some success dialog.
			echo  "<div class='col-xs-12'>";
			echo 		"<div class='description' style='margin-top: 5px; margin-bottom: 0px; padding: 5px'>";
			
			if ( $Type_One['Name'] !== "Normal" )  {
				echo "Your " . $Type_One['Name'] . $Pokedex_One['Name'] . " has been moved to Slot " . $_POST['slot'] . ".";
			} else {
				echo "Your " .  $Pokedex_One['Name'] . " has been moved to Slot " . $_POST['slot'] . ".";
			}
								
			echo 		"</div>";
			echo 	"</div>";
		}
		# ============== END   :: SLOT CHANGING ============== #
		
		# If the $_POST['request'] variable has been set to 'pokecenter_bag', retrieve the appropriate bag information.
		if ( $_POST['request'] === 'pokecenter_bag' ) {
			# Verify that the user is only trying to browse their bag.
			if ( $_POST['id'] === $My_Data['id'] ) {
				echo 	"<div class='col-xs-12' style='margin-bottom: -1px; margin-top: 5px'>";
				echo		"<div class='description' style='display: none;'></div>";
				echo 		"<div class='error' style='display: none;'></div>";
				echo	"</div>";
				
				echo	"<div class='col-xs-6'>";
				echo		"<div class='panel panel-default'>";
				echo			"<div class='panel-heading'>Your Items</div>";
				echo			"<div class='panel-body items' style='padding: 3px'>";
										$Get_Items = mysqli_query($con, "SELECT * FROM items_owned WHERE Owner_Current = '" . $My_Data['id'] . "' AND Equipped_To = '0'");
										
										while ( $Query = mysqli_fetch_assoc($Get_Items) ) {
											$Items_Ref = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM items WHERE Item_ID = '" . $Query['Item_ID'] . "'"));
											
											echo	"<img src='images/Items/" . $Query['Item_Name'] . ".png' onclick='selectItem(" . $Query['Item_ID'] . ")' />";
										}
										
										if ( mysqli_num_rows($Get_Items) === 0 ) {
											echo	"There are no items in your bag.";
										}
				echo			"</div>";
				echo		"</div>";
				echo	"</div>";
				
				echo	"<div class='col-xs-6'>";
				echo		"<div class='panel panel-default'>";
				echo			"<div class='panel-heading'>Selected Item</div>";
				echo			"<div class='panel-body' id='selectedItem' style='padding: 5px'>";
				echo				"Please select an item.";
				echo			"</div>";
				echo		"</div>";
				echo	"</div>";
				
				echo	"<div class='col-xs-12'>";
				echo		"<div class='panel panel-default' style='margin-bottom: 0px; margin-top: 5px;'>";
				echo			"<div class='panel-heading'>Attached Items</div>";
				echo			"<div class='panel-body' style='padding-top: 10px'>";
										$Check_Equipped = mysqli_query($con, "SELECT * FROM items_owned WHERE Owner_Current = '" . $My_Data['id'] . "' AND Equipped_To >= '1'");
										
										while ( $Query = mysqli_fetch_assoc($Check_Equipped) ) {
											$Check_Pokemon = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM pokemon WHERE Item = '" . $Query['Item_ID'] . "'"));
											$Check_Type = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM types WHERE id = '" . $Check_Pokemon['Type'] . "'"));
											$Check_Pokedex = mysqli_Fetch_assoc(mysqli_query($con, "SELECT * FROM pokedex WHERE id = '" . $Check_Pokemon['Pokedex_ID'] . "'"));
											
											echo	"<div class='col-xs-3'>";
											echo		"<div class='panel panel-default'>";
											echo			"<div class='panel-heading' style='background: #444'>";
																	if ( $Check_Type['Name'] != "Normal" ) {
																		echo $Check_Type['Name'] . $Check_Pokedex['Name'];
																	} else {
																		echo $Check_Pokedex['Name'];
																	}
											echo				"<br />" . "(" . $Check_Pokemon['Gender'] . ") " . "(Level: " . number_format($Check_Pokemon['Level']) . ")";
											echo			"</div>";
											echo			"<div class='panel-body item_body' style='background: #333; border-bottom-left-radius: 0px; border-bottom-right-radius: 0px'>";
											echo				"<img src='images/Pokemon/" . $Check_Type['Name'] . "/" . $Check_Pokemon['Pokedex_ID'] . ".png' />";
											echo			"</div>";
											echo			"<div class='panel-subheading item_subhead' style='background: #444; padding: 0px;'>";
											echo				"<div style=' float: left; height: 34px; margin-top: -4px; padding-left: 3px; padding-right: 3px; padding-top: 5px;'>";
											echo					"<img src='images/Items/" . $Query['Item_Name'] . ".png' />";
											echo				"</div>";
											echo				"<div style='margin-left: 30px; padding: 2px;'>";
											echo					"<a href='javascript:void()' onclick='removeItem(" . $Query['Item_ID'] . ")'>Remove</a>";
											echo				"</div>";
											echo			"</div>";
											echo		"</div>";
											echo	"</div>";
										}
				echo			"</div>";
				echo		"</div>";
				echo	"</div>";
			} else {
				echo "This user's bag doesn't belong to you.";
			}
		}
		
		# If the $_POST['request'] variable has been set to 'pokecenter_item', display the correct item's information.
		if ( $_POST['request'] === 'pokecenter_item' ) {
			# Verify that the user requesting this data actually owns this item.
			$Item_Data = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM items_owned WHERE Owner_Current = '" . $My_Data['id'] . "' AND Item_ID = '" . $_POST['id'] . "'"));
			$Item_Description = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM items WHERE Item_ID = '" . $Item_Data['id'] . "'"));
			
			if ( $Item_Data['Owner_Current'] === $My_Data['id'] ) {
				echo	"<img src='images/Items/" . $Item_Data['Item_Name'] . ".png' /><br />";
				echo	$Item_Data['Item_Name'] . "<br />";
				echo	'"<i>' . $Item_Description['Item_Description'] . '</i>"<br /><br />';
				echo	"<b>Attach To:</b><br />";
				
				for ( $i = 1; $i <= 6; $i++ ) {
					$Get_Roster_Data = mysqli_query($con, "SELECT * FROM pokemon WHERE Owner_Current = '" . $My_Data['id'] . "' AND slot = $i AND item = '0'");
					$Slot_Data[$i] = mysqli_fetch_assoc($Get_Roster_Data);
									
					if ( $Slot_Data[$i] ) {
						$type = mysqli_fetch_assoc(mysqli_query($con, "SELECT Name FROM types WHERE ID = " . $Slot_Data[$i]['Type']));
						$Slot_Data[$i]["Type"] = $type["Name"];
						$name = mysqli_fetch_assoc(mysqli_query($con, "SELECT Name FROM pokedex WHERE ID = " . $Slot_Data[$i]["Pokedex_ID"]));
						$Slot_Data[$i]["Name"] = $name["Name"];
					} else {
						$Slot_Data[$i] = "Empty";
					}
					
					if ( $Slot_Data[$i] != "Empty" ) {
						echo "[ <a href='javascript:void()' onclick='attachItem(" . $Slot_Data[$i]['id'] . ", " . $Item_Data['Item_ID'] . ", $i)'>$i</a> ] ";
					}
					
				}
			} else {
				echo "This item does not belong to you.";
			}
		}
		
		# If the $_POST['request'] variable has been set to 'pokecenter_removeitem', remove the item from the appropriate Pokemon.
		if ( $_POST['request'] === 'pokecenter_removeitem' ) {
			# Verify that the user requesting this data actually owns this item.
			$Item_Data = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM items_owned WHERE Item_ID = '" . $_POST['id'] . "' AND Owner_Current = '" . $My_Data['id'] . "'"));
			$Pokemon_Data = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM pokemon WHERE Item = '" . $Item_Data['Item_ID'] . "' AND Owner_Current = '" . $My_Data['id'] . "'"));
			$Pokemon_Type = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM types WHERE id = '" . $Pokemon_Data['Type'] . "'"));
			$Pokedex_Data =  mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM pokedex WHERE id = '" . $Pokemon_Data['Pokedex_ID'] . "'"));
			
			if ( $Item_Data['Owner_Current'] === $My_Data['id'] ) {
				if ( $_POST['id'] === $Item_Data['Item_ID'] ) {
					if ( $Pokemon_Type['Name'] != "Normal" ) {
						echo		"<div class='col-xs-12'>";
						echo			"<div class='description' style='margin-bottom: 5px; margin-top: 5px;'>The <b>" . $Item_Data['Item_Name'] . "</b> has been removed from your <b>" . $Pokemon_Type['Name'] . $Pokedex_Data['Name'] . "</b>.</div>";
						echo		"</div>";
					} else {
						echo		"<div class='col-xs-12'>";
						echo			"<div class='description' style='margin-bottom: 5px; margin-top: 5px;'>The <b>" . $Item_Data['Item_Name'] . "</b> has been removed from your <b>" . $Pokedex_Data['Name'] . "</b>.</div>";
						echo		"</div>";
					}
					
					# Remove the item from the Pokemon.
					mysqli_query($con, "UPDATE pokemon SET Item = '0' WHERE id = '" . $Pokemon_Data['id'] . "'");
					
					# Update the item's `Equipped_To` value.
					mysqli_query($con, "UPDATE items_owned SET Equipped_To = '0' WHERE Equipped_To = '" . $Pokemon_Data['id'] . "'");
					
					echo	"<div class='col-xs-6'>";
					echo		"<div class='panel panel-default'>";
					echo			"<div class='panel-heading'>Your Items</div>";
					echo			"<div class='panel-body items'>";
											$Get_Items = mysqli_query($con, "SELECT * FROM items_owned WHERE Owner_Current = '" . $My_Data['id'] . "' AND Equipped_To = '0'");
											
											while ( $Query = mysqli_fetch_assoc($Get_Items) ) {
												$Items_Ref = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM items WHERE Item_ID = '" . $Query['Item_ID'] . "'"));
												
												echo "<img src='images/Items/" . $Query['Item_Name'] . ".png' onclick='selectItem(" . $Query['Item_ID'] . ")' />";
											}
											
											if ( mysqli_num_rows($Get_Items) === 0 ) {
												echo	"There are no items in your bag.";
											}
					echo			"</div>";
					echo		"</div>";
					echo	"</div>";
					
					echo	"<div class='col-xs-6'>";
					echo		"<div class='panel panel-default'>";
					echo			"<div class='panel-heading'>Selected Item</div>";
					echo			"<div class='panel-body' id='selectedItem'>";
					echo				"Please select an item.";
					echo			"</div>";
					echo		"</div>";
					echo	"</div>";
					
					echo	"<div class='col-xs-12'>";
					echo		"<div class='panel panel-default' style='margin-bottom: 0px'>";
					echo			"<div class='panel-heading'>Attached Items</div>";
					echo			"<div class='panel-body' style='padding-top: 10px'>";
											$Check_Equipped = mysqli_query($con, "SELECT * FROM items_owned WHERE Owner_Current = '" . $My_Data['id'] . "' AND Equipped_To >= '1'");
											
											while ( $Query = mysqli_fetch_assoc($Check_Equipped) ) {
												$Check_Pokemon = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM pokemon WHERE Item = '" . $Query['Item_ID'] . "'"));
												$Check_Type = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM types WHERE id = '" . $Check_Pokemon['Type'] . "'"));
												$Check_Pokedex = mysqli_Fetch_assoc(mysqli_query($con, "SELECT * FROM pokedex WHERE id = '" . $Check_Pokemon['Pokedex_ID'] . "'"));
												
												echo	"<div class='col-xs-3'>";
												echo		"<div class='panel panel-default'>";
												echo			"<div class='panel-heading' style='background: #444'>";
																		if ( $Check_Type['Name'] != "Normal" ) {
																			echo $Check_Type['Name'] . $Check_Pokedex['Name'];
																		} else {
																			echo $Check_Pokedex['Name'];
																		}
												echo				"<br />" . "(" . $Check_Pokemon['Gender'] . ") " . "(Level: " . number_format($Check_Pokemon['Level']) . ")";
												echo			"</div>";
												echo			"<div class='panel-body' style='background: #333; border-bottom-left-radius: 0px; border-bottom-right-radius: 0px'>";
												echo				"<img src='images/Pokemon/" . $Check_Type['Name'] . "/" . $Check_Pokemon['Pokedex_ID'] . ".png' />";
												echo			"</div>";
												echo			"<div class='panel-subheading' style='background: #444; padding: 0px;'>";
												echo				"<div style=' float: left; height: 34px; margin-top: -4px; padding-left: 3px; padding-right: 3px; padding-top: 5px;'>";
												echo					"<img src='images/Items/" . $Query['Item_Name'] . ".png' />";
												echo				"</div>";
												echo				"<div style='border-left: 2px solid #00aa00; margin-left: 30px; padding: 2px;'>";
												echo					"<a href='javascript:void()' onclick='removeItem(" . $Query['Item_ID'] . ")'>Remove</a>";
												echo				"</div>";
												echo			"</div>";
												echo		"</div>";
												echo	"</div>";
											}
					echo			"</div>";
					echo		"</div>";
					echo	"</div>";
				} else {				
					echo 	"<div class='col-xs-12' style='margin-bottom: -1px; margin-top: 5px'>";
					echo		"<div class='description' style='display: none;'></div>";
					echo 		"<div class='error'>This item doesn't belong to you.</div>";
					echo	"</div>";
					
					echo	"<div class='col-xs-6'>";
					echo		"<div class='panel panel-default'>";
					echo			"<div class='panel-heading'>Your Items</div>";
					echo			"<div class='panel-body items'>";
											$Get_Items = mysqli_query($con, "SELECT * FROM items_owned WHERE Owner_Current = '" . $My_Data['id'] . "' AND Equipped_To = '0'");
											
											while ( $Query = mysqli_fetch_assoc($Get_Items) ) {
												$Items_Ref = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM items WHERE Item_ID = '" . $Query['Item_ID'] . "'"));
												
												echo "<img src='images/Items/" . $Query['Item_Name'] . ".png' onclick='selectItem(" . $Query['Item_ID'] . ")' />";
											}
											
											if ( mysqli_num_rows($Get_Items) === 0 ) {
												echo	"There are no items in your bag.";
											}
					echo			"</div>";
					echo		"</div>";
					echo	"</div>";
					
					echo	"<div class='col-xs-6'>";
					echo		"<div class='panel panel-default'>";
					echo			"<div class='panel-heading'>Selected Item</div>";
					echo			"<div class='panel-body' id='selectedItem'>";
					echo				"Please select an item.";
					echo			"</div>";
					echo		"</div>";
					echo	"</div>";
					
					echo	"<div class='col-xs-12'>";
					echo		"<div class='panel panel-default' style='margin-bottom: 0px'>";
					echo			"<div class='panel-heading'>Attached Items</div>";
					echo			"<div class='panel-body' style='padding-top: 10px'>";
											$Check_Equipped = mysqli_query($con, "SELECT * FROM items_owned WHERE Owner_Current = '" . $My_Data['id'] . "' AND Equipped_To >= '1'");
											
											while ( $Query = mysqli_fetch_assoc($Check_Equipped) ) {
												$Check_Pokemon = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM pokemon WHERE Item = '" . $Query['Item_ID'] . "'"));
												$Check_Type = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM types WHERE id = '" . $Check_Pokemon['Type'] . "'"));
												$Check_Pokedex = mysqli_Fetch_assoc(mysqli_query($con, "SELECT * FROM pokedex WHERE id = '" . $Check_Pokemon['Pokedex_ID'] . "'"));
												
												echo	"<div class='col-xs-3'>";
												echo		"<div class='panel panel-default'>";
												echo			"<div class='panel-heading' style='background: #444'>";
																		if ( $Check_Type['Name'] != "Normal" ) {
																			echo $Check_Type['Name'] . $Check_Pokedex['Name'];
																		} else {
																			echo $Check_Pokedex['Name'];
																		}
												echo				"<br />" . "(" . $Check_Pokemon['Gender'] . ") " . "(Level: " . number_format($Check_Pokemon['Level']) . ")";
												echo			"</div>";
												echo			"<div class='panel-body' style='background: #333; border-bottom-left-radius: 0px; border-bottom-right-radius: 0px'>";
												echo				"<img src='images/Pokemon/" . $Check_Type['Name'] . "/" . $Check_Pokemon['Pokedex_ID'] . ".png' />";
												echo			"</div>";
												echo			"<div class='panel-subheading' style='background: #444; padding: 0px;'>";
												echo				"<div style=' float: left; height: 34px; margin-top: -4px; padding-left: 3px; padding-right: 3px; padding-top: 5px;'>";
												echo					"<img src='images/Items/" . $Query['Item_Name'] . ".png' />";
												echo				"</div>";
												echo				"<div style='border-left: 2px solid #00aa00; margin-left: 30px; padding: 2px;'>";
												echo					"<a href='javascript:void()' onclick='removeItem(" . $Query['Item_ID'] . ")'>Remove</a>";
												echo				"</div>";
												echo			"</div>";
												echo		"</div>";
												echo	"</div>";
											}
					echo			"</div>";
					echo		"</div>";
					echo	"</div>";
				}
			}
		}
		
		if ( $_POST['request'] === 'pokecenter_attachitem' ) {
			# Verify that the user requesting this data actually owns this item.
			$Item_Data = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM items_owned WHERE Owner_Current = '" . $My_Data['id'] . "' AND Item_ID = '" . $_POST['item_id'] . "'"));
			
			# Verify that the user owns the Pokemon he's attemping to attach the item to.
			$Pokemon_Data = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM pokemon WHERE id = '" . $_POST['id'] . "' AND Owner_Current = '" . $My_Data['id'] . "' AND Slot = '" . $_POST['slot'] . "'"));
			$Pokemon_Type = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM types WHERE id = '" . $Pokemon_Data['Type'] . "'"));
			$Pokedex_Data = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM pokedex WHERE id = '" . $Pokemon_Data['Pokedex_ID'] . "'"));
			
			# A second verification check to ensure that you're the owner of the item.
			if ( $Item_Data['Owner_Current'] === $My_Data['id'] ) {
				# A second verification check to ensure that you're the owner of the Pokemon.
				if ( $Pokemon_Data['Owner_Current'] === $My_Data['id'] ) {
					# Update the `items_owned` table.
					mysqli_query($con, "UPDATE items_owned SET Equipped_To = '" . $Pokemon_Data['id'] . "' WHERE Owner_Current = '" . $My_Data['id'] . "' AND Item_ID = '" . $Item_Data['Item_ID'] . "'");
					
					# Update the `pokemon` table.
					mysqli_query($con, "UPDATE pokemon SET Item = '" . $Item_Data['id'] . "' WHERE id = '" . $Pokemon_Data['id'] . "' AND Slot = '" . $_POST['slot'] . "'");
					
					# Echo dialog.			
					if ( $Pokemon_Data['Type'] != "1" ) {
						echo		"<div class='col-xs-12'>";
						echo			"<div class='description' style='margin-top: 5px;'>The <b>" . $Item_Data['Item_Name'] . "</b> has been attached to your <b>" . $Pokemon_Type['Name'] . $Pokedex_Data['Name'] . "</b>.</div>";
						echo		"</div>";
					} else {
						echo		"<div class='col-xs-12'>";
						echo			"<div class='description' style='margin-top: 5px;'>The <b>" . $Item_Data['Item_Name'] . "</b> has been attached to your <b>" . $Pokedex_Data['Name'] . "</b>.</div>";
						echo		"</div>";
					}
				} else {
					echo		"<div class='col-xs-12'>";
					echo			"<div class='error' style='margin-top: 5px;'>The Pokemon that you attemped to attach the item to doesn't belong to you.</div>";
					echo		"</div>";
				}
			} else {
					echo		"<div class='col-xs-12'>";
					echo			"<div class='error' style='margin-top: 5px;'>The item that you attemped to attach the Pokemon to doesn't belong to you.</div>";
					echo		"</div>";
			}
			
			# Echo the Pokemon Center.
			echo	"<div class='col-xs-6'>";
			echo		"<div class='panel panel-default'>";
			echo			"<div class='panel-heading'>Your Items</div>";
			echo			"<div class='panel-body items'>";
									$Get_Items = mysqli_query($con, "SELECT * FROM items_owned WHERE Owner_Current = '" . $My_Data['id'] . "' AND Equipped_To = '0'");
											
									while ( $Query = mysqli_fetch_assoc($Get_Items) ) {
										$Items_Ref = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM items WHERE Item_ID = '" . $Query['Item_ID'] . "'"));
												
										echo "<img src='images/Items/" . $Query['Item_Name'] . ".png' onclick='selectItem(" . $Query['Item_ID'] . ")' />";
									}
									
									if ( mysqli_num_rows($Get_Items) === 0 ) {
										echo	"There are no items in your bag.";
									}
			echo			"</div>";
			echo		"</div>";
			echo	"</div>";
					
			echo	"<div class='col-xs-6'>";
			echo		"<div class='panel panel-default'>";
			echo			"<div class='panel-heading'>Selected Item</div>";
			echo			"<div class='panel-body' id='selectedItem'>";
			echo				"Please select an item.";
			echo			"</div>";
			echo		"</div>";
			echo	"</div>";
					
			echo	"<div class='col-xs-12'>";
			echo		"<div class='panel panel-default' style='margin-bottom: 0px'>";
			echo			"<div class='panel-heading'>Attached Items</div>";
			echo			"<div class='panel-body' style='padding-top: 10px'>";
									$Check_Equipped = mysqli_query($con, "SELECT * FROM items_owned WHERE Owner_Current = '" . $My_Data['id'] . "' AND Equipped_To >= '1'");
											
									while ( $Query = mysqli_fetch_assoc($Check_Equipped) ) {
										$Check_Pokemon = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM pokemon WHERE Item = '" . $Query['Item_ID'] . "'"));
										$Check_Type = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM types WHERE id = '" . $Check_Pokemon['Type'] . "'"));
										$Check_Pokedex = mysqli_Fetch_assoc(mysqli_query($con, "SELECT * FROM pokedex WHERE id = '" . $Check_Pokemon['Pokedex_ID'] . "'"));
											
										echo	"<div class='col-xs-3'>";
										echo		"<div class='panel panel-default'>";
										echo			"<div class='panel-heading' style='background: #444'>";
																if ( $Check_Type['Name'] != "Normal" ) {
																	echo $Check_Type['Name'] . $Check_Pokedex['Name'];
																} else {
																	echo $Check_Pokedex['Name'];
																}
										echo				"<br />" . "(" . $Check_Pokemon['Gender'] . ") " . "(Level: " . number_format($Check_Pokemon['Level']) . ")";
										echo			"</div>";
										echo			"<div class='panel-body' style='background: #333; border-bottom-left-radius: 0px; border-bottom-right-radius: 0px'>";
										echo				"<img src='images/Pokemon/" . $Check_Type['Name'] . "/" . $Check_Pokemon['Pokedex_ID'] . ".png' />";
										echo			"</div>";
										echo			"<div class='panel-subheading' style='background: #444; padding: 0px;'>";
										echo				"<div style=' float: left; height: 34px; margin-top: -4px; padding-left: 3px; padding-right: 3px; padding-top: 5px;'>";
										echo					"<img src='images/Items/" . $Query['Item_Name'] . ".png' />";
										echo				"</div>";
										echo				"<div style='border-left: 2px solid #00aa00; margin-left: 30px; padding: 2px;'>";
										echo					"<a href='javascript:void()' onclick='removeItem(" . $Query['Item_ID'] . ")'>Remove</a>";
										echo				"</div>";
										echo			"</div>";
										echo		"</div>";
										echo	"</div>";
									}
			echo			"</div>";
			echo		"</div>";
			echo	"</div>";
		}
		
		# If the $_POST['request'] variable has been set to 'pokecenter_nickname', retrieve the appropriate nickname information.
		if ( $_POST['request'] === 'pokecenter_nickname' ) {
			$Active_Data = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM pokemon WHERE Slot = '1' AND Owner_Current = '" . $My_Data['id'] . "'"));
			$Active_Type = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM types WHERE id = '" . $Active_Data['Type'] . "'"));
			$Active_Name = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM pokedex WHERE id = '" . $Active_Data['Pokedex_ID'] . "'"));
			
			echo	"<div class='col-xs-12'>";
			echo		"<div class='description' style='display: none; margin-bottom: 0px; margin-top: 5px;'></div>";
			echo		"<div class='error' style='display: none; margin-bottom: 0px; margin-top: 5px;'></div>";
			
			echo		"<div class='panel panel-default' style='margin: 5px 0px 0px 0px'>";
			echo			"<div class='panel-heading'>Nickname Your Pokemon</div>";
			echo			"<div class='panel-body'>";
									# Shall we record all nickname changes? Or would that be a waste of a database table?
			echo				"<div class='description'>";
			echo					"Here, you may set the nickname of your Pokemon.<br />";
			echo					"Please note that any inappropriate or profane names are not allowed.";
			echo				"</div>";
			
									if ( $Active_Type['Name'] != "Normal" ) {
										echo "<b>" . $Active_Type['Name'] . $Active_Name['Name'] . "</b><br />";
									} else {
										echo "<b>" . $Active_Name['Name'] . "</b><br />";
									}
									
									if ( $Active_Data['Nickname'] != "" ) {
										echo "<div style='font-size: 12px'>(" . $Active_Data['Nickname'] . ")</div>";
									}
			
			echo				"<img src='images/Pokemon/" . $Active_Type['Name'] . "/" . $Active_Data['Pokedex_ID']. ".png' /><br />";
			echo				"<i>Changing your Pokemon's nickname will deduct $1,000 from your account.</i>";
			
			echo				"<form method='post' onsubmit='changeNickname(); return false;'>";
			echo					"<input type='text' name='nickname' style='margin-bottom: 0px' /><br />";
			echo					"<input type='submit' name='submit' value='Change Nickname' style='margin-top: 3px'/>";
			echo				"</form>";			
			echo			"</div>";
			echo		"</div>";
			echo	"</div>";
		}
		
		# If the $_POST['request'] variable has been set to 'pokecenter_release', retrieve the appropriate release Pokemon information.
		if ( $_POST['request'] === 'pokecenter_release' ) {
			
			echo	"<div class='col-xs-12'>";
			echo		"<div class='panel panel-default' style='margin-top: 5px'>";
			echo			"<div class='panel panel-heading'>Release Pokemon</div>";
			echo				"<div class='panel panel-body' style='padding: 3px'>";
			echo					"This feature is under maintanence.";
			echo				"</div>";
			echo			"</div>";
			echo		"</div>";
			echo	"</div>";
			
			/*
			echo	"<div class='col-xs-12'>";
			echo		"<div class='description' style='display: none; margin-bottom: 0px; margin-top: 5px;'></div>";
			echo		"<div class='error' style='display: none; margin-bottom: 0px; margin-top: 5px;'></div>";
			
			echo		"<div class='panel panel-default' style='margin: 5px 0px 0px 0px'>";
			echo			"<div class='panel-heading'>Release Pokemon</div>";
			echo			"<div class='panel-body'>";
									# We'll record all Pokemon that get released, because Wynaut?
			echo				"<div class='description'>";
			echo					"You may release your Pokemon here, if you don't feel the need to own them any longer.";
			echo				"</div>";
			
			echo				"<form method='post'>";
			echo					"<div class='col-xs-6'>";
			echo						"<div class='panel panel-default'>";
			echo							"<div class='panel-heading'>Your Pokemon</div>";
			echo							"<div class='panel-body'>";
			echo								"<select class='release' id='release' name='release' multiple='multiple' style='height: 300px; width: 90%'>";
														$User_Box = mysqli_query($con, "SELECT * FROM pokemon WHERE Owner_Current = '" . $My_Data['id'] . "' AND Slot = '7'");
														
														while ( $Query = mysqli_fetch_assoc($User_Box) ) {
															$Box_Name = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM pokedex WHERE id = '" . $Query['Pokedex_ID'] . "'"));
															$Box_Type = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM types WHERE id = '" . $Query['Type'] . "'"));
															
															echo	"<option style='border-bottom: 1px solid #fff; padding: 3px;' value='" . $Query['id'] . "'>";
																		if ( $Query['Type'] != "Normal" ) {
																			echo $Box_Type['Name'] . $Box_Name['Name'];
																		} else {
																			echo $Box_Name['Name'];
																		}
															echo		" " . $Query['Gender'] . " (Level: " . number_format($Query['Level']) . ")";
															echo	"</option>";
														}
			echo								"</select>";
			echo							"</div>";
			echo						"</div>";
			echo					"</div>";
			
			echo					"<div class='col-xs-6'>";
			echo						"<div class='panel panel-default'>";
			echo							"<div class='panel-heading'>Selected Pokemon</div>";
			echo							"<div class='panel-body'>";
			echo								"A list of the Pokemon that you've selected to release.";
			echo							"</div>";
			echo						"</div>";
			echo					"</div>";
			
			echo					"<input type='submit' name='confirmRelease' value='Release' onclick='releasePokemon( $('#release').val(); ); return false;' />";
			echo				"</form>";
			echo			"</div>";
			echo		"</div>";
			echo	"</div>";
			*/
		}
		# ------------------ END   :: POKEMON CENTER AJAX REQUESTS ------------------ #
		
		# ------------------ BEGIN :: EVOLUTION CENTER AJAX REQUESTS ------------------ #
		if ( $_POST['request'] === 'evolution_info' ) {
			$Pokemon_Info = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM pokemon WHERE id = '" . $_POST['id'] . "'"));
			$Pokemon_Type = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM types WHERE id = '" . $Pokemon_Info['Type'] . "'"));
			$Pokedex_Info = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM pokedex WHERE id = '" . $Pokemon_Info['Pokedex_ID'] . "'"));
			$Item_Info = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM items_owned WHERE Equipped_To = '" . $Pokemon_Info['ID'] . "'"));
			$Evolution_Data = mysqli_query($con, "SELECT * FROM pokedex WHERE id = '" . $Pokemon_Info['Pokedex_ID'] . "'");
			
			echo	"<div class='row'>";
			echo		"<div class='col-xs-6'>";
			echo 			"<img src='images/Pokemon/" . $Pokemon_Type['Name'] . "/" . $Pokemon_Info['Pokedex_ID'] . ".png' /><br />";
								if ( $Pokemon_Type['Name'] != "Normal" ) {
									echo 	"<b>" . $Pokemon_Type['Name'] . $Pokedex_Info['Name'] . "</b>";
								} else {
									echo 	"<b>" . $Pokedex_Info['Name'] . "</b>";
								}
			echo		"</div>";
			
			echo		"<div class='col-xs-6' style='padding: 35px 5px 5px 5px;'>";
			echo			"<b>Level: </b>" . number_format($Pokemon_Info['Level']) . "<br />";
			echo			"<b>Held Item: </b>";
								if ( $Pokemon_Info['Item'] === '0' ) {
									echo "None";
								} else {
									echo $Item_Info['Item_Name'];
								}
			echo		"</div>";
			echo	"</div>";
			
			while ( $Evolutions = mysqli_fetch_assoc($Evolution_Data) ) {
				$Pokedex_Data = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM pokedex WHERE Name = '" . $Evolutions['Evolves_Into'] . "'"));

				echo "<pre>";
				var_dump($Evolutions);
				echo "</pre>";
				
				if ( $Evolutions['Evolves_Into'] === null ) {
					echo	"<div class='evolution' style='padding: 5px'>";
					echo		"This Pokemon does not evolve into anything.";
					echo	"</div>"; 
				} else {
					echo	"<div class='evolution'>";
					echo		"<div class='row'>";
					echo			"<div class='col-xs-12'>";
					echo				"<div class='col-xs-4'>";
					echo					"<img src='images/Pokemon/" . $Pokemon_Type['Name'] . "/" . $Pokedex_Data['ID'] . ".png' /><br />";
												if ( $Pokemon_Type['Name'] != "Normal" ) {
													echo $Pokemon_Type['Name'] . $Pokedex_Data['Name'];
												} else {
													echo $Pokedex_Data['Name'];
												}
					echo				"</div>";
					echo				"<div class='col-xs-4'>";
					echo					"<b>Requirements</b>";
					echo				"</div>";
					echo				"<div class='col-xs-4'>";
					echo					"Evolve button";
					echo				"</div>";
					echo			"</div>";
					echo		"</div>";
					echo	"</div>";
				}				
			}
		}
		# ------------------ END   :: EVOLUTION CENTER AJAX REQUESTS ------------------ #
		
		# ------------------ BEGIN   :: TRADE CENTER AJAX REQUESTS ------------------ #
		if ( $_POST['request'] === 'trade_stats' ) {
			$Pokemon_Data = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM pokemon WHERE id = '" . $_POST['id'] . "'"));
			$Pokemon_Type = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM types WHERE id = '" . $Pokemon_Data['Type'] . "'"));
			$Pokedex_Data = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM pokedex WHERE id = '" . $Pokemon_Data['Pokedex_ID'] . "'"));
			$Pokemon_Item = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM items_owned WHERE Item_ID = '" . $Pokemon_Data['Item'] . "'"));
			
			echo	"<div class='row'>";
			echo		"<div class='col-xs-6'>";
			echo			"<img src='images/Pokemon/" . $Pokemon_Type['Name'] . "/" . $Pokemon_Data['Pokedex_ID'] . ".png' />";
			echo		"</div>";
			
			echo		"<div class='col-xs-6' style='padding-top: 20px'>";
								if ( $Pokemon_Type['Name'] != "Normal" ) {
									echo	"<b>" . $Pokemon_Type['Name'] . $Pokedex_Data['Name'] . " (" . $Pokemon_Data['Gender'] . ")</b><br />";
								} else {
									echo	"<b>" . $Pokedex_Data['Name'] . " (" . $Pokemon_Data['Gender'] . ")</b><br />";
								}
								
								echo 	"Level: " . number_format($Pokemon_Data['Level']) . "<br />";
								if ( $Pokemon_Data['Item'] === 0 ) {
									echo	"Item: " . $Pokemon_Item['Item_Name'];
								} else {
									echo	"Item: None";
								}
			echo		"</div>";
			
			echo		"<div class='col-xs-12'>";
			echo			"<a href='javascript:void()' onclick='addToPartner(" . $Pokemon_Data['id'] . ")'>Add To Trade</a>";
			echo		"</div>";
			echo	"</div>";
		}
		# ------------------ END   :: TRADE CENTER AJAX REQUESTS ------------------ #

		if ( $_POST['request'] === 'stylish' ) {
			$Stylish = $_POST['style'];

			$Query_Check = mysqli_query($con, "SELECT * FROM stylish WHERE Player_ID = '".$My_Data['id']."'");
			if ( $Query_Check === true ) {
				mysqli_query($con, "UPDATE stylish SET Stylish = '".$Stylish."' WHERE Player_ID = '".$My_Data['id']."'");
			}	else {
				mysqli_query($con, "INSERT INTO stylish (Player_ID, Player_Name, Player_Style, getDate) VALUES ('".$My_Data['id']."', '".$My_Data['Username']."', '".$Stylish."', '".$Date."')");
			}
		}
	}
?>