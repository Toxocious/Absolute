<?php
	require 'core/required/layout_top.php';
?>

<div class='panel content'>
	<div class='head'>Trade Interest</div>
	<div class='body'>
		<div id='AJAX'></div>

		<div class='panel' style='margin-bottom: 5px;'>
			<div class='head'>Filter</div>
			<div class='body navi'>
				<div>
					<div><a href='javascript:void(0);' onclick='Filter("Normal")' style='display: block; float: left; padding: 2px; width: calc(100% / 2);'>Normal</a></div>
					<div><a href='javascript:void(0);' onclick='Filter("Shiny")' style='display: block; float: left; padding: 2px; width: calc(100% / 2);'>Shiny</a></div>
				</div>
			</div>
		</div>

		<div id='PokeList'>
			<div class='notice'>Please select an option from the filter list.</div>
		</div>
	</div>
</div>

<script type='text/javascript'>
	function Filter(Type)
	{
		$.ajax({
			type: 'POST',
			url: '<?= Domain(1); ?>/core/ajax/trading/interest.php',
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
			url: '<?= Domain(1); ?>/core/ajax/trading/interest.php',
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