<?php
	require '../../core/required/session.php';
	require '../../core/functions/staff.php';

	/**
	 * Attempting to spawn in the selected Pokemon.
	 */
	if ( isset($_POST['Action']) )
	{
		$Action = $Purify->Cleanse($_POST['Action']);

		if ( $Action == 'Create' )
		{
			if ( isset($_POST['db_id']) )
			{
				if ( !isset($_POST['SendTo']) || $_POST['SendTo'] == '' )
				{
					die("<div class='error' style='margin-bottom: 5px;'>An error has occurred while attempting to spawn in this Pokemon.</div>");
				}

				$IVs = $Purify->Cleanse($_POST['IV']);
				$IVs = $IVs['IV_HP'] . ',' . $IVs['IV_Att'] . ',' . $IVs['IV_Def'] . ',' . $IVs['IV_SpAtt'] . ',' . $IVs['IV_SpDef'] . ',' . $IVs['IV_Speed'];
				$EVs = $Purify->Cleanse($_POST['EV']);
				$EVs = $EVs['EV_HP'] . ',' . $EVs['EV_Att'] . ',' . $EVs['EV_Def'] . ',' . $EVs['EV_SpAtt'] . ',' . $EVs['EV_SpDef'] . ',' . $EVs['EV_Speed'];

				$ID = $Purify->Cleanse($_POST['db_id']);
				$Type = $Purify->Cleanse($_POST['Type']);
				$Level = $Purify->Cleanse($_POST['Level']);
				$Recipient = $Purify->Cleanse($_POST['SendTo']);
				$Location = $Purify->Cleanse($_POST['Location']);

				$Recipient_Data = $User_Class->DisplayUserName($Recipient);

				if ( $Location == '' )
				{
					$Location = "Gift";
				}

				try
				{
					$FetchPokedex = $PDO->prepare("SELECT * FROM `pokedex` WHERE `id` = ? LIMIT 1");
					$FetchPokedex->execute([ $ID ]);
					$FetchPokedex->setFetchMode(PDO::FETCH_ASSOC);
					$Pokedex = $FetchPokedex->fetch();

					if ( !isset($Pokedex) )
					{
						die("<div class='error' style='margin-bottom: 5px;'>An error has occurred while attempting to spawn in this Pokemon.</div>");
					}

					$Poke_Class->CreatePokemon( $Pokedex['Pokedex_ID'], $Pokedex['Alt_ID'], $Level, $Type, null, $Location, 'Box', 7, $Recipient, null, $IVs, $EVs );
					$Spawned_Pokemon = $Poke_Class->FetchPokedexData( $Pokedex['Pokedex_ID'], $Pokedex['Alt_ID'], $Type );
				}
				catch ( PDOException $e )
				{
					HandleError( $e->getMessage() );
				}

				echo "<div class='success' style='margin-bottom: 5px;'>You have spawned in the configured Pokemon for {$Recipient_Data}.<br /></div>";

				$Notification->SendNotification( $User_Data['ID'], $Recipient, "{$User_Data['Username']} has spawned in a {$Spawned_Pokemon['Display_Name']} for you." );
			}
			else
			{
				echo "<div class='error' style='margin-bottom: 5px;'>An error has occurred while attempting to spawn in this Pokemon.</div>";
			}
		}
	}

	/**
	 * Retrieve the necessary data if the user has selected a Pokemon.
	 */
	if ( isset($_GET['ID']) )
	{
		$DB_ID = $Purify->Cleanse($_GET['ID']);

		try
		{
			$Fetch_Pokedex = $PDO->prepare("SELECT * FROM `pokedex` WHERE `id` = ?");
			$Fetch_Pokedex->execute([ $DB_ID ]);
			$Fetch_Pokedex->setFetchMode(PDO::FETCH_ASSOC);
			$Pokedex = $Fetch_Pokedex->fetch();
		}
		catch ( PDOException $e )
		{
			HandleError( $e->getMessage() );
		}

		$Pokedex['Pokedex_ID'] = str_pad($Pokedex['Pokedex_ID'], 3, "0", STR_PAD_LEFT);
		if ( $Pokedex['Alt_ID'] != 0)
		{
			$Identify = $Pokedex['Pokedex_ID'] . "." . $Pokedex['Alt_ID'];
			$Name = $Pokedex['Name'] . " " . $Pokedex['Name_Alter'];
		}
		else
		{
			$Identify = $Pokedex['Pokedex_ID'];
			$Name = $Pokedex['Name'];
		}

		$JSON = [
			"Name" => $Name,
			"ID" => $Pokedex['id'],
			"Sprite" => DOMAIN_SPRITES . '/images/Pokemon/Sprites/Normal/' . $Identify . '.png',
			"Base_HP" => $Pokedex['HP'],
			"Base_Attack" => $Pokedex['Attack'],
			"Base_Defense" => $Pokedex['Defense'],
			"Base_SpAttack" => $Pokedex['SpAttack'],
			"Base_SpDefense" => $Pokedex['SpDefense'],
			"Base_Speed" => $Pokedex['Speed'],
			"Base_Total" => $Pokedex['HP'] + $Pokedex['Attack'] + $Pokedex['Defense'] + $Pokedex['SpAttack'] + $Pokedex['SpDefense'] + $Pokedex['Speed'],
		];

		header('Content-Type: application/json');
		echo json_encode($JSON);
	}