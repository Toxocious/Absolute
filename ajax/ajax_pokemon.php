<?php
	require '../session.php';

	if ( isset($_GET['id']) ) {
		$Pokemon_Data = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM pokemon WHERE id = '" . $_GET['id'] . "'"));
		$Pokedex_Data = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM pokedex WHERE id = '" . $Pokemon_Data['Pokedex_ID'] . "'"));
		$Item_Data = mysqli_fetch_assoc(mysqli_query($con, "SELECT Item_Name FROM items_owned WHERE Equipped_To = '" . $Pokemon_Data['ID'] . "'"));
		$Owner_Current = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM members WHERE id = '" . $Pokemon_Data['Owner_Current'] . "'"));
		$Owner_Original = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM members WHERE id = '" . $Pokemon_Data['Owner_Original'] . "'"));
		
		// can condense this into a single query i believe
		$Rarity_Normal = mysqli_num_rows(mysqli_query($con, "SELECT * FROM pokemon WHERE Pokedex_ID = '" . $Pokemon_Data['Pokedex_ID'] . "' AND Type = 'Normal'"));
		$Rarity_Shiny = mysqli_num_rows(mysqli_query($con, "SELECT * FROM pokemon WHERE Pokedex_ID = '" . $Pokemon_Data['Pokedex_ID'] . "' AND Type = 'Shiny'"));

		if ( $Pokemon_Data['Item'] != '0' ) {
			$Equipped_Item = "<img class='item' src='../images/Items/" . $Item_Data['Item_Name'] . ".png' />";
		} else {
			$Equipped_Item = null;
		}

		echo	"
			<style>
				a { color: #4A618F; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; text-decoration: none; }
				a:hover { color: #669ac1; }
				a:visited { color: #4A618F; }

				img.gender { height: 20px; margin-top: 5px; position: absolute; width: 20px; }
				img.item { margin-left: -15px; margin-top: 5px; position: absolute; }

				.panel { background: rgba(74,97,143, 0.2); border: 2px solid #4A618F; border-radius: 4px; color: #fff; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; text-align: center; }
				.panel-heading { background: #4A618F;	background: -moz-linear-gradient(top, #888 0%, #4A618F 100%, #4A618F 100%);	background: -webkit-linear-gradient(top, #888 0%, #4A618F 100%, #4A618F 100%); background: linear-gradient(to bottom, #888 0%, #4A618F 100%, #4A618F 100%); font-size: 14px; font-weight: bold; padding: 5px; text-align: center; }
				.panel-body { font-size: 14px; text-align: center; }

				table.moves { border-collapse: collapse; color: #fff; font-size: 14px; width: 100%; }
				table.moves tbody tr:not(:last-child) { border-bottom: 2px solid #4A618F !important; }
				table.moves tbody tr td:nth-child(1) { border-right: 2px solid #4A618F; }
				table.moves tbody tr td { border-color: #fff; padding: 3px; text-align: center; width: 50%; }

				table.rarity { border-collapse: collapse; color: #fff; font-size: 14px; width: 100%; }
				table.rarity tbody tr:not(:last-child) { border-bottom: 2px solid #4A618F !important; }
				table.rarity tbody tr td:nth-child(1) { border-right: 2px solid #4A618F; width: 50px; }
				table.rarity tbody tr td { border-color: #fff; text-align: center; }

				table.special { -webkit-border-radius: 4px;	background: #4A618F; border: 0px solid #4A618F; border-collapse: separate; border-radius: 4px; border-spacing: 2px;	color: #fff; display: table; float: left; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 14px; margin: 0 auto; width: 162px; }
				table.special thead td { background: #4A618F;	background: -moz-linear-gradient(top, #888 0%, #4A618F 100%, #4A618F 100%);	background: -webkit-linear-gradient(top, #888 0%, #4A618F 100%, #4A618F 100%); background: linear-gradient(to bottom, #888 0%, #4A618F 100%, #4A618F 100%); font-weight: bold; padding: 4px; text-align: center; }
				table.special tbody {	background: #1d212b; }
				table.special tbody td { padding: 3px 2px; text-align: center; }
				table.special tbody td img { float: left;	margin-top: 5px; }
				table.special tbody td:nth-child(2) {	text-align: center; width: 30px; }
				table.special tbody td:hover { background: #2c3a55; }
			</style>
		";
		
		echo	"<div class='column' style='float: left; margin-right: 8px; width: 150px;'>";
		echo		"<div class='panel'>";
		echo			"<div class='panel-heading'>";

		if ( $Pokemon_Data['Type'] !== "Normal" ) { 
			echo $Pokemon_Data['Type']; 
		}

		echo				$Pokedex_Data['Name'];
		echo			"</div>";
		echo			"<div class='panel-body' style='text-align: center;'>";
		echo				$Equipped_Item;
		echo				"<img src='../images/Pokemon/{$Pokemon_Data['Type']}/{$Pokemon_Data['Pokedex_ID']}.png' />";
		echo				"<img class='gender' src='../images/Assets/{$Pokemon_Data['Gender']}.svg' />";

		if ( $Pokemon_Data['Nickname'] != null || $Pokemon_Data['Nickname'] != "" ) {
			echo '<div style="font-size: 12px; margin-top: -14px; position: absolute; text-align: center; width: 146px;"><i>"' . $Pokemon_Data["Nickname"] . '"</i></div>';
		}

		echo			"</div>";
		echo		"</div>";

		echo		"<div class='panel' style='margin-top: 8px;'>";
		echo			"<div class='panel-heading'>Level</div>";
		echo			"<div class='panel-body' style='padding: 3px;'>" . number_format($Pokemon_Data['Level']) . "</div>";
		echo		"</div>";

		echo		"<div class='panel' style='margin-top: 8px;'>";
		echo			"<div class='panel-heading'>Experience</div>";
		echo			"<div class='panel-body' style='padding: 3px;'>" . number_format($Pokemon_Data['Experience']) . "</div>";
		echo		"</div>";

		echo		"<div class='panel' style='margin-top: 8px;'>";
		echo			"<div class='panel-heading'>Rarity</div>";
		echo			"<div class='panel-body'>";
		echo				"<table class='rarity'>";
		echo					"<tbody>";
		echo						"<tr>";
		echo							"<td>";
												# Substitute all MEGA icons with the normal variant.
												if ( strpos($Pokedex_Data['Name'], ' (Mega)') )
												{
													echo "<img class='popup cboxElement' src='../images/Icons/Normal/{$Pokemon_Data['Pokedex_ID']}.png' href='ajax/ajax_pokemon.php?id={$Pokemon_Data['ID']}' />";
												}
												else
												{
													echo "<img class='popup cboxElement' src='../images/Icons/{$Pokemon_Data['Type']}/{$Pokemon_Data['Pokedex_ID']}.png' href='ajax/ajax_pokemon.php?id={$Pokemon_Data['ID']}' />";
												}
		echo							"</td>";
		echo							"<td>";
		echo								number_format($Rarity_Normal);
		echo							"</td>";
		echo						"</tr>";
		echo						"<tr>";
		echo							"<td>";
												# Substitute all MEGA icons with the normal variant.
												if ( strpos($Pokedex_Data['Name'], ' (Mega)') )
												{
													echo "<img class='popup cboxElement' src='../images/Icons/Normal/{$Pokemon_Data['Pokedex_ID']}.png' href='ajax/ajax_pokemon.php?id={$Pokemon_Data['ID']}' />";
												}
												else
												{
													echo "<img class='popup cboxElement' src='../images/Icons/{$Pokemon_Data['Type']}/{$Pokemon_Data['Pokedex_ID']}.png' href='ajax/ajax_pokemon.php?id={$Pokemon_Data['ID']}' />";
												}
		echo							"</td>";
		echo							"<td>";
		echo								number_format($Rarity_Shiny);
		echo							"</td>";
		echo						"</tr>";
		echo					"</tbody>";
		echo				"</table>";
		echo			"</div>";
		echo		"</div>";

		echo		"<div class='panel' style='margin-top: 8px;'>";
		echo			"<div class='panel-heading'>Current Owner</div>";
		echo			"<div class='panel-body' style='padding: 3px;'><a href='profiles.php?id={$Owner_Current['id']}'>{$Owner_Current['Username']}</a></div>";
		echo		"</div>";

		echo		"<div class='panel' style='margin-top: 8px;'>";
		echo			"<div class='panel-heading'>Original Owner</div>";
		echo			"<div class='panel-body' style='padding: 3px;'><a href='profiles.php?id={$Owner_Original['id']}'>{$Owner_Original['Username']}</a></div>";
		echo		"</div>";
		
		echo	"</div>";

		echo	"<div class='column' style='margin-left: 158px; position: absolute; width: 506px;'>";
		echo		"<div class='panel' style='margin-bottom: 8px;'>";
		echo			"<div class='panel-heading'>Biography</div>";
		echo			"<div class='panel-body' style='height: 86px; padding: 5px;'>";

		if ( $Pokemon_Data['Biography'] === null ) { 
			echo "<div style='margin-top: 35px;'>This owner hasn't set a biography for this Pokemon yet.</div>"; 
		}
		else { 
			echo $Pokemon_Data['Biography']; 
		}

		echo			"</div>";
		echo		"</div>";

		echo		"<table class='special'>";
		echo			"<thead>";
		echo				"<tr><td colspan='2'>Current Stats</td></tr>";
		echo			"</thead>";
		echo			"<tbody>";
		echo				"<tr>";
		echo					"<td>HP</td>";
		echo					"<td>{$Pokemon_Data['HP']}</td>";
		echo				"</tr>";
		echo				"<tr>";
		echo					"<td>Attack</td>";
		echo					"<td>{$Pokemon_Data['Attack']}</td>";
		echo				"</tr>";
		echo				"<tr>";
		echo					"<td>Defense</td>";
		echo					"<td>{$Pokemon_Data['Defense']}</td>";
		echo				"</tr>";
		echo				"<tr>";
		echo					"<td>Sp. Attack</td>";
		echo					"<td>{$Pokemon_Data['SpAttack']}</td>";
		echo				"</tr>";
		echo				"<tr>";
		echo					"<td>Sp. Defense</td>";
		echo					"<td>{$Pokemon_Data['SpDefense']}</td>";
		echo				"</tr>";
		echo				"<tr>";
		echo					"<td>Speed</td>";
		echo					"<td>{$Pokemon_Data['Speed']}</td>";
		echo				"</tr>";
		echo			"</tbody>";
		echo		"</table>";

		echo		"<table class='special' style='margin: 0px 10px;'>";
		echo			"<thead>";
		echo				"<tr><td colspan='2'>Individual Values</td></tr>";
		echo			"</thead>";
		echo			"<tbody>";
		echo				"<tr>";
		echo					"<td>HP</td>";
		echo					"<td>{$Pokemon_Data['IV_HP']}</td>";
		echo				"</tr>";
		echo				"<tr>";
		echo					"<td>Attack</td>";
		echo					"<td>{$Pokemon_Data['IV_Attack']}</td>";
		echo				"</tr>";
		echo				"<tr>";
		echo					"<td>Defense</td>";
		echo					"<td>{$Pokemon_Data['IV_Defense']}</td>";
		echo				"</tr>";
		echo				"<tr>";
		echo					"<td>Sp. Attack</td>";
		echo					"<td>{$Pokemon_Data['IV_SpAttack']}</td>";
		echo				"</tr>";
		echo				"<tr>";
		echo					"<td>Sp. Defense</td>";
		echo					"<td>{$Pokemon_Data['IV_SpDefense']}</td>";
		echo				"</tr>";
		echo				"<tr>";
		echo					"<td>Speed</td>";
		echo					"<td>{$Pokemon_Data['IV_Speed']}</td>";
		echo				"</tr>";
		echo			"</tbody>";
		echo		"</table>";

		echo		"<table class='special'>";
		echo			"<thead>";
		echo				"<tr>";
		echo					"<td colspan='2'>Effort Values</td>";
		echo				"</tr>";
		echo			"</thead>";
		echo			"<tbody>";
		echo				"<tr>";
		echo					"<td>HP</td>";
		echo					"<td>{$Pokemon_Data['EV_HP']}</td>";
		echo				"</tr>";
		echo				"<tr>";
		echo					"<td>Attack</td>";
		echo					"<td>{$Pokemon_Data['EV_Attack']}</td>";
		echo				"</tr>";
		echo				"<tr>";
		echo					"<td>Defense</td>";
		echo					"<td>{$Pokemon_Data['EV_Defense']}</td>";
		echo				"</tr>";
		echo				"<tr>";
		echo					"<td>Sp. Attack</td>";
		echo					"<td>{$Pokemon_Data['EV_SpAttack']}</td>";
		echo				"</tr>";
		echo				"<tr>";
		echo					"<td>Sp. Defense</td>";
		echo					"<td>{$Pokemon_Data['EV_SpDefense']}</td>";
		echo				"</tr>";
		echo				"<tr>";
		echo					"<td>Speed</td>";
		echo					"<td>{$Pokemon_Data['EV_Speed']}</td>";
		echo				"</tr>";
		echo			"</tbody>";
		echo		"</table>";

		echo		"<div class='panel' style='margin-top: 188px;'>";
		echo			"<div class='panel-heading'>Moves</div>";
		echo			"<div class='panel-body'>";
		echo				"<table class='moves'>";
		echo					"<tr>";
		echo						"<td>{$Pokemon_Data['Move_1']}</td>";
		echo						"<td>{$Pokemon_Data['Move_2']}</td>";
		echo					"</tr>";
		echo					"<tr>";
		echo						"<td>{$Pokemon_Data['Move_3']}</td>";
		echo						"<td>{$Pokemon_Data['Move_4']}</td>";
		echo					"</tr>";
		echo				"</table>";
		echo			"</div>";
		echo		"</div>";

		echo		"<div class='panel' style='margin-top: 8px;'>";
		echo			"<div class='panel-body'>";
		echo				"<table class='moves'>";
		echo					"<tr>";
		echo						"<td>Obtained On</td>";
		echo						"<td>{$Pokemon_Data['Creation_Date']}</td>";
		echo					"</tr>";
		echo					"<tr>";
		echo						"<td>Location Obtained</td>";
		echo						"<td>{$Pokemon_Data['Creation_Location']}</td>";
		echo					"</tr>";
		echo				"</table>";
		echo			"</div>";
		echo		"</div>";

		echo		"<div style='margin-top: 8px; text-align: center;'>";
		echo			"<a style='font-size: 14px;' href='statistics.php?pokemon={$Pokemon_Data['ID']}'>View Complete Statistics</a>";
		echo		"</div>";

		echo	"</div>";
	}
?>