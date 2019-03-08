<?php
	require 'core/required/layout_top.php';
	require 'core/classes/battle.php';

	$Battle_Version = 1;
?>

<div class='content'>
	<div class='head'>Battle</div>
	<div class='box' id='BattleWindow'>
		<?php
			if ( !isset($_SESSION['Battle']) )
			{
				unset($_SESSION['Battle']);
				
				echo "
					<div class='error'>
						A battle has not yet been created.
					</div>
				";
			}
			else
			{
				$Battle = new Battle();
		
				$Roster_Check = $Battle->VerifyRoster( $User_Data['Roster'] );
		
				if ( $Roster_Check )
				{
					unset($_SESSION['Battle']);
		
					echo "
						<div class='error'>
							Your roster has changed. Please restart the battle.
						</div>
					";
				}
			}
		?>
	</div>
</div>

<script type='text/javascript'>
	/**
	 * Preload the battle.
	 */
	$(function()
	{
		$('#BattleWindow').html("<div style='padding: 10px;'><div class='spinner' style='left: 48.5%; position: relative;'></div></div>");

		$.ajax({
			type: 'POST',
			url: 'core/ajax/battle/handler.php?v=<?= $Battle_Version; ?>',
			data: { bid: '<?= $_SESSION['Battle']['Battle_ID'] ?>' },
			success: function(data)
			{
				$('#BattleWindow').html(data);
			},
			error: function(data)
			{
				$('#BattleWindow').html(data);
			}
		});
	});
</script>

<?php
	require 'core/required/layout_bottom.php';