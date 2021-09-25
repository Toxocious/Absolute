<?php
	require_once 'core/required/session.php';
	require_once 'battles/classes/battle.php';
	require_once 'battles/fights/trainer.php';

	if
	(
		empty($User_Data) ||
		empty($User_Data['Roster']) ||
		$User_Data['RPG_Ban']
	)
	{
    echo 'User is banned';
    exit;

		header('Location: /index.php');
		exit;
	}

	if
	(
		empty($_GET['Battle_Type']) ||
		empty($_GET['Foe'])
	)
	{
    echo 'Invalid Battle Type Or Foe';
    exit;

		header('Location: /battle_search.php');
		exit;
	}

  unset($_SESSION['Battle']);

	$Battle_Type = strtolower(Purify($_GET['Battle_Type']));
	$Foe = Purify($_GET['Foe']);

	$_SESSION['Battle']['Battle_Type'] = $Battle_Type;

	switch ($Battle_Type)
	{
		case 'trainer':
      $Foe = (int) $Foe;
			$Battle = new Trainer($User_Data['ID'], $Foe);
			break;

		default:
      $Foe = (int) $Foe;
			$Battle = new Trainer($User_Data['ID'], $Foe);
			break;
	}

	$Create_Battle = $Battle->CreateBattle();

	if ( $Create_Battle )
	{
		header('Location: /battle.php');
		exit;
	}
	else
	{
		unset($_SESSION['Battle']);
		header("Location: /battle_search.php");

		exit;
	}
