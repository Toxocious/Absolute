<?php
	require '../../required/session.php';

	if ( isset($User_Data['id']) && isset($_POST['PokeID']) )
	{
		$Pokemon_Data = $PokeClass->FetchPokemonData(Purify($_POST['PokeID']));
		$Pokemon_Level = FetchLevel($Pokemon_Data['Experience'], 'Pokemon');
		

		echo "
			<div style='float: left;'>
				<img class='spricon popup cboxElement' src='{$Pokemon_Data['Sprite']}' href='" . Domain(1) . "/core/ajax/pokemon.php?id={$Pokemon_Data['ID']}' />
			</div>
			<div>
				<u><b>{$Pokemon_Data['Display_Name']}</b></u><br />
				<b>Level:</b> " . number_format($Pokemon_Level) . "<br />
				<b>Experience:</b> " . number_format($Pokemon_Data['Experience']) . "<br />
				<b>Gender:</b> " . $Pokemon_Data['Gender'] . "
			</div>
		";
	}
	else
	{
		echo "An error has occured while attempting to gather the stats of the selected Pokemon.";
	}