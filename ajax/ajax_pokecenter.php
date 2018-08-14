<?php
  # This file will handle the majority of the Pokemon Center's AJAX requests that are requested by the user's of the RPG.
	# Unless given direct permission, please do not edit or use this file.
  # Created by Jesse Mack, February 2018.
  # Last update: Feb 11th, 2018.
  
  /* =======================================================================
                                TO-DO LIST
  --------------------------------------------------------------------------
          -> Allow the removal of Pokemon from the user's roster.
  ======================================================================= */
	
	# Require a connection to 'session.php'.
  require '../session.php';
  
  # Get the current date.
  date_default_timezone_set('America/Los_Angeles');
  $Date = date("M dS, Y g:i:s A");
  
  # An AJAX request has been submitted.
  if ( isset($_POST['request']) )
  {
    # Grab the user's database information.
    $User_Data = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM members WHERE id = '" . $row['id'] . "'"));
    
    /* =======================================================================
                        POKEMON CENTER 'ROSTER' TAB
    ======================================================================= */

    # Grab the user's roster.
    if ( $_POST['request'] === 'pokecenter_roster' )
    {
      echo  "<div class='description' style='display: none; margin-bottom: 0px; margin-top: 5px;'></div>";
      echo  "<div class='error' style='display: none; margin-bottom: 0px; margin-top: 5px;'></div>";
              
      echo  "<div class='panel' style='margin-bottom: 5px;'>";
      echo    "<div class='panel-heading'>Roster</div>";
      echo    "<div class='panel-body'>";

      for ( $i = 1; $i <= 6; $i++ ) {
        $Slot[$i] = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM `pokemon` WHERE `Owner_Current` = '" . $User_Data['id'] . "' AND `Slot` = $i"));
        
        # Determine whether or not the slot is empty.
        if ( $Slot[$i] ) {
          $Name = mysqli_fetch_assoc(mysqli_query($con, "SELECT `Name` FROM `pokedex` WHERE `ID` = '" . $Slot[$i]["Pokedex_ID"] . "'"));
          $Slot[$i]['Name'] = $Name['Name'];
        }
        else {
          $Slot[$i] = "Empty";
        }
        
        # If the slot IS NOT empty.
        if ( $Slot[$i] !== "Empty" ) {
          $Item = mysqli_fetch_assoc(mysqli_query($con, "SELECT `Item_Name` FROM `items_owned` WHERE `Equipped_To` = '" . $Slot[$i]['ID'] . "'"));
          
          echo	"<div class='roster_slot'>";
          
          # Echo held item information if necessary.
          if ( $Slot[$i]['Item'] != '0' ) {
            echo	"<img class='item' src='images/Items/" . $Item['Item_Name'] . ".png' />";
          }
            
          # Echo the gender icon.
          if ( $Slot[$i]['Gender'] === 'Female' ) {
            echo	"<img class='gender' src='images/Assets/female.svg' />";
          }
          elseif ( $Slot[$i]['Gender'] === 'Male' ) {
            echo	"<img class='gender' src='images/Assets/male.svg' />";
          }
          else {
            echo $Slot[$i]['Gender'];
          }
            
          # Echo the Pokemon's sprite.
          echo 		"<img src='images/Pokemon/" . $Slot[$i]['Type'] . "/" . $Slot[$i]['Pokedex_ID'] . ".png' /><br />";
          
          # Echo the Pokemon's Name, Level, and Slots.
          # The Pokemon isn't Normal.
          if ( $Slot[$i]['Type'] !== "Normal" ) {
            echo $Slot[$i]['Type'] . $Slot[$i]['Name'] . "<br />";
          }
          # The Pokemon is Normal.
          else {
            echo $Slot[$i]['Name'] . "<br />";
          }
          
          echo 	"<b>Level:</b> " . number_format($Slot[$i]['Level']) . "<br />";
          echo	"<b>Exp:</b> " . number_format($Slot[$i]['Experience']) . "<br />";
            
          for ( $x = 1; $x <= 6; $x++ ) {
            $Slots[$x] = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM `pokemon` WHERE `Owner_Current` = '" . $User_Data['id'] . "' AND `Slot` = $x"));
            
            if ( !$Slots[$x] ) {
              $Slots[$x] = "Empty";
            }
            
            # If the Slot isn't empty.
						if ( $Slots[$x] !== "Empty" ) {
							if ( $x < 7 ) {
								if ( $x % 2 != 0 ) {// if not a multiple of 2
									echo "<a class='slotLink odd' href='javascript:void(0);' onclick='changeSlot(" . $Slots[$i]['ID'] . ", $x);'>$x</a>";
								} else {
									echo "<a class='slotLink even' href='javascript:void(0);' onclick='changeSlot(" . $Slots[$i]['ID'] . ", $x);'>$x</a>";
								}
							}
						}
						# If the Slot is empty.
						else {
							if ( $x < 7 ) {
								if ( $x % 2 != 0 ) {
									echo "<a class='slotLink odd void' href='#'>x</a>";
								} else {
									echo "<a class='slotLink even void' href='#'>x</a>";
								}
							}
						}
          }
            
          echo		"<a style='border-top: 1px solid #4A618F; display: block; padding: 2px; width: 100%;' href='javascript:void(0);' onclick='changeSlot({$Slots[$i]['ID']}, 7);'>Remove</a>";
          echo	"</div>";
        } 
        # Else the slot IS empty.
        else {
          echo "<div class='roster_slot' style='float: left; padding-top: 50px;'>";
          echo		"<img src='images/Assets/pokeball.png' /><br />";
          echo		"Empty";
          echo "</div>";
        }
      }

      echo    "</div>";
      echo  "</div>";

      echo  "<div class='panel' style='float: left; margin-right: 1%; width: 35.5%;'>";
      echo    "<div class='panel-heading'>Boxed Pokemon</div>";
      echo    "<div class='panel-body boxed_pokemon'>";
      
      $Query_Box = mysqli_query($con, "SELECT * FROM `pokemon` WHERE `slot` = '7' AND `Owner_Current` = '" . $User_Data['id'] . "'");

      while ( $rows = mysqli_fetch_assoc($Query_Box) ) {
        echo "<img src='images/Icons/{$rows['Type']}/" . $rows['Pokedex_ID'] . ".png' onclick='showPokemon(" . $rows['ID'] . ");' />";
      }
      
      if ( mysqli_num_rows($Query_Box) == 0 ) {
        echo	"<div style='padding: 5px;'>There are no Pokemon in your box.</div>";
      }

      echo    "</div>";
      echo  "</div>";

      echo  "<div class='panel' style='float: left; width: 63.5%;'>";
      echo    "<div class='panel-heading'>Selected Pokemon</div>";
      echo    "<div class='panel-body' id='selectedPokemon'>";
      echo      "<div style='padding: 10px;'>Please select a Pokemon.</div>";
      echo    "</div>";
      echo  "</div>";
    }

    # Display a boxed Pokemon's stats.
    if ( $_POST['request'] === 'pokemon_statistics' ) 
    {
			$Pokemon_Data = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM `pokemon` WHERE `id` = '" . $_POST['id'] . "' AND `Owner_Current` = '" . $User_Data['id'] . "'"));
			$Pokemon_Item = mysqli_fetch_assoc(mysqli_query($con, "SELECT `Item_Name` FROM `items_owned` WHERE `Equipped_To` = '" . $Pokemon_Data['ID'] . "'"));
			$Pokedex_Data = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM `pokedex` WHERE `id` = '" . $Pokemon_Data['Pokedex_ID'] . "'"));
			
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
								
			echo 			"<img src='images/Pokemon/" . $Pokemon_Data['Type'] . "/" . $Pokemon_Data['Pokedex_ID'] . ".png' /><br />";
			echo			"<b>";
								if ( $Pokemon_Data['Type'] != "Normal" ) {
									echo $Pokemon_Data['Type'] . $Pokedex_Data['Name'] . "<br />";
								} else {
									echo $Pokedex_Data['Name'] . "<br />";
								}
			echo			"</b>";
      echo 			"<b>Level:</b> " . number_format($Pokemon_Data['Level']) . "<br />";
      echo      "<b>Exp:</b> " . number_format($Pokemon_Data['Experience']);
			
			echo			"<div style='padding-bottom: 5px; padding-top: 5px'>";
      
      for ( $i = 1; $i <= 6; $i++ )
      {
        $Slot[$i] = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM pokemon WHERE Owner_Current = '" . $User_Data['id'] . "' AND Slot = $i"));

        if ( $Slot[$i] ) 
        {
          $Name = mysqli_fetch_assoc(mysqli_query($con, "SELECT Name FROM pokedex WHERE ID = '" . $Slot[$i]["Pokedex_ID"] . "'"));
          $Slot[$i]['Name'] = $Name['Name'];
        }
        else 
        {
          $Slot[$i] = "Empty";
        }

        if ( $Slot[$i] !== "Empty" )
        {
          echo "<img class='pokemonSlot' src='images/Icons/{$Slot[$i]['Type']}/{$Slot[$i]['Pokedex_ID']}.png' onclick='changeSlot({$_POST['id']}, $i);' />";
        }
        else 
        {
          echo "<img class='pokemonSlot' style='height: 32px; width: 32px;' src='images/Assets/Pokeball.png' onclick='changeSlot({$_POST['id']}, $i);' />";
        }
      }

			echo			"</div>";
			echo		"</div>";
			
			echo		"<div style='float: left; margin-top: 8px; padding-top: 3px; width: 306px'>";
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
    
    # Changing roster slots.
    if ( $_POST['request'] === 'slot_change' ) {
			# Pokemon data for the slot you're moving from.
			$Pokemon_One = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM `pokemon` WHERE `id` = '" .  $_POST['id'] . "' AND `Owner_Current` = '" . $User_Data['id'] . "'"));
			$Pokedex_One = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM `pokedex` WHERE `id` = '" . $Pokemon_One['Pokedex_ID'] . "'"));
			
			# Pokemon data for the slot you're moving to.
			$Pokemon_Two = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM `pokemon` WHERE `Slot` = '" .  $_POST['slot'] . "' AND `Owner_Current` = '" . $User_Data['id'] . "'"));
			$Pokedex_Two = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM `pokedex` WHERE `id` = '" . $Pokemon_Two['Pokedex_ID'] . "'"));
      
      # Moving the Pokemon into the same slot.
      if ( $Pokemon_One['Slot'] === $_POST['slot'] )
      {
        if ( $Pokemon_One['Type'] !== "Normal" ) {
          echo "<div class='description' style='border-color: #ff0000; margin-top: 0px; margin-bottom: 5px; padding: 5px; width: 100%;'>Your <b>{$Pokemon_One['Type']}{$Pokedex_One['Name']}</b> is already in slot {$_POST['slot']}.</div>";
        } else {
          echo "<div class='description' style='border-color: #ff0000; margin-top: 0px; margin-bottom: 5px; padding: 5px; width: 100%;'>Your <b>{$Pokedex_One['Name']}</b> is already in Slot {$_POST['slot']}.</div>";
        }
      }

      # Removing the Pokemon from your roster.
      else if ( $_POST['slot'] === '7' )
      {
        echo "<div class='description' style='border-color: #00ff00; margin-top: 0px; margin-bottom: 5px; padding: 5px; width: 100%;'>";
        if ( $Pokemon_One['Type'] !== "Normal" ) {
          echo "Your <b>{$Pokemon_One['Type']}{$Pokedex_One['Name']}</b> has been moved to your box.";
        } else {
          echo "Your <b>{$Pokedex_One['Name']}</b> has been moved to your box.";
        }
        echo "</div>";

        if ( $Pokemon_One['ID'] === null ) {
          # If the slot IS empty, loop through any previous slots to determine if they are empty as well.
          for ( $i = $_POST['slot']; $i >= 1; $i-- ) {
            $Check_Slots = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM `pokemon` WHERE `Slot` = '" . $i . "' AND `Owner_Current` = '" . $User_Data['id'] . "'"));

            # Looping through all previous slots to find out which ones are empty.
            if ( $Check_Slots['ID'] === null ) {
              # Get the very first empty slot.
              $Empty_Slot = $i;
            }
          }
          
          # Update the `pokemon` database table with the updated slot change(s).
        } else {
          # Update the `pokemon` database table with the updated slot change(s).
          mysqli_query($con, "UPDATE pokemon SET Slot = '" . $_POST['slot'] . "' WHERE id = '" . $Pokemon_One['ID'] . "' AND Owner_Current = '" . $User_Data['id'] . "'");

          # Check for empty slots in between Pokemon.
          for ( $i = $_POST['slot']; $i >= 1; $i-- ) {
            $Check_Slots = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM `pokemon` WHERE `Slot` = '" . $i . "' AND `Owner_Current` = '" . $User_Data['id'] . "'"));

            # Looping through all previous slots to find out which ones are empty.
            if ( $Check_Slots['ID'] === null ) {
              # Get the very first empty slot.
              $Empty_Slot = "Slot: " . $i . "<br />";

              # Move Pokemon over one space to the left.
            }
          }

          mysqli_query($con, "UPDATE pokemon SET Slot = '7' WHERE id = '" . $Pokemon_One['ID'] . "' AND Owner_Current = '" . $User_Data['id'] . "'");

          /*
          echo "pokemon_one's id wasn't null<br />";
          echo "Pokemon One's Slot = {$Pokemon_One['Slot']}<br />";
          echo "Pokemon_One = {$Pokemon_One['ID']}<br />";
          echo "_POST['slot'] = {$_POST['slot']}";
          */
        }
      }

      # Moving the Pokemon to a different slot.
      else
      {
        # Check to see if the slot you're moving the Pokemon to is empty.
        if ( $Pokemon_Two['ID'] === null ) {
          # If the slot IS empty, loop through any previous slots to determine if they are empty as well.
          for ( $i = $_POST['slot']; $i >= 1; $i-- ) {
            $Check_Slots = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM `pokemon` WHERE `Slot` = '" . $i . "' AND `Owner_Current` = '" . $User_Data['id'] . "'"));

            # Looping through all previous slots to find out which ones are empty.
            if ( $Check_Slots['ID'] === null ) {
              # Get the very first empty slot.
              $Empty_Slot = $i;
            }
          }
          
          # Update the `pokemon` database table with the updated slot change(s).
          mysqli_query($con, "UPDATE pokemon SET Slot = '" . $Empty_Slot . "' WHERE id = '" . $Pokemon_One['ID'] . "' AND Owner_Current = '" . $User_Data['id'] . "'");
        } else {
          # Update the `pokemon` database table with the updated slot change(s).
          mysqli_query($con, "UPDATE pokemon SET Slot = '" . $_POST['slot'] . "' WHERE id = '" . $Pokemon_One['ID'] . "' AND Owner_Current = '" . $User_Data['id'] . "'");
          mysqli_query($con, "UPDATE pokemon SET Slot = '" . $Pokemon_One['Slot'] . "' WHERE id = '" . $Pokemon_Two['ID'] . "' AND Owner_Current = '" . $User_Data['id'] . "'");
        }

        # Echo some success dialog.
        echo 	"<div class='description' style='border-color: #00ff00; margin-top: 0px; margin-bottom: 5px; padding: 5px; width: 100%;'>";

        if ( $Pokemon_One['Type'] !== "Normal" ) {
          echo "Your <b>{$Pokemon_One['Type']}{$Pokedex_One['Name']}</b> has been moved to slot {$_POST['slot']}.";          
        } else {
          echo "Your <b>{$Pokedex_One['Name']}</b> has been moved to slot {$_POST['slot']}.";
        }
                  
        echo 	"</div>";
      }
      
      echo  "<div class='panel' style='margin-bottom: 5px;'>";
      echo    "<div class='panel-heading'>Roster</div>";
      echo    "<div class='panel-body'>";

      for ( $i = 1; $i <= 6; $i++ ) {
        $Slot[$i] = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM `pokemon` WHERE `Owner_Current` = '" . $User_Data['id'] . "' AND `Slot` = $i"));
        
        # Determine whether or not the slot is empty.
        if ( $Slot[$i] ) {
          $Name = mysqli_fetch_assoc(mysqli_query($con, "SELECT `Name` FROM `pokedex` WHERE `ID` = '" . $Slot[$i]["Pokedex_ID"] . "'"));
          $Slot[$i]['Name'] = $Name['Name'];
        }
        else {
          $Slot[$i] = "Empty";
        }
        
        # If the slot IS NOT empty.
        if ( $Slot[$i] !== "Empty" ) {
          $Item = mysqli_fetch_assoc(mysqli_query($con, "SELECT `Item_Name` FROM `items_owned` WHERE `Equipped_To` = '" . $Slot[$i]['ID'] . "'"));
          
          echo	"<div class='roster_slot'>";
          
          # Echo held item information if necessary.
          if ( $Slot[$i]['Item'] != '0' ) {
            echo	"<img class='item' src='images/Items/" . $Item['Item_Name'] . ".png' />";
          }
            
          # Echo the gender icon.
          if ( $Slot[$i]['Gender'] === 'Female' ) {
            echo	"<img class='gender' src='images/Assets/female.svg' />";
          }
          elseif ( $Slot[$i]['Gender'] === 'Male' ) {
            echo	"<img class='gender' src='images/Assets/male.svg' />";
          }
          else {
            echo $Slot[$i]['Gender'];
          }
            
          # Echo the Pokemon's sprite.
          echo 		"<img src='images/Pokemon/" . $Slot[$i]['Type'] . "/" . $Slot[$i]['Pokedex_ID'] . ".png' /><br />";
          
          # Echo the Pokemon's Name, Level, and Slots.
          # The Pokemon isn't Normal.
          if ( $Slot[$i]['Type'] !== "Normal" ) {
            echo $Slot[$i]['Type'] . $Slot[$i]['Name'] . "<br />";
          }
          # The Pokemon is Normal.
          else {
            echo $Slot[$i]['Name'] . "<br />";
          }
          
          echo 	"<b>Level:</b> " . number_format($Slot[$i]['Level']) . "<br />";
          echo	"<b>Exp:</b> " . number_format($Slot[$i]['Experience']) . "<br />";
            
          for ( $x = 1; $x <= 6; $x++ ) {
            $Slots[$x] = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM `pokemon` WHERE `Owner_Current` = '" . $User_Data['id'] . "' AND `Slot` = $x"));
            
            if ( !$Slots[$x] ) {
              $Slots[$x] = "Empty";
            }
            
            # If the Slot isn't empty.
						if ( $Slots[$x] !== "Empty" ) {
							if ( $x < 7 ) {
								if ( $x % 2 != 0 ) {// if not a multiple of 2
									echo "<a class='slotLink odd' href='javascript:void(0);' onclick='changeSlot(" . $Slots[$i]['ID'] . ", $x);'>$x</a>";
								} else {
									echo "<a class='slotLink even' href='javascript:void(0);' onclick='changeSlot(" . $Slots[$i]['ID'] . ", $x);'>$x</a>";
								}
							}
						}
						# If the Slot is empty.
						else {
							if ( $x < 7 ) {
								if ( $x % 2 != 0 ) {
									echo "<a class='slotLink odd void' href='#'>x</a>";
								} else {
									echo "<a class='slotLink even void' href='#'>x</a>";
								}
							}
						}
          }
          
          echo		"<a style='border-top: 1px solid #4A618F; display: block; padding: 2px; width: 100%;' href='javascript:void(0);' onclick='changeSlot({$Slots[$i]['ID']}, 7);'>Remove</a>";
          echo	"</div>";
        } 
        # Else the slot IS empty.
        else {
          echo "<div class='roster_slot' style='float: left; padding-top: 50px;'>";
          echo		"<img src='images/Assets/pokeball.png' /><br />";
          echo		"Empty";
          echo "</div>";
        }
      }

      echo    "</div>";
      echo  "</div>";

      echo  "<div class='panel' style='float: left; margin-right: 1%; width: 35.5%;'>";
      echo    "<div class='panel-heading'>Boxed Pokemon</div>";
      echo    "<div class='panel-body boxed_pokemon'>";
      
      $Query_Box = mysqli_query($con, "SELECT * FROM `pokemon` WHERE `slot` = '7' AND `Owner_Current` = '" . $User_Data['id'] . "'");

      while ( $rows = mysqli_fetch_assoc($Query_Box) ) {
        echo "<img src='images/Icons/{$rows['Type']}/" . $rows['Pokedex_ID'] . ".png' onclick='showPokemon(" . $rows['ID'] . ");' />";
      }
      
      if ( mysqli_num_rows($Query_Box) == 0 ) {
        echo	"<div style='padding: 5px;'>There are no Pokemon in your box.</div>";
      }

      echo    "</div>";
      echo  "</div>";

      echo  "<div class='panel' style='float: left; width: 63.5%;'>";
      echo    "<div class='panel-heading'>Selected Pokemon</div>";
      echo    "<div class='panel-body' id='selectedPokemon'>";
      echo      "<div style='padding: 10px;'>Please select a Pokemon.</div>";
      echo    "</div>";
      echo  "</div>";
    }
    
    /* =======================================================================
                          POKEMON CENTER 'BAG' TAB
    ======================================================================= */
    if ( $_POST['request'] === 'pokecenter_bag' )
    {
			# Verify that the user is only trying to browse their bag.
			if ( $_POST['id'] === $User_Data['id'] ) {
        echo		"<div class='panel panel-default' style='float: left; margin-bottom: 0px; margin-right: 1%; min-height: 135px; width: 49.5%;'>";
				echo			"<div class='panel-heading'>Attached Items</div>";
        echo			"<div class='panel-body' style='align-items: center;  padding: 5px;'>";

        $Check_Equipped = mysqli_query($con, "SELECT * FROM items_owned WHERE Owner_Current = '" . $User_Data['id'] . "' AND Equipped_To > 0");
        while ( $Query = mysqli_fetch_assoc($Check_Equipped) ) {
          $Check_Pokemon = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM pokemon WHERE Item = '" . $Query['id'] . "'"));
          $Check_Pokedex = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM pokedex WHERE id = '" . $Check_Pokemon['Pokedex_ID'] . "'"));
          
          echo	"<div class='panel panel-default equippedPanel' style='width: 19.5%;'>";
          echo		"<div class='panel-heading' style='background: #444'>";
                    if ( $Check_Pokemon['Type'] != "Normal" ) {
                      echo $Check_Pokemon['Type'] . $Check_Pokedex['Name'];
                    } else {
                      echo $Check_Pokedex['Name'];
                    }
          echo		"</div>";
          echo    "<div class='panel-body'>";
          echo    	"<div>";
          echo        "<img style='border-right: 1px solid #4A618F;' src='images/Icons/{$Check_Pokemon['Type']}/{$Check_Pokemon['Pokedex_ID']}.png' />";
          echo        "<img style='border-right: 1px solid #4A618F; padding: 3px 3px;' src='images/Items/{$Query['Item_Name']}.png' />";
          echo        "<img style='border-right: 1px solid #4A618F; height: 30px; padding: 3px; width: 28px;' src='images/Assets/{$Check_Pokemon['Gender']}.svg' />";
          echo        "<div style='float: right; margin-top: 5px; width: 105px;'>";
          echo          "Level: " . number_format($Check_Pokemon['Level']);
          echo        "</div>";
          echo      "</div>";
          echo      "<div style='border-top: 1px solid #4A618F; padding: 2px; text-align: center;'>";
          echo        "<a href='javascript:void(0);' onclick='removeItem({$Query['id']});'>Remove {$Query['Item_Name']}</a>";
          echo      "</div>";
          echo    "</div>";
          echo	"</div>";
        }

        if ( mysqli_num_rows($Check_Equipped) == 0 ) {
          echo "<div style='padding-top: 35px;'>None of your Pokemon have an item equipped.</div>";
        }

				echo			"</div>";
        echo		"</div>";
        
        echo  "<div class='panel' style='float: right; margin-bottom: 5px; width: 49.5%;'>";
        echo    "<div class='panel-heading'>Selected Item</div>";
        echo    "<div class='panel-body' id='selectedItem' style='padding: 7px'>";
        echo      "Select an item.";
        echo    "</div>";
        echo  "</div>";

        echo  "<div class='panel' style='float: right; width: 49.5%;'>";
        echo    "<div class='panel-heading'>Inventory</div>";
        echo    "<div class='panel-body items' style='padding: 5px;'>";

        $Get_Items = mysqli_query($con, "SELECT * FROM items_owned WHERE Owner_Current = '" . $User_Data['id'] . "' AND Equipped_To = '0'");
										
				while ( $Query = mysqli_fetch_assoc($Get_Items) ) {
          $Items_Ref = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM items WHERE Item_ID = '" . $Query['Item_ID'] . "'"));
											
          echo	"<img src='images/Items/" . $Query['Item_Name'] . ".png' onclick='selectItem(" . $Query['id'] . ");' />";
				}
										
				if ( mysqli_num_rows($Get_Items) === 0 ) {
					echo	"<div style='padding: 2px;'>There are no items in your inventory.</div>";
				}

        echo    "</div>";
        echo  "</div>";
			} else {
				echo "This user's bag doesn't belong to you.";
			}
    }
    
    # Selecting an item.
    if ( $_POST['request'] === 'pokecenter_item' )
    {
			$Item_Data = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM `items_owned` WHERE `id` = '" . $_POST['id'] . "'"));
			$Item_Description = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM `items` WHERE `Item_ID` = '" . $Item_Data['Item_ID'] . "'"));
			
			if ( $Item_Data['Owner_Current'] === $User_Data['id'] ) {
        echo  "<img style='float: left; margin-top: 20px;' src='images/Items/{$Item_Data['Item_Name']}.png' />";
        echo	"<b>{$Item_Data['Item_Name']}</b><br />";
        echo	"<i>{$Item_Description['Item_Description']}</i><br />";
        
        for ( $i = 1; $i <= 6; $i++ ) {
					$Get_Roster_Data = mysqli_query($con, "SELECT * FROM pokemon WHERE Owner_Current = '" . $User_Data['id'] . "' AND slot = $i AND item = '0'");
					$Slot_Data[$i] = mysqli_fetch_assoc($Get_Roster_Data);
									
					if ( $Slot_Data[$i] ) {
						$Pokedex_Data = mysqli_fetch_assoc(mysqli_query($con, "SELECT Name FROM pokedex WHERE ID = " . $Slot_Data[$i]["Pokedex_ID"]));
						$Slot_Data[$i]['Name'] = $Pokedex_Data['Name'];
					} else {
						$Slot_Data[$i] = "Empty";
					}
					
					if ( $Slot_Data[$i] != "Empty" ) {
            echo "<img class='pokemonSlot' src='images/Icons/{$Slot_Data[$i]['Type']}/{$Slot_Data[$i]['Pokedex_ID']}.png' onclick='attachItem({$Item_Data['id']}, $i);' />";
          }
        }
			} else {
        echo "This item does not belong to you.";
			}
    }
    
    # Removing equipped items.
    if ( $_POST['request'] === 'pokecenter_removeitem' )
    {
			$Item_Data =    mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM items_owned WHERE id = '" . $_POST['id'] . "' AND Owner_Current = '" . $User_Data['id'] . "'"));
			$Pokemon_Data = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM pokemon WHERE Item = '" . $_POST['id'] . "' AND Owner_Current = '" . $User_Data['id'] . "'"));
      $Pokedex_Data = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM pokedex WHERE id = '" . $Pokemon_Data['Pokedex_ID'] . "'"));

			if ( $Item_Data['Owner_Current'] === $User_Data['id'] ) {
				if ( $_POST['id'] === $Item_Data['id'] ) {
          echo  "<div class='description' style='border-color: #00ff00; margin-bottom: 5px; margin-top: 0px; width: 100%;'>";
					if ( $Pokemon_Data['Type'] != "Normal" ) {
						echo  "The <b>{$Item_Data['Item_Name']}</b> has been removed from your <b>{$Pokemon_Data['Type']}{$Pokedex_Data['Name']}</b>.";
					} else {
						echo  "The <b>{$Item_Data['Item_Name']}</b> has been removed from your <b>{$Pokedex_Data['Name']}</b>.";
          }
          echo  "</div>";
					
					mysqli_query($con, "UPDATE pokemon SET Item = '0' WHERE `id` = '" . $Pokemon_Data['ID'] . "'");
					mysqli_query($con, "UPDATE items_owned SET Equipped_To = '0' WHERE Equipped_To = '" . $Pokemon_Data['ID'] . "'");
					
					echo		"<div class='panel panel-default' style='float: left; margin-bottom: 0px; margin-right: 1%; min-height: 129px; width: 49.5%;'>";
          echo			"<div class='panel-heading'>Attached Items</div>";
          echo			"<div class='panel-body' style='padding: 5px;'>";

          $Check_Equipped = mysqli_query($con, "SELECT * FROM items_owned WHERE Owner_Current = '" . $User_Data['id'] . "' AND Equipped_To > '0'");
          while ( $Query = mysqli_fetch_assoc($Check_Equipped) ) {
            $Check_Pokemon = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM pokemon WHERE Item = '" . $Query['id'] . "'"));
            $Check_Pokedex = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM pokedex WHERE id = '" . $Check_Pokemon['Pokedex_ID'] . "'"));
            
            echo	"<div class='panel panel-default equippedPanel' style='width: 19.5%;'>";
            echo		"<div class='panel-heading' style='background: #444'>";
                      if ( $Check_Pokemon['Type'] != "Normal" ) {
                        echo $Check_Pokemon['Type'] . $Check_Pokedex['Name'];
                      } else {
                        echo $Check_Pokedex['Name'];
                      }
            echo		"</div>";
            echo    "<div class='panel-body'>";
            echo    	"<div>";
            echo        "<img style='border-right: 1px solid #4A618F;' src='images/Icons/{$Check_Pokemon['Type']}/{$Check_Pokemon['Pokedex_ID']}.png' />";
            echo        "<img style='border-right: 1px solid #4A618F; padding: 3px 3px;' src='images/Items/{$Query['Item_Name']}.png' />";
            echo        "<img style='border-right: 1px solid #4A618F; height: 30px; padding: 3px; width: 28px;' src='images/Assets/{$Check_Pokemon['Gender']}.svg' />";
            echo        "<div style='float: right; margin-top: 5px; width: 105px;'>";
            echo          "Level: " . number_format($Check_Pokemon['Level']);
            echo        "</div>";
            echo      "</div>";
            echo      "<div style='border-top: 1px solid #4A618F; padding: 2px; text-align: center;'>";
            echo        "<a href='javascript:void(0);' onclick='removeItem({$Query['id']});'>Remove {$Query['Item_Name']}</a>";
            echo      "</div>";
            echo    "</div>";
            echo	"</div>";
          }

          if ( mysqli_num_rows($Check_Equipped) == 0 ) {
            echo "None of your Pokemon have an item equipped.";
          }

          echo			"</div>";
          echo		"</div>";
          
          echo  "<div class='panel' style='float: right; margin-bottom: 5px; width: 49.5%;'>";
          echo    "<div class='panel-heading'>Selected Item</div>";
          echo    "<div class='panel-body' id='selectedItem' style='padding: 7px'>";
          echo      "Select an item.";
          echo    "</div>";
          echo  "</div>";

          echo  "<div class='panel' style='float: right; width: 49.5%;'>";
          echo    "<div class='panel-heading'>Inventory</div>";
          echo    "<div class='panel-body items' style='padding: 5px;'>";

          $Get_Items = mysqli_query($con, "SELECT * FROM items_owned WHERE Owner_Current = '" . $User_Data['id'] . "' AND Equipped_To = '0'");
                      
          while ( $Query = mysqli_fetch_assoc($Get_Items) ) {
            $Items_Ref = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM items WHERE Item_ID = '" . $Query['Item_ID'] . "'"));
                        
            echo	"<img src='images/Items/" . $Query['Item_Name'] . ".png' onclick='selectItem(" . $Query['id'] . ");' />";
          }
                      
          if ( mysqli_num_rows($Get_Items) === 0 ) {
            echo	"<div style='padding: 2px;'>There are no items in your inventory.</div>";
          }

          echo    "</div>";
          echo  "</div>";
				} else {
					echo	"<div class='description' style='border-color: #ff0000; margin-bottom: 5px; margin-top: 0px; width: 100%;'>This item doesn't belong to you.</div>";
					
					echo		"<div class='panel panel-default' style='float: left; margin-bottom: 0px; margin-right: 1%; min-height: 129px; width: 49.5%;'>";
          echo			"<div class='panel-heading'>Attached Items</div>";
          echo			"<div class='panel-body' style='padding: 5px;'>";

          $Check_Equipped = mysqli_query($con, "SELECT * FROM items_owned WHERE Owner_Current = '" . $User_Data['id'] . "' AND Equipped_To > '0'");
          while ( $Query = mysqli_fetch_assoc($Check_Equipped) ) {
            $Check_Pokemon = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM pokemon WHERE Item = '" . $Query['id'] . "'"));
            $Check_Pokedex = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM pokedex WHERE id = '" . $Check_Pokemon['Pokedex_ID'] . "'"));
            
            echo	"<div class='panel panel-default equippedPanel' style='width: 19.5%;'>";
            echo		"<div class='panel-heading' style='background: #444'>";
                      if ( $Check_Pokemon['Type'] != "Normal" ) {
                        echo $Check_Pokemon['Type'] . $Check_Pokedex['Name'];
                      } else {
                        echo $Check_Pokedex['Name'];
                      }
            echo		"</div>";
            echo    "<div class='panel-body'>";
            echo    	"<div>";
            echo        "<img style='border-right: 1px solid #4A618F;' src='images/Icons/{$Check_Pokemon['Type']}/{$Check_Pokemon['Pokedex_ID']}.png' />";
            echo        "<img style='border-right: 1px solid #4A618F; padding: 3px 3px;' src='images/Items/{$Query['Item_Name']}.png' />";
            echo        "<img style='border-right: 1px solid #4A618F; height: 30px; padding: 3px; width: 28px;' src='images/Assets/{$Check_Pokemon['Gender']}.svg' />";
            echo        "<div style='float: right; margin-top: 5px; width: 105px;'>";
            echo          "Level: " . number_format($Check_Pokemon['Level']);
            echo        "</div>";
            echo      "</div>";
            echo      "<div style='border-top: 1px solid #4A618F; padding: 2px; text-align: center;'>";
            echo        "<a href='javascript:void(0);' onclick='removeItem({$Query['id']});'>Remove {$Query['Item_Name']}</a>";
            echo      "</div>";
            echo    "</div>";
            echo	"</div>";
          }

          if ( mysqli_num_rows($Check_Equipped) == 0 ) {
            echo "None of your Pokemon have an item equipped.";
          }

          echo			"</div>";
          echo		"</div>";
          
          echo  "<div class='panel' style='float: right; margin-bottom: 5px; width: 49.5%;'>";
          echo    "<div class='panel-heading'>Selected Item</div>";
          echo    "<div class='panel-body' id='selectedItem' style='padding: 7px'>";
          echo      "Select an item.";
          echo    "</div>";
          echo  "</div>";

          echo  "<div class='panel' style='float: right; width: 49.5%;'>";
          echo    "<div class='panel-heading'>Inventory</div>";
          echo    "<div class='panel-body items' style='padding: 5px;'>";

          $Get_Items = mysqli_query($con, "SELECT * FROM items_owned WHERE Owner_Current = '" . $User_Data['id'] . "' AND Equipped_To = '0'");
                      
          while ( $Query = mysqli_fetch_assoc($Get_Items) ) {
            $Items_Ref = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM items WHERE Item_ID = '" . $Query['Item_ID'] . "'"));
                        
            echo	"<img src='images/Items/" . $Query['Item_Name'] . ".png' onclick='selectItem(" . $Query['id'] . ");' />";
          }
                      
          if ( mysqli_num_rows($Get_Items) === 0 ) {
            echo	"<div style='padding: 2px;'>There are no items in your inventory.</div>";
          }

          echo    "</div>";
          echo  "</div>";
				}
			}
    }
    
    if ( $_POST['request'] === 'pokecenter_attachitem' )
    {
      # Verify that the user requesting this data actually owns this item.
      $Item_Data = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM items_owned WHERE id = '" . $_POST['id'] . "'"));
            
      # Verify that the user owns the Pokemon he's attemping to attach the item to.
      $Pokemon_Data = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM pokemon WHERE Slot = '" . $_POST['slot'] . "'"));
      $Pokedex_Data = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM pokedex WHERE id = '" . $Pokemon_Data['Pokedex_ID'] . "'"));

      if ( $Item_Data['Owner_Current'] == $User_Data['id'] ) {
				if ( $Pokemon_Data['Owner_Current'] == $User_Data['id'] ) {
          mysqli_query($con, "UPDATE items_owned SET Equipped_To = '" . $Pokemon_Data['ID'] . "' WHERE Owner_Current = '" . $User_Data['id'] . "' AND id = '" . $Item_Data['id'] . "'");
          mysqli_query($con, "UPDATE pokemon SET Item = '" . $Item_Data['id'] . "' WHERE id = '" . $Pokemon_Data['ID'] . "' AND Slot = '" . $_POST['slot'] . "'");
							
					if ( $Pokemon_Data['Type'] != "Normal" ) {
						echo "<div class='description' style='border-color: #00ff00; margin-bottom: 5px; margin-top: 0px; width: 100%;'>The <b>{$Item_Data['Item_Name']}</b> has been attached to your <b>{$Pokemon_Data['Type']}{$Pokedex_Data['Name']}</b>.</div>";
					} else {
						echo "<div class='description' style='border-color: #00ff00; margin-bottom: 5px; margin-top: 0px; width: 100%;'>The <b>{$Item_Data['Item_Name']}</b> has been attached to your <b>{$Pokedex_Data['Name']}</b>.</div>";
					}
				} else {
					echo "<div class='description' style='border-color: #ff0000; margin-bottom: 5px; margin-top: 0px; width: 100%;'>The Pokemon that you attemped to attach the item to doesn't belong to you.</div>";
				}
			} else {
					echo "<div class='description' style='border-color: #ff0000; margin-bottom: 5px; margin-top: 0px; width: 100%;'>The item that you attemped to attach the Pokemon to doesn't belong to you.</div>";
      }
      
      echo		"<div class='panel panel-default' style='float: left; margin-bottom: 0px; margin-right: 1%; min-height: 129px; width: 49.5%;'>";
				echo			"<div class='panel-heading'>Attached Items</div>";
        echo			"<div class='panel-body' style='padding: 5px;'>";

        $Check_Equipped = mysqli_query($con, "SELECT * FROM items_owned WHERE Owner_Current = '" . $User_Data['id'] . "' AND Equipped_To > '0'");
        while ( $Query = mysqli_fetch_assoc($Check_Equipped) ) {
          $Check_Pokemon = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM pokemon WHERE Item = '" . $Query['id'] . "'"));
          $Check_Pokedex = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM pokedex WHERE id = '" . $Check_Pokemon['Pokedex_ID'] . "'"));
          
          echo	"<div class='panel panel-default equippedPanel' style='width: 19.5%;'>";
          echo		"<div class='panel-heading' style='background: #444'>";
                    if ( $Check_Pokemon['Type'] != "Normal" ) {
                      echo $Check_Pokemon['Type'] . $Check_Pokedex['Name'];
                    } else {
                      echo $Check_Pokedex['Name'];
                    }
          echo		"</div>";
          echo    "<div class='panel-body'>";
          echo    	"<div>";
          echo        "<img style='border-right: 1px solid #4A618F;' src='images/Icons/{$Check_Pokemon['Type']}/{$Check_Pokemon['Pokedex_ID']}.png' />";
          echo        "<img style='border-right: 1px solid #4A618F; padding: 3px 3px;' src='images/Items/{$Query['Item_Name']}.png' />";
          echo        "<img style='border-right: 1px solid #4A618F; height: 30px; padding: 3px; width: 28px;' src='images/Assets/{$Check_Pokemon['Gender']}.svg' />";
          echo        "<div style='float: right; margin-top: 5px; width: 105px;'>";
          echo          "Level: " . number_format($Check_Pokemon['Level']);
          echo        "</div>";
          echo      "</div>";
          echo      "<div style='border-top: 1px solid #4A618F; padding: 2px; text-align: center;'>";
          echo        "<a href='javascript:void(0);' onclick='removeItem({$Query['id']});'>Remove {$Query['Item_Name']}</a>";
          echo      "</div>";
          echo    "</div>";
          echo	"</div>";
        }

        if ( mysqli_num_rows($Check_Equipped) == 0 ) {
          echo "<div style='padding-top: 35px;'>None of your Pokemon have an item equipped.</div>";
        }

				echo			"</div>";
        echo		"</div>";
        
        echo  "<div class='panel' style='float: right; margin-bottom: 5px; width: 49.5%;'>";
        echo    "<div class='panel-heading'>Selected Item</div>";
        echo    "<div class='panel-body' id='selectedItem' style='padding: 7px'>";
        echo      "Select an item.";
        echo    "</div>";
        echo  "</div>";

        echo  "<div class='panel' style='float: right; width: 49.5%;'>";
        echo    "<div class='panel-heading'>Inventory</div>";
        echo    "<div class='panel-body items' style='padding: 5px;'>";

        $Get_Items = mysqli_query($con, "SELECT * FROM items_owned WHERE Owner_Current = '" . $User_Data['id'] . "' AND Equipped_To = '0'");
										
				while ( $Query = mysqli_fetch_assoc($Get_Items) ) {
          $Items_Ref = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM items WHERE Item_ID = '" . $Query['Item_ID'] . "'"));
											
          echo	"<img src='images/Items/" . $Query['Item_Name'] . ".png' onclick='selectItem(" . $Query['id'] . ");' />";
				}
										
				if ( mysqli_num_rows($Get_Items) === 0 ) {
					echo	"<div style='padding: 2px;'>There are no items in your inventory.</div>";
				}

        echo    "</div>";
        echo  "</div>";
    }

    # Display the nickname tab.
    if ( $_POST['request'] === 'pokecenter_nickname' )
    {
			$Active_Data = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM pokemon WHERE Slot = '1' AND Owner_Current = '" . $User_Data['id'] . "'"));
			$Active_Name = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM pokedex WHERE id = '" . $Active_Data['Pokedex_ID'] . "'"));
			
			echo		"<div class='panel panel-default' style='margin: 0px 0px 0px 0px'>";
			echo			"<div class='panel-heading'>Nickname Your Pokemon</div>";
			echo			"<div class='panel-body'>";
			echo				"<div class='description' style='margin-bottom: 5px;'>";
			echo					"Here, you may set the nickname of your Pokemon.<br />";
      echo					"Please note that any inappropriate or profane names are not allowed.<br /><br />";
      echo          "<i>Changing your Pokemon's nickname will cost $1,000.";
      echo				"</div>";
      echo        "<div class='panel nickContainer' style='margin: 0 auto 3px; width: 99.5%;'>";
      echo          "<div class='panel-heading'>Roster</div>";
      echo          "<div class='panel-body'>";

      for ( $i = 1; $i <= 6; $i++ )
      {
        $Roster_Slot[$i] = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM pokemon WHERE Owner_Current = {$User_Data['id']} AND Slot = {$i}"));

        if ( $Roster_Slot[$i] )
        {
          $Pokedex_Data = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM pokedex WHERE ID = '{$Roster_Slot[$i]['Pokedex_ID']}'"));
        }
        else {
          $Roster_Slot[$i] = "Empty";
        }

        if ( $Roster_Slot[$i] !== "Empty" )
        {
          $Item = mysqli_fetch_assoc(mysqli_query($con, "SELECT Item_Name FROM items_owned WHERE Equipped_To = '" . $Roster_Slot[$i]['ID'] . "'"));

          echo  "<div class='roster_slot'>";
          echo    "<div style='position: absolute; text-align: center; width: 145px;'><i>{$Roster_Slot[$i]['Nickname']}</i></div>";
          if ( $Roster_Slot[$i]['Item'] !== '0' )
          {
            echo    "<img class='item' src='images/Items/" . $Item['Item_Name'] . ".png' />";
          }
          echo    "<img class='gender' src='images/Assets/{$Roster_Slot[$i]['Gender']}.svg' />";
          echo 		"<img src='images/Pokemon/{$Roster_Slot[$i]['Type']}/{$Roster_Slot[$i]['Pokedex_ID']}.png' /><br />";
          
          if ( $Roster_Slot[$i]['Type'] !== "Normal" ) {
            echo $Roster_Slot[$i]['Type'] . $Pokedex_Data['Name'] . "<br />";
          }
          else {
            echo $Pokedex_Data['Name'] . "<br />";
          }

          echo    "<b>Level:</b> " . number_format($Roster_Slot[$i]['Level']) . "<br />";
          echo	  "<b>Exp:</b> " . number_format($Roster_Slot[$i]['Experience']) . "<br />";
          echo				"<form method='post' name='changeNick' onsubmit='changeNickname(event, {$Roster_Slot[$i]['ID']}, {$i});'>";
          echo					"<input type='text' name='nickname{$i}' style='margin-bottom: 0px; width: 138px;' /><br />";
          echo					"<input type='submit' name='submit' value='Change Nickname' style='margin-top: 3px; width: 138px;'/>";
          echo				"</form>";
          echo  "</div>";
        }
        else 
        {
          echo  "<div class='roster_slot' style='height: 229px; padding-top: 55px;'>";
          echo    "<img src='images/Assets/Pokeball.png' /><br />";
          echo    "Empty";
          echo  "</div>";
        }
      }

			echo			"</div>";
			echo		"</div>";
    }

    # Change the Pokemon's nickname.
    if ( $_POST['request'] === 'pokecenter_nickchange' )
    {
      if ( $User_Data['Money'] < '10000' )
      {
        echo "<div class='description' style='border-color: #ff0000; margin-top: 0px; margin-bottom: 5px; padding: 5px; width: 100%;'>You don't have enough money to change your Pokemon's nickname.</div>";
      }
      else
      {
        $Pokemon_Data = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM pokemon WHERE id = {$_POST['id']} AND Owner_Current = {$User_Data['id']}"));
        $Pokedex_Data = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM pokedex WHERE id = '{$Pokemon_Data['Pokedex_ID']}'"));

        mysqli_query($con, "UPDATE pokemon SET Nickname = '" . $_POST['nickname'] . "' WHERE id = {$Pokemon_Data['ID']}");
        mysqli_query($con, "UPDATE members SET Money = Money - 1000 WHERE id = {$User_Data['id']}");

        echo "<div class='description' style='border-color: #00ff00; margin-top: 0px; margin-bottom: 5px; padding: 5px; width: 100%;'>";
        if ( $_POST['nickname'] == '' )
        {
          if ( $Pokemon_Data['Type'] !== "Normal" )
          {
            echo "Your <b>{$Pokemon_Data['Type']}{$Pokedex_Data['Name']}</b>'s nickname has been removed.";
          }
          else
          {
            echo "Your <b>{$Pokedex_Data['Name']}</b>'s nickname has been removed.";
          }
        }
        else
        {
          if ( $Pokemon_Data['Type'] !== "Normal" )
          {
            echo "Your <b>{$Pokemon_Data['Type']}{$Pokedex_Data['Name']}</b>'s nickname has been changed to <b>{$_POST['nickname']}</b>.";
          }
          else
          {
            echo "Your <b>{$Pokedex_Data['Name']}</b>'s nickname has been changed to <b>{$_POST['nickname']}</b>.";
          }
        }
        echo  "</div>";
      }
			
			echo		"<div class='panel panel-default' style='margin: 0px 0px 0px 0px'>";
			echo			"<div class='panel-heading'>Nickname Your Pokemon</div>";
			echo			"<div class='panel-body'>";
			echo				"<div class='description' style='margin-bottom: 5px;'>";
			echo					"Here, you may set the nickname of your Pokemon.<br />";
      echo					"Please note that any inappropriate or profane names are not allowed.<br /><br />";
      echo          "<i>Changing your Pokemon's nickname will cost $1,000.";
      echo				"</div>";
      echo        "<div class='panel nickContainer' style='margin: 0 auto 3px; width: 99.5%;'>";
      echo          "<div class='panel-heading'>Roster</div>";
      echo          "<div class='panel-body'>";

      for ( $i = 1; $i <= 6; $i++ )
      {
        $Roster_Slot[$i] = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM pokemon WHERE Owner_Current = {$User_Data['id']} AND Slot = {$i}"));

        if ( $Roster_Slot[$i] )
        {
          $Pokedex_Data = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM pokedex WHERE ID = '{$Roster_Slot[$i]['Pokedex_ID']}'"));
        }
        else {
          $Roster_Slot[$i] = "Empty";
        }

        if ( $Roster_Slot[$i] !== "Empty" )
        {
          $Item = mysqli_fetch_assoc(mysqli_query($con, "SELECT Item_Name FROM items_owned WHERE Equipped_To = '" . $Roster_Slot[$i]['ID'] . "'"));

          echo  "<div class='roster_slot'>";
          echo    "<div style='position: absolute; text-align: center; width: 145px;'><i>{$Roster_Slot[$i]['Nickname']}</i></div>";
          if ( $Roster_Slot[$i]['Item'] !== '0' )
          {
            echo    "<img class='item' src='images/Items/" . $Item['Item_Name'] . ".png' />";
          }
          echo    "<img class='gender' src='images/Assets/{$Roster_Slot[$i]['Gender']}.svg' />";
          echo 		"<img src='images/Pokemon/{$Roster_Slot[$i]['Type']}/{$Roster_Slot[$i]['Pokedex_ID']}.png' /><br />";
          
          if ( $Roster_Slot[$i]['Type'] !== "Normal" ) {
            echo $Roster_Slot[$i]['Type'] . $Pokedex_Data['Name'] . "<br />";
          }
          else {
            echo $Pokedex_Data['Name'] . "<br />";
          }

          echo    "<b>Level:</b> " . number_format($Roster_Slot[$i]['Level']) . "<br />";
          echo	  "<b>Exp:</b> " . number_format($Roster_Slot[$i]['Experience']) . "<br />";
          echo				"<form method='post' name='changeNick' onsubmit='changeNickname(event, {$Roster_Slot[$i]['ID']}, {$i});'>";
          echo					"<input type='text' name='nickname{$i}' style='margin-bottom: 0px; width: 138px;' /><br />";
          echo					"<input type='submit' name='submit' value='Change Nickname' style='margin-top: 3px; width: 138px;'/>";
          echo				"</form>";

          echo  "</div>";
        }
        else 
        {
          echo  "<div class='roster_slot' style='height: 229px; padding-top: 55px;'>";
          echo    "<img src='images/Assets/Pokeball.png' /><br />";
          echo    "Empty";
          echo  "</div>";
        }
      }
    }
    
    # Release Pokemon.
    if ( $_POST['request'] === 'pokecenter_release' )
    {
      echo  "
        <script type='text/javascript'>
          function countSelector(el) {
            var countID = document.getElementById(el.id + '_count');
          
            if (!countID)
            {
              return false;
            }
          
            var options = el.getElementsByTagName('OPTION');
            var selected = 0;
            
            for (var i = 0; i < options.length; i++){
              if (options[i].selected) selected++;
            }
            
            if (selected > 0)
            {
              $('#releaseButton').removeAttr('disabled');
              countID.style.color = 'green';
              countID.innerHTML = selected;
            }
            else
            {
              $('#releaseButton').attr('disabled', 'disabled');
            }
          }

          function releasePokemon(phase, event) {
            if ( confirm('Are you sure you want to release these Pokemon?') )
            {
              if ( $('#release :selected').length > 0 )
              {
                var selectedPokemon = [];
                $('#release :selected').each(function(i, selected) {
                    selectedPokemon[i] = $(selected).val();
                });

                $.ajax({
                  type: 'post',
                  url: 'ajax/ajax_pokecenter.php',
                  data: { request: 'release_pokemon', pokemon: JSON.stringify(selectedPokemon), phase: phase },
                  success: function(data) {
                    $('#pokemon_center').html(data);
                  },
                  error: function(data) {
                    $('#pokemon_center').html(data);
                  }
                });
              }
            }
    
            event.preventDefault();
          }
        </script>
      ";

			echo		"<div class='panel panel-default'>";
			echo			"<div class='panel-heading'>Release Pokemon</div>";
      echo				"<div class='panel-body' style='padding: 3px'>";
      echo          "<div>";
      echo              "<button id='releaseButton' style=\"background: #2c3a55 !important; border: 2px solid #000; border-radius: 4px; margin-bottom: 5px; padding: 3px; width: 100%;\" onclick=\" releasePokemon(0); \">Release Pokemon</button>";
      echo          "</div>";

      echo            "<div class='panel' style='margin-bottom: 5px;'>";
      echo              "<div class='panel-heading'>Box Filters</div>";
      echo                "<div class='panel-body'>";

      echo                  "<div style='border-right: 1px solid #4A618F; float: left; padding: 29px 5px;'>";
      echo                    "<b>Search for a Pokemon<br />";
      echo                    "<input type='text' name='pokemonSearch' value='' />";
      echo                  "</div>";

      echo                  "<div style='border-right: 1px solid #4A618F; float: left;'>";
      echo                    "<table>";
      echo                      "<tr style='border-bottom: 1px solid #4A618F;'>";
      echo                        "<td colspan='2'><b>Types</b></td>";
      echo                      "</tr>";
      echo                      "<tr style='border-bottom: 1px solid #4A618F;'>";
      echo                        "<td style='border-right: 1px solid #4A618F;'><a style='display: block; padding: 5px 0px;' href='javascript:void(0);' onclick='filterBox(normal);'>Normal</a></td>";
      echo                        "<td style='width: 80px;'><a style='display: block; padding: 5px 0px;' href='javascript:void(0);' onclick='filterBox(shiny);'>Shiny</a></td>";
      echo                      "</tr>";
      echo                      "<tr style='border-bottom: 1px solid #4A618F;'>";
      echo                        "<td style='border-right: 1px solid #4A618F; width: 80px;'><a style='display: block; padding: 5px 0px;' href='javascript:void(0);' onclick='filterBox(normal);'>Nature</a></td>";
      echo                        "<td><a style='display: block; padding: 5px 0px;' href='javascript:void(0);' onclick='filterBox(shiny);'>Sunset</a></td>";
      echo                      "</tr>";
      echo                      "<tr>";
      echo                        "<td style='border-right: 1px solid #4A618F; width: 80px;'><a style='display: block; padding: 5px 0px;' href='javascript:void(0);' onclick='filterBox(normal);'>???</a></td>";
      echo                        "<td><a style='display: block; padding: 5px 0px;' href='javascript:void(0);' onclick='filterBox(shiny);'>???</a></td>";
      echo                      "</tr>";
      echo                    "</table>";
      echo                  "</div>";

      echo                  "<div style='border-right: 1px solid #4A618F; float: left;'>";
      echo                    "<table style='width: 162px;'>";
      echo                      "<tr style='border-bottom: 1px solid #4A618F;'>";
      echo                        "<td colspan='3'><b>IV Sorting</b></td>";
      echo                      "</tr>";
      echo                      "<tr style='border-bottom: 1px solid #4A618F;'>";
      echo                        "<td style='border-right: 1px solid #4A618F; width: 80px;'><a style='display: block; padding: 5px 0px;' href='javascript:void(0);' onclick='filterBox(aIV)'>Ascending</a></td>";
      echo                        "<td style='width: 80px;'><a style='display: block; padding: 5px 0px;' href='javascript:void(0);' onclick='filterBox(dIV)'>Descending</a></td>";
      echo                      "</tr>";
      echo                    "</table>";
      echo                    "<table>";
      echo                      "<tr style='border-bottom: 1px solid #4A618F;'>";
      echo                        "<td style='width: calc(160px / 3);'><input style='border: none; margin: 0px; padding: 5px; text-align: center; width: 100%;' type='text' name='attackIV' placeholder='HP' value='' /></td>";
      echo                        "<td style='border-left: 1px solid #4A618F; border-right: 1px solid #4A618F; width: calc(160px / 3);'><input style='border: none; margin: 0px; padding: 5px; text-align: center; width: 100%;' type='text' name='attackIV' placeholder='ATT' value='' /></td>";
      echo                        "<td style='width: calc(160px / 3);'><input style='border: none; margin: 0px; padding: 5px; text-align: center; width: 100%;' type='text' name='attackIV' placeholder='DEF' value='' /></td>";
      echo                      "</tr>";
      echo                      "<tr>";
      echo                        "<td style='width: calc(160px / 3);'><input style='border: none; margin: 0px; padding: 5px; text-align: center; width: 100%;' type='text' name='attackIV' placeholder='S.ATT' value='' /></td>";
      echo                        "<td style='border-left: 1px solid #4A618F; border-right: 1px solid #4A618F; width: calc(160px / 3);'><input style='border: none; margin: 0px; padding: 5px; text-align: center; width: 100%;' type='text' name='attackIV' placeholder='S.DEF' value='' /></td>";
      echo                        "<td style='width: calc(160px / 3);'><input style='border: none; margin: 0px; padding: 5px; text-align: center; width: 100%;' type='text' name='attackIV' placeholder='SPEED' value='' /></td>";
      echo                      "</tr>";
      echo                    "</table>";
      echo                  "</div>";

      echo                  "<div style='border-right: 1px solid #4A618F; float: left;'>";
      echo                    "<table style='width: 162px;'>";
      echo                      "<tr style='border-bottom: 1px solid #4A618F;'>";
      echo                        "<td colspan='3'><b>EV Sorting</b></td>";
      echo                      "</tr>";
      echo                      "<tr style='border-bottom: 1px solid #4A618F;'>";
      echo                        "<td style='border-right: 1px solid #4A618F; width: 80px;'><a style='display: block; padding: 5px 0px;' href='javascript:void(0);' onclick='filterBox(aIV)'>Ascending</a></td>";
      echo                        "<td style='width: 80px;'><a style='display: block; padding: 5px 0px;' href='javascript:void(0);' onclick='filterBox(dIV)'>Descending</a></td>";
      echo                      "</tr>";
      echo                    "</table>";
      echo                    "<table>";
      echo                      "<tr style='border-bottom: 1px solid #4A618F;'>";
      echo                        "<td style='width: calc(160px / 3);'><input style='border: none; margin: 0px; padding: 5px; text-align: center; width: 100%;' type='text' name='attackIV' placeholder='HP' value='' /></td>";
      echo                        "<td style='border-left: 1px solid #4A618F; border-right: 1px solid #4A618F; width: calc(160px / 3);'><input style='border: none; margin: 0px; padding: 5px; text-align: center; width: 100%;' type='text' name='attackIV' placeholder='ATT' value='' /></td>";
      echo                        "<td style='width: calc(160px / 3);'><input style='border: none; margin: 0px; padding: 5px; text-align: center; width: 100%;' type='text' name='attackIV' placeholder='DEF' value='' /></td>";
      echo                      "</tr>";
      echo                      "<tr>";
      echo                        "<td style='width: calc(160px / 3);'><input style='border: none; margin: 0px; padding: 5px; text-align: center; width: 100%;' type='text' name='attackIV' placeholder='S.ATT' value='' /></td>";
      echo                        "<td style='border-left: 1px solid #4A618F; border-right: 1px solid #4A618F; width: calc(160px / 3);'><input style='border: none; margin: 0px; padding: 5px; text-align: center; width: 100%;' type='text' name='attackIV' placeholder='S.DEF' value='' /></td>";
      echo                        "<td style='width: calc(160px / 3);'><input style='border: none; margin: 0px; padding: 5px; text-align: center; width: 100%;' type='text' name='attackIV' placeholder='SPEED' value='' /></td>";
      echo                      "</tr>";
      echo                    "</table>";
      echo                  "</div>";

      echo                  "<input style='margin-top: 40px; width: 150px;' type='submit' value='Filter Box' onclick='aFilterBox(); return false;' />";
      echo              "</div>";
      echo           "</div>";

      echo            "<div class='description' style='margin-bottom: 5px; width: 100%;'>";
      echo              "You may select multiple Pokemon by holding down SHIFT.<br />";
      echo              "You can select multiple individual Pokemon by holding down CTRL.";
      echo            "</div>";

      echo           "<div class='panel' style='float: right; width: 34.5%;'>";
      echo             "<div class='panel-heading'>Release List</div>";
      echo             "<div class='panel-body' id='release'>";
      echo               "<select style='border: none; border-bottom: 1px solid #4A618F; border-radius: 0px; height: 300px; outline: none; width: 100%;' id='release' name='release[]' multiple='multiple' onchange='countSelector(this)'>";

      $Query_Box = mysqli_query($con, "SELECT * FROM `pokemon` WHERE `slot` = '7' AND `Owner_Current` = '" . $User_Data['id'] . "'");

      while ( $Box_Data = mysqli_fetch_assoc($Query_Box) )
      {
        $Pokedex_Data = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM `pokedex` WHERE id = '" . $Box_Data['Pokedex_ID'] . "'"));

        $Gender = substr($Box_Data['Gender'], 0, 1);

        if ( $Box_Data['Type'] !== "Normal" )
        {
          $Type = $Box_Data['Type'];
        }
        else
        {
          $Type = "";
        }

        echo  "<option value='{$Box_Data['ID']}'>{$Type}{$Pokedex_Data['Name']} {$Gender} (Level: " . number_format($Box_Data['Level']) . ")</option>";

        if ( $Box_Data['Item'] != '0' )
        {
          $Item_Data = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM `items_owned` WHERE id = '" . $Box_Data['Item'] . "'"));

          echo  "<option value disabled='disabled' style='font-size: 12px;'>^ Item: {$Item_Data['Item_Name']}</option>";
        }
      }

      echo               "</select>";
      echo               "<div style='background: #111; margin-top: -1px; padding: 3px;'>";
      echo                "You have selected <b><span id='release_count'>0</span></b> Pokemon to release.";
      echo               "</div>";
      echo             "</div>";
      echo           "</div>";

      echo            "<div class='panel' style='float: left; margin-bottom: 5px; width: 65%;'>";
      echo              "<div class='panel-heading'>Selected Pokemon</div>";
      echo              "<div class='panel-body' id='selectedPokemon'>";
      echo                "<div style='padding: 10px;'>Please select a Pokemon to view it's stats.</div>";
      echo              "</div>";
      echo            "</div>";

      echo		  			"<div class='panel' style='float: left; width: 65%;'>";
      echo             "<div class='panel-heading'>Box</div>";
      echo             "<div class='panel-body boxed_pokemon'>";
      
      $Query_Box = mysqli_query($con, "SELECT * FROM `pokemon` WHERE `slot` = '7' AND `Owner_Current` = '" . $User_Data['id'] . "'");

      while ( $rows = mysqli_fetch_assoc($Query_Box) ) {
        echo "<img src='images/Icons/{$rows['Type']}/" . $rows['Pokedex_ID'] . ".png' onclick='showPokemon(" . $rows['ID'] . ");' />";
      }
      
      if ( mysqli_num_rows($Query_Box) == 0 ) {
        echo	"<div style='padding: 5px;'>There are no Pokemon in your box.</div>";
      }

      echo              "</div>";
      echo           "</div>";
      echo		  	"</div>";
			echo		  "</div>";
      echo	  "</div>";
    }

    # Processing the releasing of Pokemon.
    if ( $_POST['request'] === 'release_pokemon' && $_POST['phase'] === '0' )
    {
      echo  "
        <script type='text/javascript'>
          function releasePokemon(phase, event) {
            if ( confirm('Are you sure you want to release these Pokemon?') )
            {
              var selectedPokemon = [];
              $('.panel').each(function(i) {
                selectedPokemon[i] = $(this).attr('name');
                console.log(selectedPokemon);
              });
              
              $.ajax({
                type: 'post',
                url: 'ajax/ajax_pokecenter.php',
                data: { request: 'release_pokemon', pokemon: JSON.stringify(selectedPokemon), phase: phase },
                success: function(data) {
                  $('#pokemon_center').html(data);
                },
                error: function(data) {
                  $('#pokemon_center').html(data);
                }
              });
            }
    
            event.preventDefault();
          }
        </script>
      ";

      # JSON_Decode the Pokemon.
      $Pokemon = json_decode($_POST['pokemon'], true);

      echo  "<style>";
      echo    ".content .box.pokecenter .panel .panel-body.releaseList { padding: 0px 5px 5px 5px !important; }";
      echo    "#pokemon_center > div > div.panel-body.releaseList > div { margin-top: 5px; }";
      echo    "#pokemon_center > div > div.panel-body.releaseList > div:nth-child(3n+2) { margin-left: 1.25%; margin-right: 1.25%; }";
      echo  "</style>";

      echo  "<input type='button' onclick='releasePokemon(1);' value='Release Pokemon' style='width: 100%;' />";

      echo  "<div class='panel'>";
      echo    "<div class='panel-heading'>Attempting To Release</div>";
      echo    "<div class='panel-body releaseList' style='padding: 5px;'>";

      # Loop 'x' amount of times, depending on count($_POST) number.
      # Retrieve the correct Pokemon's DB row to release.
      foreach ( $Pokemon as $num => $Database_ID )
      {
        $Pokemon_Data = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM `pokemon` WHERE id = '" . $Database_ID . "' AND Owner_Current = '" . $User_Data['id'] . "'"));
        $Pokedex_Data = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM `pokedex` WHERE id = '" . $Pokemon_Data['Pokedex_ID'] . "'"));
        $Item_Data = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM `items_owned` WHERE id = '" . $Pokemon_Data['Item'] . "' AND Owner_Current = '" . $User_Data['id'] . "'"));

        if ( $Pokemon_Data['Type'] !== "Normal" ) {
          $Pokemon_Type = $Pokemon_Data['Type'];
        } else {
          $Pokemon_Type = "";
        }

        echo  "<div class='panel' name='{$Database_ID}' style='float: left; width: 32.5%;'>";
        echo    "<div class='panel-heading'>{$Pokemon_Type}{$Pokedex_Data['Name']}</div>";
        echo    "<div class='panel-body' style='background: rgba(29, 33, 43, 1); text-align: left;'>";
        echo      "<img src='images/Icons/{$Pokemon_Data['Type']}/{$Pokemon_Data['Pokedex_ID']}.png' style='border-right: 1px solid #4A618F;' />";
        echo      "<img src='images/Assets/{$Pokemon_Data['Gender']}.svg' style='border-right: 1px solid #4A618F; height: 30px; padding: 3px; width: 28px;' />";

        if ( $Pokemon_Data['Item'] !== '0' )
        {
          echo    "<img src='images/Items/" . $Item_Data['Item_Name'] . ".png' style='border-right: 1px solid #4A618F; padding: 3px;' />";
        }

        echo      " Level: " . number_format($Pokemon_Data['Level']);

        echo    "</div>";
        echo  "</div>";
      }

      echo    "</div>";
      echo  "</div>";
    }

    # Actually release the Pokemon.
    if ( $_POST['request'] === 'release_pokemon' && $_POST['phase'] === '1' )
    {
      # JSON_Decode the Pokemon.
      $Pokemon = json_decode($_POST['pokemon'], true);
      
      foreach ( $Pokemon as $num => $Database_ID )
      {
        # Remove the item from the Pokemon.
        mysqli_query($con, "UPDATE `pokemon` SET Item = '0' WHERE id = '" . $Database_ID . "' WHERE Owner_Current = '" . $User_Data['id'] . "'");
        mysqli_query($con, "UPDATE `items_owned` SET Equipped_To = '0' WHERE Equipped_To = '" . $Database_ID . "'");

        # Copy the Pokemon into the `released` database table.
        mysqli_query($con, "INSERT INTO `released` SELECT * FROM `pokemon` WHERE id = '" . $Database_ID . "'");

        # Delete the copy that exists within the `pokemon` table.
        mysqli_query($con, "DELETE FROM `pokemon` WHERE id = '" . $Database_ID . "'");

        # List all of the Pokemon that the user released, possibly?
        $Pokemon_Data = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM `pokemon` WHERE id = '" . $Database_ID . "' AND Owner_Current = '" . $User_Data['id'] . "'"));
        $Pokedex_Data = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM `pokedex` WHERE id = '" . $Pokemon_Data['Pokedex_ID'] . "'"));
        $Item_Data = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM `items_owned` WHERE id = '" . $Pokemon_Data['Item'] . "' AND Owner_Current = '" . $User_Data['id'] . "'"));
      }

      echo  "
        <script type='text/javascript'>
          function countSelector(el) {
            var countID = document.getElementById(el.id + '_count');
          
            if (!countID)
            {
              return false;
            }
          
            var options = el.getElementsByTagName('OPTION');
            var selected = 0;
            
            for (var i = 0; i < options.length; i++){
              if (options[i].selected) selected++;
            }
            
            if (selected > 0)
            {
              $('#releaseButton').removeAttr('disabled');
              countID.style.color = 'green';
              countID.innerHTML = selected;
            }
            else
            {
              $('#releaseButton').attr('disabled', 'disabled');
            }
          }

          function releasePokemon(phase, event) {
            if ( confirm('Are you sure you want to release these Pokemon?') )
            {
              if ( $('#release :selected').length > 0 )
              {
                var selectedPokemon = [];
                $('#release :selected').each(function(i, selected) {
                    selectedPokemon[i] = $(selected).val();
                });

                $.ajax({
                  type: 'post',
                  url: 'ajax/ajax_pokecenter.php',
                  data: { request: 'release_pokemon', pokemon: JSON.stringify(selectedPokemon), phase: phase },
                  success: function(data) {
                    $('#pokemon_center').html(data);
                  },
                  error: function(data) {
                    $('#pokemon_center').html(data);
                  }
                });
              }
            }
    
            event.preventDefault();
          }
        </script>
      ";

      echo "<div class='description' style='border-color: #00ff00; margin: 0px 0px 5px; width: 100%;'>Your Pokemon have been released.</div>";

      echo		"<div class='panel panel-default'>";
			echo			"<div class='panel-heading'>Release Pokemon</div>";
      echo				"<div class='panel-body' style='padding: 3px'>";
      echo          "<div>";
      echo              "<button id='releaseButton' style=\"background: #2c3a55 !important; border: 2px solid #000; border-radius: 4px; margin-bottom: 5px; padding: 3px; width: 100%;\" onclick=\" releasePokemon(0); \">Release Pokemon</button>";
      echo          "</div>";

      echo            "<div class='panel' style='margin-bottom: 5px;'>";
      echo              "<div class='panel-heading'>Box Filters</div>";
      echo                "<div class='panel-body'>";

      echo                  "<div style='border-right: 1px solid #4A618F; float: left; padding: 29px 5px;'>";
      echo                    "<b>Search for a Pokemon<br />";
      echo                    "<input type='text' name='pokemonSearch' value='' />";
      echo                  "</div>";

      echo                  "<div style='border-right: 1px solid #4A618F; float: left;'>";
      echo                    "<table>";
      echo                      "<tr style='border-bottom: 1px solid #4A618F;'>";
      echo                        "<td colspan='2'><b>Types</b></td>";
      echo                      "</tr>";
      echo                      "<tr style='border-bottom: 1px solid #4A618F;'>";
      echo                        "<td style='border-right: 1px solid #4A618F;'><a style='display: block; padding: 5px 0px;' href='javascript:void(0);' onclick='filterBox(normal);'>Normal</a></td>";
      echo                        "<td style='width: 80px;'><a style='display: block; padding: 5px 0px;' href='javascript:void(0);' onclick='filterBox(shiny);'>Shiny</a></td>";
      echo                      "</tr>";
      echo                      "<tr style='border-bottom: 1px solid #4A618F;'>";
      echo                        "<td style='border-right: 1px solid #4A618F; width: 80px;'><a style='display: block; padding: 5px 0px;' href='javascript:void(0);' onclick='filterBox(normal);'>Nature</a></td>";
      echo                        "<td><a style='display: block; padding: 5px 0px;' href='javascript:void(0);' onclick='filterBox(shiny);'>Sunset</a></td>";
      echo                      "</tr>";
      echo                      "<tr>";
      echo                        "<td style='border-right: 1px solid #4A618F; width: 80px;'><a style='display: block; padding: 5px 0px;' href='javascript:void(0);' onclick='filterBox(normal);'>???</a></td>";
      echo                        "<td><a style='display: block; padding: 5px 0px;' href='javascript:void(0);' onclick='filterBox(shiny);'>???</a></td>";
      echo                      "</tr>";
      echo                    "</table>";
      echo                  "</div>";

      echo                  "<div style='border-right: 1px solid #4A618F; float: left;'>";
      echo                    "<table style='width: 162px;'>";
      echo                      "<tr style='border-bottom: 1px solid #4A618F;'>";
      echo                        "<td colspan='3'><b>IV Sorting</b></td>";
      echo                      "</tr>";
      echo                      "<tr style='border-bottom: 1px solid #4A618F;'>";
      echo                        "<td style='border-right: 1px solid #4A618F; width: 80px;'><a style='display: block; padding: 5px 0px;' href='javascript:void(0);' onclick='filterBox(aIV)'>Ascending</a></td>";
      echo                        "<td style='width: 80px;'><a style='display: block; padding: 5px 0px;' href='javascript:void(0);' onclick='filterBox(dIV)'>Descending</a></td>";
      echo                      "</tr>";
      echo                    "</table>";
      echo                    "<table>";
      echo                      "<tr style='border-bottom: 1px solid #4A618F;'>";
      echo                        "<td style='width: calc(160px / 3);'><input style='border: none; margin: 0px; padding: 5px; text-align: center; width: 100%;' type='text' name='attackIV' placeholder='HP' value='' /></td>";
      echo                        "<td style='border-left: 1px solid #4A618F; border-right: 1px solid #4A618F; width: calc(160px / 3);'><input style='border: none; margin: 0px; padding: 5px; text-align: center; width: 100%;' type='text' name='attackIV' placeholder='ATT' value='' /></td>";
      echo                        "<td style='width: calc(160px / 3);'><input style='border: none; margin: 0px; padding: 5px; text-align: center; width: 100%;' type='text' name='attackIV' placeholder='DEF' value='' /></td>";
      echo                      "</tr>";
      echo                      "<tr>";
      echo                        "<td style='width: calc(160px / 3);'><input style='border: none; margin: 0px; padding: 5px; text-align: center; width: 100%;' type='text' name='attackIV' placeholder='S.ATT' value='' /></td>";
      echo                        "<td style='border-left: 1px solid #4A618F; border-right: 1px solid #4A618F; width: calc(160px / 3);'><input style='border: none; margin: 0px; padding: 5px; text-align: center; width: 100%;' type='text' name='attackIV' placeholder='S.DEF' value='' /></td>";
      echo                        "<td style='width: calc(160px / 3);'><input style='border: none; margin: 0px; padding: 5px; text-align: center; width: 100%;' type='text' name='attackIV' placeholder='SPEED' value='' /></td>";
      echo                      "</tr>";
      echo                    "</table>";
      echo                  "</div>";

      echo                  "<div style='border-right: 1px solid #4A618F; float: left;'>";
      echo                    "<table style='width: 162px;'>";
      echo                      "<tr style='border-bottom: 1px solid #4A618F;'>";
      echo                        "<td colspan='3'><b>EV Sorting</b></td>";
      echo                      "</tr>";
      echo                      "<tr style='border-bottom: 1px solid #4A618F;'>";
      echo                        "<td style='border-right: 1px solid #4A618F; width: 80px;'><a style='display: block; padding: 5px 0px;' href='javascript:void(0);' onclick='filterBox(aIV)'>Ascending</a></td>";
      echo                        "<td style='width: 80px;'><a style='display: block; padding: 5px 0px;' href='javascript:void(0);' onclick='filterBox(dIV)'>Descending</a></td>";
      echo                      "</tr>";
      echo                    "</table>";
      echo                    "<table>";
      echo                      "<tr style='border-bottom: 1px solid #4A618F;'>";
      echo                        "<td style='width: calc(160px / 3);'><input style='border: none; margin: 0px; padding: 5px; text-align: center; width: 100%;' type='text' name='attackIV' placeholder='HP' value='' /></td>";
      echo                        "<td style='border-left: 1px solid #4A618F; border-right: 1px solid #4A618F; width: calc(160px / 3);'><input style='border: none; margin: 0px; padding: 5px; text-align: center; width: 100%;' type='text' name='attackIV' placeholder='ATT' value='' /></td>";
      echo                        "<td style='width: calc(160px / 3);'><input style='border: none; margin: 0px; padding: 5px; text-align: center; width: 100%;' type='text' name='attackIV' placeholder='DEF' value='' /></td>";
      echo                      "</tr>";
      echo                      "<tr>";
      echo                        "<td style='width: calc(160px / 3);'><input style='border: none; margin: 0px; padding: 5px; text-align: center; width: 100%;' type='text' name='attackIV' placeholder='S.ATT' value='' /></td>";
      echo                        "<td style='border-left: 1px solid #4A618F; border-right: 1px solid #4A618F; width: calc(160px / 3);'><input style='border: none; margin: 0px; padding: 5px; text-align: center; width: 100%;' type='text' name='attackIV' placeholder='S.DEF' value='' /></td>";
      echo                        "<td style='width: calc(160px / 3);'><input style='border: none; margin: 0px; padding: 5px; text-align: center; width: 100%;' type='text' name='attackIV' placeholder='SPEED' value='' /></td>";
      echo                      "</tr>";
      echo                    "</table>";
      echo                  "</div>";

      echo                  "<input style='margin-top: 40px; width: 150px;' type='submit' value='Filter Box' onclick='aFilterBox(); return false;' />";
      echo              "</div>";
      echo           "</div>";

      echo            "<div class='description' style='margin-bottom: 5px; width: 100%;'>";
      echo              "You may select multiple Pokemon by holding down SHIFT.<br />";
      echo              "You can select multiple individual Pokemon by holding down CTRL.";
      echo            "</div>";

      echo           "<div class='panel' style='float: right; width: 34.5%;'>";
      echo             "<div class='panel-heading'>Release List</div>";
      echo             "<div class='panel-body' id='release'>";
      echo               "<select style='border: none; border-bottom: 1px solid #4A618F; border-radius: 0px; height: 300px; outline: none; width: 100%;' id='release' name='release[]' multiple='multiple' onchange='countSelector(this)'>";

      $Query_Box = mysqli_query($con, "SELECT * FROM `pokemon` WHERE `slot` = '7' AND `Owner_Current` = '" . $User_Data['id'] . "'");

      while ( $Box_Data = mysqli_fetch_assoc($Query_Box) )
      {
        $Pokedex_Data = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM `pokedex` WHERE id = '" . $Box_Data['Pokedex_ID'] . "'"));

        $Gender = substr($Box_Data['Gender'], 0, 1);

        if ( $Box_Data['Type'] !== "Normal" )
        {
          $Type = $Box_Data['Type'];
        }
        else
        {
          $Type = "";
        }

        echo  "<option value='{$Box_Data['ID']}'>{$Type}{$Pokedex_Data['Name']} {$Gender} (Level: " . number_format($Box_Data['Level']) . ")</option>";

        if ( $Box_Data['Item'] != '0' )
        {
          $Item_Data = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM `items_owned` WHERE id = '" . $Box_Data['Item'] . "'"));

          echo  "<option value disabled='disabled' style='font-size: 12px;'>^ Item: {$Item_Data['Item_Name']}</option>";
        }
      }

      echo               "</select>";
      echo               "<div style='background: #111; margin-top: -1px; padding: 3px;'>";
      echo                "You have selected <b><span id='release_count'>0</span></b> Pokemon to release.";
      echo               "</div>";
      echo             "</div>";
      echo           "</div>";

      echo            "<div class='panel' style='float: left; margin-bottom: 5px; width: 65%;'>";
      echo              "<div class='panel-heading'>Selected Pokemon</div>";
      echo              "<div class='panel-body' id='selectedPokemon'>";
      echo                "<div style='padding: 10px;'>Please select a Pokemon to view it's stats.</div>";
      echo              "</div>";
      echo            "</div>";

      echo		  			"<div class='panel' style='float: left; width: 65%;'>";
      echo             "<div class='panel-heading'>Box</div>";
      echo             "<div class='panel-body boxed_pokemon'>";
      
      $Query_Box = mysqli_query($con, "SELECT * FROM `pokemon` WHERE `slot` = '7' AND `Owner_Current` = '" . $User_Data['id'] . "'");

      while ( $rows = mysqli_fetch_assoc($Query_Box) ) {
        echo "<img src='images/Icons/{$rows['Type']}/" . $rows['Pokedex_ID'] . ".png' onclick='showPokemon(" . $rows['ID'] . ");' />";
      }
      
      if ( mysqli_num_rows($Query_Box) == 0 ) {
        echo	"<div style='padding: 5px;'>There are no Pokemon in your box.</div>";
      }

      echo              "</div>";
      echo           "</div>";
      echo		  	"</div>";
			echo		  "</div>";
      echo	  "</div>";
    }
  }
?>