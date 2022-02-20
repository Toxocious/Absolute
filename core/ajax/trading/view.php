<?php
	require_once '../../required/session.php';

	if ( !isset($_POST['Trade_ID']) )
	{
		echo "
			<div class='error'>
				The trade that you are trying to view doesn't exist.
			</div>
		";

		return;
	}

	$Trade_ID = Purify($_POST['Trade_ID']);

	try
	{
		$Trade_Query = $PDO->prepare("SELECT * FROM `trades` WHERE `ID` = ? AND (`Sender` = ? OR `Recipient` = ?)");
		$Trade_Query->execute([ $Trade_ID, $User_Data['ID'], $User_Data['ID'] ]);
		$Trade_Query->setFetchMode(PDO::FETCH_ASSOC);
		$Trade = $Trade_Query->fetch();
	}
	catch( PDOException $e )
	{
		HandleError($e);
	}

	if ( count($Trade) === 0 )
	{
		echo "
			<div class='error'>
				You may not view trades that you did not take part in, or that do not exist.
			</div>
		";

		return;
	}

	$Sender = $User_Class->FetchUserData($Trade['Sender']);
	$Recipient = $User_Class->FetchUserData($Trade['Recipient']);

	switch ( $Trade['Status'] )
	{
		case 'Accepted':
			$Color = "#00ff00";
			break;
		case 'Declined':
			$Color = "#ff0000";
			break;
		case 'Deleted':
			$Color = "#999";
			break;
	}

	$Trade_Status = '';
	if ( $Trade['Status'] != 'Pending' )
	{
		$Trade_Status = "
			<br />
			This trade was <b style='color: {$Color}'>" . strtolower($Trade['Status']) . "</b>.
		";
	}
?>

<div class='description' style='flex-basis: 85%;'>
	Viewing the offered contents of Trade #<?= number_format($Trade_ID); ?>.
	<?= $Trade_Status; ?>
</div>

<div style='flex-basis: 85%;'>
	<?php
		if ( $Trade['Status'] == 'Pending' )
		{
			if ( $Trade['Sender'] != $User_Data['ID'] )
			{
				echo "
					<div>
						<button onclick=\"TradeManage({$Trade['ID']}, 'Accepted');\" style='padding: 5px; width: 200px;'>
							Accept Trade
						</button>
						<button onclick=\"TradeManage({$Trade['ID']}, 'Declined');\" style='padding: 5px; width: 200px;'>
							Decline Trade
						</button>
					</div>
				";
			}
			else
			{
				echo "
					<div>
						<button onclick=\"TradeManage({$Trade['ID']}, 'Deleted');\" style='padding: 5px; width: 200px;'>Cancel Trade</button>
					</div>
				";
			}
		}
	?>
</div>

<table class='border-gradient' style='flex-basis: 46%; margin: 5px;'>
	<thead>
		<tr>
			<th colspan='3'>
				<b><?= $Sender['Username']; ?>'s Offer</b>
			</th>
		</tr>
	</thead>
	<tbody>
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
				HandleError($e);
			}

			if
			(
				empty($Sender_Content['Sender_Pokemon']) &&
				empty($Sender_Content['Sender_Items']) &&
				empty($Sender_Content['Sender_Currency'])
			)
			{
				echo "
					<tr>
						<td colspan='3' style='padding: 12px;'>
							<b>This user has nothing included in their side of the trade.</b>
						</td>
					</tr>
				";
			}
			else
			{
				if ( !empty($Sender_Content['Sender_Pokemon']) )
				{
					$Sender_Pokemon = explode(',', $Sender_Content['Sender_Pokemon']);
					foreach ( $Sender_Pokemon as $Key => $Pokemon )
					{
						$Pokemon_Data = $Poke_Class->FetchPokemonData($Pokemon);

						echo "
							<tr>
								<td colspan='1' style='width: 76px;'>
									<img src='{$Pokemon_Data['Icon']}' />
									" . ( $Pokemon_Data['Item'] ? "<img src='{$Pokemon_Data['Item_Icon']}' />" : '' ) . "
								</td>
								<td colspan='1' style='width: 34px;'>
									<img src='{$Pokemon_Data['Gender_Icon']}' style='height: 20px; width: 20px;' />
								</td>
								<td colspan='1'>
									{$Pokemon_Data['Display_Name']} (Level: " . number_format($Pokemon_Data['Level']) . ")
									" . ($Pokemon_Data['Nickname'] ? "<br /><i>{$Pokemon_Data['Nickname']}</i>" : '')  . "
								</td>
							</tr>
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
							<tr>
								<td colspan='2' style='width: 76px;'>
									<img src='{$Item_Data['Icon']}' />
								</td>
								<td colspan='1'>
									{$Item_Data['Name']}<br />
									x" . number_format($Item_Params[2]) . "
								</td>
							</tr>
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
							<tr>
								<td colspan='2' style='width: 76px;'>
									<img src='{$Currency_Data['Icon']}' />
								</td>
								<td colspan='1'>
									{$Currency_Data['Name']}<br />
									" . number_format($Currency_Info[1]) . "
								</td>
							</tr>
						";
					}
				}
			}
		?>
	</tbody>
