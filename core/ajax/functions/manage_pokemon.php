<?php
	require '../../required/session.php';

	if ( isset($_SESSION['abso_user']) )
	{
		try
		{
			$Roster_Fetch = $PDO->prepare("SELECT `ID`, `Slot` FROM `pokemon` WHERE `Owner_Current` = ? AND `Location` = 'Roster' ORDER BY `Slot` ASC LIMIT 6");
			$Roster_Fetch->execute([$User_Data['id']]);
			$Roster_Fetch->setFetchMode(PDO::FETCH_ASSOC);
			$Roster = $Roster_Fetch->fetchAll();
		}
		catch ( PDOException $e )
		{
			HandleError( $e->getMessage() );
		}

		$Roster_Count = count($Roster);

		/**
		 * Handle moving a Pokemon within the user's account.
		 */
		if ( $_POST['Request'] == 'Move' && isset($_POST['PokeID']) && isset($_POST['Slot']) )
		{
			$Pokemon_Data = $PokeClass->FetchPokemonData($_POST['PokeID']);
			$Pokemon_Move = $PokeClass->MovePokemon($Pokemon_Data['ID'], $_POST['Slot']);

			if ( $Pokemon_Move === true )
			{
				if ( $_POST['Slot'] === 7 )
				{
					echo "<div class='success'><b>{$Pokemon_Data['Display_Name']}</b> has been moved to your box.</div>";
				}
				else
				{
					echo "<div class='success'><b>{$Pokemon_Data['Display_Name']}</b> has been moved to slot {$_POST['Slot']}.</div>";
				}
			}
			else
			{
				echo $Pokemon_Move;
			}

			echo "
				<div class='panel' style='margin-bottom: 5px; margin-top: 5px; width: 100%;'>
					<div class='panel-heading'>Roster</div>
					<div class='panel-body' id='pokecenter_roster'>
			";

			for ( $i = 0; $i <= 5; $i++ )
      {
        if ( isset($Roster[$i]['ID']) )
        {
          $Roster_Slot[$i] = $PokeClass->FetchPokemonData($Roster[$i]['ID']);

          if ( $Roster_Slot[$i]['Item'] != null )
          {
            $Item = "<img src='{$Roster_Slot[$i]['Item_Icon']}' style='margin: 5px 0px 0px -10px; position: absolute;' />";
          }
          else
          {
            $Item = "";
          }
          
          echo "
            <div class='roster_slot full' style='/*width: calc(100% / 3);*/'>
              <div style='float: left;' class='slots left'>
          ";

          for ($x = 1; $x <= 3; ++$x) {
            if ( $x == $i + 1 || $x > count($Fetch_Roster) )
            {
              echo "<div><span style='color: #000; display: block; padding: 13px;'>$x</span></div>";
            }
            else
            {
              echo "<div><a href='javascript:void(0);' onclick=\"handlePokemon('Move', {$Roster_Slot[$i]['ID']}, $x);\" style='display: block; padding: 13px;'>$x</a></div>";
            }
          }

          echo "
            </div>
            <img src='{$Roster_Slot[$i]['Gender_Icon']}' style='height: 20px; margin: 10px 0px 0px -20px; position: absolute; width: 20px;' />
            <img class='spricon' src='{$Roster_Slot[$i]['Sprite']}' ?>
            $Item
            <div style='float: right;' class='slots right'>
          ";

          for ($x = 4; $x <= 6; ++$x) {
            if ( $x == $i + 1 || $x > count($Fetch_Roster) )
            {
              echo "<div><span style='color: #000; display: block; padding: 13px;'>$x</span></div>";
            }
            else
            {
              echo "<div><a href='javascript:void(0);' onclick=\"handlePokemon('Move', {$Roster_Slot[$i]['ID']}, $x);\" style='display: block; padding: 13px;'>$x</a></div>";
            }
          }

          echo "
              </div>
              <div><b>{$Roster_Slot[$i]['Display_Name']}</b></div>
              <div class='info'>
                <div>Level</div>
                <div>{$Roster_Slot[$i]['Level']}</div>
              </div>
              <div class='info'>
                <div>Experience</div>
                <div>{$Roster_Slot[$i]['Experience']}</div>
              </div>
            </div>
          ";
        }
        else
        {
          $Roster_Slot[$i]['Sprite'] = Domain(3) . 'images/pokemon/0.png';
          $Roster_Slot[$i]['Display_Name'] = 'Empty';
          $Roster_Slot[$i]['Level'] = '0';
          $Roster_Slot[$i]['Experience'] = '0';

          echo "
            <div class='roster_slot full' style='/*width: calc(100% / 3);*/'>
              <img src='{$Roster_Slot[$i]['Sprite']}' />
              <div><b>{$Roster_Slot[$i]['Display_Name']}</b></div>
              <div class='info'>
                <div>Level</div>
                <div>{$Roster_Slot[$i]['Level']}</div>
              </div>
              <div class='info'>
                <div>Experience</div>
                <div>{$Roster_Slot[$i]['Experience']}</div>
              </div>
            </div>
          ";
        }
      }
			
			echo "
					</div>
				</div>

				<div class='panel' style='float: left; width: calc(100% / 2 - 2.5px);'>
					<div class='panel-heading'>Box</div>
					<div class='panel-body boxed_pokemon' style='padding: 3px;'>
			";

			try
			{
				$Box_Query = $PDO->prepare("SELECT * FROM `pokemon` WHERE `Owner_Current` = ? AND `Slot` = 7 ORDER BY `Pokedex_ID` ASC LIMIT 50");
				$Box_Query->execute([$User_Data['id']]);
				$Box_Query->setFetchMode(PDO::FETCH_ASSOC);
				$Box_Pokemon = $Box_Query->fetchAll();
			}
			catch (PDOException $e)
			{
				HandleError( $e->getMessage() );
			}
			
			foreach ( $Box_Pokemon as $Index => $Pokemon )
			{
				$Pokemon = $PokeClass->FetchPokemonData($Pokemon['ID']);
				echo "<img class='spricon popup cboxElement' src='{$Pokemon['Icon']}' onclick='displayPokeData({$Pokemon['ID']});'/>";
			}
			
			if ( count($Box_Pokemon) == 0 )
			{
				echo "No Pokemon were found in your box.";
			}

			echo "
					</div>
				</div>

				<div class='panel' style='float: right; width: calc(100% / 2 - 2.5px);'>
					<div class='panel-heading'>Selected Pokemon</div>
					<div class='panel-body' style='padding: 3px;' id='pokeData'>
						<div style='padding: 5px;'>Please select a Pokemon to view it's statistics.</div>
					</div>
				</div>
			";
		}

		/**
		 * Display misc stats of a Pokemon when you select it from your box.
		 * Allow the user to add the Pokemon to their roster.
		 */
		if ( $_POST['Request'] == 'Stats' )
		{
			if ( isset($_POST['PokeID']) )
			{
				$Pokemon = $PokeClass->FetchPokemonData($_POST['PokeID']);

				echo "
					<div class='panel-heading'><div>{$Pokemon['Display_Name']}</div><div style='float: right; margin-top: -21px;'>(#".number_format($Pokemon['ID']).")</div></div>
					<div class='panel-body' style='padding: 5px;'>
						<div style='float: left;'>
							<img class='cboxElement popup' src='{$Pokemon['Sprite']}' href='core/ajax/pokemon.php?id={$Pokemon['ID']}' />
						</div>
						<div style='text-align: left;'>
							<div style='text-align: center;'><b>{$Pokemon['Display_Name']}</b></div>
							<b>Level</b>: {$Pokemon['Level']}<br />
							<b>Exp</b>: {$Pokemon['Experience']}<br />
						</div>
						<div>
							Choose a slot to put your Pokemon in.<br />
						</div>
					</div>
				";
			}
		}

		/**
		 * Handles updating the user's rosters.
		 * -> Userbar roster.
		 * -> Pokecenter roster.
		 */
		if ( $_POST['Request'] == 'Roster' )
		{
			if ( $_POST['Location'] == 'pokecenter' )
			{
				for ( $i = 0; $i <= 5; $i++ )
				{
          if ( isset($Roster[$i]['ID']) )
          {
						$Roster_Slot[$i] = $PokeClass->FetchPokemonData($Roster[$i]['ID']);

						if ( $Roster_Slot[$i]['Item'] != null )
						{
							$Item = "<img src='{$Roster_Slot[$i]['Item_Icon']}' style='margin: 5px 0px 0px -10px; position: absolute;' />";
						}
						else
						{
							$Item = "";
						}
								
						echo "
							<div class='roster_slot full' style='width: calc(100% / 3);'>
								<div style='float: left;' class='slots left'>
						";

						for ( $x = 1; $x <= 3; ++$x )
						{
							if ( $x == $i + 1 || $x > $Roster_Count )
							{
								echo "<div><span style='color: #000; display: block; padding: 13px;'>$x</span></div>";
							}
							else
							{
								echo "<div><a href='javascript:void(0);' onclick=\"handlePokemon('Move', {$Roster_Slot[$i]['ID']}, $x);\" style='display: block; padding: 13px;'>$x</a></div>";
							}
						}

						echo "
							</div>
							<img src='{$Roster_Slot[$i]['Gender_Icon']}' style='height: 20px; margin: 10px 0px 0px -20px; position: absolute; width: 20px;' />
							<img class='spricon popup cboxElement' src='{$Roster_Slot[$i]['Sprite']}' href='core/ajax/pokemon.php?id={$Roster_Slot[$i]['ID']}' />
							$Item
							<div style='float: right;' class='slots right'>
						";

						for ( $x = 4; $x <= 6; ++$x )
						{
							if ( $x == $i + 1 || $x > $Roster_Count )
							{
								echo "<div><span style='color: #000; display: block; padding: 13px;'>$x</span></div>";
							}
							else
							{
								echo "<div><a href='javascript:void(0);' onclick=\"handlePokemon('Move', {$Roster_Slot[$i]['ID']}, $x);\" style='display: block; padding: 13px;'>$x</a></div>";
							}
						}

						echo "
								</div>
								<div><b>{$Roster_Slot[$i]['Display_Name']}</b></div>
								<div class='info'>
									<div>Level</div>
									<div>{$Roster_Slot[$i]['Level']}</div>
								</div>
								<div class='info'>
									<div>Experience</div>
									<div>{$Roster_Slot[$i]['Experience']}</div>
								</div>
            	</div>
						";
          }
          else
          {
            $Roster_Slot[$i]['Sprite'] = Domain(3) . 'images/pokemon/0.png';
            $Roster_Slot[$i]['Display_Name'] = 'Empty';
            $Roster_Slot[$i]['Level'] = '0';
						$Roster_Slot[$i]['Experience'] = '0';

						echo "
							<div class='roster_slot full' style='width: calc(100% / 3);'>
								<img src='{$Roster_Slot[$i]['Sprite']}' />
								<div><b>{$Roster_Slot[$i]['Display_Name']}</b></div>
								<div class='info'>
									<div>Level</div>
									<div>{$Roster_Slot[$i]['Level']}</div>
								</div>
								<div class='info'>
									<div>Experience</div>
									<div>{$Roster_Slot[$i]['Experience']}</div>
								</div>
							</div>
						";
          }
        }
			}
			else if ( $_POST['Location'] == 'userbar' )
			{
				for ( $i = 0; $i <= 5; $i++ )
					{
						if ( isset($Roster[$i]['ID']) )
						{
							$RosterPoke[$i] = $PokeClass->FetchPokemonData($Roster[$i]['ID']);
						}
						else
						{
							$RosterPoke[$i]['Icon'] = Domain(1) . "/images/Pokemon/0_mini.png";
							$RosterPoke[$i]['Sprite'] = Domain(1) . "/images/Pokemon/0.png";
							$RosterPoke[$i]['Display_Name'] = "Empty";
							$RosterPoke[$i]['Level'] = '0';
							$RosterPoke[$i]['Experience'] = '0';
							$RosterPoke[$i]['Gender_Icon'] = null;
							$RosterPoke[$i]['Gender'] = null;
							$RosterPoke[$i]['Item'] = null;
						}

						echo "
							<div class='roster_slot' onmouseover='showSlot({$i});' onmouseout='hideSlot({$i});' style='text-align: center; min-width: 40px;'>
								<div class='roster_mini'>
									<img src='{$RosterPoke[$i]['Icon']}' />
								</div>
								<div class='roster_tooltip' id='rosterTooltip{$i}'>
						";

						if ( $RosterPoke[$i]['Gender_Icon'] != null && $RosterPoke[$i]['Gender_Icon'] != 'G' && $RosterPoke[$i]['Gender_Icon'] != "(?)" )
						{
							echo "<img src='{$RosterPoke[$i]['Gender_Icon']}' style='height: 20px; margin: 10px 0px 0px -20px; position: absolute; width: 20px;' />";
						}

						echo "<img src='{$RosterPoke[$i]['Sprite']}' />";

						if ( $RosterPoke[$i]['Item'] != null || $RosterPoke[$i]['Item'] != 0 )
						{
							echo "<img src='{$RosterPoke[$i]['Item_Icon']}' style='margin: 5px 0px 0px -10px; position: absolute;' />";
						}

						echo "
									<div><b>{$RosterPoke[$i]['Display_Name']}</b></div>
									<div class='info'>
										<div>Level</div>
										<div>{$RosterPoke[$i]['Level']}</div>
									</div>
									<div class='info'>
										<div>Experience</div>
										<div>{$RosterPoke[$i]['Experience']}</div>
									</div>
								</div>
							</div>
						";
					}
			}
		}
	}
	else
	{
		echo "Invalid session.";
	}

	echo "
			</div>
		</div>
	";