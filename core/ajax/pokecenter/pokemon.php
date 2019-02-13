<?php
	require '../../required/session.php';

	if ( isset($User_Data['id']) && isset($_POST['PokeID']) )
	{
		$Pokemon = $PokeClass->FetchPokemonData(Purify($_POST['PokeID']));
		$Pokemon_Level = FetchLevel($Pokemon['Experience'], 'Pokemon');
		
		if ( $Pokemon['Item_ID'] != null )
		{
			$Item = "<img src='{$Pokemon['Item_Icon']}' />";
		}
		else
		{
			$Item = '';
		}

		$Slots = '';
		for ( $i = 0; $i <= 5; $i++ )
		{
			if ( isset($Roster[$i]['ID'])  )
			{
				$Roster_Slot[$i] = $PokeClass->FetchPokemonData($Roster[$i]['ID']);

				$Slots .= "<img class='spricon' src='{$Roster_Slot[$i]['Icon']}' onclick=\"handlePokemon('Move', {$Pokemon['ID']}, " . ($i + 1) . ");\" />";		
			}
			else
			{
				$Slots .= "<img class='spricon' src='" . Domain(1) . "/images/Pokemon/0_mini.png' style='width: 40px;' onclick=\"handlePokemon('Move', {$Pokemon['ID']}, " . ($i + 1) . ");\" />";
			}

			if ( $i == 2 )
			{
				$Slots .= "<br />";
			}
		}

		$Total_Stat = array_sum($Pokemon['Stats']);
		$Total_IV = array_sum($Pokemon['IVs']);
		$Total_EV = array_sum($Pokemon['EVs']);

		echo "
			<div class='panel-heading'>
				{$Pokemon['Display_Name']}
				<div style='float: right;'>
					(#{$Pokemon['ID']})
				</div>
			</div>
			<div class='panel-body' style='padding: 3px;'>
				<div style='float: left; width: 50%;'>
					<div>
						<img src='{$Pokemon['Gender_Icon']}' style='height: 20px; width: 20px;' />
						<img class='spricon popup cboxElement' src='{$Pokemon['Sprite']}' href='" . Domain(1) . "/core/ajax/pokemon.php?id={$Pokemon['ID']}' />
						$Item
					</div>
					<b>Level</b><br />" .
					number_format($Pokemon_Level) . "<br />
					<b>Experience</b><br />" .
					number_format($Pokemon['Experience']) . "
				</div>
				<div style='float: left; padding-top: 10px; width: 50%;'>
					Move to your roster!
					<br />
					$Slots
				</div>
				<div>
					<table class='standard' style='float: left; margin: 12px auto; width: calc(100% - 140px);'>
						<thead>
							<tr>
								<th> </th>
								<th>HP</th>
								<th>Att</th>
								<th>Def</th>
								<th>SpA</th>
								<th>SpD</th>
								<th>Spe</th>
								<th>Total</th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td><b>Base</b></td>
								<td>" . number_format($Pokemon['Stats'][0]) . "</td>
								<td>" . number_format($Pokemon['Stats'][1]) . "</td>
								<td>" . number_format($Pokemon['Stats'][2]) . "</td>
								<td>" . number_format($Pokemon['Stats'][3]) . "</td>
								<td>" . number_format($Pokemon['Stats'][4]) . "</td>
								<td>" . number_format($Pokemon['Stats'][5]) . "</td>
								<td>" . number_format($Total_Stat) . "</td>
							</tr>
							<tr>
								<td><b>IV's</b></td>
								<td>" . number_format($Pokemon['IVs'][0]) . "</td>
								<td>" . number_format($Pokemon['IVs'][1]) . "</td>
								<td>" . number_format($Pokemon['IVs'][2]) . "</td>
								<td>" . number_format($Pokemon['IVs'][3]) . "</td>
								<td>" . number_format($Pokemon['IVs'][4]) . "</td>
								<td>" . number_format($Pokemon['IVs'][5]) . "</td>
								<td>" . number_format($Total_IV) . "</td>
							</tr>
							<tr>
								<td><b>EV's</b></td>
								<td>" . number_format($Pokemon['EVs'][0]) . "</td>
								<td>" . number_format($Pokemon['EVs'][1]) . "</td>
								<td>" . number_format($Pokemon['EVs'][2]) . "</td>
								<td>" . number_format($Pokemon['EVs'][3]) . "</td>
								<td>" . number_format($Pokemon['EVs'][4]) . "</td>
								<td>" . number_format($Pokemon['EVs'][5]) . "</td>
								<td>" . number_format($Total_EV) . "</td>
							</tr>
						</tbody>
					</table>
				</div>
      </div>
		";
	}
	else
	{
		echo "An error has occured while attempting to gather the stats of the selected Pokemon.";
	}