<?php
	require '../../required/session.php';

	if ( isset($_POST['id']) )
	{
		$Pokemon = $PokeClass->FetchPokemonData($_POST['id']);

		try
		{
			$Fetch_Evolutions = $PDO->prepare("SELECT * FROM `evolution_data` WHERE `poke_id` = ? AND `alt_id` = ?");
			$Fetch_Evolutions->execute([$Pokemon['Pokedex_ID'], $Pokemon['Alt_ID']]);
			$Fetch_Evolutions->setFetchMode(PDO::FETCH_ASSOC);
		}
		catch ( PDOException $e )
		{
			HandleError( $e->getMessage() );
		}

		$EvoStatus = false;
		while ( $Evolution = $Fetch_Evolutions->fetch() )
		{
			$EvoStatus = true;

			$Evolution_Data = $PokeClass->FetchPokedexData($Evolution['to_poke_id'], $Evolution['to_alt_id']);

			//echo "<pre>"; var_dump($Evolution); echo "</pre>";
			//echo "<pre>"; var_dump($Evolution_Data); echo "</pre>";

			if ( $Evolution['alt_id'] !== 0 )
			{
				$Image_ID = str_pad($Evolution_Data['Pokedex_ID'], 3, "0", STR_PAD_LEFT) . "." . $Evolution_Data['Alt_ID'];
			}
			else
			{
				$Image_ID = str_pad($Evolution_Data['Pokedex_ID'], 3, "0", STR_PAD_LEFT);
			}

			if ( $Pokemon['Type'] != "Normal" )
			{
				$Evo_Name = $Pokemon['Type'] . $Evolution_Data['Full_Name'];
			}
			else
			{
				$Evo_Name = $Evolution_Data['Full_Name'];
			}

			echo "
				<div class='evolution'>
					<div class='image'>
						<img src='" . Domain(1) . "/images/Pokemon/{$Pokemon['Type']}/{$Image_ID}.png' />
						<div>
							<b>{$Evo_Name}</b>
						</div>
					</div>
					<div class='requirements'>
						<div><b>Requirements</b></div>
			";

			//if ( $Evolution['level'] != null || $Evolution['level'] != 0 || $Evolution_Data['Evo_Level'] != 0 || $Evolution_Data['Evo_Level'] != null )
			if ( $Evolution_Data['Evo_Level'] != 0 && $Evolution_Data['Evo_Level'] != null )
			{
				echo "<b>Level</b>: " . $Evolution_Data['Evo_Level'] . "<br />";
			}
			else
			{
				//echo "<b>Level</b>: N/A<br />";
			}

			if ( $Evolution['gender'] != null )
			{
				echo "<b>Gender</b>: " . $Evolution['gender'] . "<br />";
			}
			else
			{
				//echo "<b>Gender</b>: N/A<br />";
			}

			if ( $Evolution['item'] != null )
			{
				echo "<b>Held Item</b>: " . $Evolution['item'] . "<br />";
			}
			else
			{
				//echo "<b>Held Item</b>: N/A<br />";
			}

			if ( $Evolution['time'] != null )
			{
				echo "<b>Time</b>: " . $Evolution['time'] . "<br />";
			}
			else
			{
				//echo "<b>Time</b>: N/A<br />";
			}

			if ( $Evolution['min_happy'] != null )
			{
				echo "<b>Happiness</b>: " . $Evolution['min_happy'] . "<br />";
			}
			else
			{
				//echo "<b>Happiness</b>: N/A<br />";
			}

			echo "
					</div>
					<div class='button'>
						Evolve
					</div>
				</div>
			";
		}

		if ( !$EvoStatus )
		{
			echo "<div class='error' style='margin: 5px auto; width: 90%;'><b>{$Pokemon['Display_Name']}</b> can not evolve.</div>";
		}
	}
	else
	{
		return "An error has occurred.";
	}