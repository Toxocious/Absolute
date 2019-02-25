<?php
	require 'core/required/layout_top.php';
?>

<div class='content'>
	<div class='head'>Trade Center</div>
	<div class='box'>
		<div id='TradeAJAX'>
			<?php
				if ( !isset($_GET['Action']) && !isset($_GET['id']) )
				{
					echo "
						<div class='description' style='margin-bottom: 5px;'>Enter a user's ID to begin a trade with them.</div>
						
						<input type='text' placeholder='User ID' id='recipientID' style='text-align: center; width: 200px;'/><br />
						<button onclick='TradePrepare();' style='width: 200px;'>Begin A Trade</button>
					";
				}
				else
				{
					echo "
						<div class='description' style='margin-bottom: 5px;'>Preparing your trade!</div>
						<div style='padding: 5px;'>
							<div class='spinner' style='left: 48%; position: relative;'></div><br />
						</div>
					";
				}
			?>
		</div>
	</div>
</div>

<script type='text/javascript'>
	<?php
		/**
		 * If a trade has been initiated via a URL redirect, trigger the proper function.
		 */
		if ( isset($_GET['Action']) && isset($_GET['ID']) )
		{
			$Action = $Purify->Cleanse($_GET['Action']);
			$User_ID = $Purify->Cleanse($_GET['ID']);
			
			if ( $Action == "Create" )
			{
				echo "TradePrepare($User_ID);";
			}
		}
	?>
	/**
	 * Show the proper UI if you're preparing to send a trade.
	 */
	function TradePrepare(ID)
	{
		let Recipient = 0;
		if ( ID == null )
		{
			Recipient = $('input#recipientID').val();
		}
		else
		{
			Recipient = ID;
		}

		$.ajax({
			type: 'POST',
			url: '<?= Domain(1); ?>/core/ajax/trading/prepare.php',
			data: { ID: Recipient },
			success: function(data)
			{
				$('#TradeAJAX').html(data);
			},
			error: function(data)
			{
				$('#TradeAJAX').html(data);
			}
		});
	}

	/**
	 * Create the trade.
 	 */
	function TradeCreate(id)
	{

	}

	/**
	 * Add or Remove Pokemon, Items, or Currency from the trade.
	 */
	function Action(User_ID, Action, Type, Data = null)
	{
		if ( Type == 'Currency' )
		{
			Data = {
				"Name": $('#TabContent' + User_ID + ' #' + Data).attr('ID'),
				"Amount": $('#TabContent' + User_ID + ' #' + Data).val(),
			};
		}

		$('#TradeIncluded' + User_ID).html("<div style='height: 190px; padding: 10px;'><div class='spinner' style='margin: -10px -10px; position: relative;'></div>");

		$.ajax({
      url: 'core/ajax/trading/action.php',
      type: 'POST',
			data: { ID: User_ID, Action: Action, Type: Type, Data: Data },
			success: function(data)
			{
				$('#TradeIncluded' + User_ID).html(data);
			},
			error: function(data)
			{
				$('#TradeIncluded' + User_ID).html(data);
			}
		});
	}

	/**
	 * Swap through the tabs: Pokemon, Inventory, and Currency.
	 */
	function Swap(Tab, User_ID)
	{
		$('#TabContent' + User_ID).html("<div style='height: 190px; padding: 10px;'><div class='spinner' style='margin: -10px -10px; position: relative;'></div>");

		$.ajax({
      url: 'core/ajax/trading/' + Tab + '.php',
      type: 'POST',
			data: { tab: Tab, id: User_ID },
			success: function(data)
			{
				$('#TabContent' + User_ID).html(data);
			},
			error: function(data)
			{
				$('#TabContent' + User_ID).html(data);
			}
		});
	}

	CurrentSearch = [0,0,0];
	function updateBox(Page, User_ID)
  {
    if ( Page == 'auto' )
    {
      Page = currpage;
    }
    else
    {
      currpage = Page;
    }

    $.ajax({
      url: 'core/ajax/trading/box.php',
      type: 'POST',
      data: {
        id: User_ID,
        filter_type: CurrentSearch[0],
        //filter_search: $('[name=pokemon_search]').val(),
        //filter_select: $('[name=pokemon_select]').val(),
        filter_gender: CurrentSearch[1],
        filter_search_order: CurrentSearch[2],
        //filter_order: CurrentSearch[2],
        Page: Page
      },
      success: function(data)
      {
        $('#TabContent' + User_ID).html(data);
      },
      error: function(data)
      {
        $('#TabContent' + User_ID).html(data);
      }
    });
  }
</script>

<?php
	require 'core/required/layout_bottom.php';
?>