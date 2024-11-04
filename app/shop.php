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
      foreach ( ['Pokemon', 'Items'] as $Shop_Catalog )
      {
        switch ( $Shop_Catalog )
        {
          case 'Pokemon':
            $Shop_Objects = $Shop_Class->FetchShopPokemon($Shop_ID);
            break;

          case 'Items':
            $Shop_Objects = $Shop_Class->FetchShopItems($Shop_ID);
            break;
        }

        if ( $Shop_Objects )
        {
          echo "
            <div class='flex wrap' style='justify-content: center;'>
              <div style='width: 100%;'>
                <h3>Shop {$Shop_Catalog}</h3>
              </div>
          ";

          foreach ( $Shop_Objects as $Shop_Object )
          {
            if ( !$Shop_Object['Prices'] )
              continue;

            switch ( $Shop_Catalog )
            {
              case 'Pokemon':
                $Object_Data = GetPokedexData($Shop_Object['Pokedex_ID'], $Shop_Object['Alt_ID'], $Shop_Object['Type']);
                break;

              case 'Items':
                $Object_Data = $Item_Class->FetchItemData($Shop_Object['Item_ID']);
                break;
            }

            $Can_Afford = true;
            $Price_String = '';

            $Price_Array = $Shop_Class->FetchPriceList($Shop_Object['Prices']);
            foreach ( $Price_Array[0] as $Currency => $Amount )
            {
              $Price_String .= "
                <div style='display: flex; align-items: center; justify-content: flex-start; gap: 5px;'>
                  <div>
                    <img src='" . DOMAIN_SPRITES . "/Assets/{$Currency}.png' />
                  </div>
                  <div>
                    " . number_format($Amount) . "
                  </div>
                </div>
              ";

              if ( $User_Data[$Currency] < $Amount )
              {
                $Can_Afford = false;
                break;
              }
            }

            if ( $Shop_Object['Remaining'] < 1 )
            {
              $Purchase_Button = "
                <button class='disabled'>
                  Not In Stock
                </button>
              ";
            }
            else if ( $Can_Afford )
            {
              $Purchase_Button = "
                <button onclick='Purchase({\"ID\": {$Shop_Object['ID']}, \"Type\": \"{$Shop_Catalog}\"});'>
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

            $Object_Name = $Object_Data['Display_Name'] ?? $Object_Data['Name'];
            $Object_Image = $Object_Data['Sprite'] ?? $Object_Data['Icon'];

            echo "
              <table class='border-gradient' style='flex-basis: 200px; margin: 5px 5px;'>
                <thead>
                  <tr>
                    <th colspan='2'>
                      {$Object_Name}
                    </th>
                  </tr>
                </thead>

                <tbody>
                  <tr>
                    <td colspan='1' style='width: 96px;'>
                      <img src='{$Object_Image}' />
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

          echo "
            </div>
          ";
        }
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
