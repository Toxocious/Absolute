<?php
	require 'core/required/layout_top.php';
?>

<div class='content'>
	<div class='head'>Evolution Center</div>
	<div class='box'>
		<div id='status'></div>

		<div class='panel' style='margin-bottom: 5px;'>
			<div class='panel-heading'>Roster</div>
			<div class='panel-body'>
				<?php
					for ( $i = 0; $i <= 5; $i++ )
					{
						if ( isset($Roster[$i]['ID']) )
						{
							$Roster_Slot[$i] = $Poke_Class->FetchPokemonData($Roster[$i]['ID']);
				
							echo "
								<div class='roster_slot mini' style='padding: 5px;' onclick='displayEvos({$Roster_Slot[$i]['ID']});'>
									<img class='spricon' src='{$Roster_Slot[$i]['Icon']}' ?><br />
									<b>{$Roster_Slot[$i]['Display_Name']}</b><br />
								</div>
							";
						}
						else
						{
							$Roster_Slot[$i]['Sprite'] = Domain(3) . 'images/pokemon/0.png';
							$Roster_Slot[$i]['Icon'] = Domain(3) . 'images/pokemon/0_mini.png';
							$Roster_Slot[$i]['Display_Name'] = 'Empty';
							$Roster_Slot[$i]['Level'] = '0';
							$Roster_Slot[$i]['Experience'] = '0';
				
							echo "
								<div class='roster_slot mini' style='padding: 5px;'>
									<img class='spricon' src='{$Roster_Slot[$i]['Icon']}' ?><br />
									<b>{$Roster_Slot[$i]['Display_Name']}</b>
								</div>
							";
						}
					}
				?>
			</div>
		</div>

		<div class='panel'>
			<div class='panel-heading'>Evolutions</div>
			<div class='panel-body' id='evoData'>
					<div style='padding: 5px;'>Please select the Pokemon that you wish to evolve.</div>
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
			},
			error: function(data)
			{
				$('#status').html(data);
			}
		});
	}
</script>

<?php
	require 'core/required/layout_bottom.php';