<?php
  require '../db.php';
	require '../global_functions.php';
	
	if ( isset($_POST['req']) )
	{
		if ( $_POST['req'] == 'spawn' )
		{
			if ( isset($_POST['pokemon']) )
			{
				$_POST['pokemon'] = json_decode($_POST['pokemon']);
				$pokemonStats = $_POST['pokemon'];
				
				$Pokemon_Data = mysqli_fetch_assoc(mysqli_query($con, "SELECT HP, Attack, Defense, SpAttack, SpDefense, Speed FROM pokedex WHERE ID = '" . $pokemonStats->pokemon->id . "'"));
				$Data = array();

				echo "<div class='description' style='border-color: #00ff00; margin-bottom: 3px; margin-top: 3px; width: 100%;'>You have spawned in 'x' Pokemon.</div>";

				foreach ( $pokemonStats->pokemon as $stat => $val )
				{
					if ( $stat == 'id' )
						$val = $pokemonStats->pokemon->id;
					else if ( $stat == 'bhp' && !$val )
						$val = $Pokemon_Data['HP'];
					else if ( $stat == 'battk' && !$val )
						$val = $Pokemon_Data['Attack'];
					else if ( $stat == 'bdef' && !$val )
						$val = $Pokemon_Data['Defense'];
					else if ( $stat == 'bspatt' && !$val )
						$val = $Pokemon_Data['SpAttack'];
					else if ( $stat == 'bspdef' && !$val )
						$val = $Pokemon_Data['SpDefense'];
					else if ( $stat == 'bspd' && !$val )
						$val = $Pokemon_Data['Speed'];
					else
						$val = 0;

					$Data[$stat] = $val;
						
					//echo $stat . ": " . $val . "<br />";
				}

				foreach ( $Data as $stat => $val )
				{
					//echo $stat . ": " . $val . " ~ ";
				}
				//echo "<br />";

				//mysqli_query($con, "INSERT INTO pokemon (owner_current, owner_original, pokedex_id, hp, attack, defense, spattack, spdefense, speed, iv_hp, iv_attack, iv_defense, iv_spattack, iv_spdefense, iv_speed, ev_hp, ev_attack, ev_defense, ev_spattack, ev_spdefense, ev_speed) VALUES (1, 1, {$Data['id']}, {$Data['bhp']}, {$Data['battk']}, {$Data['bdef']}, {$Data['bspatt']}, {$Data['bspdef']}, {$Data['bspd']}, {$Data['ihp']}, {$Data['iattk']}, {$Data['idef']}, {$Data['ispatt']}, {$Data['ispdef']}, {$Data['ispd']}, {$Data['ehp']}, {$Data['eattk']}, {$Data['edef']}, {$Data['espatt']}, {$Data['espdef']}, {$Data['espd']})");
			}
			else
			{
				echo "<div class='description' style='border-color: #ff0000; margin-bottom: 3px; margin-top: 3px; width: 100%;'>Select a Pokemon, if you want to spawn something into the game.</div>";
			}

			if ( !isset($Selected_Pokemon['Name']) )			$Selected_Pokemon['Name']				= "???";
			if ( !isset($Selected_Pokemon['HP']) )				$Selected_Pokemon['HP']					= "0";
			if ( !isset($Selected_Pokemon['Attack']) )		$Selected_Pokemon['Attack']			= "0";
			if ( !isset($Selected_Pokemon['Defense']) )		$Selected_Pokemon['Defense']		= "0";
			if ( !isset($Selected_Pokemon['SpAttack']) )	$Selected_Pokemon['SpAttack']		= "0";
			if ( !isset($Selected_Pokemon['SpDefense']) )	$Selected_Pokemon['SpDefense']	= "0";
			if ( !isset($Selected_Pokemon['Speed']) )			$Selected_Pokemon['Speed']			= "0";
		}

		else if ( $_POST['req'] == 'select' )
		{
			if ( isset($_POST['id']) )
			{
				if ( $_POST['id'] < 722 || $_POST['id'] > 0 )
				{
					$Pokemon_Data = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM pokedex WHERE ID = '" . $_POST['id'] . "'"));

					if ( $Pokemon_Data['ID'] <= 151 )
						$Slot_Gen = 'Generation 1';
					else if ( $Pokemon_Data['ID'] <= 251 && $Pokemon_Data['ID'] >= 152 )
						$Slot_Gen = 'Generation 2';
					else if ( $Pokemon_Data['ID'] <= 386 && $Pokemon_Data['ID'] >= 252 )
						$Slot_Gen = 'Generation 3';
					else if ( $Pokemon_Data['ID'] <= 493 && $Pokemon_Data['ID'] >= 387 )
						$Slot_Gen = 'Generation 4';
					else if ( $Pokemon_Data['ID'] <= 649 && $Pokemon_Data['ID'] >= 494 )
						$Slot_Gen = 'Generation 5';
					else if ( $Pokemon_Data['ID'] <= 721 && $Pokemon_Data['ID'] >= 650 )
						$Slot_Gen = 'Generation 6';
					else
						$Slot_Gen = 'Generation 7';

					if ( strpos($Pokemon_Data['Name'], '(Mega)') )
					{
						$Slot_Gen = 'Mega';
						$Slot_pID = substr($Pokemon_Data['ID'], 0, -1);
						$Slot_pID .= '-mega';
					}
					else
					{
						$Slot_pID = $Pokemon_Data['ID'];
					}

					/*if ( !isset($Selected_Pokemon['ID']) )				*/ $Selected_Pokemon['ID']					= $Slot_pID;
					/*if ( !isset($Selected_Pokemon['Name']) )			*/ $Selected_Pokemon['Name']				= $Pokemon_Data['Name'];
					/*if ( !isset($Selected_Pokemon['HP']) )				*/ $Selected_Pokemon['HP']					= $Pokemon_Data['HP'];
					/*if ( !isset($Selected_Pokemon['Attack']) )		*/ $Selected_Pokemon['Attack']			= $Pokemon_Data['Attack'];
					/*if ( !isset($Selected_Pokemon['Defense']) )		*/ $Selected_Pokemon['Defense']		= $Pokemon_Data['Defense'];
					/*if ( !isset($Selected_Pokemon['SpAttack']) )	*/ $Selected_Pokemon['SpAttack']		= $Pokemon_Data['SpAttack'];
					/*if ( !isset($Selected_Pokemon['SpDefense']) )	*/ $Selected_Pokemon['SpDefense']	= $Pokemon_Data['SpDefense'];
					/*if ( !isset($Selected_Pokemon['Speed']) )			*/ $Selected_Pokemon['Speed']			= $Pokemon_Data['Speed'];

					//echo "<div class='description' style='margin: 0px auto 3px; width: 100%;'>You've chosen a valid Pokemon! :D</div>";
				}
				else
				{
					echo "<div class='description'>Please select a valid Pokemon.</div>";
				}
			}
		}
?>
<style>
  body > div.content > div.box > button { background: #1d2639 !important; border: 1px solid #4A618F; font-weight: bold; margin-bottom: 0px; margin-top: 3px; width: 100%; }
	body > div.content > div.box > button:hover { background: #3b4d72 !important; }

	#popup { height: 100%; left: 0; position: absolute; top: 0; width: 100%; z-index: 999999999999999; }
	#popup > .popup_bg { background: rgba(0, 0, 0, 0.7); height: 100%; width: 100%; }
	#popup > .popup_content { background: #111; border: 2px solid #4A618F; border-radius: 4px; height: 600px; left: 50%; margin-left: -300px; margin-top: -300px; overflow: auto; padding: 5px; position: absolute; top: 50%; width: 600px; }
</style>
	
<div class='panel' style='float: left; margin-bottom: -1px; width: 33%;'>
	<div class='panel-heading'>Pokemon Preview</div>
	<div class='panel-body' id='selectedPokemon'>
		<div>
			<?php
				echo $Selected_Pokemon['Name'];
				if ( $Selected_Pokemon['Name'] != "???" )
					echo " (#" . $Selected_Pokemon['ID'] . ")";
			?>
		</div>
		<div>
      <?php
				if ( $Selected_Pokemon['Name'] != "???" )
					showImage('sprite', $Selected_Pokemon['ID'], 'pokedex');
				else
					echo "<img src='images/Assets/pokeball.png' />";
      ?>
		</div>
		<div class='stats'>
			<div>Base Stats</div>
			<table>
				<tr>
					<td>HP</td>
					<td>Attack</td>
					<td>Defense</td>
				</tr>
				<tr>
					<td id='base_hp'><?php echo number_format($Selected_Pokemon['HP']); ?></td>
					<td id='base_att'><?php echo number_format($Selected_Pokemon['Attack']); ?></td>
					<td id='base_def'><?php echo number_format($Selected_Pokemon['Defense']); ?></td>
				</tr>
				<tr>
					<td>Sp.Att</td>
					<td>Sp.Def</td>
					<td>Speed</td>
				</tr>
				<tr>
					<td id='base_spatt'><?php echo number_format($Selected_Pokemon['SpAttack']); ?></td>
					<td id='base_spdef'><?php echo number_format($Selected_Pokemon['SpDefense']); ?></td>
					<td id='base_speed'><?php echo number_format($Selected_Pokemon['Speed']); ?></td>
				</tr>
			</table>
			<div>Individual Values</div>
			<table>
				<tr>
					<td>HP</td>
					<td>Attack</td>
					<td>Defense</td>
				</tr>
				<tr>
					<td id='iv_hp'>0</td>
					<td id='iv_att'>0</td>
					<td id='iv_def'>0</td>
				</tr>
				<tr>
					<td>Sp.Att</td>
					<td>Sp.Def</td>
					<td>Speed</td>
				</tr>
				<tr>
					<td id='iv_spatt'>0</td>
					<td id='iv_spdef'>0</td>
					<td id='iv_speed'>0</td>
				</tr>
			</table>
			<div>Effort Values</div>
			<table>
				<tr>
					<td>HP</td>
					<td>Attack</td>
					<td>Defense</td>
				</tr>
				<tr>
					<td id='ev_hp'>0</td>
					<td id='ev_att'>0</td>
					<td id='ev_def'>0</td>
				</tr>
				<tr>
					<td>Sp.Att</td>
					<td>Sp.Def</td>
					<td>Speed</td>
				</tr>
				<tr>
					<td id='ev_spatt'>0</td>
					<td id='ev_spdef'>0</td>
					<td id='ev_speed'>0</td>
				</tr>
			</table>
			<div>Moves</div>
			<table>
				<tr>
					<td id='move_1'>null</td>
					<td id='move_2'>null</td>
				</tr>
				<tr>
					<td id='move_3'>null</td>
					<td id='move_4'>null</td>
				</tr>
			</table>
		</div>
	</div>
</div>

<div class='panel' style='height: 80px; margin-bottom: 3px; margin-left: 33.3%; width: 66.6%;'>
	<div class='panel-heading'>Type & Version</div>
	<div class='panel-body btnContainer'>
		<div style='float: left; width: 50%;'>
			<button onclick="selectVersion('Version 1');">Version 1</button>
			<button onclick="selectVersion('Version 2');">Version 2</button>
			<button onclick="selectVersion('Version 3');">Version 3</button>
			<button onclick="selectVersion('Version 4');">Version 4</button>
			<button onclick="selectVersion('Version 5');">Version 5</button>
			<button onclick="selectVersion('Version 6');">Version 6</button>
			<button onclick="selectVersion('Version 7');">Version 7</button>
		</div>
		<div style='margin-left: 50%; width: 50%;'>
			<button onclick='selectType("Normal");'>Normal</button>
			<button onclick='selectType("Shiny");'>Shiny</button>
			<button onclick='selectType("Sunset");'>Sunset</button>
			<button onclick='selectType("Shiny Sunset");'>Shiny Sunset</button>
		</div>
	</div>
</div>

<div class='panel' style='height: 340px; margin-bottom: 3px; margin-left: 33.3%; width: 66.6%;'>
	<div class='panel-heading'>Stats</div>
	<div class='panel-body' id='selectedStats'>
		<div class='stats'>
			<div>Base Stats</div>
			<table>
				<tr>
					<td>HP</td>
					<td>Attack</td>
					<td>Defense</td>
				</tr>
				<tr>
					<td><input type='text' placeholder='<?php echo number_format($Selected_Pokemon['HP']); ?>' pattern="\d*" maxlength='9' id='base_sHP' /></td>
					<td><input type='text' placeholder='<?php echo number_format($Selected_Pokemon['Attack']); ?>' pattern="\d*" maxlength='9' id='base_sATT' /></td>
					<td><input type='text' placeholder='<?php echo number_format($Selected_Pokemon['Defense']); ?>' pattern="\d*" maxlength='9' id='base_sDEF' /></td>
				</tr>
				<tr>
					<td>Sp.Att</td>
					<td>Sp.Def</td>
					<td>Speed</td>
				</tr>
				<tr>
					<td><input type='text' placeholder='<?php echo number_format($Selected_Pokemon['SpAttack']); ?>' pattern="\d*" maxlength='9' id='base_sSPATT' /></td>
					<td><input type='text' placeholder='<?php echo number_format($Selected_Pokemon['SpDefense']); ?>' pattern="\d*" maxlength='9' id='base_sSPDEF' /></td>
					<td><input type='text' placeholder='<?php echo number_format($Selected_Pokemon['Speed']); ?>' pattern="\d*" maxlength='9' id='base_sSPEED' /></td>
				</tr>
			</table>
			<div>Individual Values</div>
			<table>
				<tr>
					<td>HP</td>
					<td>Attack</td>
					<td>Defense</td>
				</tr>
				<tr>
					<td><input type='text' placeholder='0' pattern="\d*" maxlength='2' id='iv_sHP' /></td>
					<td><input type='text' placeholder='0' pattern="\d*" maxlength='2' id='iv_sATT' /></td>
					<td><input type='text' placeholder='0' pattern="\d*" maxlength='2' id='iv_sDEF' /></td>
				</tr>
				<tr>
					<td>Sp.Att</td>
					<td>Sp.Def</td>
					<td>Speed</td>
				</tr>
				<tr>
					<td><input type='text' placeholder='0' pattern="\d*" maxlength='2' id='iv_sSPATT' /></td>
					<td><input type='text' placeholder='0' pattern="\d*" maxlength='2' id='iv_sSPDEF' /></td>
					<td><input type='text' placeholder='0' pattern="\d*" maxlength='2' id='iv_sSPEED' /></td>
				</tr>
			</table>
			<div>Effort Values</div>
			<table>
				<tr>
					<td>HP</td>
					<td>Attack</td>
					<td>Defense</td>
				</tr>
				<tr>
					<td><input type='text' placeholder='0' pattern="[0-9]+" maxlength='3' id='ev_sHP' /></td>
					<td><input type='text' placeholder='0' pattern="\d*" maxlength='3' id='ev_sATT' /></td>
					<td><input type='text' placeholder='0' pattern="\d*" maxlength='3' id='ev_sDEF' /></td>
				</tr>
				<tr>
					<td>Sp.Att</td>
					<td>Sp.Def</td>
					<td>Speed</td>
				</tr>
				<tr>
					<td><input type='text' placeholder='0' pattern="\d*" maxlength='3' id='ev_sSPATT' /></td>
					<td><input type='text' placeholder='0' pattern="\d*" maxlength='3' id='ev_sSPDEF' /></td>
					<td><input type='text' placeholder='0' pattern="\d*" maxlength='3' id='ev_sSPEED' /></td>
				</tr>
			</table>
		</div>
	</div>
</div>

<div class='panel' style='margin-left: 33.3%; width: 66.6%;'>
	<div class='panel-heading'>Moves</div>
	<div class='panel-body' id='selectedMoves'>
		<select>
			<option>Select Move #1</option>
		</select>
		<select>
			<option>Select Move #2</option>
		</select>
		<select>
			<option>Select Move #3</option>
		</select>
		<select>
			<option>Select Move #4</option>
		</select>
	</div>
</div>

<div id='popup' style='display: none;'>
	<div class='popup_bg'></div>
	<div class='popup_content'></div>
</div>

<?php
	}
?>

<script type='text/javascript'>
	$('#popup > .popup_bg').click(function() { 
		$('#popup').css('display', 'none');
	});

	function displayPopup()
	{
		$('#popup').css('display', 'block');

		$.ajax({
			type: 'post',
			url: 'ajax/pokemon_spawn.php',
			data: { req: 'user_list' },
			success: function(data)
			{
				$('#popup > .popup_content').html(data);
			},
			error: function(data)
			{
				$('#popup > .popup_content').html(data);
			}
		});
	}

	function selectType(type)
	{
		let version = $("#selectedPokemon > div:nth-child(2) > img").attr('src').split("/")[2];
		let pokemon = $('#selectedPokemon > div:nth-child(1)').text().split("(#")[1].split(")")[0];
		let generation = $("#selectedPokemon > div:nth-child(2) > img").attr('src').split("/")[4];

		if ( type == 'Normal' )
			type = "1 - Normal";
		else if ( type == "Shiny" )
			type = "2 - Shiny";
		if ( type == 'Sunset' )
			type = "3 - Sunset";
		else if ( type == "Shiny Sunset" )
			type = "4 - Shiny Sunset";
		
		$("#selectedPokemon > div:nth-child(2) > img").attr("src", "images/Pokemon/"+version+"/"+type+"/"+generation+"/"+pokemon+".gif");
	}

	function selectVersion(version)
	{
		let type = $("#selectedPokemon > div:nth-child(2) > img").attr('src').split("/")[3];
		let pokemon = $('#selectedPokemon > div:nth-child(1)').text().split("(#")[1].split(")")[0];
		let generation = $("#selectedPokemon > div:nth-child(2) > img").attr('src').split("/")[4];

		$("#selectedPokemon > div:nth-child(2) > img").attr("src", "images/Pokemon/"+version+"/"+type+"/"+generation+"/"+pokemon+".gif");
	}
</script>
	