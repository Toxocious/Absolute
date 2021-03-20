<?php
	require_once '../core/required/session.php';
	require_once '../core/functions/staff.php';

	try
	{
		$Query_Itemdex = $PDO->prepare("SELECT * FROM `item_dex` ORDER BY `Item_ID` ASC");
		$Query_Itemdex->execute();
		$Query_Itemdex->setFetchMode(PDO::FETCH_ASSOC);
		$Itemdex = $Query_Itemdex->fetchAll();
	}
	catch ( PDOException $e )
	{
		HandleError( $e->getMessage() );
	}
?>

<div class='head'>Edit Items</div>
<div class='body'>
	<div class='description' style='margin-bottom: 5px;'>
		Click on an item to edit it's database properties.
	</div>

	<div class='row'>
		<div class='panel' style='float: left; margin-right: 5px; width: calc(100% / 2 - 2.5px);'>
			<div class='head'>Item List</div>
			<div class='body' style='height: 563px; overflow: auto; padding-top: 3px;'>
				<?php
					foreach ( $Itemdex as $Key => $Value )
					{
						echo "
							<img class='iconSelect' src='" . DOMAIN_SPRITES . "/images/Items/{$Value['Item_Name']}.png' onclick='FetchItem({$Value['Item_ID']});' />
						";
					}
				?>
			</div>
		</div>

		<div class='panel' style='float: left; width: calc(100% / 2 - 2.5px);'>
			<div class='head'>Selected Item</div>
			<div class='body' id='AJAX' style='padding: 5px;'>
					Please select an item from the Itemdex.
			</div>
		</div>
	</div>
	
</div>

<script type='text/javascript'>
	function FetchItem(ID)
	{
		$.ajax({
			type: 'get',
			url: 'ajax/edit_item.php',
			data: { Item: ID },
			success: function(data)
			{
				$('#AJAX').html(data);
			},
			error: function(data)
			{
				$('#AJAX').html(data);
			}
		});
	}
</script>