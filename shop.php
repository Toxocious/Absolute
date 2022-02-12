<?php
	require_once 'core/required/layout_top.php';
	require_once 'core/classes/shop.php';

	if ( isset($_GET['Shop']) )
		$Shop_ID = Purify($_GET['Shop']);
	else
		$Shop_ID = 1;

	$Shop = $Shop_Class->FetchShopData($Shop_ID);
?>

<div class='panel content'>
	<div class='head'><?= ($Shop ? $Shop['Name'] : 'Shop'); ?></div>
	<div class='body'>
		<?php
			if ( !$Shop )
			{
				echo "
					<div style='margin: auto; padding: 10px;'>
						An error occurred while loading the shop.
					</div>
				";

				require_once 'core/required/layout_bottom.php';

				return;
			}
		?>

		<div class='nav'>
			<div>
				<a href='<?= DOMAIN_ROOT; ?>/shop.php?Shop=1' style='display: block;'>Pokemon</a>
			</div>
		</div>

		<div class='description'>
			<?= $Shop['Description']; ?>
		</div>

		<div id='ShopAJAX'></div>

		<?php
			$Selling_Pokemon = $Shop_Class->FetchShopPokemon($Shop_ID);
			if ( $Selling_Pokemon )
			{
        echo "
          <div class='flex wrap' style='justify-content: center;'>
          <div style='width: 100%;'>
            <h3>Shop Pok&eacute;mon</h3>
          </div>
        ";

				foreach ( $Selling_Pokemon as $Shop_Pokemon )
				{
					if ( !$Shop_Pokemon['Prices'] )
						continue;

					$Can_Afford = true;
					$Price_String = '';

					$Pokedex_Data = $Poke_Class->FetchPokedexData($Shop_Pokemon['Pokedex_ID'], $Shop_Pokemon['Alt_ID'], $Shop_Pokemon['Type']);

					$Price_Array = $Shop_Class->FetchPriceList($Shop_Pokemon['Prices']);
					foreach ( $Price_Array[0] as $Currency => $Amount )
					{
						$Price_String .= "
							<img src='" . DOMAIN_SPRITES . "/Assets/{$Currency}.png' />" . number_format($Amount) . "<br />
						";

						if ( $User_Data[$Currency] < $Amount )
						{
							$Can_Afford = false;
							break;
						}
						else
						{
							$Can_Afford = true;
						}
					}

					if ( $Can_Afford )
					{
						$Purchase_Button = "
							<button onclick='Purchase({\"ID\": {$Shop_Pokemon['ID']}, \"Type\": \"Pokemon\"});'>
								Purchase
							</button>
						";
					}
					else
					{
						$Purchase_Button = "
							<button class='disabled'>
								Can't Afford
							</button>
						";
					}

					if ( $Shop_Pokemon['Remaining'] < 1 )
					{
						$Purchase_Button = "
							<button class='disabled'>
								Not In Stock
							</button>
						";
					}

					echo "
						<table class='border-gradient' style='flex-basis: 200px; margin: 5px 5px;'>
							<thead>
								<tr>
									<th colspan='2'>
										{$Pokedex_Data['Display_Name']}
									</th>
								</tr>
							</thead>

							<tbody>
								<tr>
									<td colspan='1' style='width: 96px;'>
										<img src='{$Pokedex_Data['Sprite']}' />
									</td>
									<td colspan='1'>
										{$Price_String}
									</td>
								</tr>
							</tbody>
							<tbody>
								<tr>
									<td colspan='2' id='Pokemon_{$Shop_Pokemon['ID']}'>
									" . ($Shop_Pokemon['Remaining'] < 1 ? "Out of stock!" : "In stock: " . number_format($Shop_Pokemon['Remaining'])) . "
									</td>
								</tr>
							</tbody>
							<tbody>
								<tr>
									<td colspan='2'>
										{$Purchase_Button}
									</td>
								</tr>
							</tbody>
						</table>
					";
				}

        echo "</div>";
			}

			$Selling_Items = $Shop_Class->FetchShopItems($Shop_ID);
			if ( $Selling_Items )
			{
        echo "
          <div class='flex wrap' style='justify-content: center;'>
          <div style='width: 100%;'>
            <h3>Shop Items</h3>
          </div>
        ";

        foreach ( $Selling_Items as $Shop_Items )
				{
					if ( !$Shop_Items['Prices'] )
						continue;

          $Item_Data = $Item_Class->FetchItemData($Shop_Items['Item_ID']);

					$Can_Afford = true;
					$Price_String = '';

					$Price_Array = $Shop_Class->FetchPriceList($Shop_Items['Prices']);
					foreach ( $Price_Array[0] as $Currency => $Amount )
					{
						$Price_String .= "
							<img src='" . DOMAIN_SPRITES . "/Assets/{$Currency}.png' />" . number_format($Amount) . "<br />
						";

						if ( $User_Data[$Currency] < $Amount )
						{
							$Can_Afford = false;
							break;
						}
						else
						{
							$Can_Afford = true;
						}
					}

					if ( $Can_Afford )
					{
						$Purchase_Button = "
							<button onclick='Purchase({\"ID\": {$Shop_Items['ID']}, \"Type\": \"Item\"});'>
								Purchase
							</button>
						";
					}
					else
					{
						$Purchase_Button = "
							<button class='disabled'>
								Can't Afford
							</button>
						";
					}

					if ( $Shop_Items['Remaining'] < 1 )
					{
						$Purchase_Button = "
							<button class='disabled'>
								Not In Stock
							</button>
						";
					}

					echo "
						<table class='border-gradient' style='flex-basis: 200px; margin: 5px 5px;'>
							<thead>
								<tr>
									<th colspan='2'>
										{$Item_Data['Name']}
									</th>
								</tr>
							</thead>

							<tbody>
								<tr>
									<td colspan='1'>
										<img src='{$Item_Data['Icon']}' item_id='{$Item_Data['ID']}' />
									</td>
									<td colspan='1'>
										{$Price_String}
									</td>
								</tr>
							</tbody>
							<tbody>
								<tr>
									<td colspan='2'>
										{$Purchase_Button}
									</td>
								</tr>
							</tbody>
						</table>
					";
				}

        echo "</div>";
			}
		?>
	</div>
