<?php
	require_once 'core/required/session.php';

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

	$Battle_Mode = $_GET['Battle'];

	$Battle = new $Battle_Mode();
	$Create = $Battle->Create_Battle( $_GET['Foe'] );

	if ( $Create )
	{
		header('Location: /battle.php');
		return;
	}
	else
	{
		header("Location: /battle_search.php");
		return;
	}
