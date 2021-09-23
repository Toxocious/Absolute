<?php
	require_once 'core/required/layout_top.php';
?>

<div class='panel content'>
	<div class='head'>Evolution Center</div>
	<div class='body padding-5px'>
		<div class='description'>
			Select one of the Pok&eacute;mon in your roster, and you'll be given a list of Pok&eacute;mon that it may evolve into.
		</div>

		<table class='border-gradient' style='margin-bottom: 5px;'>
			<thead>
				<th colspan='6'>Roster</th>
			</thead>
			
			<tbody>
				<tr id='Evo_Roster'>
					<?php
						for ( $i = 0; $i <= 5; $i++ )
						{
							if ( isset($User_Data['Roster'][$i]['ID']) )
							{
								$Roster_Slot[$i] = $Poke_Class->FetchPokemonData($User_Data['Roster'][$i]['ID']);
					
								echo "
									<td style='width: calc(100% / 6);' onclick='Display_Evos({$Roster_Slot[$i]['ID']});'>
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
	function Display_Evos(Pokemon_ID)
	{
		$.ajax({
			type: 'POST',
			url: '<?= DOMAIN_ROOT; ?>/core/ajax/evocenter/evolutions.php',
			data: {
				Request: 'Show_Evos',
				Pokemon_ID: Pokemon_ID
			},
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

	function Evolve_Pokemon(Pokemon_ID, Evolution_ID, Evolution_Alt_ID)
	{
		$.ajax({
			type: 'POST',
			url: 'core/ajax/evocenter/evolutions.php',
			data: {
				Request: 'Evolve',
				Pokemon_ID: Pokemon_ID,
				Evolution_ID: Evolution_ID,
				Evolution_Alt_ID: Evolution_Alt_ID,
			},
			success: function(data)
			{
				$('#Evo_Data').html(data);
				Display_Roster();
			},
			error: function(data)
			{
				$('#Evo_Data').html(data);
				Display_Roster();
			}
		});
	}

	function Display_Roster()
	{
		$.ajax({
			type: 'GET',
			url: 'core/ajax/evocenter/roster.php',
			data: { },
			success: function(data)
			{
				$('#Evo_Roster').html(data);
			},
			error: function(data)
			{
				$('#Evo_Roster').html(data);
			}
		});
	}
</script>

<?php
	require_once 'core/required/layout_bottom.php';
