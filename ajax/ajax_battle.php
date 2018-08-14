<?php
	# Require 'session.php' information.
	require 'session.php';
	
	# Don't display errors.
	error_reporting(0);
	
	# Get the current date info.
	date_default_timezone_set('America/Los_Angeles');
	$Date = date("M dS, Y g:i:s A");
	
	# ------------------ TO DO LIST ------------------ #
	#		-> Incorporating both Pokemon's Speed.
	#		---> IF yourSpeed > foeSpeed : you move first, ELSE : foe moves first.
	#		---> This will help cut down on the amount of battleDialog that actually gets echoed to the user.

	# The user's database info.
	$My_Data = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM members WHERE id = '" . $_SESSION['battle']['my']['id'] . "'"));

	echo	"<pre>";
	print_r($_POST);
	echo	"</pre>";
	
	if ( isset($_POST['move']) ) {
		# Get the move variable.
		$Move_ID = $_POST['move'];
	
		# Get the Move and Pokemon data of your Active Pokemon.
		$My_Active = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM pokemon WHERE id = '" . $_SESSION['battle']['my']['active']['id'] . "'"));
		$My_Pokedex = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM pokedex WHERE id = '" . $_SESSION['battle']['my']['active']['Pokedex_ID'] . "'"));
		$My_Move = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM moves WHERE Name = '" . $_SESSION['battle']['my']['active'][$_POST['move']] . "'"));
			
		# Choose a random move from the Foe's Active Pokemon's move pool.
		$Foe_Random = mt_rand(1,4);
		$Foe_Move = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM moves WHERE Name = '" . $_SESSION['battle']['foe']['active']['Move_' . $Foe_Random] . "'"));
		
		// Using their first move.
		if ( $Move_ID === '1' ) {

		}
		// Using their second move.
		else if ( $Move_ID === '2' ) {

		}
		// Using their third move.
		else if ( $Move_ID === '3' ) {

		}
		// Using their fourth move.
		else if ( $Move_ID === '4' ) {
			
		}
		// This user is macroing or some bullshit.
		else {
			
		}

		# Insert the proper information into the `battle_logs` database table.
		mysqli_query($con, "INSERT INTO battle_logs (User_ID, Foe_ID, Move_Used, Coord_X, Coord_Y, Click_Date) VALUES ('" . $My_Data['id'] . "', '" . $_POST['foe_id'] . "', '" . $My_Move['Name'] . "', '" . $_POST['x'] . "', '" . $_POST['y'] . "', '" . $Date . "')");
		
		# ========== Battle Formula Stuff ========== #
		# Determine my critical hit data.
		$My_Crit = mt_rand(1, 16);
		if ( $My_Crit === 12 ) {
			$My_Crit_Damage = 1.5;
			$My_Crit_Dialog = "<br /><b>The attack was a critical hit!</b>";
		} else {
			$My_Crit_Damage = 1;
			$My_Crit_Dialog = null;
		}
			
		# Determine my critical hit data.
		$Foe_Crit = mt_rand(1, 16);
		if ( $Foe_Crit === 12 ) {
			$Foe_Crit_Damage = 1.5;
			$Foe_Crit_Dialog = "<br /><b>The attack was a critical hit!</b>";
		} else {
			$Foe_Crit_Damage = 1;
			$Foe_Crit_Dialog = null;
		}
			
		# The RNG aspect of the official damage formula.
		$RNG = mt_rand(85, 100) / 100;
			
		# My Damage
		//$My_Damage = floor(((((2 * $My_Active['Level']) / 5 + 2) * $My_Move['Power'] * ($My_Active['Attack'] / $_SESSION['battle']['foe']['active']['Defense'])) / 50 + 2) * $My_Crit_Damage * $RNG);
		$My_Damage = 1;
			
		# Foe Damage
		//$Foe_Damage =  floor(((((2 * $_SESSION['battle']['foe']['active']['Level']) / 5 + 2) * $Foe_Move['Power'] * ($_SESSION['battle']['foe']['active']['Attack'] / $My_Active['Defense'])) / 50 + 2) * $Foe_Crit_Damage * $RNG);
		$Foe_Damage = 1;	

		# Update both Active Pokemon's HP.
		$_SESSION['battle']['foe']['active']['Cur_HP'] -= $My_Damage;
		$_SESSION['battle']['my']['active']['Cur_HP'] -= $Foe_Damage;
		
		# If the Foe's Active Pokemon's HP goes below 0, update the Pokemon's HP to be 0.
		if ( $_SESSION['battle']['foe']['active']['Cur_HP'] <= 0 ) {
			$_SESSION['battle']['foe']['active']['Cur_HP'] = 0;
		}
		
		# If the Users's Active Pokemon's HP goes below 0, update the Pokemon's HP to be 0.
		if ( $_SESSION['battle']['my']['active']['Cur_HP'] <= 0 ) {
			$_SESSION['battle']['my']['active']['Cur_HP'] = 0;
		}
			
		# Echo the rosters of both users.
		echo	"<div class='row'>";
		echo		"<div class='col-xs-6 rosters'>";
							for ( $i = 1; $i <= 6; $i++ ) {
								echo	"<div class='battle_slot'>";
								if ( $_SESSION['battle']['my']['roster']['Slot' . $i] != "Empty" ) {
									echo	"<img src='images/Icons/" . $_SESSION['battle']['my']['roster']['Slot' . $i]['Pokedex_ID'] . ".png' />";
								} else {
									echo	"<img src='images/Assets/pokeball.png' style='height: 30px; width: 30px;' />";
								}
								echo	"</div>";
							}
		echo		"</div>";
		
		echo		"<div class='col-xs-6 rosters'>";
							for ( $i = 1; $i <= 6; $i++ ) {
								echo	"<div class='battle_slot'>";
								if ( $_SESSION['battle']['foe']['roster']['Slot' . $i] != "Empty" ) {
									echo	"<img src='images/Icons/" . $_SESSION['battle']['foe']['roster']['Slot' . $i]['Pokedex_ID'] . ".png' />";
								} else {
									echo	"<img src='images/Assets/pokeball.png' style='height: 30px; width: 30px;' />";
								}
								echo	"</div>";
							}
		echo		"</div>";
		echo	"</div>";
		
		# Echo the Active Pokemon's data of both users.
		echo	"<div class='row'>";
		echo		"<div class='col-xs-6 active_pokemon'>";
		echo			"<div class='panel panel-default'>";
		echo				"<div class='panel-heading'>";
		echo 					$_SESSION['battle']['my']['username'] . "'s ";
									if ( $_SESSION['battle']['my']['active']['Type'] != "Normal" ) {
										echo	$_SESSION['battle']['my']['active']['Type'] . $_SESSION['battle']['my']['active']['Name'];
									} else {
										echo	$_SESSION['battle']['my']['active']['Name'];
									}
		echo				"</div>";
		echo				"<div class='panel-body'>";
		echo					"<div class='col-xs-6' style='font-size: 14px; margin-top: 10px;'>";
		echo						"<b>HP:</b> (" . $_SESSION['battle']['my']['active']['Cur_HP'] . "/" . $_SESSION['battle']['my']['active']['Max_HP'] . ")";
		echo						"<div class='hp_bar' style='margin-bottom: 10px'>";
		echo							"<span style='width: 120px'></span>";
		echo						"</div>";
										
		echo						"<b>Level:</b> (" . number_format($_SESSION['battle']['my']['active']['Level']) . ")";
		echo						"<div class='exp_bar'>";
		echo							"<span style='width: 120px'></span>";
		echo						"</div>";
		echo					"</div>";
		
		echo					"<div class='col-xs-6'>";
										if ( $_SESSION['battle']['my']['active']['Item'] != "" ) {
											echo	"<img src='images/Items/" . $_SESSION['battle']['my']['active']['Item'] . ".png' style='left: 96px; position: absolute; transform: scaleX(-1);'/>";
										}
		echo						"<img src='images/Pokemon/" . $_SESSION['battle']['my']['active']['Type'] . "/" . $_SESSION['battle']['my']['active']['Pokedex_ID'] . ".png' />";
		echo					"</div>";
		echo				"</div>";
		echo			"</div>";
		echo		"</div>";
		
		echo		"<div class='options'>";
		echo			"<span>";
		echo				"<img src='images/Assets/options.png' onclick='displayOptions()' />";
		echo			"</span>";
		echo		"</div>";
		
		echo		"<div class='col-xs-6 active_pokemon'>";
		echo			"<div class='panel panel-default'>";
		echo				"<div class='panel-heading'>";
		echo 					$_SESSION['battle']['foe']['username'] . "'s ";
									if ( $_SESSION['battle']['foe']['active']['Type'] != "Normal" ) {
										echo	$_SESSION['battle']['foe']['active']['Type'] . $_SESSION['battle']['foe']['active']['Name'];
									} else {
										echo	$_SESSION['battle']['foe']['active']['Name'];
									}
		echo				"</div>";
		echo				"<div class='panel-body'>";
		echo					"<div class='col-xs-6'>";
										if ( $_SESSION['battle']['foe']['active']['Item'] != "" ) {
											echo	"<img src='images/Items/" . $_SESSION['battle']['foe']['active']['Item'] . ".png' style='position: absolute;'/>";
										}
		echo						"<img src='images/Pokemon/" . $_SESSION['battle']['foe']['active']['Type'] . "/" . $_SESSION['battle']['foe']['active']['Pokedex_ID'] . ".png' />";
		echo					"</div>";
		
		echo					"<div class='col-xs-6' style='font-size: 14px; margin-top: 10px;'>";
		echo						"<b>HP:</b> (" . $_SESSION['battle']['foe']['active']['Cur_HP'] . "/" . $_SESSION['battle']['foe']['active']['Max_HP'] . ")";
		echo						"<div class='hp_bar' style='margin-bottom: 10px'>";
		echo							"<span style='width: 120px'></span>";
		echo						"</div>";
										
		echo						"<b>Level:</b> (" . number_format($_SESSION['battle']['foe']['active']['Level']) . ")";
		echo						"<div class='exp_bar'>";
		echo							"<span style='width: 120px'></span>";
		echo						"</div>";
		echo					"</div>";
		echo				"</div>";
		echo			"</div>";
		echo		"</div>";			
		echo	"</div>";
		
		# Echo the active Pokemon's moves IF either Pokemon's HP is above 0.
		if ( $_SESSION['battle']['my']['active']['Cur_HP'] <= 0 || $_SESSION['battle']['foe']['active']['Cur_HP'] <= 0 ) {
			echo	"";
		} else {
			echo	"<div class='row'>";
			echo		"<div class='col-xs-12 moves'>";
			echo			"<div class='panel panel-default'>";
			echo				"<div class='panel-body'>";
										for ( $i = 1; $i <= 4; $i++ ) {
											echo "<button id='Move_" . $i . "'>" . $_SESSION['battle']['my']['active']['Move_' . $i] . "</button> ";
										}
			echo				"</div>";
			echo			"</div>";
			echo		"</div>";
			echo	"</div>";
		}
		
		# Echo the Battle Dialog.
		echo	"<div class='row'>";
		echo		"<div class='col-xs-12 battleDialog'>";
		echo			"<div class='panel panel-default'>";
		echo				"<div class='panel-body' id='battleDialog'>";
									# Restarting the battle.
									if ( isset($_POST['restart']) ) {										
										echo	"Please use a move to begin the battle!";
									} else {
										# Echo The Player's Battle Dialog.
										if ( $_SESSION['battle']['my']['active']['Type'] != 'Normal' ) {
											echo "Your " . $_SESSION['battle']['my']['active']['Type'] . $_SESSION['battle']['my']['active']['Name'] . " has used <b>" . $My_Move['Name'] . "</b> and has dealt <b>" . number_format($My_Damage) . "</b> damage.";
											echo $My_Crit_Dialog;
										} else {
											echo "Your " . $Pokedex_Data['Name'] . " has used <b>" . $My_Move['Name'] . "</b> and has dealt <b>" . number_format($My_Damage) . "</b> damage.";
											echo $My_Crit_Dialog;
										}
											
										echo "<br /><br />";
											
										# Echo the Foe's Battle Dialog.
										if ( $_SESSION['battle']['foe']['active']['Type'] != 'Normal' ) {
											echo "The Foe's " . $_SESSION['battle']['foe']['active']['Type'] . $_SESSION['battle']['foe']['active']['Name'] . " has used <b>" . $Foe_Move['Name'] . "</b> and has dealt <b>" . number_format($Foe_Damage) . "</b> damage.";
											echo $Foe_Crit_Dialog;
										} else {
											echo "The Foe's " . $_SESSION['battle']['foe']['active']['Name'] . " has used <b>" . $Foe_Move['Name'] . "</b> and has dealt <b>" . number_format($Foe_Damage) . "</b> damage.";
											echo $Foe_Crit_Dialog;
										}
			
										if ( $_SESSION['battle']['foe']['active']['Cur_HP'] <= 0 ) {
											$_SESSION['battle']['foe']['active']['Cur_HP'] = 0;
											$Pokedex_Info = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM pokedex WHERE id = '" . $_SESSION['battle']['foe']['active']['Pokedex_ID'] . "'"));
											$Stat_Total = $_SESSION['battle']['foe']['active']['Level'] + $Pokedex_Info['HP'] + $Pokedex_Info['Attack'] + $Pokedex_Info['Defense'] + $Pokedex_Info['SpecialAttack'] + $Pokedex_Info['SpecialDefense'] + $Pokedex_Info['Speed'];
											$Money = floor($_SESSION['battle']['foe']['active']['Level'] + $Pokedex_Info['HP'] + $Pokedex_Info['Attack'] + $Pokedex_Info['Defense'] + $Pokedex_Info['SpecialAttack'] + $Pokedex_Info['SpecialDefense'] + $Pokedex_Info['Speed']) * 5;
											$Experience = pow($Stat_Total, 1.6);
											
											# Held Item Effects
											if ( $_SESSION['battle']['my']['active']['Item'] === "Amulet Coin" ) {
												$Money = $Money * 2;
											}
											
											if ( $_SESSION['battle']['my']['active']['Item'] === "Lucky Egg" ) {
												$Experience = $Experience * 2;
											}
											
											for ( $i = 2; $i <= 6; $i++ ) {
												if ( $_SESSION['battle']['my']['roster']['Slot' . $i]['Item'] === "Exp Share" ) {
													# -------------- TO DO LIST --------------- #
													#	---> This, for some reason, isn't working properly..?
													
													# Half the experience that's gained by the active and holding Pokemon.
													$Experience = $Experience / 2;
													
													# Update the Pokemon's experience.
													$_SESSION['battle']['my']['roster']['Slot' . $i]['Experience'] = $_SESSION['battle']['my']['roster']['Slot' . $i]['Experience'] + $Experience;
													
													# Calculate the next level of the holding Pokemon.
													$Holders_Next_Level = $_SESSION['battle']['my']['roster']['Slot' . $i]['Level'] + 1;
													$Holders_Next_Exp = pow($_SESSION['battle']['my']['roster']['Slot' . $i]['Level'], 3);
													
													# Update the user's money, and the holders Pokemon's experience + level.
													mysqli_query($con, "UPDATE members SET Money = Money + $Money WHERE id = '" . $_SESSION['battle']['my']['roster']['Slot' . $i]['id'] . "'");
													mysqli_query($con, "UPDATE pokemon SET Experience = Experience + $Experience WHERE id = '" . $_SESSION['battle']['my']['roster']['Slot' . $i]['id'] . "'");
													mysqli_query($con, "UPDATE pokemon SET Level = '" . $Holders_Next_Level . "' WHERE id = '" . $_SESSION['battle']['my']['roster']['Slot' . $i]['id'] . "'");
																										
													if ( $_SESSION['battle']['my']['roster']['Slot' . $i]['Experience'] > $Holders_Next_Exp ) {
														$Shared_Dialog = "<br />The Exp.Share holder has gained " . number_format($Experience) . " experience.<br />The Pokemon reached level " . number_format($Holders_Next_Level) . ".<br /><br />";
														
														# Update the Pokemon's experience and level.
														mysqli_query($con, "UPDATE pokemon SET Level = '" . $Holders_Next_Level . "' WHERE id = '" . $_SESSION['battle']['my']['roster']['Slot' . $i]['id'] . "'");
														mysqli_query($con, "UPDATE pokemon SET Experience = Experience + $Experience WHERE id = '" . $_SESSION['battle']['my']['roster']['Slot' . $i]['id'] . "'");
													} else {
														$Shared_Dialog = "<br />The Exp.Share holder has gained " . number_format($Experience) . " experience.<br /><br />";
														
														# Echo the Pokemon's experience.
														mysqli_query($con, "UPDATE pokemon SET Experience = Experience + $Experience WHERE id = '" . $_SESSION['battle']['my']['roster']['Slot' . $i]['id'] . "'");
														mysqli_query($con, "UPDATE pokemon SET Level = '" . $Holders_Next_Level . "' WHERE id = '" . $_SESSION['battle']['my']['roster']['Slot' . $i]['id'] . "'");
													}
												}
											}
											
											# Update the Pokemon's experience.
											$_SESSION['battle']['my']['active']['Experience'] = $_SESSION['battle']['my']['active']['Experience'] + $Experience;
											
											# Calculate the next level of your Active Pokemon.
											$My_Next_Level = $_SESSION['battle']['my']['active']['Level'] + 1;
											$My_Next_Exp = pow($_SESSION['battle']['my']['active']['Level'], 3);
											
											# Update the user's money, and their active Pokemon's experience + level.
											mysqli_query($con, "UPDATE members SET Money = Money + $Money WHERE id = '" . $_SESSION['battle']['my']['id'] . "'");
											mysqli_query($con, "UPDATE pokemon SET Experience = Experience + $Experience WHERE id = '" . $_SESSION['battle']['my']['active']['id'] . "'");
											mysqli_query($con, "UPDATE pokemon SET Level = '" . $My_Next_Level . "' WHERE id = '" . $_SESSION['battle']['my']['active']['id'] . "'");
												
											echo 	"<br /><br />";
											echo	"You have gained " . number_format($Experience) . " experience.<br />";
																						
											# If the Pokemon has enough EXP to level up :
											if ( $_SESSION['battle']['my']['active']['Experience'] > $My_Next_Exp ) {
												echo	"Your Pokemon has reached level " . number_format($My_Next_Level) . "!<br />";
												mysqli_query($con, "UPDATE pokemon SET Level = '" . $My_Next_Level . "' WHERE id = '" . $_SESSION['battle']['my']['active']['id'] . "'");
												
												$_SESSION['battle']['my']['active']['Level'] = $My_Next_Level;
												
												$My_Next_Level = $_SESSION['battle']['my']['active']['Level'] + 1;
												$My_Next_Exp = pow($_SESSION['battle']['my']['active']['Level'], 3);
											}
											
											echo	$Shared_Dialog;
											
											echo 	"You have won the battle!<br />";
											echo	"For winning, you have been awarded $" . number_format($Money) . ".<br />";
											echo	"<button id='restart'>Restart Battle</button>";
										}
										
										if ( $_SESSION['battle']['my']['active']['Cur_HP'] <= 0 ) {
											$_SESSION['battle']['my']['active']['Cur_HP'] = 0;
											
											echo 	"<br />";
											echo 	"You have lost the battle.";
										}
									}
		echo				"</div>";
		echo			"</div>";
		echo		"</div>";
		echo	"</div>";
		
	# Since 'move' isn't being sent as a POST variable, check to see if 'restart' is.
	} elseif ( isset($_POST['restart']) ) {
		# Retrieve the user's information.
		$My_Data = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM members WHERE id = '" . $_SESSION['battle']['my']['id'] . "'"));
			
		# Insert the proper information into the `battle_logs` database table.
		mysqli_query($con, "INSERT INTO battle_logs (User_ID, Foe_ID, Move_Used, Coord_X, Coord_Y, Click_Date) VALUES ('" . $My_Data['id'] . "', '" . $_POST['foe_id'] . "', 'Restart Battle', '" . $_POST['x'] . "', '" . $_POST['y'] . "', '" . $Date . "')");
		
		# Reset both of the Active Pokemon's HP.
		$_SESSION['battle']['my']['active']['Cur_HP'] = $_SESSION['battle']['my']['active']['Max_HP'];
		$_SESSION['battle']['foe']['active']['Cur_HP'] = $_SESSION['battle']['foe']['active']['Max_HP'];
		
		# Echo the rosters of both users.
		echo	"<div class='row'>";
		echo		"<div class='col-xs-6 rosters'>";
							for ( $i = 1; $i <= 6; $i++ ) {
								echo	"<div class='battle_slot'>";
								if ( $_SESSION['battle']['my']['roster']['Slot' . $i] != "Empty" ) {
									echo	"<img src='images/Icons/" . $_SESSION['battle']['my']['roster']['Slot' . $i]['Pokedex_ID'] . ".png' />";
								} else {
									echo	"<img src='images/Assets/pokeball.png' style='height: 30px; width: 30px;' />";
								}
								echo	"</div>";
							}
		echo		"</div>";
		
		echo		"<div class='col-xs-6 rosters'>";
							for ( $i = 1; $i <= 6; $i++ ) {
								echo	"<div class='battle_slot'>";
								if ( $_SESSION['battle']['foe']['roster']['Slot' . $i] != "Empty" ) {
									echo	"<img src='images/Icons/" . $_SESSION['battle']['foe']['roster']['Slot' . $i]['Pokedex_ID'] . ".png' />";
								} else {
									echo	"<img src='images/Assets/pokeball.png' style='height: 30px; width: 30px;' />";
								}
								echo	"</div>";
							}
		echo		"</div>";
		echo	"</div>";
		
		# Echo the Active Pokemon's data of both users.
		echo	"<div class='row'>";
		echo		"<div class='col-xs-6 active_pokemon'>";
		echo			"<div class='panel panel-default'>";
		echo				"<div class='panel-heading'>";
		echo 					$_SESSION['battle']['my']['username'] . "'s ";
									if ( $_SESSION['battle']['my']['active']['Type'] != "Normal" ) {
										echo	$_SESSION['battle']['my']['active']['Type'] . $_SESSION['battle']['my']['active']['Name'];
									} else {
										echo	$_SESSION['battle']['my']['active']['Name'];
									}
		echo				"</div>";
		echo				"<div class='panel-body'>";
		echo					"<div class='col-xs-6' style='font-size: 14px; margin-top: 10px;'>";
		echo						"<b>HP:</b> (" . $_SESSION['battle']['my']['active']['Cur_HP'] . "/" . $_SESSION['battle']['my']['active']['Max_HP'] . ")";
		echo						"<div class='hp_bar' style='margin-bottom: 10px'>";
		echo							"<span style='width: 120px'></span>";
		echo						"</div>";
										
		echo						"<b>Level:</b> (" . number_format($_SESSION['battle']['my']['active']['Level']) . ")";
		echo						"<div class='exp_bar'>";
		echo							"<span style='width: 120px'></span>";
		echo						"</div>";
		echo					"</div>";
		
		echo					"<div class='col-xs-6'>";
										if ( $_SESSION['battle']['my']['active']['Item'] != "" ) {
											echo	"<img src='images/Items/" . $_SESSION['battle']['my']['active']['Item'] . ".png' style='left: 96px; position: absolute; transform: scaleX(-1);'/>";
										}
		echo						"<img src='images/Pokemon/" . $_SESSION['battle']['my']['active']['Type'] . "/" . $_SESSION['battle']['my']['active']['Pokedex_ID'] . ".png' />";
		echo					"</div>";
		echo				"</div>";
		echo			"</div>";
		echo		"</div>";
		
		echo		"<div class='options'>";
		echo			"<span>";
		echo				"<img src='images/Assets/options.png' onclick='displayOptions()' />";
		echo			"</span>";
		echo		"</div>";
		
		echo		"<div class='col-xs-6 active_pokemon'>";
		echo			"<div class='panel panel-default'>";
		echo				"<div class='panel-heading'>";
		echo 					$_SESSION['battle']['foe']['username'] . "'s ";
									if ( $_SESSION['battle']['foe']['active']['Type'] != "Normal" ) {
										echo	$_SESSION['battle']['foe']['active']['Type'] . $_SESSION['battle']['foe']['active']['Name'];
									} else {
										echo	$_SESSION['battle']['foe']['active']['Name'];
									}
		echo				"</div>";
		echo				"<div class='panel-body'>";
		echo					"<div class='col-xs-6'>";
										if ( $_SESSION['battle']['foe']['active']['Item'] != "" ) {
											echo	"<img src='images/Items/" . $_SESSION['battle']['foe']['active']['Item'] . ".png' style='position: absolute;'/>";
										}
		echo						"<img src='images/Pokemon/" . $_SESSION['battle']['foe']['active']['Type'] . "/" . $_SESSION['battle']['foe']['active']['Pokedex_ID'] . ".png' />";
		echo					"</div>";
		
		echo					"<div class='col-xs-6' style='font-size: 14px; margin-top: 10px;'>";
		echo						"<b>HP:</b> (" . $_SESSION['battle']['foe']['active']['Cur_HP'] . "/" . $_SESSION['battle']['foe']['active']['Max_HP'] . ")";
		echo						"<div class='hp_bar' style='margin-bottom: 10px'>";
		echo							"<span style='width: 120px'></span>";
		echo						"</div>";
										
		echo						"<b>Level:</b> (" . number_format($_SESSION['battle']['foe']['active']['Level']) . ")";
		echo						"<div class='exp_bar'>";
		echo							"<span style='width: 120px'></span>";
		echo						"</div>";
		echo					"</div>";
		echo				"</div>";
		echo			"</div>";
		echo		"</div>";			
		echo	"</div>";
		
		# Echo the active Pokemon's moves IF either Pokemon's HP is above 0.
		if ( $_SESSION['battle']['my']['active']['Cur_HP'] <= 0 || $_SESSION['battle']['foe']['active']['Cur_HP'] <= 0 ) {
			echo	"";
		} else {
			echo	"<div class='row'>";
			echo		"<div class='col-xs-12 moves'>";
			echo			"<div class='panel panel-default'>";
			echo				"<div class='panel-body'>";
										for ( $i = 1; $i <= 4; $i++ ) {
											echo "<button id='Move_" . $i . "'>" . $_SESSION['battle']['my']['active']['Move_' . $i] . "</button> ";
										}
			echo				"</div>";
			echo			"</div>";
			echo		"</div>";
			echo	"</div>";
		}
		
		# Echo the Battle Dialog.
		echo	"<div class='row'>";
		echo		"<div class='col-xs-12 battleDialog'>";
		echo			"<div class='panel panel-default'>";
		echo				"<div class='panel-body' id='battleDialog'>";
		echo					"Please select a move in order to start the battle.";
		echo				"</div>";
		echo			"</div>";
		echo		"</div>";
		echo	"</div>";
	}
	
	# 'move' as well as 'restart' aren't set/being posted.
	else {
		echo	"<b>An error has occurred. Please restart your battle session.</b>";
		mysqli_query($con, "INSERT INTO battle_logs (User_ID, Foe_ID, Move_Used, Coord_X, Coord_Y, Click_Date, Notes) VALUES ('" . $My_Data['id'] . "', '" . $_POST['foe_id'] . "', 'Restart Battle', '" . $_POST['x'] . "', '" . $_POST['y'] . "', '" . $Date . "', 'This user is likely macroing.')");
	}
?>