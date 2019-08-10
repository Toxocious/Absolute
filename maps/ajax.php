<?php
	require '../core/required/session.php';

	/**
	 * Auto load the necessary PHP classes.
	 */
	spl_autoload_register(function($Class)
	{
		include 'php/' . $Class . '.php';
	});

	//var_dump($_SESSION['Maps']);

	/**
	 * Instantiate the player and the map.
	 */
	$Map = new Map();
	$Player = Player::FetchInstance();
	$Player->Map = $Map;

	/**
	 * If a tmx file is set, include the map's tmx file.
	 */
	if ( isset($_GET['tmx']) )
	{
		header('Content-type: text/xml');
		include 'Maps/' . $Map->Player->FetchMap() . '.tmx';

		exit;
	}

	/**
	 * If a tileset is set, load the tileset.
	 */
	if ( isset($_GET['tileset']) )
	{
		$Tileset_Name = $Purify->Cleanse($_GET['tileset']);

		foreach ( $Map->TiledMap->tileset as $Tileset )
		{
			if ( $Tileset->name == $Tileset_Name )
			{
				$Tileset_Location = explode( '/', $Tileset->image[0]->source );
				$Tileset_Location = $Tileset_Location[count($Tileset_Location) - 1];
				$Tileset_Location = 'Tilesets/' . $Tileset_Location;

				header('Content-type: image/png');
				include $Tileset_Location;

				exit;
			}
		}

		exit;
	}

	/**
	 * Data that's been loaded from PHP.
	 */
	if ( isset($_GET['load']) || isset($_GET['Load']) )
	{
		$Sync = new Sync($Map);

		header('Content-Type: application/json');
		echo json_encode( $Sync->Load() );

		exit;
	}

	/**
	 * Handle any actions that get performed.
	 */
	if ( isset($_POST['action']) )
	{
		header('Content-Type: application/json');

		$Action = $Purify->Cleanse($_POST['action']);

		switch ($Action)
		{
			case 'Move':
				$x = $Purify->Cleanse($_POST['x']);
				$y = $Purify->Cleanse($_POST['y']);
				$z = $Purify->Cleanse($_POST['z']);

				if ( $Map->MoveCheck($x, $y, $z) )
				{
					$Map->Player->SetPosition(false, $x, $y, $z);
					$Map->Move();

					echo json_encode( $Map->OutputFetch() );
				}
				else
				{
					echo json_encode([ 'error' => 'Invalid Session', 'code' => '001' ]);
				}
				break;

			case 'Interact':
				$x = $Purify->Cleanse($_POST['x']);
				$y = $Purify->Cleanse($_POST['y']);
				$z = $Purify->Cleanse($_POST['z']);

				$Map->Interact($x, $y, $z);

				echo json_encode( $Map->OutputFetch() );
				break;
				
			case 'run':
				unset($_SESSION['Maps']['Encounter']);
				echo json_encode( $Map->OutputFetch() );
				break;

			case 'Warp':
				echo 'Warping to a new location.';
				echo json_encode($Map->OutputAdd());
				break;
		}

		exit;
	}

	echo $Map->Print();