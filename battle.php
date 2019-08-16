<?php
	require 'core/required/layout_top.php';
	require 'battles/battle.php';

	if ( !isset($_SESSION['Battle']) )
	{
		$Error = "
			<div class='error'>
				A battle has not yet been created.
			</div>
		";
	}
	else
	{
		$Fight = $_SESSION['Battle']['Status']['Battle_Fight'];
		$Battle = new $Fight();
	}
?>

<div class='content'>
	<div class='head'>Battle</div>
	<div class='box' id='BattleWindow'>
		<?= ( isset($Error) ? $Error : '' ); ?>

		<div style='margin: 0 auto; width: 75%;'>
			<div>
				<!-- Your Pokemon -->
				<div style='float: left; width: 45%;'>
					<!-- roster -->
					<div class='roster'>
						<div class="slot" id='A_1' style='display: none;'></div>
						<div class="slot" id='A_2' style='display: none;'></div>
						<div class="slot" id='A_3' style='display: none;'></div>
						<div class="slot" id='A_4' style='display: none;'></div>
						<div class="slot" id='A_5' style='display: none;'></div>
						<div class="slot" id='A_6' style='display: none;'></div>
					</div>
					<!-- roster -->

					<!-- active -->
					<div class='active'>
						<div class='sprite'>
							<img id='A_A' />
						</div>
						<div class='name'>
							<div>
								<div id='A_A_Name'></div>
								<div style='font-size: 12px; text-align: left;'>
									HP: (<span id='A_A_HP_Cur'></span>/<span id='A_A_HP_Max'></span>)
								</div>
								<div class='hp_bar'>
									<span id='A_A_HP'></span>
								</div>
								<div style='font-size: 12px; text-align: left;'>
									Level: <span id='A_A_Level'></span>
								</div>
								<div class='exp_bar'>
									<span id='A_A_EXP'></span>
								</div>
							</div>
						</div>
					</div>
					<!-- active -->
				</div>
				<!-- Your Pokemon -->

				<!-- Battle Options/Bag -->
				<div class='battle_options'>
					<div style='padding-top: 6px;'>
						<img src='<?= Domain(1); ?>/images/Assets/options.png' style='height: 50px; width: 50px;' />
					</div>
				</div>
				<!-- Battle Options/Bag -->

				<!-- Enemy Pokemon -->
				<div style='float: left; width: 45%;'>
					<!-- roster -->
					<div class='roster'>
						<div class="slot" id='D_1' style='display: none;'></div>
						<div class="slot" id='D_2' style='display: none;'></div>
						<div class="slot" id='D_3' style='display: none;'></div>
						<div class="slot" id='D_4' style='display: none;'></div>
						<div class="slot" id='D_5' style='display: none;'></div>
						<div class="slot" id='D_6' style='display: none;'></div>
					</div>
					<!-- roster -->

					<!-- active -->
					<div class='active'>
						<div class='sprite'>
							<img id='D_A' />
						</div>
						<div class='name'>
							<div>
								<div id='D_A_Name'></div>
								<div style='font-size: 12px; text-align: left;'>
									HP: (<span id='D_A_HP_Cur'></span>/<span id='D_A_HP_Max'></span>)
								</div>
								<div class='hp_bar'>
									<span id='D_A_HP'></span>
								</div>
								<div style='font-size: 12px; text-align: left;'>
									Level: <span id='D_A_Level'></span>
								</div>
								<div class='exp_bar'>
									<span id='D_A_EXP'></span>
								</div>
							</div>
						</div>
					</div>
				</div>
				<!-- Enemy Pokemon -->
			</div>

			<br /><br />
			<br /><br />
			<br /><br />

			<!-- Moves -->
			<div class='battle_moves'>
				<input id='Move_1' style='padding: 5px; width: 150px;' type='button' />
				<input id='Move_2' style='padding: 5px; width: 150px;' type='button' />
				<input id='Move_3' style='padding: 5px; width: 150px;' type='button' />
				<input id='Move_4' style='padding: 5px; width: 150px;' type='button' />
			</div>
			<!-- Moves -->

			<div id='BattleStatus'></div>
		</div>
	</div>
</div>

