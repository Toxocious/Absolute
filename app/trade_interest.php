<?php
	require_once $_SERVER['DOCUMENT_ROOT'] . '/core/required/layout_top.php';

    // require_once $_SERVER['DOCUMENT_ROOT'] . '/pages/trade_interest/functions/____________.php';

    $Pokemon_Type = isset($_GET['Type']) ? $_GET['Type'] : 'Normal';
?>

<div class='panel content'>
	<div class='head'>Trade Interest</div>
	<div class='body padding-5px' style='display: flex; flex-direction: column; gap: 0.5em;'>
        <div style='margin: 0.5em auto;'>
			Below, you can set how interested you are in trading each of your Pok&eacute;mon.
			<br />
			Pok&eacute;mon that you set to 'No', will not show up in trades.
		</div>

        <div style='flex: 1;'>
            <div class='page-nav-container'>
                <div class='page-nav-item <?= ($Pokemon_Type == 'Normal' ? 'active' : '') ;?>' id='interest_nav_normal'>
                    <a href='javascript:void(0);' style='display: block;' onclick='GetBoxedPokemon("Normal", 1);'`>
                        Normal
                    </a>
                </div>
                <div class='page-nav-item <?= ($Pokemon_Type == 'Shiny' ? 'active' : '') ;?>' id='interest_nav_shiny'>
                    <a href='javascript:void(0);' style='display: block;' onclick='GetBoxedPokemon("Shiny", 1);'`>
                        Shiny
                    </a>
                </div>
            </div>
         </div>

		<div id='TradeInterestAJAX'></div>

        <table class='border-gradient' style='width: 700px;'>
            <thead>
                <tr>
                    <th colspan='21'><?= $Pokemon_Type; ?> Pok&eacute;mon</th>
            </thead>

            <tbody id='Box_Pagination' style='height: 30px;'>
                <tr>
                    <td colspan='21'>Loading</td>
                </tr>
            </tbody>

            <tbody id='Boxed_Pokemon'>
                <tr>
                    <td colspan='21'>
                        <div style='display: flex; align-items: center; justify-content: center;'>
                            <div class='loading-element'></div>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<script src='<?= DOMAIN_ROOT; ?>/pages/trade_interest/js/ajax_functions.js'></script>
<script src='<?= DOMAIN_ROOT; ?>/pages/trade_interest/js/trade_interest.js'></script>

<script>
    (function()
    {
        GetBoxedPokemon('<?= $Pokemon_Type; ?>');
    })();
</script>

<?php
	require_once $_SERVER['DOCUMENT_ROOT'] . '/core/required/layout_bottom.php';

    exit;
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
						<a href='javascript:void(0);' onclick="Update_Box(1, <?= $User_Data['ID']; ?>, 'Normal');">
							<b style='font-size: 14px;'>Normal</b>
						</a>
					</td>
					<td colspan='7' style='min-width: 350px;'>
						<a href='javascript:void(0);' onclick="Update_Box(1, <?= $User_Data['ID']; ?>, 'Shiny');">
							<b style='font-size: 14px;'>Shiny</b>
						</a>
					</td>
				</tr>
			</tbody>

			<tbody id='PokeList'></tbody>
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
	require_once $_SERVER['DOCUMENT_ROOT'] . '/core/required/layout_bottom.php';
