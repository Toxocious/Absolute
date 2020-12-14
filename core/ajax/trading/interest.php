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

		if ( $Poke_Data )
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
				<b style='color: #0f0;'>
					The trade interest of your {$Poke_Data['Display_Name']} has been set to {$Interest}.
				</b>
			";
		}
		else
		{
			echo "
				<b style='color: #f00;'>
					An error has occurred. Please try again.
				</b>
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
			<tr>
				<td colspan='6' style='padding: 5px;'>
					<b>{$Type} Pok&eacute;mon</b>
				</td>
			</tr>

			<tr>
				<td id='AJAX' colspan='6' style='padding: 5px;'>
					Please update the trade interest status of your Pok&eacute;mon.
				</td>
			</tr>
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
					$Check_1 = '';
					$Check_2 = " checked";
					$Check_3 = '';
					break;
				case 'No':
					$Check_1 = '';
					$Check_2 = '';
					$Check_3 = " checked";
					break;
				default:
					$Check_1 = '';
					$Check_2 = '';
					$Check_3 = '';
					break;
			}

			echo "
				<td colspan='1' style='width: 76px;'>
					<img src='" . $Poke_Data['Icon'] . "' />
					<img src='images/Assets/" . $Poke_Data['Gender'] . ".svg' style='height: 20px; width: 20px;' />
				</td>
				<td colspan='2' style='width: 224px;'>
					{$Poke_Data['Display_Name']}<br />
					(Level: {$Poke_Data['Level']})
					<div>
						Yes <input type='radio' name='id[{$Poke_Data['ID']}]' value='y' onclick='Update(this);' {$Check_2}>
						No <input type='radio' name='id[{$Poke_Data['ID']}]' value='n' onclick='Update(this);' {$Check_3}>
						Undecided <input type='radio' name='id[{$Poke_Data['ID']}]' value='u' onclick='Update(this);' {$Check_1}>
					</div>
				</td>
			";
			
			$Pokemon_Count++;
		}

		if ( $Pokemon_Count == 0 )
		{
			echo "
				<tr>
					<td style='padding: 5px;'>
						No Poke&eacute;mon have been found given your search parameters.
					</td>
				</tr>
			";
		}

		if ( $Pokemon_Count % 2 == 1 )
		{
			echo "
				<td colspan='3'></td>
			";
		}
	}
	else
	{
		echo "
			<tr>
				<td colspan='6'>
					<b style='color: #f00;'>
						An error has occurred while fetching the specified type of Pok&eacute;mon.
					</b>
				</td>
			</tr>
		";
	}