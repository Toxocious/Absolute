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
			$Slot = Purify($_POST['Slot']);
			$Poke_ID = Purify($_POST['PokeID']);
			$Pokemon_Data = $Poke_Class->FetchPokemonData($Poke_ID);
			$Pokemon_Move = $Poke_Class->MovePokemon($Pokemon_Data['ID'], $Slot);

			try
			{
				$Roster_Query = $PDO->prepare("SELECT `ID` FROM `pokemon` WHERE `Owner_Current` = ? AND `Location` = 'Roster' AND `Slot` <= 6 ORDER BY `Slot` ASC LIMIT 6");
        $Roster_Query->execute([ $User_Data['id'] ]);
        $Roster_Query->setFetchMode(PDO::FETCH_ASSOC);
				$Roster_Pokemon = $Roster_Query->fetchAll();
				
				$Roster = '';
				foreach ( $Roster_Pokemon as $Key => $Value )
				{
					$Roster .= "{$Value['ID']}";
				}

				$User_Roster_Update = $PDO->prepare("UPDATE `users` SET `Roster` = ? WHERE `id` = ? LIMIT 1");
				$User_Roster_Update->execute([ $Roster, $User_Data['id'] ]);
			}
			catch( PDOException $e )
			{
				HandleError( $e->getMessage() );
			}

			if ( $Pokemon_Move == true )
			{
				if ( $Slot == 7 )
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
					$Roster_Slot[$i] = $Poke_Class->FetchPokemonData($Roster[$i]['ID']);
			
					if ( $Roster_Slot[$i]['Item'] != null )
					{
						$Item = "<img src='{$Roster_Slot[$i]['Item_Icon']}' style='margin-top: 48px;' />";
					}
					else
					{
						$Item = "";
					}

					$Slots = '';
					for ( $x = 1; $x <= 7; ++$x )
					{
						if ( $x == 7 )
						{
							$Slots .= "
								<div>
									<a href='javascript:void(0);' onclick=\"handlePokemon('Move', {$Roster_Slot[$i]['ID']}, $x);\" style='padding: 0px 13px; width: calc(100% / 7);'>X</a>
								</div>
							";
						}
						else if ( $x == $i + 1 || $x > count($Roster) )
						{
							$Slots .= "
								<div>
									<span style='color: #000; padding: 0px 13px; width: calc(100% / 7);'>$x</span>
								</div>
							";
						}
						else
						{
							$Slots .= "
								<div>
									<a href='javascript:void(0);' onclick=\"handlePokemon('Move', {$Roster_Slot[$i]['ID']}, $x);\" style='padding: 0px 13px; width: calc(100% / 7);'>$x</a>
								</div>
							";
						}
					}
								
					echo "
						<div class='roster_slot full'>
							<div class='slots'>
								$Slots
							</div>

							<div style='float: left; padding-top: 3px; text-align: center; width: 30px;'>
								<img src='{$Roster_Slot[$i]['Gender_Icon']}' style='height: 20px; width: 20px;' /><br />
								$Item
							</div>

							<div style='float: left; margin-left: -30px; padding: 3px;'>
								<img class='spricon popup cboxElement' src='{$Roster_Slot[$i]['Sprite']}' href='" . Domain(1) . "/core/ajax/pokemon.php?id={$Roster_Slot[$i]['ID']}' />
							</div>

							<div class='info_cont' style='float: right; width: 189px;'>
								<div style='font-weight: bold; padding: 2px;'>
									{$Roster_Slot[$i]['Display_Name']}
								</div>
								<div class='info'>Level</div>
								<div>{$Roster_Slot[$i]['Level']}</div>
								<div class='info'>Experience</div>
								<div>" . number_format($Roster_Slot[$i]['Experience']) . "</div>
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
						<div class='roster_slot full' style='height: 132px; padding: 0px;'>
							<div style='float: left; padding: 18px 3px 3px;'>
								<img class='spricon' src='{$Roster_Slot[$i]['Sprite']}' />
							</div>

							<div class='info_cont' style='float: right; height: 132px; padding-top: 15px; width: 189px;'>
								<div style='font-weight: bold; padding: 2px;'>
									{$Roster_Slot[$i]['Display_Name']}
								</div>
								<div class='info'>Level</div>
								<div>{$Roster_Slot[$i]['Level']}</div>
								<div class='info'>Experience</div>
								<div>" . number_format($Roster_Slot[$i]['Experience']) . "</div>
							</div>
						</div>
					";
				}
			}

			$Page = (isset($_POST['page'])) ? $_POST['page'] : 1;
			$Filter_Type = (isset($_POST['filter_type'])) ? $_POST['filter_type'] : '0';
			$Filter_Gender = (isset($_POST['filter_gender'])) ? $_POST['filter_gender'] : '0';
			$Filter_Dir = (isset($_POST['filter_search_order'])) ? $_POST['filter_search_order'] : 'ASC';

			$Begin = ($Page - 1) * 35;
			if ( $Begin < 0 )
				$Begin = 1;

			$Query = "SELECT `ID` FROM `pokemon` WHERE `Owner_Current` = ? AND `Location` = 'Box'";
			$Inputs = [$User_Data['id']];

			if ( $Filter_Type != '0' )
			{
				$Query .= " AND `type` = ?";
				$Inputs[] = $Filter_Type;
			}

			switch ($Filter_Gender)
			{
				case 'm': $Query .= " AND `gender` = 'Male'"; break;
				case 'f': $Query .= " AND `gender` = 'Female'"; break;
				case 'g': $Query .= " AND `gender` = 'Genderless'"; break;
				case '?': $Query .= " AND `gender` = '(?)'"; break;
			}

			if ( $Filter_Dir != 'ASC' )
			{
				$Filter_Dir = 'DESC';
			}
			else
			{
				$Filter_Dir = 'ASC';
			}

			$Query .= " ORDER BY `Pokedex_ID`, `ID` ASC";
			
			echo "
					</div>
				</div>

				<div class='panel' style='float: left; width: calc(100% / 3);'>
					<div class='panel-heading'>Box</div>
					<div class='panel-body' id='Pokebox'>
						<div class='page_nav'>";
						Pagi(str_replace('SELECT `ID`', 'SELECT COUNT(*)', $Query), $User_Data['id'], $Inputs, $Page, 'onclick="updateBox(\'' . $Page . '\'); return false;"', 35);
			echo "
						</div>
			";

			try
      {
        $Box_Query = $PDO->prepare("SELECT * FROM `pokemon` WHERE `Owner_Current` = ? AND `Slot` = 7 ORDER BY `Pokedex_ID` ASC LIMIT 35");
        $Box_Query->execute([$User_Data['id']]);
        $Box_Query->setFetchMode(PDO::FETCH_ASSOC);
        $Box_Pokemon = $Box_Query->fetchAll();
      }
      catch (PDOException $e)
      {
        HandleError( $e->getMessage() );
			}
			
			echo "<div style='height: 156px; padding: 3px;'>";
      foreach ( $Box_Pokemon as $Index => $Pokemon )
      {
        $Pokemon = $Poke_Class->FetchPokemonData($Pokemon['ID']);
        echo "<img class='spricon' src='{$Pokemon['Icon']}' onclick='displayPokeData({$Pokemon['ID']});'/>";
      }
      echo "</div>";

      if ( count($Box_Pokemon) == 0 )
      {
        echo "<div style='padding: 3px;'>No Pokemon were found in your box.</div>";
      }

			echo "
					</div>
				</div>

				<div class='panel' id='pokeData' style='float: right; width: calc(100% / 1.5 - 5px);'>
					<div class='panel-heading'>Selected Pokemon</div>
					<div class='panel-body' style='padding: 3px;'>
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
				$Pokemon = $Poke_Class->FetchPokemonData($_POST['PokeID']);

				echo "
					<div class='panel-heading'><div>{$Pokemon['Display_Name']}</div><div style='float: right; margin-top: -21px;'>(#".number_format($Pokemon['ID']).")</div></div>
					<div class='panel-body' style='padding: 5px;'>
						<div style='float: left;'>
							<img class='cboxElement popup' src='{$Pokemon['Sprite']}' href='core/ajax/pokemon.php?id={$Pokemon['ID']}' />
						</div>
						<div style='text-align: left;'>
							<div style='text-align: center;'><b>{$Pokemon['Display_Name']}</b></div>
							<b>Level</b>: {$Pokemon['Level']}<br />
							<b>Exp</b>: " . number_format($Pokemon['Experience']) . "<br />
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
						$Roster_Slot[$i] = $Poke_Class->FetchPokemonData($Roster[$i]['ID']);
				
						if ( $Roster_Slot[$i]['Item'] != null )
						{
							$Item = "<img src='{$Roster_Slot[$i]['Item_Icon']}' style='margin-top: 48px;' />";
						}
						else
						{
							$Item = "";
						}

						$Slots = '';
						for ( $x = 1; $x <= 7; ++$x )
						{
							if ( $x == 7 )
							{
								$Slots .= "
									<div>
										<a href='javascript:void(0);' onclick=\"handlePokemon('Move', {$Roster_Slot[$i]['ID']}, $x);\" style='padding: 0px 13px; width: calc(100% / 7);'>X</a>
									</div>
								";
							}
							else if ( $x == $i + 1 || $x > count($Roster) )
							{
								$Slots .= "
									<div>
										<span style='color: #000; padding: 0px 13px; width: calc(100% / 7);'>$x</span>
									</div>
								";
							}
							else
							{
								$Slots .= "
									<div>
										<a href='javascript:void(0);' onclick=\"handlePokemon('Move', {$Roster_Slot[$i]['ID']}, $x);\" style='padding: 0px 13px; width: calc(100% / 7);'>$x</a>
									</div>
								";
							}
						}
									
						echo "
							<div class='roster_slot full'>
								<div class='slots'>
									$Slots
								</div>

								<div style='float: left; padding-top: 3px; text-align: center; width: 30px;'>
									<img src='{$Roster_Slot[$i]['Gender_Icon']}' style='height: 20px; width: 20px;' /><br />
									$Item
								</div>

								<div style='float: left; margin-left: -30px; padding: 3px;'>
									<img class='spricon popup cboxElement' src='{$Roster_Slot[$i]['Sprite']}' href='" . Domain(1) . "/core/ajax/pokemon.php?id={$Roster_Slot[$i]['ID']}' />
								</div>

								<div class='info_cont' style='float: right; width: 189px;'>
									<div style='font-weight: bold; padding: 2px;'>
										{$Roster_Slot[$i]['Display_Name']}
									</div>
									<div class='info'>Level</div>
									<div>{$Roster_Slot[$i]['Level']}</div>
									<div class='info'>Experience</div>
									<div>" . number_format($Roster_Slot[$i]['Experience']) . "</div>
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
							<div class='roster_slot full' style='height: 132px; padding: 0px;'>
								<div style='float: left; padding: 18px 3px 3px;'>
									<img class='spricon' src='{$Roster_Slot[$i]['Sprite']}' />
								</div>

								<div class='info_cont' style='float: right; height: 132px; padding-top: 15px; width: 189px;'>
									<div style='font-weight: bold; padding: 2px;'>
										{$Roster_Slot[$i]['Display_Name']}
									</div>
									<div class='info'>Level</div>
									<div>{$Roster_Slot[$i]['Level']}</div>
									<div class='info'>Experience</div>
									<div>" . number_format($Roster_Slot[$i]['Experience']) . "</div>
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
							$RosterPoke[$i] = $Poke_Class->FetchPokemonData($Roster[$i]['ID']);
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
										<div>" . number_format($Roster_Slot[$i]['Experience']) . "</div>
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