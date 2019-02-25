<?php
	require '../../required/session.php';
	require '../../functions/trading.php';

	if ( isset($_POST['Action']) && isset($_POST['Type']) && isset($_POST['Data']) && isset($_POST['ID']) )
	{
		$Action = $Purify->Cleanse($_POST['Action']);
		$Type = $Purify->Cleanse($_POST['Type']);
		$Data = $Purify->Cleanse($_POST['Data']);
		$User_ID = $Purify->Cleanse($_POST['ID']);

		$User = $UserClass->FetchUserData($User_ID);

		/**
		 * Add things to the trade.
		 */
		if ( $Action == 'Add' )
		{
			$Already_Included = false;

			/**
			 * Add Pokemon to the trade.
			 */
			if ( $Type == 'Pokemon' )
			{
				$Pokemon = $PokeClass->FetchPokemonData($Data);

				if ( isset($_SESSION['Trade'][$User['ID']]['Pokemon']) )
				{
					foreach ( $_SESSION['Trade'][$User['ID']]['Pokemon'] as $Key => $Value )
					{
						if ( $Value['ID'] == $Pokemon['ID'] )
						{
							$Already_Included = true;
						}
					}
				}

				if ( !isset($Pokemon) || $Pokemon == "Error" )
				{
					echo "
						<div class='error' style='margin: 5px; width: calc(100% - 10px);'>
							This Pokemon doesn't exist.
						</div>
					";
				}
				else if ( $Pokemon['Owner_Current'] != $User['ID'] )
				{
					echo "
						<div class='error' style='margin: 5px; width: calc(100% - 10px);'>
							This Pokemon doesn't belong to {$User['Username']}.
						</div>
					";
				}
				else if ( $Already_Included )
				{
					echo "
						<div class='error' style='margin: 5px; width: calc(100% - 10px);'>
							This Pokemon is already included in the trade.
						</div>
					";
				}
				else
				{
					echo "
						<div class='success' style='margin: 5px; width: calc(100% - 10px);'>
							<b>{$Pokemon['Display_Name']}</b> has been added to the trade.
						</div>
					";

					$_SESSION['Trade'][$User['ID']]['Pokemon'][] = [
						'ID' => $Pokemon['ID'],
					];
				}
			}

			/**
			 * Add Items to the trade.
			 */
			if ( $Type == 'Item' )
			{
				/**
				 * Add Tradeable check
				 * if ( in_array($Item['Item_ID'], [untradeable item id's]) ) { echo error }
				 */
				$Item = $Item_Class->FetchOwnedItem($User['ID'], $Data);

				if ( isset( $_SESSION['Trade'][$User['ID']]['Items']) )
				{
					foreach ( $_SESSION['Trade'][$User['ID']]['Items'] as $Key => $Value )
					{
						if ( $Value['ID'] == $Item['ID'] )
						{
							$Already_Included = true;
						}
					}
				}

				if ( !isset($Item) || $Item == "Error" )
				{
					echo "
						<div class='error' style='margin: 5px; width: calc(100% - 10px);'>
							This Item doesn't exist.
						</div>
					";
				}
				else if ( $Item['Owner'] != $User['ID'] )
				{
					echo "
						<div class='error' style='margin: 5px; width: calc(100% - 10px);'>
							This Item doesn't belong to {$User['Username']}.
						</div>
					";
				}
				else if ( $Already_Included )
				{
					echo "
						<div class='error' style='margin: 5px; width: calc(100% - 10px);'>
							This Item is already included in the trade.
						</div>
					";
				}
				else if ( $Item['Quantity'] <= 0 )
				{
					echo "
						<div class='error' style='margin: 5px; width: calc(100% - 10px);'>
							This user doesn't own enough of this item.
						</div>
					";
				}
				else
				{
					echo "
						<div class='success' style='margin: 5px; width: calc(100% - 10px);'>
							<b>{$Item['Name']}</b> has been added to the trade.
						</div>
					";

					$_SESSION['Trade'][$User['ID']]['Items'][] = [
						'Row' => $Item['Row'],
						'ID' => $Item['ID'],
						'Quantity' => 1,
						'Owner' => $Item['Owner'],
					];
				}
			}

			/**
			 * Add Currencies to the trade.
			 */
			if ( $Type == 'Currency' )
			{
				$Currency_Data = [
					"Name" => $Data['Name'],
					"Amount" => $Data['Amount'],
				];

				if ( isset( $_SESSION['Trade'][$User['ID']]['Currency']) )
				{
					foreach ( $_SESSION['Trade'][$User['ID']]['Currency'] as $Key => $Value )
					{
						if ( $Value['Currency'] == $Currency_Data['Name'] )
						{
							$Already_Included = true;
						}
					}
				}

				try
				{
					$Currency_Query = $PDO->prepare("SELECT `Money`, `Abso_Coins` FROM `users` WHERE `id` = ?");
					$Currency_Query->execute([ $User['ID'] ]);
					$Currency_Query->setFetchMode(PDO::FETCH_ASSOC);
					$Currency = $Currency_Query->fetch();
				}
				catch( PDOException $e )
				{
					HandleError( $e->getMessage() );
				}

				if ( $Currency_Data['Amount'] <= 0 )
				{
					echo "
						<div class='error' style='margin: 5px; width: calc(100% - 10px);'>
							Please add currency to the trade at a value of 0 or higher.
						</div>
					";
				}
				else if ( $Currency_Data['Amount'] > $Currency[$Currency_Data['Name']] )
				{
					echo "
						<div class='error' style='margin: 5px; width: calc(100% - 10px);'>
							{$User['Username']} does not have enough {$Currency_Data['Name']} to add to the trade.
						</div>
					";
				}
				else if ( $Already_Included )
				{
					echo "
						<div class='error' style='margin: 5px; width: calc(100% - 10px);'>
							You have already added {$Currency_Data['Name']} to {$User['Username']}'s side of the trade.
						</div>
					";
				}
				else
				{
					echo "
						<div class='success' style='margin: 5px; width: calc(100% - 10px);'>
							{$Currency_Data['Amount']} {$Currency_Data['Name']} has been added to the trade.
						</div>
					";

					$_SESSION['Trade'][$User['ID']]['Currency'][] = [
						'Currency' => $Currency_Data['Name'],
						'Quantity' => $Currency_Data['Amount'],
					];
				}
			}
		}

		/**
		 * Remove things from the trade.
		 */
		else if ( $Action == 'Remove' )
		{
			/**
			 * Remove Pokemon to the trade.
			 */
			if ( $Type == 'Pokemon' )
			{
				echo "Removing a Pokemon to the trade!";
			}

			/**
			 * Remove Items to the trade.
			 */
			if ( $Type == 'Item' )
			{
				echo "Removing an Item to the trade!";
			}

			/**
			 * Remove Currencies to the trade.
			 */
			if ( $Type == 'Currency' )
			{
				echo "Removing a Currency to the trade!";
			}
		}

		DisplayTradeContent($User);
	}