<?php
	require '../../required/session.php';

	if ( isset($User_Data['id']) && isset($_POST['PokeID']) )
	{
		$Pokemon = $Poke_Class->FetchPokemonData(Purify($_POST['PokeID']));
		$Pokemon_Level = number_format(FetchLevel($Pokemon['Experience_Raw'], 'Pokemon'));

		if ( $Pokemon['Item_ID'] != null )
		{
			$Item = "
				<div class='gradient-icon' style='float: left; height: 28px; margin: -28px 0px 0px 81px; width: 28px;'>
					<img src='{$Pokemon['Item_Icon']}' style='height: 24px; width: 24px;' />
				</div>
			";
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
				$Roster_Slot[$i] = $Poke_Class->FetchPokemonData($Roster[$i]['ID']);

				$Slots .= "
					<div class='border-gradient hover' style='margin: 0px 0px 0px 5px; float: left; height: 32px; width: 42px;'>
						<div style='padding: 2px;'>
							<img src='{$Roster_Slot[$i]['Icon']}' onclick=\"handlePokemon('Move', {$Pokemon['ID']}, " . ($i + 1) . ");\" />
						</div>
					</div>
				";
			}
			else
			{
				$Slots .= "
					<div class='border-gradient hover' style='margin: 0px 0px 0px 5px; float: left; height: 32px; width: 42px;'>
						<div style='padding: 2px;'>
							<img src='" . Domain(1) . "/images/Pokemon/0_mini.png' style='height: 30px; width: 40px;' onclick=\"handlePokemon('Move', {$Pokemon['ID']}, " . ($i + 1) . ");\" />
						</div>
					</div>
				";
			}
		}

		$Total_Stat = array_sum($Pokemon['Stats']);
		$Total_IV = array_sum($Pokemon['IVs']);
		$Total_EV = array_sum($Pokemon['EVs']);

		echo "
			<div style='flex-basis: 60%;'>
				<div class='border-gradient hover hw-96px padding-0px' style='margin: 0 auto;'>
					<div>
						<img class='popup cboxElement' src='{$Pokemon['Sprite']}' href='" . Domain(1) . "/core/ajax/pokemon.php?id={$Pokemon['ID']}' />
					</div>
				</div>

				<div style='float: left; margin-top: -65px; margin-left: 50px;'>
					<div class='border-gradient hw-30px' style='margin: 5px 0px 5px 5px;'>
						<div>
							<img src='{$Pokemon['Gender_Icon']}' style='height: 24px; width: 24px;' />
						</div>
					</div>
					{$Item}
				</div>

				<div class='flex' style='margin: 5px;'>
					<div style='flex-basis: 50%;'>
						<b>Level</b><br />
						{$Pokemon_Level}<br />
					</div>
					<div style='flex-basis: 50%;'>
						<b>Experience</b><br />
						{$Pokemon['Experience']}
					</div>
				</div>
				
				<div style='margin: 0 auto; padding-top: 3px; width: fit-content;'>
					{$Slots}
				</div>
			</div>

			<div style='flex-basis: 40%;'>
				<table class='border-gradient' style='width: 100%;'>
					<thead>
						<tr>
							<th style='width: 25%;'>Stat</th>
							<th style='width: 25%;'>Base</th>
							<th style='width: 25%;'>IV</th>
							<th style='width: 25%;'>EV</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td><b>HP</b></td>
							<td>" . number_format($Pokemon['Stats'][0]) . "</td>
							<td>" . number_format($Pokemon['IVs'][0]) . "</td>
							<td>" . number_format($Pokemon['EVs'][0]) . "</td>
						</tr>
						<tr>
							<td><b>Attack</b></td>
							<td>" . number_format($Pokemon['Stats'][1]) . "</td>
							<td>" . number_format($Pokemon['IVs'][1]) . "</td>
							<td>" . number_format($Pokemon['EVs'][1]) . "</td>
						</tr>
						<tr>
							<td><b>Defense</b></td>
							<td>" . number_format($Pokemon['Stats'][2]) . "</td>
							<td>" . number_format($Pokemon['IVs'][2]) . "</td>
							<td>" . number_format($Pokemon['EVs'][2]) . "</td>
						</tr>
						<tr>
							<td><b>Sp. Att</b></td>
							<td>" . number_format($Pokemon['Stats'][3]) . "</td>
							<td>" . number_format($Pokemon['IVs'][3]) . "</td>
							<td>" . number_format($Pokemon['EVs'][3]) . "</td>
						</tr>
						<tr>
							<td><b>Sp. Def</b></td>
							<td>" . number_format($Pokemon['Stats'][4]) . "</td>
							<td>" . number_format($Pokemon['IVs'][4]) . "</td>
							<td>" . number_format($Pokemon['EVs'][4]) . "</td>
						</tr>
						<tr>
							<td><b>Speed</b></td>
							<td>" . number_format($Pokemon['Stats'][5]) . "</td>
							<td>" . number_format($Pokemon['IVs'][5]) . "</td>
							<td>" . number_format($Pokemon['EVs'][5]) . "</td>
						</tr>
					</tbody>
				</table>
			</div>
		";
	}
	else
	{
		echo "An error has occured while attempting to gather the stats of the selected Pokemon.";
	}