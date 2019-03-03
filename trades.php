<?php
	require 'core/required/layout_top.php';
?>

<div class='content'>
	<div class='head'>Trade Center</div>
	<div class='box'>
		<div class='nav'>
			<div onclick="Tab('pending');">
				Pending Trades
			</div>
			<div onclick="Tab('history');">
				Trade History
			</div>
		</div>

		<div id='TradeAJAX'>
			<?php
				if ( !isset($_GET['Action']) && !isset($_GET['id']) )
				{
			?>
			
				<div class='description' style='margin-bottom: 5px;'>Enter a user's ID to begin a trade with them.</div>
				<div class='panel' style='margin-top: 5px;'>
					<div class='panel-heading'>Create A Trade</div>
					<div class='panel-body' style='padding: 5px;'>
						<input type='text' placeholder='User ID' id='recipientID' style='text-align: center; width: 200px;'/><br />
						<button onclick='TradePrepare();' style='width: 200px;'>Begin A Trade</button>
					</div>
				</div>

				<div class='description' style='margin: 5px 0px;'>All ingoing and outgoing trades that involve you are listed below.</div>
				<div class='panel' style='margin-top: 5px;'>
					<div class='panel-heading'>Pending Trades</div>
					<div class='panel-body' style='padding: 5px;'>
						<?php
							try
							{
								$Pending_Query = $PDO->prepare("SELECT `ID`, `Sender`, `Receiver`, `Status` FROM `trades` WHERE (`Sender` = ? OR `Receiver` = ?) AND `Status` = ?");
								$Pending_Query->execute([ $User_Data['id'], $User_Data['id'], 'Pending' ]);
								$Pending_Query->setFetchMode(PDO::FETCH_ASSOC);
								$Pending_Trades = $Pending_Query->fetchAll();
							}
							catch( PDOException $e )
							{
								HandleError( $e->getMessage() );
							}

							if ( count($Pending_Trades) === 0 )
							{
								echo "<div class='notice'>You do not currently have any pending trades.</div>";
							}
							else
							{
								echo "
									<table class='standard' style='margin: 0px auto; width: calc(100% - 10px);'>
										<thead>
											<th style='width: 25%;'>Trade ID</th>
											<th style='width: 25%;'>Sender</th>
											<th style='width: 25%;'>Recipient</th>
											<th style='width: 25%;'>Status</th>
										</thead>
										<tbody>
								";
								foreach( $Pending_Trades as $Key => $Value )
								{
									$Sender = $UserClass->FetchUserData($Value['Sender']);
									$Recipient = $UserClass->FetchUserData($Value['Receiver']);
	
									echo "
										<tr>
											<td><a href='javascript:void(0);' onclick='TradeView({$Value['ID']});'>#" . number_format($Value['ID']) . "</a></td>
											<td><a href='" . Domain(1) . "/profile.php?id={$Sender['ID']}'>{$Sender['Username']}</a></td>
											<td><a href='" . Domain(1) . "/profile.php?id={$Recipient['ID']}'>{$Recipient['Username']}</a></td>
											<td style='color: #888;'>{$Value['Status']}</td>
										</tr>
									";
								}
								echo "
										</tbody>
									</table>
								";
							}
						?>
					</div>
				</div>

			<?php
				}
				else
				{
			?>
				
				<div class='description' style='margin-bottom: 5px;'>Preparing your trade!</div>
				<div style='padding: 5px;'>
					<div class='spinner' style='left: 48%; position: relative;'></div><br />
				</div>

			<?php
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
	 * Swap between each tab's contents.
	 */
	function Tab(Path)
	{
		$.get('core/ajax/trading/tabs/' + Path + '.php', function(data)
		{
			$('#TradeAJAX').html(data);
		});
	}

	function TradeView(Trade_ID)
	{
		$.ajax({
			type: 'POST',
			url: '<?= Domain(1); ?>/core/ajax/trading/view.php',
			data: { Trade_ID: Trade_ID },
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
	function TradeCreate()
	{
		$.ajax({
			type: 'POST',
			url: '<?= Domain(1); ?>/core/ajax/trading/create.php',
			data: { },
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
	 * Add or Remove Pokemon, Items, or Currency from the trade.
	 */
	function Action(User_ID, Action, Type, Data = null)
	{
		if ( Type == 'Currency' )
		{
			let Input_ID = Data;
			Data = {
				"Name": $('#TabContent' + User_ID + ' #' + Data).attr('ID'),
				"Amount": $('#TabContent' + User_ID + ' #' + Data).val(),
			};
			$('#TabContent' + User_ID + ' #' + Input_ID).val('');
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

	//CurrentSearch = [0,0,0];
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
        Page: Page
        //filter_type: CurrentSearch[0],
        //filter_search: $('[name=pokemon_search]').val(),
        //filter_select: $('[name=pokemon_select]').val(),
        //filter_gender: CurrentSearch[1],
        //filter_search_order: CurrentSearch[2],
        //filter_order: CurrentSearch[2],
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