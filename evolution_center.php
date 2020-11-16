<?php
	require 'core/required/layout_top.php';
?>

<div class='panel content'>
	<div class='head'>Evolution Center</div>
	<div class='body padding-5px'>
		<div id='status'></div>

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
									<td style='width: calc(100% / 6);' onclick='displayEvos({$Roster_Slot[$i]['ID']});'>
										<img class='spricon' src='{$Roster_Slot[$i]['Icon']}' ?><br />
										<b>{$Roster_Slot[$i]['Display_Name']}</b><br />
									</td>
								";
							}
							else
							{
								$Roster_Slot[$i]['Icon'] = DOMAIN_SPRITES . 'images/pokemon/0_mini.png';
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

		<div id='evoData'>
			<div class='panel' style='margin: 0 auto; width: 80%;'>
				<div class='head'>Evolutions</div>
				<div class='body'>
						<div style='padding: 5px;'>Please select the Pokemon that you wish to evolve.</div>
				</div>
			</div>
		</div>
	</div>
</div>

<script type='text/javascript'>
	function displayEvos(id)
	{
		$.ajax({
			type: 'post',
			url: 'core/ajax/evocenter/evolutions.php',
			data: { id: id },
			success: function(data)
			{
				$('#evoData').html(data);
			},
			error: function(data)
			{
				$('#evoData').html(data);
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
				$('#evoData').html(`
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
				$('#evoData').html(`
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