</table>

<table class='border-gradient' style='flex-basis: 46%; margin: 5px;'>
	<thead>
		<tr>
			<th colspan='3'>
				<b><?= $Recipient['Username']; ?>'s Offer</b>
			</th>
		</tr>
	</thead>
	<tbody>
	<?php
			try
			{
				$Recipient_Query = $PDO->prepare("SELECT `Recipient`, `Recipient_Pokemon`, `Recipient_Currency`, `Recipient_Items` FROM `trades` WHERE `ID` = ?");
				$Recipient_Query->execute([ $Trade_ID ]);
				$Recipient_Query->setFetchMode(PDO::FETCH_ASSOC);
				$Recipient_Content = $Recipient_Query->fetch();
			}
			catch( PDOException $e )
			{
				HandleError($e);
			}

			if
			(
				empty($Recipient_Content['Recipient_Pokemon']) &&
				empty($Recipient_Content['Recipient_Items']) &&
				empty($Recipient_Content['Recipient_Currency'])
			)
			{
				echo "
					<tr>
						<td colspan='3' style='padding: 12px;'>
							<b>This user has nothing included in their side of the trade.</b>
						</td>
					</tr>
				";
			}
			else
			{
				if ( !empty($Recipient_Content['Recipient_Pokemon']) )
				{
					$Recipient_Pokemon = explode(',', $Recipient_Content['Recipient_Pokemon']);
					foreach ( $Recipient_Pokemon as $Key => $Pokemon )
					{
						$Pokemon_Data = $Poke_Class->FetchPokemonData($Pokemon);

						echo "
							<tr>
								<td colspan='1' style='width: 76px;'>
									<img src='{$Pokemon_Data['Icon']}' />
									" . ( $Pokemon_Data['Item'] ? "<img src='{$Pokemon_Data['Item_Icon']}' />" : '' ) . "
								</td>
								<td colspan='1' style='width: 34px;'>
									<img src='{$Pokemon_Data['Gender_Icon']}' style='height: 20px; width: 20px;' />
								</td>
								<td colspan='1'>
									{$Pokemon_Data['Display_Name']} (Level: " . number_format($Pokemon_Data['Level']) . ")
									" . ($Pokemon_Data['Nickname'] ? "<br /><i>{$Pokemon_Data['Nickname']}</i>" : '')  . "
								</td>
							</tr>
						";
					}
				}

				if ( !empty($Recipient_Content['Recipient_Items']) )
				{
					$Recipient_Items = explode(',', $Recipient_Content['Recipient_Items']);
					foreach ( $Recipient_Items as $Key => $Item )
					{
						// row-id-quantity-owner
						$Item_Params = explode('-', $Item);
						$Item_Data = $Item_Class->FetchOwnedItem($Recipient_Content['Recipient'], $Item_Params[1]);

						echo "
							<tr>
								<td colspan='2' style='width: 76px;'>
									<img src='{$Item_Data['Icon']}' />
								</td>
								<td colspan='1'>
									{$Item_Data['Name']}<br />
									x" . number_format($Item_Params[2]) . "
								</td>
							</tr>
						";
					}
				}

				if ( !empty($Recipient_Content['Recipient_Currency']) )
				{
					$Recipient_Currency = explode(',', $Recipient_Content['Recipient_Currency']);
					foreach ( $Recipient_Currency as $Key => $Currency )
					{
						$Currency_Info = explode('-', $Currency);
						$Currency_Data = $Constants->Currency[$Currency_Info[0]];

						echo "
							<tr>
								<td colspan='2' style='width: 76px;'>
									<img src='{$Currency_Data['Icon']}' />
								</td>
								<td colspan='1'>
									{$Currency_Data['Name']}<br />
									" . number_format($Currency_Info[1]) . "
								</td>
							</tr>
						";
					}
				}
			}
		?>
	</tbody>
</table>
