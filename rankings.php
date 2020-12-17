<?php
	require 'core/required/layout_top.php';

	try
	{
		$Query_Rankings = $PDO->prepare("SELECT `ID` FROM `pokemon` ORDER BY `Experience` DESC LIMIT 20");
		$Query_Rankings->execute();
		$Query_Rankings->setFetchMode(PDO::FETCH_ASSOC);
		$Rankings = $Query_Rankings->fetchAll();

		$Query_Top = $PDO->prepare("SELECT `ID` FROM `pokemon` ORDER BY `Experience` DESC LIMIT 1");
		$Query_Top->execute();
		$Query_Top->setFetchMode(PDO::FETCH_ASSOC);
		$Top = $Query_Top->fetch();
		$Top = $Poke_Class->FetchPokemonData($Top['ID']);
	}
	catch( PDOException $e )
	{
		HandleError( $e->getMessage() );
	}
?>

<div class='panel content'>
	<div class='head'>Global Rankings</div>
	<div class='body' style='padding: 5px;'>
		<div class='flex' style='flex-direction: row; flex-wrap: wrap; justify-content: center;'>
			<table class='border-gradient' style='width: 65%;'>
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
				<table class='border-gradient' style='margin: 5px auto; flex-basis: 35%; width: 35%;'>
					<thead>
						<th colspan='3'>
							<b><?= $Top['Display_Name']; ?></b>
						</th>
					</thead>
					<tbody>
						<tr>
							<td colspan='1' rowspan='2' style='width: 100px;'>
								<img src='<?= $Top['Sprite']; ?>' />
							</td>
							<td colspan='2'>
								<b><?= $Top['Display_Name'] ?></b>
								<?= ($Top['Nickname'] ? "<br />( <i>{$Top['Nickname']}</i> )" : '') ?>
							</td>
						</tr>
						<tr>
							<td colspan='2'>
								<b>Level</b>: <?= $Top['Level'] ?>
								<br />
								<b>Experience</b>: <?= $Top['Experience']; ?>
							</td>
						</tr>
						<tr>
							<td colspan='3' style='padding: 5px;'>
								<b>Current Owner</b>
								<?= $User_Class->DisplayUserName($Top['Owner_Current'], false, true, true); ?>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>

		<!--
		<div id='RankingAJAX32'>
			<div class='panel' style='margin: 0px auto 5px; width: 50%;'>
				<div class='head'>Top Pokemon</div>
				<div class='body'>
					<div style='float: left; width: 50%;'>
						<img src='<?= $Top['Sprite']; ?>' /><br />
						<b><?= $Top['Display_Name']; ?></b>
					</div>
					<div style='float: left; width: 50%;'>
						<b>Owner</b><br />
						<a href='profile.php?id=<?= $Top['Owner_Current']; ?>'>
							<?= $User_Class->DisplayUsername($Top['Owner_Current']); ?>
						</a>
						<br /><br />

						<b>Level / Exp</b><br />
						<?= $Top['Level']; ?><br />
						(<i style='font-size: 12px;'><?= number_format($Top['Experience']); ?></i>)
					</div>
				</div>
			</div>

			<table class='standard' style='margin: 0 auto; width: 70%;'>
				<thead>
					<th>Pokemon</th>
					<th>Level/Exp</th>
					<th>Owner</th>
				</thead>
				<tbody>
					<?php
						foreach ( $Rankings as $Key => $Value )
						{
							$Poke_Data = $Poke_Class->FetchPokemonData($Value['ID']);

							echo "
								<tr>
									<td>
										<img src='{$Poke_Data['Icon']}' /><br />
										{$Poke_Data['Display_Name']}
									</td>
									<td>" .
										$Poke_Data['Level'] . "<br />" .
										"(<i style='font-size: 12px;'>" . number_format($Poke_Data['Experience']) . " Exp</i>)
									</td>
									<td>
										<a href='profile.php?id={$Poke_Data['Owner_Current']}'>
											" . $User_Class->DisplayUsername($Poke_Data['Owner_Current']) . "
										</a>
									</td>
								</tr>
							";
						}
					?>
				</tbody>
			</table>

		</div>
		-->
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
