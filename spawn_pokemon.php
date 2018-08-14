<?php
	require 'layout_top.php';
	
	if ( $User_Data['Rank'] !== '420' || $User_Data['id'] > 2 )
		header('Location: news.php');
?>

<style>
	body > div.content > div.box > button { background: #1d2639 !important; border: 1px solid #4A618F; font-weight: bold; margin-bottom: 0px; margin-top: 3px; width: 100%; }
	body > div.content > div.box > button:hover { background: #3b4d72 !important; }

	#selectedPokemon > div:nth-child(1) { background: #3b4d72; font-weight: bold; }
	#selectedPokemon > div:nth-child(2) { padding: 12px 0px; }
	#selectedPokemon div.stats > div { background: #4A618F; font-weight: bold; }
	#selectedPokemon div.stats table { width: 100%; }
	#selectedPokemon div.stats table:not(:last-child) tr td:nth-child(even) { border-left: 2px solid #3b4d72; border-right: 2px solid #3b4d72; }
	#selectedPokemon div.stats table:not(:last-child) tr:nth-child(odd) td { background: #3b4d72; font-weight: bold; }
	#selectedPokemon div.stats table:not(:last-child) tr td { width: calc(100% / 3); }
	#selectedPokemon div.stats table:last-child tr:not(:last-child) { border-bottom: 2px solid #3b4d72; }
	#selectedPokemon div.stats table:last-child tr td:not(:last-child) { border-right: 2px solid #3b4d72; }

	#selectedStats div.stats > div { background: #4A618F; font-weight: bold; }
	#selectedStats div.stats table { width: 100%; }
	#selectedStats div.stats table tr td:nth-child(even) { border-left: 2px solid #3b4d72; border-right: 2px solid #3b4d72; }
	#selectedStats div.stats table tr:nth-child(odd) td { background: #3b4d72; font-weight: bold; }
	#selectedStats div.stats table tr td { width: calc(100% / 3); }
	#selectedStats div.stats table input { background: transparent; border: none; display: block; margin-bottom: 0px; padding: 1px; text-align: center; width: 100%; }

	.btnContainer button { background: #1d2639 !important; border: none; border-radius: 0px; }
	.btnContainer button:hover { background: #3b4d72 !important; }
	.btnContainer div:nth-child(1) button { border-right: 1px solid #4A618F; height: 51px; width: 100%; }
	.btnContainer div:nth-child(2) button { height: 25px; margin: 0; width: 50%; }
	.btnContainer div:nth-child(2) button:nth-child(-n+2) { border-bottom: 1px solid #4A618F; }
	.btnContainer div:nth-child(2) button:nth-child(odd) { margin-right: -5px; }
	.btnContainer div:nth-child(2) button:nth-child(even) { border-left: 1px solid #4A618F; }

	#selectedMoves select { padding: 6px; text-align: center; width: 49%; }
	#selectedMoves select:nth-child(-n+2) { margin-bottom: 5px; }

	.options { margin-bottom: -4px; margin-top: 3px; }
	.options button,
	.options input,
	.options select { border: 1px solid #4A618F; padding: 5px; width: 49.5%; }
</style>

<?php
	if ( isset($_POST['poke_id']) )
	{
		echo "<div class='description'>
						You've successfully spawned in a Pokemon.<br />
						Recipient: 
					</div>";
	}
	else
	{
?>

<div class='content'>
	<div class='head'>Pokemon Spawner</div>
	<div class='box' id='pokemonSpawner'>
	
		<div class='panel' style='float: left; margin-bottom: -1px; width: 33%;'>
			<div class='panel-heading'>Pokemon Preview</div>
			<div class='panel-body' id='selectedPokemon'>
				<div>
					???
				</div>
				<div>
					<img src='images/Assets/Pokeball.png' />
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
							<td id='base_hp'>0</td>
							<td id='base_att'>0</td>
							<td id='base_def'>0</td>
						</tr>
						<tr>
							<td>Sp.Att</td>
							<td>Sp.Def</td>
							<td>Speed</td>
						</tr>
						<tr>
							<td id='base_spatt'>0</td>
							<td id='base_spdef'>0</td>
							<td id='base_speed'>0</td>
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
			<div class='panel-heading'>Pokemon & Type</div>
			<div class='panel-body btnContainer'>
				<div style='float: left; width: 50%;'>
					<button class='popup cboxElement' href='ajax/spawn_list.php'>Select a Pokemon</button>
				</div>

				<div style='margin-left: 50%; width: 50%;'>
					<button>Normal</button>
					<button>Shiny</button>
					<button>Sunset</button>
					<button>Shiny Sunset</button>
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
							<td><input type='text' placeholder='0' pattern="\d*" maxlength='9' id='base_sHP' /></td>
							<td><input type='text' placeholder='0' pattern="\d*" maxlength='9' id='base_sATT' /></td>
							<td><input type='text' placeholder='0' pattern="\d*" maxlength='9' id='base_sDEF' /></td>
						</tr>
						<tr>
							<td>Sp.Att</td>
							<td>Sp.Def</td>
							<td>Speed</td>
						</tr>
						<tr>
							<td><input type='text' placeholder='0' pattern="\d*" maxlength='9' id='base_sSPATT' /></td>
							<td><input type='text' placeholder='0' pattern="\d*" maxlength='9' id='base_sSPDEF' /></td>
							<td><input type='text' placeholder='0' pattern="\d*" maxlength='9' id='base_sSPEED' /></td>
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

		<div class='options'>
			<div>
				<select id='user'>
					<option>Username / User ID</option>
					<option value='0'>User ID</option>
					<option value='1'>User Name</option>
				</select>
				<input type='text' placeholder='Username/ID Of Recipient' />
			</div>
			<div>
				<button onclick='retrievePokemon();'>Retrieve Selected Pokemon</button>
				<button onclick='spawnPokemon();'>Spawn Pokemon</button>
			</div>
		</div>

	</div>	
</div>

<?php
	}
?>

<script type='text/javascript'>
	$(document).ready(function ()
	{
		$("input").keypress(function(e)
		{
			if ( e.which != 8 && e.which != 0 && (e.which < 48 || e.which > 57) )
			{
					return false;
			}
		});
	});

	function retrievePokemon()
	{
		console.log('attempting to retrieve selected pokemon');

    parent.$.colorbox.close();

		if ( $('.cboxElement > img').hasClass('checked') )
			$('#pokemonSpawner').html( $('.cboxElement > img').hasClass('checked').attr('src') )
		else
			$('#pokemonSpawner').html( "you didn't select a pokemon" );
	}

	function selectedPokemon()
	{
		console.log('attempting to spawn in selected pokemon');

    parent.$.colorbox.close();

		if ( $('.cboxElement > img').hasClass('checked') )
			$('#pokemonSpawner').html( $('.cboxElement > img').hasClass('checked').attr('src') )
		else
			$('#pokemonSpawner').html( "you didn't select a pokemon" );
	}
	
	$("input[id^='iv']").change(function()
	{
		if ( parseInt(this.value) > 31 )
		{
        this.value = 31;
     } 
	});

	$("input[id^='ev']").change(function()
	{
		if ( parseInt(this.value) > 255 )
		{
        this.value = 255;
     } 
	});
</script>

<?php
	require 'layout_bottom.php';
?>