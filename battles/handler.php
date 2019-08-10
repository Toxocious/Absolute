<?php
	require_once '../core/required/session.php';
	require_once 'battle.php';

	if ( !isset($_SESSION['Battle']) )
	{
		return "<div class='error'>Error #1: There is currently no battle in session.</div>";
		exit;
	}

	if ( !isset($_SESSION['Battle']) && (!isset($_POST['Restart']) || !isset($_POST['x']) || !isset($_POST['y'])) )
	{
		return "<div class='error'>Error #2: An error has occurred while attempting to process the battle.</div>";
		exit;
	}

	$Fight = $_SESSION['Battle']['Status']['Battle_Fight'];
	$Battle = new $Fight();

	$Dialogue = 'Select a move to begin the battle.';

	if ( isset($_SESSION['Battle']) && isset($_POST['Clicks']) && isset($_POST['x']) && isset($_POST['y']) )
	{
		$Battle_ID = $Purify->Cleanse($_POST['Battle_ID']);
		$Clicks = $Purify->Cleanse($_POST['Clicks']);
		$x = $Purify->Cleanse($_POST['x']);
		$y = $Purify->Cleanse($_POST['y']);

		/**
		 * Switching out to a different Pokemon.
		 */
		if ( isset($_POST['Switch']) )
		{
			$Dialogue = "Switching into a different Pokemon.";
		}

		/**
		 * Restarting the battle.
		 */
		if ( isset($_POST['Restart']) && isset($_SESSION['Battle']['Status']['Restart']) && !isset($_POST['Move']) && !isset($_POST['Continue']) )
		{
			$Restart_POST = $Purify->Cleanse($_POST['Restart']);
			$Restart_SESS = $_SESSION['Battle']['Status']['Restart']['Code'];

			/**
			 * Prepare some data for the session's logs.
			 * If it matches, the user hasn't tinkered with the code (probably).
			 * If it doesn't match, the user is using a JS macro (probably).
			 */
			$_SESSION['Battle']['Status']['Logs']['Action'] = 'Restart';
			$_SESSION['Battle']['Status']['Logs']['Postcode'] = [ "POST" => $Restart_POST, "SESS" => $Restart_SESS ];
			$_SESSION['Battle']['Status']['Logs']['Coords'] = [ "Clicks" => $Clicks, "x" => $x, "y" => $y ];

			$Battle->Logify();

			unset($_SESSION['Battle']);

			$Dialogue = "Restarting the battle!";

			$Restart_Battle = $Battle->Create_Battle($_SESSION['Battle']['Status']['Restart']['ID']);
			if ( $Restart_Battle != 'Success' )
			{
				return "<div class='error'>Error #3: An error has occurred while attempting to restart the battle.</div>";
				exit;
			}
		}

		/**
		 * Continuing the battle.
		 */
		if ( isset($_POST['Continue']) && isset($_SESSION['Battle']['Status']['Continue']) && !isset($_POST['Move']) && !isset($_POST['Restart']) )
		{
			$Continue_POST = $Purify->Cleanse($_POST['Continue']);
			$Continue_SESS = $_SESSION['Battle']['Status']['Continue']['Code'];

			$_SESSION['Battle']['Status']['Logs']['Action'] = 'Continue';
			$_SESSION['Battle']['Status']['Logs']['Postcode'] = [ "POST" => $Continue_POST, "SESS" => $Continue_SESS ];
			$_SESSION['Battle']['Status']['Logs']['Coords'] = [ "Clicks" => $Clicks, "x" => $x, "y" => $y ];

			$Battle->Logify();

			//$Battle->Continue();
			$Dialogue = "Continuing the battle!";
		}

		/**
		 * Attacking the foe.
		 */
		if ( isset($_POST['Move']) && !isset($_POST['Continue']) && !isset($_POST['Restart']) )
		{
			$Move_Data = $Purify->Cleanse($_POST['Move']);

			$Dialogue = "Attacking the foe!<br /><br />";
			$Dialogue .= $Move_Data;
		}
	}

	$_SESSION['Battle']['Status']['Text'] = $Dialogue;

	/**
	 * Convert data to JSON so that it may be exported.
	 */
	$JSON = [
		'Battle' => [
			'Attacker' => $_SESSION['Battle']['Attacker']['Username'],
			'Defender' => $_SESSION['Battle']['Defender']['Username'],
			'Weather' => $_SESSION['Battle']['Status']['Weather'],
			'Text' => $Dialogue,
		],
	];

	foreach ( ['Attacker', 'Defender'] as $Key => $Value )
	{
		$Active = $_SESSION['Battle'][$Value]['Active'];

		if ( $Active['Item'] )
		{
			$Item = [ 'Item_ID' => $Active['Item'] ];
		}
		else
		{
			$Item = null;
		}

		$JSON[$Value] = [
			'Active' => [
				'Name' => $Active['Display_Name'],
				'Sprite' => $Active['Sprite'],
				'Icon' => $Active['Icon'],
				'Level' => $Active['Level'],
				'HP' => [
					'Current' => $Active['HP_Cur'],
					'Max' => $Active['HP_Max'],
				],
				'Exp' => [
					'Current' => (float) $Active['Experience'],
					'Needed' => FetchExperience($Active['Level']  + 1, 'Pokemon'),
				],
			],
		];

		if ( $Value == 'Attacker' )
		{
			$JSON[$Value]['Active']['Moves'] = [
				'Move_1' => [
					'Move_ID' => $Active['Moves'][0][0],
					'Move_Name' => $Active['Moves'][0][1],
					'Postcode' => $Active['Moves'][0][2]
				],
				'Move_2' => [
					'Move_ID' => $Active['Moves'][1][0],
					'Move_Name' => $Active['Moves'][1][1],
					'Postcode' => $Active['Moves'][1][2]
				],
				'Move_3' => [
					'Move_ID' => $Active['Moves'][2][0],
					'Move_Name' => $Active['Moves'][2][1],
					'Postcode' => $Active['Moves'][2][2]
				],
				'Move_4' => [
					'Move_ID' => $Active['Moves'][3][0],
					'Move_Name' => $Active['Moves'][3][1],
					'Postcode' => $Active['Moves'][3][2]
				],
			];
		}

		for ( $i = 0; $i < $_SESSION['Battle'][$Value]['Total_Pokemon']; $i++ )
		{
			$Pokemon = $_SESSION['Battle'][$Value]['Slot_' . $i];

			$JSON[$Value][$i] = [
				'Name' => $Pokemon['Display_Name'],
				'Icon' => $Pokemon['Icon'],
				'Item' => $Pokemon['Item'],
				'HP' => [
					'Current' => $Pokemon['HP_Cur'],
					'Max' => $Pokemon['HP_Max'],
				],
				'Exp' => [
					'Current' => (float) $Pokemon['Experience'],
					'Needed' => FetchExperience($Pokemon['Level']  + 1, 'Pokemon'),
				],
			];
		}
	}

	header('Content-Type: application/json');
	echo json_encode($JSON);