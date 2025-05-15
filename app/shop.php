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

		<div style='margin: 0.5em 0;'>
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
                        <div class='flex wrap' style='justify-content: center; gap: 1em 0;'>
                        <div style='width: 100%;'>
                            <h3>Shop {$Shop_Catalog}</h3>
                        </div>
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
                            <table class='border-gradient' style='flex-basis: 250px;'>
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

<script src='<?= DOMAIN_ROOT; ?>/pages/shop/js/ajax_functions.js'></script>
<script src='<?= DOMAIN_ROOT; ?>/pages/shop/js/shop.js'></script>

<?php
	require_once $_SERVER['DOCUMENT_ROOT'] . '/core/required/layout_bottom.php';
