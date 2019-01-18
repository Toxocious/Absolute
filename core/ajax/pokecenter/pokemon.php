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

				$Slots .= "<img class='spricon' src='{$Roster_Slot[$i]['Icon']}' onclick=\"handlePokemon('Move', {$Roster_Slot[$i]['ID']}, " . ($i + 1) . ");\" />";		
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

		echo "
			<div class='panel-heading'>
				{$Pokemon['Display_Name']}
				<div style='float: right;'>
					(#{$Pokemon['ID']})
				</div>
			</div>
			<div class='panel-body' style='padding: 3px;'>
				<div style='float: left; width: 30px;'>
					<img src='{$Pokemon['Gender_Icon']}' style='height: 20px; width: 20px;' /><br />
					$Item
				</div>
				<div style='float: left;'>
					<img class='spricon popup cboxElement' src='{$Pokemon['Sprite']}' href='" . Domain(1) . "/core/ajax/pokemon.php?id={$Pokemon['ID']}' />
				</div>
				<div style='float: left; padding-left: 5px; padding-top: 10px; width: 123px;'>
					<b>Level</b><br />" .
					number_format($Pokemon_Level) . "<br />
					<b>Experience</b><br />" .
					number_format($Pokemon['Experience']) . "
				</div>
				<div style='float: left; width: 180px;'>
					Move to your roster!
					<br />
					$Slots
				</div>
      </div>
		";
	}
	else
	{
		echo "An error has occured while attempting to gather the stats of the selected Pokemon.";
	}