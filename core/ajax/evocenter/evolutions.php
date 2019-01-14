<?php
	require '../../required/session.php';

	if ( isset($_POST['id']) )
	{
		$Poke_ID = Purify($_POST['id']);
		$Pokemon = $PokeClass->FetchPokemonData($Poke_ID);

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

			if ( $Evolution_Data['Alt_ID'] != '0' )
			{
				$Image_ID = str_pad($Evolution_Data['Pokedex_ID'], 3, "0", STR_PAD_LEFT) . "." . $Evolution_Data['Alt_ID'];
			}
			else
			{
				$Image_ID = str_pad($Evolution_Data['Pokedex_ID'], 3, "0", STR_PAD_LEFT);
			}

			if ( $Pokemon['Type'] != "Normal" )
			{
				$Evo_Name = $Pokemon['Type'] . $Evolution_Data['Name'];
			}
			else
			{
				$Evo_Name = $Evolution_Data['Name'];
			}

			echo "
				<div class='evolution'>
					<div class='image'>
						<img src='" . Domain(1) . "/images/Pokemon/Sprites/{$Pokemon['Type']}/{$Image_ID}.png' />
						<div>
							<b>{$Evo_Name}</b>
						</div>
					</div>
					<div class='requirements'>
						<div><b>Requirements</b></div>
			";

			if ( $Evolution['level'] != null && $Evolution['level'] != 0 )
			{
				echo "
					<div>
						<b>Level</b><br /><br />
						
						Lv.<br />
						{$Evolution['level']}
					</div>
				";
			}

			if ( $Evolution['gender'] != null )
			{
				echo "
					<div>
						<b>Gender</b><br /><br />
						
						<img src='" . Domain(1) . "images/Assets/{$Evolution['gender']}.svg' style='height: 20px; width: 20px;' /><br />
						" . ucfirst($Evolution['gender']) . "
					</div>
				";
			}

			if ( $Evolution['item'] != null )
			{
				echo "
					<div>
						<b>Item</b><br /><br />

						<img src='" . Domain(1) . "images/Items/{$Evolution['item']}.png' /><br />
						{$Evolution['item']}
					</div>
				";
			}

			if ( $Evolution['held_item'] != null )
			{
				echo "
					<div>
						<b>Held Item</b><br /><br />

						<img src='" . Domain(1) . "images/Items/{$Evolution['held_item']}.png' /><br />
						{$Evolution['held_item']}
					</div>
				";
			}

			if ( $Evolution['time'] != null )
			{
				echo "
					<div>
						<b>Time</b><br /><br />
						
						~sun/crescent moon icon~<br />
						{$Evolution['time']}
					</div>
				";
			}

			if ( $Evolution['min_happy'] != null )
			{
				echo "
					<div style='border-right: none;'>
						<b>Happiness</b><br /><br />
						
						~heart icon~<br />
						{$Evolution['min_happy']}
					</div>
				";
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