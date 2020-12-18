<?php
	require 'core/required/layout_top.php';
?>

<div class='panel content'>
	<div class='head'>Global Rankings</div>
	<div class='body' style='padding: 5px;'>
		<div class='flex' style='flex-direction: row; flex-wrap: wrap; justify-content: center;'>
			<table class='border-gradient' style='width: 570px;'>
				<tbody>
					<tr>
						<td style='padding: 5px;'>
							<a href='javascript:void(0);' onclick="Display_Tab('Pokemon');" style='font-size: 14px;'>
								<b>Pok&eacute;mon</b>
							</a>
						</td>
						<td style='padding: 5px;'>
							<a href='javascript:void(0);' onclick="Display_Tab('Trainer');" style='font-size: 14px;'>
								<b>Trainers</b>
							</a>
						</td>
					</tr>
				</tbody>
			</table>

			<div id='Rankings_AJAX' style='flex-basis: 100%;'>
				Loading..
			</div>
		</div>
	</div>
</div>

<script type='text/javascript'>
	let Current_Tab = 'Pokemon';

	function Display_Tab(Tab = Current_Tab)
	{
		$.ajax({
			type: 'POST',
			url: '<?= DOMAIN_ROOT; ?>/core/ajax/rankings/tab.php',
			data: { Tab: Tab },
			success: (data) =>
			{
				$('#Rankings_AJAX').html(data);
			},
			error: (data) =>
			{
				$('#Rankings_AJAX').html(data);
			},
		});
	}

	$(function()
	{
		Display_Tab(Current_Tab);
	});
</script>

<?php
	require 'core/required/layout_bottom.php';
