<?php
	require_once 'core/required/session.php';
	require_once 'battles/classes/battle.php';

	if
	(
		!$User_Data ||
		!$User_Data['Roster'] ||
		$User_Data['Banned_RPG']
	)
	{
		header('Location: /index.php');
		return;
	}

	if
	(
		!isset($_GET['Battle_Type']) ||
		!isset($_GET['Foe'])
	)
	{
		header('Location: /battle_search.php');
		return;
	}

  unset($_SESSION['Battle']);

	$Battle_Type = strtolower(Purify($_GET['Battle_Type']));
	$Foe = strtolower(Purify($_GET['Foe']));

	$_SESSION['Battle']['Battle_Type'] = $Battle_Type;
	$_SESSION['Battle']['Ally']['ID'] = $User_Data['ID'];

	switch ($Battle_Type)
	{
		case 'trainer':
			$Battle = new Trainer();
			break;
		default:
			$Battle = new Trainer();
			break;
	}

	$Create_Battle = $Battle->CreateBattle($_GET['Foe']);

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