<script type='text/javascript'>
	$(function()
	{
		Battle.Loading();

		$.ajax({
			type: 'POST',
			url: 'battles/handler.php',
			data: { bid: '<?= $_SESSION['Battle']['Status']['Battle_ID'] ?>' },
			success: function(JSON)
			{
				Battle.Render(JSON);
			},
			error: function(JSON)
			{
				Battle.Render(JSON);
			}
		});
	});

	/**
	 * Battle Functions
	 */
	Battle = {};
	Battle.Clicks = 0;

	$(document).on('click', function()
	{
		Battle.Clicks++;
	});

	Battle.Loading = function()
	{
		$('#BattleStatus').html("<div style='padding: 10px;'><div class='spinner' style='left: 48.5%; position: relative;'></div></div>");
	}

	Battle.Render = function(JSON)
	{
		console.log(JSON);
		var Pokemon;
		var Prefix;

		/**
		 * Render both user's rosters.
		 */
		for ( let i = 0; i < 2; i++ )
		{
			switch ( i )
			{
				case 0:
					Pokemon = JSON.Attacker.Active;
					Prefix = "A_";
					break;
				case 1:
					Pokemon = JSON.Defender.Active;
					Prefix = "D_";
					break;
			}

			if ( Pokemon != undefined )
			{
				$('#' + Prefix + 'A').attr( 'src', Pokemon['Sprite'] );
				$('#' + Prefix + 'A_Name').html( `<b>${Pokemon['Name']}</b>` );
				$('#' + Prefix + 'A_Level').html( Pokemon['Level'] );
				$('#' + Prefix + 'A_HP_Cur').html( Pokemon['HP']['Current'] );
				$('#' + Prefix + 'A_HP_Max').html( Pokemon['HP']['Max'] );

				$('#' + Prefix + 'A_HP').attr('title', Pokemon['HP']['Current'] + '/' + Pokemon['HP']['Max']).animate({ 'width': ((Pokemon['HP']['Current'] * 124) / Pokemon['HP']['Max']) + 'px' }, 200);
				$('#' + Prefix + 'A_EXP').attr('title', Pokemon['Exp']['Current'] + '/' + Pokemon['Exp']['Needed']).animate({ 'width': (Pokemon['Exp']['Current'] / Pokemon['Exp']['Needed']) * 124 + 'px' }, 200);
			}

			for ( let o = 0; o <= 5; o++ )
			{
				if ( Prefix == 'A_' )
				{
					var Roster_Pokemon = JSON.Attacker[o];
				}

				if ( Prefix == 'D_' )
				{
					var Roster_Pokemon = JSON.Defender[o];
				}

				if ( Roster_Pokemon != undefined )
				{
					$('#' + Prefix + ( o + 1 )).html( `<div><img src='${Roster_Pokemon['Icon']}' /></div>` ).css({ 'display':'block' });
				}
			}
		}

		/**
		 * Render the user's moves.
		 */
		$('#Move_1').attr('value', JSON.Attacker.Active.Moves.Move_1.Move_Name).attr('Postcode', JSON.Attacker.Active.Moves.Move_1.Postcode);
		$('#Move_2').attr('value', JSON.Attacker.Active.Moves.Move_2.Move_Name).attr('Postcode', JSON.Attacker.Active.Moves.Move_2.Postcode);
		$('#Move_3').attr('value', JSON.Attacker.Active.Moves.Move_3.Move_Name).attr('Postcode', JSON.Attacker.Active.Moves.Move_3.Postcode);
		$('#Move_4').attr('value', JSON.Attacker.Active.Moves.Move_4.Move_Name).attr('Postcode', JSON.Attacker.Active.Moves.Move_4.Postcode);

		//Battle.Render_Inputs();

		/**
		 * Render the battle dialogue.
		 */
		$('#BattleStatus').html(JSON.Battle.Text);
	}

	$('.battle_moves input').on('pointerdown', function(e)
	{
		Battle.Move( $(this).attr('Postcode'), e );
		return false;
	});

	/**
	 * Render the user's moves.
	 */
	Battle.Render_Inputs = function(JSON)
	{
		
	}

	/**
	 * Handle attacking.
	 */
	Battle.Move = function(move, e)
	{
		let Element = {
			'PostCode': $(e.target).attr('postcode'),
			'Position': $(e.target).position(),
			'Width'		: parseInt( $(e.target).css('width') ),
			'Height'	: parseInt( $(e.target).css('height' ) ),
		};

		$.ajax({
			type: 'POST',
			url: 'battles/handler.php',
			data: {
				Battle_ID: '<?= $_SESSION['Battle']['Status']['Battle_ID'] ?>',
				Element: Element,
				Clicks: Battle.Clicks,
				Move: move,
				x: e.pageX,
				y: e.pageY,
			},
			success: function(JSON)
			{
				Battle.Render(JSON);
				Battle.Clicks = 0;
			},
			error: function(JSON)
			{
				Battle.Render(JSON);
				Battle.Clicks = 0;
			}
		});
	}
</script>

<?php
	require 'core/required/layout_bottom.php';