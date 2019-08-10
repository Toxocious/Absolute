<?php
	require '../../required/session.php';

	/**
	 * Update the trade interest status of the chosen Pokemon.
	 */
	if ( isset($_POST['Update']) )
	{
		$Poke_ID = $Purify->Cleanse($_POST['Update'][0]);
		$Interest = $Purify->Cleanse($_POST['Update'][1]);

		switch($Interest)
		{
			case 'u':
				$Interest = "Undecided";
				break;
			case 'y':
				$Interest = "Yes";
				break;
			case 'n':
				$Interest = "No";
				break;
			default:
				$Interest = "Undecided";
				break;
		}

		$Poke_ID = substr($Poke_ID, 3, -1);
		$Poke_Data = $Poke_Class->FetchPokemonData($Poke_ID);

		if ( $Poke_Data != 'Error' )
		{
			try
			{
				$Prep_Update = $PDO->prepare("UPDATE `pokemon` SET `Trade_Interest` = ? WHERE `ID` = ?");
				$Prep_Update->execute([ $Interest, $Poke_ID ]);
			}
			catch ( PDOException $e )
			{
				HandleError( $e->getMessage() );
			}

			echo "
				<div class='success' style='margin-bottom: 5px;'>
					The trade interest of your <b>{$Poke_Data['Display_Name']}</b> has been set to <b>{$Interest}</b>.
				</div>
			";
		}
		else
		{
			echo "
				<div class='error' style='margin-bottom: 5px;'>
					An error has occurred. Please try again.
				</div>
			";
		}
	}
	
	/**
	 * Display the user's Pokemon, given the selected Pokemon type.
	 */
	else if ( isset($_POST['Type']) )
	{
		$Type = $Purify->Cleanse($_POST['Type']);

		try
		{
			$Query_Box = $PDO->prepare("SELECT `ID`, `Name`, `Type`, `Trade_Interest` FROM `pokemon` WHERE `Type` = ? AND `Owner_Current` = ?");
			$Query_Box->execute([ $Type, $User_Data['id'] ]);
			$Query_Box->setFetchMode(PDO::FETCH_ASSOC);
			$Poke_List = $Query_Box->fetchAll();
		}
		catch( PDOException $e )
		{
			HandleError( $e->getMessage() );
		}

		echo "
			<div class='panel'>
				<div class='panel-heading'>Boxed " . $Type . " Pokemon</div>
				<div class='panel-body'>
					<table class='box_cont' style='width: 100%;'>
		";
		
		$Pokemon_Count = 0;
		foreach( $Poke_List as $Key => $Value )
		{
			$Poke_Data = $Poke_Class->FetchPokemonData($Value['ID']);

      if ( $Pokemon_Count % 2 == 0 )
      {
        echo "</tr><tr>";
			}

			switch( $Value['Trade_Interest'] )
			{
				case 'Undecided':
					$Check_1 = " checked";
					$Check_2 = '';
					$Check_3 = '';
					break;
				case 'Yes':
					$Check_2 = " checked";
					$Check_1 = '';
					$Check_3 = '';
					break;
				case 'No':
					$Check_3 = " checked";
					$Check_1 = '';
					$Check_2 = '';
					break;
				default:
					$Check_1 = '';
					$Check_2 = '';
					$Check_3 = '';
					break;
			}

			echo "
				<td class='box_slot' style='padding: 0px;'>
					<img src='images/Assets/" . $Poke_Data['Gender'] . ".svg' style='float: left; height: 20px; margin-top: 5px; width: 20px;' />
					<span style='float: left;'>
						<img src='" . $Poke_Data['Icon'] . "' />
					</span>
					<div style='padding-top: 2px;'>
						<div style='font-size: 12px; margin-right: 55px; padding-top: 0px;'>
							" . $Poke_Data['Display_Name'] . "<br />
							(Level: " . $Poke_Data['Level'] . ")
						</div>

						<div>
							<div style='background: #25e845; float: left; padding: 0px 2px; width: calc(100% / 3);'>
								Yes
								<input type='radio' name='id[{$Poke_Data['ID']}]' value='y' onclick='Update(this);' {$Check_2}>
							</div>
							<div style='background: #fcbc19; float: left; padding: 0px 2px; width: calc(100% / 3);'>
								No
								<input type='radio' name='id[{$Poke_Data['ID']}]' value='n' onclick='Update(this);' {$Check_3}>
							</div>
							<div style='background: #444; float: left; padding: 0px 2px; width: calc(100% / 3);'>
								Undecided
								<input type='radio' name='id[{$Poke_Data['ID']}]' value='u' onclick='Update(this);' {$Check_1}>
							</div>
						</div>
					</div>
				</td>
			";
			
			$Pokemon_Count++;
		}

		if ( $Pokemon_Count == 0 )
		{
			echo "<div style='padding: 5px;'>No Pokemon have been found given your search parameters.</div>";
		}

		if ( $Pokemon_Count % 2 == 1 )
		{
			echo "<td class='box_slot'></td>";
		}

		if ( $Pokemon_Count % 2 == 2 )
		{
			echo "<td class='box_slot'></td>";
		}

		echo "
					</table>
				</div>
			</div>
		";
	}
	else
	{
		echo "
			<div class='error'>
				An error has occurred while attempting to fetch the specified type of Pokemon.
			</div>
		";
	}