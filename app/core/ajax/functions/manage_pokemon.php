<?php
	require_once '../../required/session.php';

	if ( isset($_SESSION['EvoChroniclesRPG']) )
	{
		try
		{
			$Roster_Fetch = $PDO->prepare("SELECT `ID`, `Slot` FROM `pokemon` WHERE `Owner_Current` = ? AND `Location` = 'Roster' ORDER BY `Slot` ASC LIMIT 6");
			$Roster_Fetch->execute([$User_Data['ID']]);
			$Roster_Fetch->setFetchMode(PDO::FETCH_ASSOC);
			$Roster = $Roster_Fetch->fetchAll();
		}
		catch ( PDOException $e )
		{
			HandleError($e);
		}

		$Roster_Count = count($Roster);

		/**
		 * Handle moving a Pokemon within the user's account.
		 */
		if ( $_POST['Request'] == 'Move' && isset($_POST['PokeID']) && isset($_POST['Slot']) )
		{
			$Slot = Purify($_POST['Slot']);
			$Poke_ID = Purify($_POST['PokeID']);
			$Pokemon_Data = GetPokemonData($Poke_ID);
			$Pokemon_Move = MovePokemon($Pokemon_Data['ID'], $Slot);

			try
			{
				$Fetch_Roster = $PDO->prepare("SELECT `ID` FROM `pokemon` WHERE `Owner_Current` = ? AND `Location` = 'Roster' ORDER BY `Slot` ASC LIMIT 6");
				$Fetch_Roster->execute([$User_Data['ID']]);
				$Fetch_Roster->setFetchMode(PDO::FETCH_ASSOC);
				$Roster_Pokemon = $Fetch_Roster->fetchAll();

				$Roster = '';
				foreach ( $Roster_Pokemon as $Key => $Value )
				{
					$Roster .= "{$Value['ID']}";
				}

				$User_Roster_Update = $PDO->prepare("UPDATE `users` SET `Roster` = ? WHERE `id` = ? LIMIT 1");
				$User_Roster_Update->execute([ $Roster, $User_Data['ID'] ]);
			}
			catch( PDOException $e )
			{
				HandleError($e);
			}

			echo "
				<div class='{$Pokemon_Move['Type']}'>
					{$Pokemon_Move['Message']}
				</div>
			";

			echo "
				<table class='border-gradient' style='flex-basis: 100%; max-width: 865px;'>
					<tbody>
			";

			$Items = '';
			$Slots = '';
			$Sprites = '';

			for ( $Slot = 0; $Slot < 6; $Slot++ )
			{
				if ( isset($Roster_Pokemon[$Slot]) )
				{
					$Pokemon = GetPokemonData($Roster_Pokemon[$Slot]['ID']);

					$Sprites .= "
						<td colspan='3'>
							<img src='{$Pokemon['Sprite']}' />
						</td>
						<td colspan='4'>
							<b>{$Pokemon['Display_Name']}</b><br />
							<b>Level</b><br />
							{$Pokemon['Level']}<br />
							<b>Experience</b><br />
							{$Pokemon['Experience']}
						</td>
					";

					$Items .= "<img src='{$Pokemon['Item_Icon']}' style='margin-top: 48px;' />";

					for ( $x = 1; $x <= 7; ++$x )
					{
						if ( $x == 7 )
						{
							$Slots .= "
								<td>
									<a href='javascript:void(0);' onclick=\"handlePokemon('Move', {$Pokemon['ID']}, $x);\" style='padding: 0px 13px;'>X</a>
								</td>
							";
						}
						else if ( $x == $Slot + 1 || $x > count($Roster_Pokemon) )
						{
							$Slots .= "
								<td>
									<span style='color: #000; padding: 0px 13px;'>$x</span>
								</td>
							";
						}
						else
						{
							$Slots .= "
								<td>
									<a href='javascript:void(0);' onclick=\"handlePokemon('Move', {$Pokemon['ID']}, $x);\" style='padding: 0px 13px;'>$x</a>
								</td>
							";
						}
					}
				}
				else
				{
					$Sprites .= "
						<td colspan='7'>
							<img src='" . DOMAIN_SPRITES . "/Pokemon/Sprites/0.png' />
						</td>
					";

					$Items .= "ITEM";

					for ( $x = 1; $x <= 7; $x++ )
					{
						$Slots .= "
							<td>
								<span style='color: #000; padding: 0px 13px;'>$x</span>
							</td>
						";
					}
				}

				if ( ($Slot + 1) % 3 === 0 )
				{
					echo "
						<tr>
							{$Slots}
						</tr>
						<tr>
							{$Sprites}
						</tr>
					";

					$Items = '';
					$Slots = '';
					$Sprites = '';
				}
			}

			echo "
					</tbody>
				</table>
			";

			$Page = (isset($_POST['page'])) ? $_POST['page'] : 1;
			$Filter_Type = (isset($_POST['filter_type'])) ? $_POST['filter_type'] : '0';
			$Filter_Gender = (isset($_POST['filter_gender'])) ? $_POST['filter_gender'] : '0';
			$Filter_Dir = (isset($_POST['filter_search_order'])) ? $_POST['filter_search_order'] : 'ASC';

			$Begin = ($Page - 1) * 35;
			if ( $Begin < 0 )
				$Begin = 1;

			$Query = "SELECT `ID` FROM `pokemon` WHERE `Owner_Current` = ? AND `Location` = 'Box'";
			$Inputs = [$User_Data['ID']];

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

			try
			{
				$Box_Query = $PDO->prepare("SELECT `ID` FROM `pokemon` WHERE `Owner_Current` = ? AND `Slot` = 7 ORDER BY `Pokedex_ID` ASC LIMIT 35");
				$Box_Query->execute([ $User_Data['ID'] ]);
				$Box_Query->setFetchMode(PDO::FETCH_ASSOC);
				$Box_Pokemon = $Box_Query->fetchAll();
			}
			catch (PDOException $e)
			{
				HandleError($e);
			}

			echo "
				<div class='panel' style='flex-basis: 300px;'>
					<div class='head'>Box</div>
					<div class='body' id='Pokebox'>
			";

      if ( count($Box_Pokemon) == 0 )
      {
        echo "
          <div class='flex' style='align-items: center; justify-content: center; height: 209px;'>
            <div style='flex-basis: 100%'>
              No Pok&eacute;mon were found in your box.
            </div>
          </div>
        ";
      }
      else
      {
        $Pagination = Pagi(str_replace('SELECT `ID`', 'SELECT COUNT(*)', $Query), $User_Data['ID'], $Inputs, $Page, 'onclick="updateBox(\'' . $Page . '\'); return false;"', 35);

        echo "
          {$Pagination}
          <div style='height: 172px; padding: 0px 0px 5px;'>
        ";

        foreach ( $Box_Pokemon as $Index => $Pokemon )
        {
          $Pokemon = GetPokemonData($Pokemon['ID']);
          echo "<img class='spricon' src='{$Pokemon['Icon']}' onclick='displayPokeData({$Pokemon['ID']});'/>";
        }

        echo "
          </div>
        ";
			}

			echo "
				  </div>
				</div>

				<div class='panel' style='flex-basis: calc(100% - 340px);'>
					<div class='head'>Selected Pok&eacute;mon</div>
					<div class='body' style='height: 203px; padding: 3px;'>
							<div class='flex' id='pokeData' style='align-items: center; justify-content: center; height: inherit;'>
								<div style='flex-basis: 100%;'>Please select a Pok&eacute;mon to view it's statistics.</div>
						</div>
					</div>
				</div>
			";
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
						$Roster_Slot[$i] = GetPokemonData($Roster[$i]['ID']);

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
									<img class='popup' src='{$Roster_Slot[$i]['Sprite']}' data-src='" . DOMAIN_ROOT . "/core/ajax/pokemon.php?id={$Roster_Slot[$i]['ID']}' />
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
						$Roster_Slot[$i]['Sprite'] = DOMAIN_SPRITES . '/Pokemon/Sprites/0.png';
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
							$RosterPoke[$i] = GetPokemonData($Roster[$i]['ID']);
						}
						else
						{
							$RosterPoke[$i]['Icon'] = DOMAIN_SPRITES . "/Pokemon/Sprites/0_mini.png";
							$RosterPoke[$i]['Sprite'] = DOMAIN_SPRITES . "/Pokemon/Sprites/0.png";
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
