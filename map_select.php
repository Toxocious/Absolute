<?php
	/**
	 * Proceeding to warp to a map.
	 */
	if ( isset($_POST['Explore']) )
	{
		require 'core/required/session.php';

		unset($_SESSION['Maps']);

		$Map = $Purify->Cleanse($_POST['Explore']);

		//try
		//{
		//	$Fetch_Map_Data = $PDO->prepare("SELECT `start_pos` FROM `maps` WHERE `name` = ? LIMIT 1");
		//	$Fetch_Map_Data->execute([ $Map ]);
		//	$Fetch_Map_Data->setFetchMode(PDO::FETCH_ASSOC);
		//	$Map_Data = $Fetch_Map_Data->fetch();
		//}
		//catch( PDOException $e )
		//{
		//	HandleError( $e->getMessage() );
		//}
		//
		//$Spawn_Pos = explode(',', $Map_Data['start_pos']);
		//
		$_SESSION['Maps']['Quip'] 		= "What'll you find today?";
		$_SESSION['Maps']['Map_ID']		= $Map;

		echo "<pre>";var_dump($_SESSION['Maps']);echo "</pre>";
		//exit;

		header("Location: map.php");

		exit;
	}

	require 'core/required/layout_top.php';

	/**
	 * Fetch all available maps.
	 */
	try
	{
		$Fetch_Maps = $PDO->prepare("SELECT * FROM `maps` WHERE `id` != 0");
		$Fetch_Maps->execute();
		$Fetch_Maps->setFetchMode(PDO::FETCH_ASSOC);
		$Maps = $Fetch_Maps->fetchAll();
	}
	catch( PDOException $e )
	{
		HandleError( $e->getMessage() );
	}
?>

<div class='panel content'>
	<div class='head'>Explore A Map</div>
	<div class='body'>
		<div class='row'>
			<?php
				foreach ( $Maps as $Key => $Value )
				{
					echo "
						<div class='panel' style='float: left; width: 49.4%;'>
							<div class='head'>{$Value['display_name']}</div>
							<div class='body' style='padding: 2px;'>
								<div style='border: 2px solid #000; border-radius: 4px; float: left; height: 204px; margin-bottom: 5px; width: 204px;'>
									<img src='images/Maps/{$Value['name']}.png' style='height: 200px; width: 200px;' />
								</div>
								<div style='float: left; height: 204px; padding: 5px; width: 224px;'>
									<div style='height: 155px;'>
										{$Value['description']}
									</div>
									<div>
										" . nl2br($Value['credits']) . "
									</div>
								</div>

								<form method='POST' action='map_select.php'>
									<input type='hidden' name='Explore' value='{$Value['name']}' />
									<input type='submit' style='padding: 5px; width: 100%;' value='Explore!' />	
								</form>
							</div>
						</div>
					";
				}
			?>
		</div>
	</div>
</div>

<style>
	.box .row .panel:nth-child(odd)
	{
		margin-right: 5px;
	}
</style>

<?php
	require 'core/required/layout_bottom.php';