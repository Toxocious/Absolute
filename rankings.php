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
	<div class='body'>

		<!--
		<div class='panel' style='margin-bottom: 5px;'>
			<div class='head'>Categories</div>
			<div class='body'>
				<div>Pokemon</div>
				<div>Trainer</div>
			</div>
		</div>
		-->

		<div id='RankingAJAX'>
			
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

	</div>
</div>

<?php
	require 'core/required/layout_bottom.php';