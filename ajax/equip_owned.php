<?php
	# background: #003300;  
	# give checked items that background via their data-id

	require '../session.php';

	echo	"<script type='text/javascript' src='../js/libraries.js'></script>";
	echo	"<link href='../css/required.css' rel='stylesheet'>";
	echo 	"<style>";
	echo		"html, body { background: #111; color: #fff; }";
	echo		"button { background: #1d212b; border: none; height: 66px; outline: none; width: calc(100% / 3); }";
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
	$owned_rings = mysqli_query($con, "SELECT * FROM equips WHERE Owner_ID = '" . $User_Data['id'] . "'");
	while ( $ring_data = mysqli_fetch_assoc($owned_rings) )
	{
		echo "<div class='halign' data-id='{$ring_data['Auto']}'>";
		echo   "<input type='checkbox' onclick=\"$('[data-id=\"{$ring_data['Auto']}\"]').click();\">";
		echo   "<div class='valign'>";
		echo     "<img src='../images/Equipment/Rings/0{$ring_data['ID']}.png' onclick='equipStats({$ring_data['ID']});' id='{$ring_data['ID']}' />";
		echo   "</div>";
		echo "</div>";
	}
	echo	"</div>";

	echo	"<div class='options'>";
	echo		"<button onclick='selectAllEquips();'>Select All Equips</button>";
	echo		"<button onclick='disposeEquip();'>Dispose of Selected Equips</button>";
	echo		"<button onclick='disposeAll();'>Dispose of All Equips</button>";
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
			
			function selectAllEquips()
			{
				$('[data-id]').each(function()
				{
					$(this).click();
				});
			}
			
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
			
			function disposeEquip()
			{
				$.ajax({
					type: 'post',
					url: 'equip_dispose.php',
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
			
			function disposeAll()
			{
				console.log('disposing all equips');
			}
		</script>
	";