<?php
	require 'core/required/layout_top.php';

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

<div class='content'>
	<div class='head'>Clan Listings</div>
	<div class='box'>
		<div class='description' style='margin-bottom: 5px;'>
			Every clan that has been created can be found below.<br />
			Can you make it to the top?
		</div>

		<table class='standard' style='margin: 0 auto; width: 80%;'>
			<thead>
				<tr>
					<th style='width: 15%;'>Rank</th>
					<th style='width: 45%;'>Name</th>
					<th style='width: 20%;'>Level</th>
					<th style='width: 20%;'>Money</th>
				</tr>
			</thead>
			<tbody>
				<?php
					$i = 1;
					foreach( $Clan as $Key => $Value )
					{
						echo "
							<tr>
								<td>#" . number_format($i) . "</td>
								<td>{$Value['Name']}</td>
								<td>" . number_format($Value['Experience']) . "</td>
								<td>" . number_format($Value['Money']) . "</td>
							</tr>
						";

						$i++;
					}
				?>
			</tbody>
		</table>
	</div>
</div>

<?php
	require 'core/required/layout_bottom.php';