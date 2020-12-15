<?php
	require 'core/required/layout_top.php';
?>

<div class='panel content'>
	<div class='head'>Trade Interest</div>
	<div class='body padding-5px'>
		<div class='description'>
			Below, you can set how interested you are in trading each of your Pok&eacute;mon.
			<br />
			Pok&eacute;mon that you set to 'No', will not show up in trades.
		</div>

		<table class='border-gradient' style='width: 700px;'>
			<thead>	
				<tr>
					<th colspan='14'>Pok&eacute;mon Type</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td colspan='7' style='min-width: 350px;'>
						<a href='javascript:void(0);' onclick='Update_Box(1, <?= $User_Data["id"]; ?>, "Normal");'>
							<b style='font-size: 14px;'>Normal</b>
						</a>
					</td>
					<td colspan='7' style='min-width: 350px;'>
						<a href='javascript:void(0);' onclick='Update_Box(1, <?= $User_Data["id"]; ?>, "Shiny");'>
							<b style='font-size: 14px;'>Shiny</b>
						</a>
					</td>
				</tr>
			</tbody>
			<tbody id='PokeList'>
				
			</tbody>
		</table>
	</div>
</div>

<script type='text/javascript'>
	let Current_Type;

	function Update_Box(Page, User_ID, Type = (Current_Type ? Current_Type : 'Normal'))
	{
		Current_Type = Type;

		$.ajax({
			type: 'POST',
			url: '<?= DOMAIN_ROOT; ?>/core/ajax/trading/interest.php',
			data: { Page: Page, User_ID: User_ID, Type: Type },
			success: function(data)
			{
				$('#PokeList').html(data);
			},
			error: function(data)
			{
				$('#PokeList').html(data);
			}
		});
	}

	function Update(ele)
	{
		$.ajax({
			type: 'POST',
			url: '<?= DOMAIN_ROOT; ?>/core/ajax/trading/interest.php',
			data: { Update: [ ele.name, ele.value ], Type: Current_Type },
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

<?php
	require 'core/required/layout_bottom.php';
?>