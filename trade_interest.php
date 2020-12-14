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

		<table class='border-gradient' style='width: 600px;'>
			<thead>	
				<tr>
					<th colspan='6'>Pok&eacute;mon Type</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td colspan='3' style='width: 50%;'>
						<a href='javascript:void(0);' onclick='Filter("Normal");'>
							<b style='font-size: 14px;'>Normal</b>
						</a>
					</td>
					<td colspan='3' style='width: 50%;'>
						<a href='javascript:void(0);' onclick='Filter("Shiny");'>
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
	function Filter(Type)
	{
		$.ajax({
			type: 'POST',
			url: '<?= DOMAIN_ROOT; ?>/core/ajax/trading/interest.php',
			data: { Type: Type },
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
		let Type = $("#PokeList .panel-heading").text().split(' ')[1];

		$.ajax({
			type: 'POST',
			url: '<?= DOMAIN_ROOT; ?>/core/ajax/trading/interest.php',
			data: { Update: [ ele.name, ele.value ], Type: Type },
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