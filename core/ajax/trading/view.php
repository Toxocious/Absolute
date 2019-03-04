<?php
	require '../../required/session.php';

	if ( isset($_POST['Trade_ID']) )
	{
		$Trade_ID = $Purify->Cleanse($_POST['Trade_ID']);

		try
		{
			$Trade_Query = $PDO->prepare("SELECT * FROM `trades` WHERE `ID` = ? AND (`Sender` = ? OR `Receiver` = ?)");
			$Trade_Query->execute([ $Trade_ID, $User_Data['id'], $User_Data['id'] ]);
			$Trade_Query->setFetchMode(PDO::FETCH_ASSOC);
			$Trade = $Trade_Query->fetch();
		}
		catch( PDOException $e )
		{
			HandleError( $e->getMessage() );
		}

		if ( count($Trade) === 0 )
		{
			echo "<div class='error'>You may not view trades that you did not take part in.</div>";
		}
		else
		{
			$Sender = $UserClass->FetchUserData($Trade['Sender']);
			$Recipient = $UserClass->FetchUserData($Trade['Receiver']);

			switch ( $Trade['Status'] )
			{
				case 'Accepted':
					$Color = "#00ff00";
					break;
				case 'Declined':
					$Color = "#ff0000";
					break;
			}

?>
			<div class='description' style='margin-bottom: 5px;'>
				Viewing the included contents of Trade #<?= number_format($Trade_ID); ?>.
				<?php
					if ( $Trade['Status'] != 'Pending' )
					{
						echo "<br />This trade was <b style='color: {$Color}'>{$Trade['Status']}</b>.";
					}
				?>
			</div>

			<?php
				if ( $Trade['Status'] == 'Pending' )
				{
					if ( $Trade['Sender'] != $User_Data['id'] )
					{
						echo "
							<div style='margin-bottom: 5px;'>
								<button onclick=\"TradeManage({$Trade['ID']}, 'Accept');\" style='padding: 5px; width: calc(100% / 2 - 2.5px);'>Accept Trade</button>
								<button onclick=\"TradeManage({$Trade['ID']}, 'Delete');\" style='padding: 5px; width: calc(100% / 2 - 2.5px);'>Decline Trade</button>
							</div>
						";
					}
					else
					{
						echo "
							<div style='margin-bottom: 5px;'>
								<button onclick=\"TradeManage({$Trade['ID']}, 'Delete');\" style='padding: 5px; width: calc(100%);'>Delete Trade</button>
							</div>
						";
					}
				}
			?>

			<div class='row'>
				<div style='float: left; margin-right: 5px; width: calc(100% / 2 - 2.5px);'>
					<div class='panel' style='margin-bottom: 5px;'>
						<div class='panel-heading'><?= $Sender['Username']; ?>'s Belongings</div>
						<div class='panel-body'>
							<?php
								try
								{
									$Sender_Query = $PDO->prepare("SELECT `Sender`, `Sender_Pokemon`, `Sender_Currency`, `Sender_Items` FROM `trades` WHERE `ID` = ?");
									$Sender_Query->execute([ $Trade_ID ]);
									$Sender_Query->setFetchMode(PDO::FETCH_ASSOC);
									$Sender_Content = $Sender_Query->fetch();
								}
								catch( PDOException $e )
								{
									HandleError( $e->getMessage() );
								}

								if
								(
									empty($Sender_Content['Sender_Pokemon']) && empty($Sender_Content['Sender_Items']) && empty($Sender_Content['Sender_Currency'])
								)
								{
									echo "<div class='notice' style='margin: 5px; width: calc(100% - 10px);'>This user has nothing included in their side of the trade.</div>";
								}
								else
								{
									if ( !empty($Sender_Content['Sender_Pokemon']) )
									{
										$Sender_Pokemon = explode(',', $Sender_Content['Sender_Pokemon']);
										foreach ( $Sender_Pokemon as $Key => $Pokemon )
										{
											$Pokemon_Data = $PokeClass->FetchPokemonData($Pokemon);

											echo "
												<div>
													<img src='{$Pokemon_Data['Icon']}' />
													<img src='{$Pokemon_Data['Gender_Icon']}' style='height: 20px; width: 20px;' />
													{$Pokemon_Data['Display_Name']} (Level: " . number_format($Pokemon_Data['Level']) . ")
												</div>
												<hr />
											";
										}
									}

									if ( !empty($Sender_Content['Sender_Items']) )
									{
										$Sender_Items = explode(',', $Sender_Content['Sender_Items']);
										foreach ( $Sender_Items as $Key => $Item )
										{
											// row-id-quantity-owner
											$Item_Params = explode('-', $Item);
											$Item_Data = $Item_Class->FetchOwnedItem($Sender_Content['Sender'], $Item_Params[1]);

											echo "
												<div>
													<img src='images/items/{$Item_Data['Name']}.png' />
													{$Item_Data['Name']} (x" . number_format($Item_Params[2]) . ")
												</div>
												<hr />
											";
										}
									}

									if ( !empty($Sender_Content['Sender_Currency']) )
									{
										$Sender_Currency = explode(',', $Sender_Content['Sender_Currency']);
										foreach ( $Sender_Currency as $Key => $Currency )
										{
											$Currency_Info = explode('-', $Currency);
											$Currency_Data = $Constants->Currency[$Currency_Info[0]];

											echo "
												<div>
													<img src='{$Currency_Data['Icon']}' style='height: 32px; width: 32px;' />
													" . number_format($Currency_Info[1]) . "
												</div>
												<hr />
											";
										}
									}
								}
							?>
						</div>
					</div>
				</div>

				<div style='float: left; width: calc(100% / 2 - 2.5px);'>
					<div class='panel' style='margin-bottom: 5px;'>
						<div class='panel-heading'><?= $Recipient['Username']; ?>'s Belongings</div>
						<div class='panel-body'>
							<?php
								try
								{
									$Receiver_Query = $PDO->prepare("SELECT `Receiver_Pokemon`, `Receiver_Currency`, `Receiver_Items` FROM `trades` WHERE `ID` = ?");
									$Receiver_Query->execute([ $Trade_ID ]);
									$Receiver_Query->setFetchMode(PDO::FETCH_ASSOC);
									$Receiver_Content = $Receiver_Query->fetch();
								}
								catch( PDOException $e )
								{
									HandleError( $e->getMessage() );
								}

								if
								(
									empty($Receiver_Content['Receiver_Pokemon']) && empty($Receiver_Content['Receiver_Items']) && empty($Receiver_Content['Receiver_Currency'])
								)
								{
									echo "<div class='notice' style='margin: 5px; width: calc(100% - 10px);'>This user has nothing included in their side of the trade.</div>";
								}
								else
								{
									if ( !empty($Receiver_Content['Receiver_Pokemon']) )
									{
										$Receiver_Pokemon = explode(',', $Receiver_Content['Receiver_Pokemon']);
										foreach ( $Receiver_Pokemon as $Key => $Pokemon )
										{
											$Pokemon_Data = $PokeClass->FetchPokemonData($Pokemon);

											echo "
												<div>
													<img src='{$Pokemon_Data['Icon']}' />
													<img src='{$Pokemon_Data['Gender_Icon']}' style='height: 20px; width: 20px;' />
													{$Pokemon_Data['Display_Name']} (Level: " . number_format($Pokemon_Data['Level']) . ")
												</div>
												<hr />
											";
										}
									}

									if ( !empty($Receiver_Content['Receiver_Items']) )
									{
										$Receiver_Items = explode(',', $Receiver_Content['Receiver_Items']);
										foreach ( $Receiver_Items as $Key => $Item )
										{
											// row-id-quantity-owner
											$Item_Params = explode('-', $Item);
											$Item_Data = $Item_Class->FetchOwnedItem($Receiver_Content['Receiver'], $Item_Params[1]);

											echo "
												<div>
													<img src='images/items/{$Item_Data['Name']}.png' />
													{$Item_Data['Name']} (x" . number_format($Item_Params[2]) . ")
												</div>
												<hr />
											";
										}
									}

									if ( !empty($Receiver_Content['Receiver_Currency']) )
									{
										$Receiver_Currency = explode(',', $Receiver_Content['Receiver_Currency']);
										foreach ( $Receiver_Currency as $Key => $Currency )
										{
											$Currency_Info = explode('-', $Currency);
											$Currency_Data = $Constants->Currency[$Currency_Info[0]];

											echo "
												<div>
													<img src='{$Currency_Data['Icon']}' style='height: 32px; width: 32px;' />
													" . number_format($Currency_Info[1]) . "
												</div>
												<hr />
											";
										}
									}
								}
							?>
						</div>
					</div>
				</div>
			</div>

<?php
		}
	}
	else
	{
		echo "<div class='error'>The trade that you're trying to view doesn't exist.</div>";
	}