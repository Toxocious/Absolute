<?php
	require_once 'core/required/session.php';
	require_once 'battles/classes/battle.php';

	if
	(
		empty($User_Data) ||
		empty($User_Data['Roster']) ||
		$User_Data['RPG_Ban']
	)
	{
		header('Location: /index.php');
		return;
	}

	if
	(
		empty($_GET['Battle_Type']) ||
		empty($_GET['Foe'])
	)
	{
		header('Location: /battle_search.php');
		return;
	}

  unset($_SESSION['Battle']);

	$Battle_Type = strtolower(Purify($_GET['Battle_Type']));
	$Foe = strtolower(Purify($_GET['Foe']));

	$_SESSION['Battle']['Battle_Type'] = $Battle_Type;

	switch ($Battle_Type)
	{
		case 'trainer':
			$Battle = new Trainer();
			break;
		default:
			$Battle = new Trainer();
			break;
	}

	$Create_Battle = $Battle->CreateBattle($User_Data['ID'], $Foe);

	if ( $Create_Battle )
	{
		header('Location: /battle.php');
		return;
	}
	else
	{
		unset($_SESSION['Battle']);
		header("Location: /battle_search.php");

		return;
	}
