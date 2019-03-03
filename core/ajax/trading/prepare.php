<?php
	require '../../required/session.php';

	/**
	 * Display the trade windows.
	 */
	if ( isset($_POST['ID']) )
	{
		if ( !isset($User_Data) )
		{
			echo "To use this feature, you must be logged in.";
		}

		$Recipient_ID = $Purify->Cleanse($_POST['ID']);
		$Recipient = $UserClass->FetchUserData($Recipient_ID);

		if ( $Recipient_ID == 0 || $Recipient == "Error" )
		{
			echo "<div class='error'>The user that you're attempting to trade with does not exist.</div>";			
		}
		else if ( $User_Data['id'] === $Recipient_ID )
		{
			echo "<div class='error'>You may not trade with yourself.</div>";
		}
		else if ( $Recipient['Banned_RPG'] )
		{
			echo "<div class='error'>The user that you're attempting to trade with is currently banned.</div>";
		}
		else
		{
			$_SESSION['Trade'] = [
				'Sender' => [
					'User' => $User_Data['id'],
					'Pokemon' => [],
					'Currency' => [],
					'Items' => []
				],
				'Receiver' => [
					'User' => $Recipient['ID'],
					'Pokemon' => [],
					'Currency' => [],
					'Items' => []
				],
			];	
?>

				<div class='description' style='margin-bottom: 5px;'>Choose the pokemon, items, and/or currency to be added to the trade.</div>
				<button onclick='TradeCreate();' style='margin-bottom: 5px; width: 50%;'>Create Trade</button>
				
				<div class='row'>
					<div style='float: left; margin-right: 5px; width: calc(100% / 2 - 2.5px);'>
						<div class='panel' style='margin-bottom: 5px;'>
							<div class='panel-heading'><?= $User_Data['Username']; ?>'s Belongings</div>
							<div class='panel-body navi'>
								<div>
									<div style='float: left; width: calc(100% / 3);'>
										<a href='javascript:void(0);' style='display: block; padding: 3px;' onclick="Swap('box', <?= $User_Data['id']; ?>)">Pokemon</a>
									</div>
									<div style='float: left; width: calc(100% / 3);'>
										<a href='javascript:void(0);' style='display: block; padding: 3px;' onclick="Swap('inventory', <?= $User_Data['id']; ?>)">Inventory</a>
									</div>
									<div style='float: left; width: calc(100% / 3);'>
										<a href='javascript:void(0);' style='display: block; padding: 3px;' onclick="Swap('currency', <?= $User_Data['id']; ?>)">Currency</a>
									</div>
								</div>
								<hr />

								<div id='TabContent<?= $User_Data['id']; ?>'>
									<?php
										try
										{
											$Sender_Query = $PDO->prepare("SELECT `ID` FROM `pokemon` WHERE `Owner_Current` = ? AND `Location` = 'Box' AND `Trade_Interest` != 'No' ORDER BY `Pokedex_ID` ASC LIMIT 50");
											$Sender_Query->execute([$User_Data['id']]);
											$Sender_Query->setFetchMode(PDO::FETCH_ASSOC);
											$Sender_Box = $Sender_Query->fetchAll();

											$Query = "SELECT `ID` FROM `pokemon` WHERE `Owner_Current` = {$User_Data['id']} AND `Location` = 'Box' AND `Trade_Interest` != 'No' ORDER BY `Pokedex_ID` ASC LIMIT 50";
											$Inputs = [];
											$Page = 1;
										}
										catch ( PDOException $e )
										{
											HandleError( $e->getMessage() );
										}

										if ( count($Sender_Box) == 0 )
										{
											echo "<div style='padding: 5px;'>There are no Pokemon in this user's box.</div>";
										}
										else
										{
											echo "<div class='page_nav'>";
											Pagi(str_replace('SELECT `ID`', 'SELECT COUNT(*)', $Query), $User_Data['id'], $Inputs, $Page, 'onclick="updateBox(' . $Page . ', ' . $User_Data['id'] . '); return false;"', 50);
											echo "</div>";

											echo "<div style='padding: 5px;'>";
											foreach( $Sender_Box as $Index => $Pokemon )
											{
												$Pokemon = $PokeClass->FetchPokemonData($Pokemon['ID']);
												echo "<img class='spricon' src='{$Pokemon['Icon']}' onclick='Action({$User_Data['id']}, \"Add\", \"Pokemon\", {$Pokemon['ID']})' />";
											}
											echo "</div>";
										}
									?>
								</div>
							</div>
						</div>

						<div class='panel'>
							<div class='panel-heading'>Included In Trade</div>
							<div class='panel-body' id='TradeIncluded<?= $User_Data['id']; ?>'>
								<div style='padding: 12px;'>Nothing has been added to the trade yet.</div>
							</div>
						</div>
					</div>

					<div style='float: left; width: calc(100% / 2 - 2.5px);'>
						<div class='panel' style='margin-bottom: 5px;'>
							<div class='panel-heading'><?= $Recipient['Username']; ?>'s Belongings</div>
							<div class='panel-body navi'>
								<div>
									<div style='float: left; width: calc(100% / 3);'>
										<a href='javascript:void(0);' style='display: block; padding: 3px;' onclick="Swap('box', <?= $Recipient['ID']; ?>)">Pokemon</a>
									</div>
									<div style='float: left; width: calc(100% / 3);'>
										<a href='javascript:void(0);' style='display: block; padding: 3px;' onclick="Swap('inventory', <?= $Recipient['ID']; ?>)">Inventory</a>
									</div>
									<div style='float: left; width: calc(100% / 3);'>
										<a href='javascript:void(0);' style='display: block; padding: 3px;' onclick="Swap('currency', <?= $Recipient['ID']; ?>)">Currency</a>
									</div>
								</div>
								<hr />

								<div id='TabContent<?= $Recipient['ID']; ?>'>
									<?php
										try
										{
											$Recipient_Query = $PDO->prepare("SELECT `ID` FROM `pokemon` WHERE `Owner_Current` = ? AND `Location` = 'Box' AND `Trade_Interest` != 'No' ORDER BY `Pokedex_ID` ASC LIMIT 50");
											$Recipient_Query->execute([$Recipient['ID']]);
											$Recipient_Query->setFetchMode(PDO::FETCH_ASSOC);
											$Recipient_Box = $Recipient_Query->fetchAll();

											$Query = "SELECT `ID` FROM `pokemon` WHERE `Owner_Current` = {$Recipient['ID']} AND `Location` = 'Box' AND `Trade_Interest` != 'No' ORDER BY `Pokedex_ID` ASC LIMIT 50";
											$Inputs = [];
											$Page = 1;
										}
										catch ( PDOException $e )
										{
											HandleError( $e->getMessage() );
										}

										if ( count($Recipient_Box) == 0 )
										{
											echo "<div style='padding: 85px 5px;'>There are no Pokemon in this user's box.</div>";
										}
										else
										{
											echo "<div class='page_nav'>";
											Pagi(str_replace('SELECT `ID`', 'SELECT COUNT(*)', $Query), $Recipient['ID'], $Inputs, $Page, 'onclick="updateBox(' . $Page . ', ' . $Recipient['ID'] . '); return false;"', 50);
											echo "</div>";

											echo "<div style='height: 160px; padding: 5px;'>";
											foreach( $Recipient_Box as $Index => $Pokemon )
											{
												$Pokemon = $PokeClass->FetchPokemonData($Pokemon['ID']);
												echo "<img class='spricon' src='{$Pokemon['Icon']}' onclick='Action({$Recipient['ID']}, \"Add\", \"Pokemon\", {$Pokemon['ID']})' />";
											}
											echo "</div>";
										}
									?>
								</div>
							</div>
						</div>

						<div class='panel'>
							<div class='panel-heading'>Included In Trade</div>
							<div class='panel-body' id='TradeIncluded<?= $Recipient['ID']; ?>'>
								<div style='padding: 12px;'>Nothing has been added to the trade yet.</div>
							</div>
						</div>
					</div>
				</div>

<?php
		}
	}
	else
	{
		echo "Please specify the recipient's ID.";
	}