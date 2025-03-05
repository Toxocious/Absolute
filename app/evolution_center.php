<?php
	require_once 'core/required/layout_top.php';
?>

<div class='panel content'>
	<div class='head'>Evolution Center</div>
	<div class='body padding-5px'>
    <div class='flex row'>
        <?php
            for ( $i = 0; $i <= 5; $i++ )
            {
              if ( isset($User_Data['Roster'][$i]['ID']) )
              {
                $Roster_Slot[$i] = GetPokemonData($User_Data['Roster'][$i]['ID']);

                echo "
                  <div style='width: calc(100% / 6);' onclick='Display_Evos({$Roster_Slot[$i]['ID']});'>
                    <img class='spricon' src='{$Roster_Slot[$i]['Sprite']}' ?><br />
                    <b>{$Roster_Slot[$i]['Display_Name']}</b><br />
                  </div>
                ";
              }
              else
              {
                $Roster_Slot[$i]['Sprite'] = DOMAIN_SPRITES . '/Pokemon/Sprites/0.png';
                $Roster_Slot[$i]['Display_Name'] = 'Empty';

                echo "
                  <div style='width: calc(100% / 6);'>
                    <img class='spricon' src='{$Roster_Slot[$i]['Sprite']}' ?><br />
                    <b>{$Roster_Slot[$i]['Display_Name']}</b>
                  </div>
                ";
              }
            }
        ?>
    </div>
    <br />

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
