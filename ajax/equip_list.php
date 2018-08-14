<?php
	require '../db.php';

	echo	"<script type='text/javascript' src='../js/libraries.js'></script>";
	echo	"<link href='../css/required.css' rel='stylesheet'>";
	echo 	"<style>";
	echo		"*::-webkit-scrollbar {width:3px!important;height:10px!important;background:var(--bg-color-first, #2a2a2a)!important;border:1px solid var(--border-color-left, #353535)!important; z-index:1000000000;}
				*::-webkit-scrollbar-thumb {min-height:28px !important;background:var(--bg-color-background, #373737)!important}
				*::-webkit-input-placeholder {color:var(--text-color-second, #979797)}
				::-webkit-scrollbar-track, ::-webkit-scrollbar-corner {background:var(--bg-color-first, #2a2a2a)!important}
				::-webkit-scrollbar-thumb:hover {background:var(--bg-color-hover, #3d3a3a)!important}";
	echo		"html, body { background: #111; color: #fff; }";
	echo		"button { background: #1d212b; border: none; height: 66px; outline: none; width: calc(100% / 1); }";
	echo		"button:hover { background: #2c3a55; }";
	echo		"button:not(:last-child) { border-right: 2px solid #4A618F; }";
	echo		".list { width: 100%; height: 425px; overflow: auto; border-bottom: 2px solid #4A618F; padding: 5px; }";
	echo		".list > div { background: #1d2639; border: 1px solid #4A618F; border-radius: 4px; float: left; height: 40px; line-height: 120px; margin-bottom: 4px; margin-left: 4.4px; position: relative; width: 40px; }";
	echo		".list > div.halign { display: flex; justify-content: center; }";
	echo		".list > div.halign > input {  position: absolute; margin-left: 12px; margin-top: 25px; }";
	echo		".list > div.halign > div.valign { display: flex; justify-content: center; flex-direction: column; }";
	echo		".list > div.halign > div.valign > img { display: block; }";
	echo		".list > div.halign > div.valign > img:hover { cursor: pointer; }";
	echo		".options { width: 100%; height: 50px; text-align: center; }";
	echo	"</style>";

	echo	"<div class='list'>";
	$all_rings = mysqli_query($con, "SELECT * FROM equip_rings");
	while ( $ring_data = mysqli_fetch_assoc($all_rings) )
	{
		echo "<div class='halign' data-id='{$ring_data['Auto']}'>";
		echo   "<input type='checkbox' onclick=\"$('[data-id=\"{$ring_data['Auto']}\"]').click();\">";
		echo   "<div class='valign'>";
		echo     "<img src='../images/Equipment/Rings/0{$ring_data['ID']}.png' id='{$ring_data['ID']}' />";
		echo   "</div>";
		echo "</div>";
	}
	echo	"</div>";

	echo	"<div class='options'>";
	echo		"<button onclick='spawnEquip();'>Spawn Selected Item(s)</button>";
	echo	"</div>";

	echo	"
		<script type='text/javascript'>
			$('[data-id]').click(function() {
                if ( $(this).hasClass('checked') )
				{
					$(this).removeClass('checked');
                    $(this).css('background','#1d2639');
					$('input', this).prop('checked', false);
                }
				else
				{
					$(this).addClass('checked');
					$(this).css('background','#003300');
					$('input', this).prop('checked', true);
				}
            });
			
			var get_equip = '';
			$('img').click(function()
			{
				get_equip = $(this).attr('id');
				console.log('get_equip = ' + get_equip);
			});
			
			function equipStats(item_id)
			{
				$.ajax({
					type: 'post',
					url: 'fetch_equip.php',
					data: { equip_id: item_id },
					success: function(data)
					{
						$('.stats').html(data);
					},
					error: function(data)
					{
						$('.stats').html(data);
					}
				});
			}
			
			function spawnEquip()
			{
				$.ajax({
					type: 'post',
					url: 'spawn_equip.php',
					data: { equip_id: get_equip },
					success: function(data)
					{
						$('.stats').html(data);
					},
					error: function(data)
					{
						$('.stats').html(data);
					}
				});
			}
		</script>
	";