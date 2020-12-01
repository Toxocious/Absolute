<?php
	require 'core/required/layout_top.php';
?>

<div class='panel content'>
	<div class='head'>Evolution Center</div>
	<div class='body padding-5px'>
		<div id='status'></div>

		<div class='description'>
			Select one of the Pok&eacute;mon in your roster, and you'll be given a list of Pok&eacute;mon that it may evolve into.
		</div>

		<table class='border-gradient' style='margin-bottom: 5px;'>
			<thead>
				<th colspan='6'>Roster</th>
			</thead>
			
			<tbody>
				<tr id='evoRoster'>
					<?php
						for ( $i = 0; $i <= 5; $i++ )
						{
							if ( isset($Roster[$i]['ID']) )
							{
								$Roster_Slot[$i] = $Poke_Class->FetchPokemonData($Roster[$i]['ID']);
					
								echo "
									<td style='width: calc(100% / 6);' onclick='DisplayEvos(\"Show_Evos\", {$Roster_Slot[$i]['ID']});'>
										<img class='spricon' src='{$Roster_Slot[$i]['Icon']}' ?><br />
										<b>{$Roster_Slot[$i]['Display_Name']}</b><br />
									</td>
								";
							}
							else
							{
								$Roster_Slot[$i]['Icon'] = DOMAIN_SPRITES . '/Pokemon/Sprites/0_mini.png';
								$Roster_Slot[$i]['Display_Name'] = 'Empty';
					
								echo "
									<td style='width: calc(100% / 6);'>
										<img class='spricon' src='{$Roster_Slot[$i]['Icon']}' ?><br />
										<b>{$Roster_Slot[$i]['Display_Name']}</b>
									</td>
								";
							}
						}
					?>
				</tr>
			</tbody>
		</table>

		<table class='border-gradient' id='Evo_Data' style='width: 750px;'>
			<thead>
				<tr>
					<th colspan='7'>
						Evolutions
					</th>
				</tr>
			</thead>

			<tbody>
				<tr>
					<td colspan='7' style='padding: 5px;'>
						Please select the Pok&eacute;mon that you wish to evolve.
					</td>
				</tr>
			</tbody>
		</table>
	</div>
</div>

<script type='text/javascript'>
	function DisplayEvos(Request, Pokemon_ID)
	{
		$.ajax({
			type: 'POST',
			url: '<?= DOMAIN_ROOT; ?>/core/ajax/evocenter/evolutions.php',
			data: { Request: 'Show_Evos', Pokemon_ID: Pokemon_ID },
			success: function(data)
			{
				$('#Evo_Data').html(data);
			},
			error: function(data)
			{
				$('#Evo_Data').html(data);
			}
		});
	}

	function evolve(evo_id, evo_to = null, evo_alt = null)
	{
		$.ajax({
			type: 'post',
			url: 'core/ajax/evocenter/evolutions.php',
			data: { evo_id: evo_id, evo_to: evo_to, evo_alt: evo_alt },
			success: function(data)
			{
				$('#status').html(data);
				$('#Evo_Data').html(`
					<div class='panel' style='margin: 0 auto; width: 80%;'>
						<div class='head'>Evolutions</div>
						<div class='body'>
								<div style='padding: 5px;'>Please select the Pokemon that you wish to evolve.</div>
						</div>
					</div>
				`);
				DisplayRoster();
			},
			error: function(data)
			{
				$('#status').html(data);
				$('#Evo_Data').html(`
					<div class='panel' style='margin: 0 auto; width: 80%;'>
						<div class='head'>Evolutions</div>
						<div class='body'>
								<div style='padding: 5px;'>Please select the Pokemon that you wish to evolve.</div>
						</div>
					</div>
				`);
				DisplayRoster();
			}
		});
	}

	function DisplayRoster()
	{
		$.ajax({
			type: 'get',
			url: 'core/ajax/evocenter/roster.php',
			success: function(data)
			{
				$('#evoRoster').html(data);
			},
			error: function(data)
			{
				$('#evoRoster').html(data);
			}
		});
	}
</script>

<?php
	require 'core/required/layout_bottom.php';
