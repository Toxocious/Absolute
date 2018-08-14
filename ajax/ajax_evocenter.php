<?php
  require '../session.php';

  if ( isset($_POST['request']) )
  {
    # Display the Pokemon's evolutions.
    if ( $_POST['request'] === 'display_evos' )
    {
      $Pokemon_Info = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM pokemon WHERE Slot = '" . $_POST['slot'] . "' AND Owner_Current = '" . $User_Data['id'] . "'"));
			$Pokedex_Info = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM pokedex WHERE id = '" . $Pokemon_Info['Pokedex_ID'] . "'"));
      $Item_Info = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM items_owned WHERE Equipped_To = '" . $Pokemon_Info['ID'] . "'"));
      $Evolution_Data = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM pokedex WHERE id = '" . $Pokemon_Info['Pokedex_ID'] . "'"));
			
			echo	"<div class='row'>";
			echo		"<div class='col-xs-6'>";
			echo 			"<img src='images/Pokemon/" . $Pokemon_Info['Type'] . "/" . $Pokemon_Info['Pokedex_ID'] . ".png' /><br />";
								if ( $Pokemon_Info['Type'] != "Normal" ) {
									echo 	"<b>" . $Pokemon_Info['Type'] . $Pokedex_Info['Name'] . "</b>";
								} else {
									echo 	"<b>" . $Pokedex_Info['Name'] . "</b>";
								}
			echo		"</div>";
			
			echo		"<div class='col-xs-6' style='padding: 35px 5px 5px 5px;'>";
			echo			"<b>Level: </b>" . number_format($Pokemon_Info['Level']) . "<br />";
			echo			"<b>Held Item: </b>";
								if ( $Pokemon_Info['Item'] == '0' ) {
									echo "None";
								} else {
									echo $Item_Info['Item_Name'];
								}
			echo		"</div>";
      echo	"</div>";
      
      echo  "<table style='margin-top: 5px; width: 100%;'>";
      echo    "<tr style='background: #3b4d72; border-top: 1px solid #4A618F;'>";
      echo      "<td style='width: calc(100% / 3);'><b>Pokemon</b></td>";
      echo      "<td style='border-left: 1px solid #4A618F; border-right: 1px solid #4A618F; width: calc(100% / 3);'><b>Requirement(s)</b></td>";
      echo      "<td style='width: calc(100% / 3);'><b>Evolve</b></td>";
      echo    "</tr>";

      $Evolution_List = array_map('trim', explode(', ', $Evolution_Data['Evolves_Into']));
      $Evolution_Item = explode(',', $Evolution_Data['Evo_Items']);
      $Evolution_Level = $Evolution_Data['Evo_Level'];
      
      # Display the Evolutions.
			foreach ( array_combine($Evolution_List, $Evolution_Item) as $Evolution_List => $Evolution_Item ) {
        $Pokedex_Data = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM pokedex WHERE `Name` = '" . $Evolution_List . "'"));

				if ( $Evolution_List == null ) {
					echo	"<tr style='border-top: 1px solid #4A618F;'>";
					echo		"<td colspan='3' style='padding: 5px;'>This Pokemon does not evolve into anything.</td>";
					echo	"</tr>";
				} else {
          echo    "<tr style='border-top: 1px solid #4A618F;'>";
          
          # Retrieve required held item data.
          if ( $Evolution_Item !== null ) {
            $Held_Item = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM `items_owned` WHERE `Item_Name` = '" . $Pokedex_Info['Evo_Items'] . "'"));
          }

          # Evolution Sprite + Name
          echo      "<td>";
          echo        "<img src='images/Pokemon/{$Pokemon_Info['Type']}/{$Pokedex_Data['ID']}.png' /><br />";
          if ( $Pokemon_Info['Type'] !== "Normal" ) {
            echo  "{$Pokemon_Info['Type']}{$Pokedex_Data['Name']}";
          } else {
            echo  "{$Pokedex_Data['Name']}";
          }
          echo      "</td>";

          # Evolution Requirements
          echo      "<td style='border-left: 1px solid #4A618F; border-right: 1px solid #4A618F;'>";
          if ( $Evolution_Level != null ) {
            echo  "<b>Level</b><br />{$Evolution_Level}<br />";
          }
          if ( $Evolution_Item != null && $Evolution_Item != "None" ) {
            echo  "<b>Held Item</b><br />{$Evolution_Item}<br />";
          }
          echo      "</td>";

          # Evolution Button
          echo      "<td>";
          if ( $Evolution_Level != null && $Pokemon_Info['Level'] >= $Evolution_Level ) 
          {
            echo  "<button style='width: 80%;' onclick='evolvePokemon({$Pokemon_Info['ID']})'>Evolve</button>";
          }
          else if ( $Evolution_Item != null && $Held_Item['id'] === $Pokemon_Info['Item'] )
          {
            echo  "<button style='width: 80%;' onclick='evolvePokemon({$Pokemon_Info['ID']})'>Evolve</button>";
          }
          else
          {
            echo  "You may not evolve this Pokemon.";
          }
          echo      "</td>";
          echo    "</tr>";
        }
      }
      echo  "</table>";
    }

    # Evolve the Pokemon.
    if ( $_POST['request'] === 'evolve_pokemon' )
    {
      $Pokemon_Data = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM `pokemon` WHERE `ID` = '" . $_POST['id'] . "' AND Owner_Current = '" . $User_Data['id'] . "'"));
      $Pokedex_Data = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM `pokedex` WHERE `ID` = '" . $Pokemon_Data['Pokedex_ID'] . "'"));
      $Held_Item = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM `items_owned` WHERE `ID` = '" . $Pokemon_Data['Item'] . "'"));

      # Verify that you own the Pokemon that you're attempting to evolve.
      if ( $Pokemon_Data['Owner_Current'] === $User_Data['id'] )
      {
        # Does the Pokemon evolve via leveling up?
        if ( $Pokedex_Data['Evo_Level'] !== null )
        {
          if ( $Pokemon_Data['Level'] >= $Pokedex_Data['Evo_Level'] )
          {
            $Get_Evolution = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM `pokedex` WHERE `Name` = '" . $Pokedex_Data['Evolves_Into'] . "'"));
            mysqli_query($con, "UPDATE `pokemon` SET `Pokedex_ID` = '" . $Get_Evolution['ID'] . "' WHERE `ID` = '" . $Pokemon_Data['ID'] . "'");

            if ( $Pokemon_Data['Type'] !== "Normal" )
            {
              $Pokemon_Type = $Pokemon_Data['Type'];
            }
            else
            {
              $Pokemon_Type = null;
            }

            echo  "<div style='padding: 5px;'>";
            echo    "Your {$Pokemon_Type}{$Pokedex_Data['Name']} has evolved into {$Pokemon_Type}{$Get_Evolution['Name']}.<br />";
            echo    "<img src='images/Pokemon/{$Pokemon_Data['Type']}/{$Get_Evolution['ID']}.png' />";
            echo  "</div>";
          }
          else
          {
            echo  "<div style='padding: 10px'>Your Pokemon doesn't meet the level requirement to evolve.</div>";
          }
        }

        # Does the Pokemon evolve via a held item?
        else if ( $Pokedex_Data['Evo_Items'] !== null )
        {
          if ( $Held_Item['Item_Name'] === $Pokedex_Data['Evo_Items'] )
          {
            $Get_Evolution = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM `pokedex` WHERE `Name` = '" . $Pokedex_Data['Evolves_Into'] . "'"));
            mysqli_query($con, "UPDATE `pokemon` SET `Pokedex_ID` = '" . $Get_Evolution['ID'] . "' WHERE `ID` = '" . $Pokemon_Data['ID'] . "'");

            if ( $Pokemon_Data['Type'] !== "Normal" )
            {
              $Pokemon_Type = $Pokemon_Data['Type'];
            }
            else
            {
              $Pokemon_Type = null;
            }

            echo  "<div style='padding: 5px;'>";
            echo    "Your {$Pokemon_Type}{$Pokedex_Data['Name']} has evolved into {$Pokemon_Type}{$Get_Evolution['Name']}.<br />";
            echo    "<img src='images/Pokemon/{$Pokemon_Data['Type']}/{$Get_Evolution['ID']}.png' />";
            echo  "</div>";
          }
          else
          {
            echo  "<div style='padding: 10px'>Your Pokemon doesn't have the proper item attached.</div>";
          }
        }

        # Does the Pokemon evolve via a particular gender?
        # Does the Pokemon evolve via a day/night time?
        else {
          echo  "<div style='padding: 10px'>This Pokemon evovles via a gender or time of day.</div>";
        }
      }

      # You don't own the Pokemon.
      else
      {
        echo  "<div style='padding: 10px;'>This Pokemon doesn't belong to you.</div>";
      }
    }
  }