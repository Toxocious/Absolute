<?php
	require_once 'core/required/layout_top.php';

	try
	{
		$Clan_Query = $PDO->prepare("SELECT * FROM `clans` ORDER BY `Experience` DESC");
		$Clan_Query->execute([ ]);
		$Clan_Query->setFetchMode(PDO::FETCH_ASSOC);
		$Clan = $Clan_Query->fetchAll();
	}
	catch ( PDOException $e )
	{
		HandleError( $e->getMessage() );
	}
?>

<div class='panel content'>
	<div class='head'>Clan Listings</div>
	<div class='body' style='padding: 5px;'>
		<div class='description'>
      Every clan that has been created can be found below.
      <br />
			Can your clan make it to the top?
		</div>

		<table class='border-gradient' style='width: 700px;'>
			<thead>
				<tr>
					<th style='width: 15%;'>Rank</th>
					<th style='width: 45%;'>Clan Name</th>
					<th style='width: 20%;'>Level</th>
					<th style='width: 20%;'>Money</th>
				</tr>
			</thead>
			<tbody>
				<?php
					foreach( $Clan as $Key => $Value )
					{
						$Key++;
						
						echo "
							<tr>
								<td>#" . number_format($Key) . "</td>
								<td>
									<a href='" . DOMAIN_ROOT . "/clan.php?clan_id={$Key}'>
										{$Value['Name']}
									</a>
								</td>
								<td>" . number_format(FetchLevel($Value['Experience'], 'Clan')) . "</td>
								<td>$" . number_format($Value['Money']) . "</td>
							</tr>
						";
					}
				?>
			</tbody>
		</table>
	</div>
</div>

<?php
  require_once 'core/required/layout_bottom.php';
  