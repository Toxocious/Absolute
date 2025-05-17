<?php
	require_once $_SERVER['DOCUMENT_ROOT'] . '/core/required/layout_top.php';

    require_once $_SERVER['DOCUMENT_ROOT'] . '/pages/shop/functions/fetch_stock.php';

	if ( isset($_GET['Shop']) )
    {
        $Shop_ID = Purify($_GET['Shop']);
    }
	else
    {
        $Shop_ID = 1;
    }

	$Shop = FetchShopData($Shop_ID);
?>

<div class='panel content'>
	<div class='head'><?= ($Shop ? $Shop['Name'] : 'Shop'); ?></div>
	<div class='body' style='padding: 5px;'>
		<?php
			if ( !$Shop )
			{
				echo "
                            <div style='margin: auto; padding: 1em;'>
                                The shop you're trying to access does not exist.
                            </div>
                        </div>
                    </div>
				";

				require_once $_SERVER['DOCUMENT_ROOT'] . '/core/required/layout_bottom.php';
				return;
			}
		?>

         <div style='flex: 1;'>
            <div class='page-nav-container'>
                <div class='page-nav-item <?= ($Shop_ID == 1 ? 'active' : '') ;?>'>
                    <a href='<?= DOMAIN_ROOT; ?>/shop.php?Shop=1' style='display: block;'>Pokemon Shop</a>
                </div>
                <div class='page-nav-item <?= ($Shop_ID == 2 ? 'active' : '') ;?>'>
                    <a href='<?= DOMAIN_ROOT; ?>/shop.php?Shop=2' style='display: block;'>Item Shop</a>
                </div>
            </div>
         </div>

		<div style='margin: 1em auto;'>
			<?= $Shop['Description']; ?>
		</div>

		<div id='ShopAJAX'></div>

		<?php
            foreach ( ['Pokemon', 'Items'] as $Shop_Catalog )
            {
                switch ( $Shop_Catalog )
                {
                    case 'Pokemon':
                        $Shop_Objects = FetchShopPokemon($Shop_ID);
                        break;

                    case 'Items':
                        $Shop_Objects = FetchShopItems($Shop_ID);
                        break;
                }

                if ( $Shop_Objects )
                {
                    echo "
                        <div class='flex flex-row wrap' style='gap: 1em; justify-content: center; width: 100%;'>
                    ";

                    foreach ( $Shop_Objects as $Shop_Object )
                    {
                        if ( !$Shop_Object['Prices'] )
                        {
                            continue;
                        }

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

                        $Price_Array = FetchPriceList($Shop_Object['Prices']);
                        foreach ( $Price_Array[0] as $Currency => $Amount )
                        {
                            $Price_String .= "
                                <div>
                                    <img src='" . DOMAIN_SPRITES . "/Assets/{$Currency}.png' /> " . number_format($Amount) . "
                                </div>
                            ";

                            if ( $User_Data[$Currency] < $Amount )
                            {
                                $Can_Afford = false;
                            }
                        }

                        if ( $Can_Afford )
                        {
                            $Purchase_Button = "
                                <button onclick='PurchaseShopObject({\"Shop_ID\": \"{$Shop_ID}\", \"Object_ID\": {$Shop_Object['ID']}, \"Object_Type\": \"{$Shop_Catalog}\"});'>
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
                            <div class='shop-card'>
                                <div class='shop-card-content'>
                                    <div class='shop-card-image'>
                                        <img src='{$Object_Image}' alt='{$Object_Name}' />
                                    </div>
                                    <div class='shop-card-title'>
                                        <div class='shop-card-info'>
                                            <div class='shop-card-name'>{$Object_Data['Name']}</div>
                                            <div class='shop-card-type'>" . ($Object_Data['Type'] ?? '') . "</div>
                                        </div>
                                        <div class='shop-card-badge'>
                                            " . (isset($Object_Data['Type']) && $Object_Data['Type'] == "Shiny" ? "<img src='https://archives.bulbagarden.net/media/upload/8/82/ShinyLGPEStar.png' />" : '') . "
                                        </div>
                                    </div>
                                    <div class='shop-card-prices'>
                                        {$Price_String}
                                    </div>
                                    <div class='shop-card-footer'>
                                        <div class='shop-card-time-remaining'>
                                            <svg xmlns='http://www.w3.org/2000/svg' height='24px' viewBox='0 -960 960 960' width='24px' fill='#e3e3e3'><path d='m612-292 56-56-148-148v-184h-80v216l172 172ZM480-80q-83 0-156-31.5T197-197q-54-54-85.5-127T80-480q0-83 31.5-156T197-763q54-54 127-85.5T480-880q83 0 156 31.5T763-763q54 54 85.5 127T880-480q0 83-31.5 156T763-197q-54 54-127 85.5T480-80Zm0-400Zm0 320q133 0 226.5-93.5T800-480q0-133-93.5-226.5T480-800q-133 0-226.5 93.5T160-480q0 133 93.5 226.5T480-160Z'/></svg>
                                            4hr 20m
                                        </div>
                                        <div class='shop-card-button'>
                                            {$Purchase_Button}
                                        </div>
                                    </div>
                                </div>
                            </div>
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

<script src='<?= DOMAIN_ROOT; ?>/pages/shop/js/ajax_functions.js'></script>
<script src='<?= DOMAIN_ROOT; ?>/pages/shop/js/shop.js'></script>

<?php
	require_once $_SERVER['DOCUMENT_ROOT'] . '/core/required/layout_bottom.php';
