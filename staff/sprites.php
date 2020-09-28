<?php
	require '../core/required/session.php';
	require '../core/functions/staff.php';

	/**
	 * Load sprites if a request has been made.
	 */
	if ( isset($_GET['Gen']) )
	{
		$Gen = $Purify->Cleanse($_GET['Gen']);

		switch ( $Gen )
		{
			case 1:
				$Range = [0, 151];
				break;
			case 2:
				$Range = [152, 251];
				break;
			case 3:
				$Range = [252, 386];
				break;
			case 4:
				$Range = [387, 493];
				break;
			case 5:
				$Range = [494, 649];
				break;
			case 6:
				$Range = [650, 721];
				break;
			case 7:
				$Range = [722, 999];
				break;
			default:
				$Range = [0, 151];
				break;
		}

		try
		{
			$Fetch_Pokedex = $PDO->prepare("SELECT * FROM `pokedex` WHERE `Pokedex_ID` >= ? AND `Pokedex_ID` <= ? ORDER BY `Pokedex_ID` ASC, `Alt_ID` ASC");
			$Fetch_Pokedex->execute([ $Range[0], $Range[1] ]);
			$Fetch_Pokedex->setFetchMode(PDO::FETCH_ASSOC);
			$Pokedex = $Fetch_Pokedex->fetchAll();
		}
		catch ( PDOException $e )
		{
			HandleError( $e->getMessage() );
		}

		echo "
			<div class='panel' style='margin-top: 5px;'>
				<div class='head'>Gen {$Gen} Pokemon Sprites</div>
				<div class='body' style='text-align: left;'>
					<table style='margin: 0 auto; width: 90%;'>
						<tr>
							<td style='font-weight: bold; text-align: center; width: 25%;'>Name And Icons</td>
							<td style='font-weight: bold; text-align: center; width: 25%;'>Normal Sprite</td>
							<td style='font-weight: bold; text-align: center; width: 25%;'>Shiny Sprite</td>
							<td style='font-weight: bold; text-align: center; width: 25%;'>Sunset Sprite</td>
						</tr>
		";
		foreach ( $Pokedex as $Index => $Poke_Data )
		{
			$Pokemon = $Poke_Class->FetchPokedexData($Poke_Data['Pokedex_ID'], $Poke_Data['Alt_ID']);

			echo "
				<tr>
					<td>
						<b>
						{$Pokemon['Name']}
						(" . str_pad($Pokemon['Pokedex_ID'], 3, "0", STR_PAD_LEFT) . (($Pokemon['Alt_ID'] == 0) ? '' : '.' . $Pokemon['Alt_ID']) . "" . ".png)
						</b>
						<br />

						<img src='" . Domain(1) . "/images/Pokemon/Icons/Normal/" . str_pad($Pokemon['Pokedex_ID'], 3, "0", STR_PAD_LEFT) . ".png' />
						<img src='" . Domain(1) . "/images/Pokemon/Icons/Shiny/" . str_pad($Pokemon['Pokedex_ID'], 3, "0", STR_PAD_LEFT) . ".png' />
						<img src='" . Domain(1) . "/images/Pokemon/Icons/Sunset/" . $Pokemon['Pokedex_ID'] . ".png' />
					</td>
					
					<td style='text-align: center;'>
						<img src='" . Domain(1) . "/images/Pokemon/Sprites/Normal/" . str_pad($Pokemon['Pokedex_ID'], 3, "0", STR_PAD_LEFT) . (($Pokemon['Alt_ID'] == 0) ? '' : '.' . $Pokemon['Alt_ID']) . ".png' />
					</td>

					<td style='text-align: center;'>
						<img src='" . Domain(1) . "/images/Pokemon/Sprites/Shiny/" . str_pad($Pokemon['Pokedex_ID'], 3, "0", STR_PAD_LEFT) . (($Pokemon['Alt_ID'] == 0) ? '' : '.' . $Pokemon['Alt_ID']) . ".png' />
					</td>

					<td style='text-align: center;'>
						No Sprite
					</td>
				</tr>
			";
		}
		echo "
					</table>
				</div>
			</div>
		";

		exit;
	}
?>

<div class='head'>Sprites</div>
<div class='body'>

	<div class='panel'>
		<div class='head'>Sprites By Generation</div>
		<div class='body navi'>
			<div>
				<div style='float: left; padding: 2px; width: calc(100% / 7);'>
					<a href='javascript:void(0);' onclick='ShowSprites("1");' style='display: block;'>
						Gen I
					</a>
				</div>
				<div style='float: left; padding: 2px; width: calc(100% / 7);'>
					<a href='javascript:void(0);' onclick='ShowSprites("2");' style='display: block;'>
						Gen II
					</a>
				</div>
				<div style='float: left; padding: 2px; width: calc(100% / 7);'>
					<a href='javascript:void(0);' onclick='ShowSprites("3");' style='display: block;'>
						Gen III
					</a>
				</div>
				<div style='float: left; padding: 2px; width: calc(100% / 7);'>
					<a href='javascript:void(0);' onclick='ShowSprites("4");' style='display: block;'>
						Gen IV
					</a>
				</div>
				<div style='float: left; padding: 2px; width: calc(100% / 7);'>
					<a href='javascript:void(0);' onclick='ShowSprites("5");' style='display: block;'>
						Gen V
					</a>
				</div>
				<div style='float: left; padding: 2px; width: calc(100% / 7);'>
					<a href='javascript:void(0);' onclick='ShowSprites("6");' style='display: block;'>
						Gen VI
					</a>
				</div>
				<div style='float: left; padding: 2px; width: calc(100% / 7);'>
					<a href='javascript:void(0);' onclick='ShowSprites("7");' style='display: block;'>
						Gen VII
					</a>
				</div>
			</div>
		</div>
	</div>

	<div id='AJAX'></div>

</div>

<script type='text/javascript'>
	function ShowSprites(Gen)
	{
		$.ajax({
			type: 'get',
			url: 'sprites.php',
			data: { Gen: Gen },
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