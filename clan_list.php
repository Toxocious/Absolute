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
		<table class='standard' style='margin: 0 auto; width: 80%;'>
			<thead>
				<tr>
					<th>Rank</th>
					<th>Name</th>
					<th>Level</th>
				</tr>
			</thead>
			<tbody>
				<?php
					$i = 1;
					foreach( $Clan as $Key => $Value )
					{
						echo "
							<tr>
								<td>#{$i}</td>
								<td>{$Value['Name']}</td>
								<td>{$Value['Experience']}</td>
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