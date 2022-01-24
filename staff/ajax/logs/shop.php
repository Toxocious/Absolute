<?php
  require_once $_SERVER['DOCUMENT_ROOT'] . '/core/required/session.php';

  try
	{
		$Get_Shop_Purchases = $PDO->prepare("
      SELECT *
      FROM `shop_logs`
      WHERE `Bought_By` = ?
      ORDER BY `ID` DESC
    ");
		$Get_Shop_Purchases->execute([
      $User_Data['ID']
    ]);
		$Get_Shop_Purchases->setFetchMode(PDO::FETCH_ASSOC);
		$Shop_Purchases = $Get_Shop_Purchases->fetchAll();
	}
	catch( PDOException $e )
	{
		HandleError( $e->getMessage() );
	}

	if ( count($Shop_Purchases) === 0 )
	{
		echo "<h3>This user has not purchased anything from the shops.</h3>";
    exit;
	}

  $Shop_Purchase_Text = '';
  foreach ( $Shop_Purchases as $Purchase )
  {
    if ( !empty($Purchase['Pokemon_ID']) )
    {
      $Pokemon_Data = $Poke_Class->FetchPokedexData($Purchase['Pokemon_Pokedex_ID'], $Purchase['Pokemon_Alt_ID'], $Purchase['Pokemon_Type']);
      $Purchase_Object_Text = "
        <td>
          <img src='{$Pokemon_Data['Icon']}' />
        </td>
        <td>
          <b>{$Pokemon_Data['Display_Name']}</b>
        </td>
      ";
    }
    else
    {
      $Item_Data = $Item_Class->FetchItemData($Purchase['Item_ID']);
      $Purchase_Object_Text = "
        <td>
          <img src='{$Item_Data['Icon']}' />
        </td>
        <td>
          <b>{$Item_Data['Name']}</b>
        </td>
      ";
    }

    $Bought_With = json_decode($Purchase['Bought_With'], true);
    $Bought_With_Text = '';
    foreach ( $Bought_With[0] as $Currency => $Amount )
    {
      $Bought_With_Text .= "
        <div style='display: flex; align-items: center; justify-content: center;'>
          <img src='" . DOMAIN_SPRITES . "/Assets/{$Currency}.png' /> &nbsp;&nbsp; " . number_format($Amount) . "
        </div>
      ";
    }


    $Shop_Purchase_Text .= "
      <tr>
        <td>
          {$Purchase['Shop_Name']}
        </td>
        {$Purchase_Object_Text}
        <td>
          {$Bought_With_Text}
        </td>
        <td>
          " . date('m/d/y&\nb\sp;&\nb\sp;h:i A', $Purchase['Timestamp']) . "
        </td>
      </tr>
    ";
  }
?>

<table class='border-gradient' style='width: 700px;'>
	<thead>
		<tr>
			<th colspan='5'>
				Shop Purchases
			</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td style='padding: 5px; width: 25%;'>
				<b>
					Shop
				</b>
			</td>
			<td colspan='2' style='width: 25%;'>
				<b>
					Object Purchased
				</b>
			</td>
			<td style='width: 25%;'>
				<b>
					Object Price
				</b>
			</td>
			<td style='width: 25%;'>
				<b>
					Bought On
				</b>
			</td>
		</tr>
	</tbody>
	<tbody>
		<?= $Shop_Purchase_Text; ?>
	</tbody>
</table>
