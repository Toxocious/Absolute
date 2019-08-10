<?php
	require '../core/required/layout_top.php';
	require '../core/functions/staff.php';
?>

<div class='content' id='StaffAJAX'>
	<div class='head'>Staff Panel</div>
	<div class='box'>
		<div style='padding: 5px'>
			Welcome to Absolute's Staff Panel.
		</div>
	</div>
</div>

<script type='text/javascript'>
	function LoadContent(URL, DIV, Data)
	{
		if ( DIV == '' || DIV == undefined )
		{
			DIV = 'StaffAJAX';
		}

		if ( Data == '' || DIV == undefined )
		{
			Data = {};
		}

		$('#' + DIV).html("<div class='description' style='margin: 5px; width: calc(100% - 10px);'><div class='spinner' style='left: 47.5%; position: relative;'></div></div>");

		$.ajax({
			type: 'POST',
			url: URL,
			data: Data,
			success: function(data)
			{
				$('#' + DIV).html(data);
			},
			error: function(data)
			{
				$('#' + DIV).html(data);
			}
		});
	}
</script>

<?php
	require '../core/required/layout_bottom.php';
?>