</div>

<script type='text/javascript'>
	const Purchase = (Object) =>
	{
		return new Promise((resolve, reject) =>
    {
      const req = new XMLHttpRequest();
      req.open('GET', `<?= DOMAIN_ROOT; ?>/core/ajax/shop/purchase.php?Shop=<?= $Shop['ID']; ?>&Object_ID=${Object.ID}&Object_Type=${Object.Type}`);
      req.send(null);
      req.onerror = (error) => reject(`Network Error: ${error}`);
      req.onload = () =>
      {
        if ( req.status === 200 )
        {
          document.querySelector('#ShopAJAX').innerHTML = req.responseText;
					FetchStock(Object);
          resolve(req.response);
        }
        else
        {
          document.querySelector('#ShopAJAX').innerHTML = req.statusText;
					FetchStock(Object);
          reject(req.statusText);
        }
      };
    });
	}

	const FetchStock = (Object) =>
	{
		return new Promise((resolve, reject) =>
    {
      const req = new XMLHttpRequest();
      req.open('GET', `<?= DOMAIN_ROOT; ?>/core/ajax/shop/fetch_stock.php?Object_ID=${Object.ID}&Object_Type=${Object.Type}`);
      req.send(null);
      req.onerror = (error) => reject(`Network Error: ${error}`);
      req.onload = () =>
      {
        if ( req.status === 200 )
        {
          document.querySelector(`#${Object.Type}_${Object.ID}`).innerHTML = req.responseText;
          resolve(req.response);
        }
        else
        {
          document.querySelector(`#${Object.Type}_${Object.ID}`).innerHTML = req.statusText;
          reject(req.statusText);
        }
      };
    });
	}
</script>

<?php
	require_once 'core/required/layout_bottom.php';
