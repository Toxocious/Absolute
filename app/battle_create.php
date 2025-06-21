<?php
  require_once 'battles/classes/battle.php';

  require_once 'battles/fights/trainer.php';
  require_once 'battles/fights/wild.php';

  require_once 'battles/classes/ability.php';
  require_once 'battles/classes/clanhandler.php';
  require_once 'battles/classes/dialogue.php';
  require_once 'battles/classes/field.php';
  require_once 'battles/classes/helditem.php';
  require_once 'battles/classes/log.php';
  require_once 'battles/classes/move.php';
  require_once 'battles/classes/options.php';
  require_once 'battles/classes/pokemonhandler.php';
  require_once 'battles/classes/rewards.php';
  require_once 'battles/classes/roster.php';
  require_once 'battles/classes/stat.php';
  require_once 'battles/classes/status.php';
  require_once 'battles/classes/terrain.php';
  require_once 'battles/classes/userhandler.php';
  require_once 'battles/classes/weather.php';

	require_once 'core/required/session.php';

	if
	(
		empty($User_Data) ||
		empty($User_Data['Roster']) ||
		$User_Data['RPG_Ban']
	)
	{
		header('Location: /index.php');
		exit;
	}

	if ( empty($_GET['Battle_Type']) )
	{
		header('Location: /battle_search.php');
		exit;
	}

  unset($_SESSION['EvoChroniclesRPG']['Battle']);

	$Battle_Type = strtolower(Purify($_GET['Battle_Type']));

  if ( isset($_GET['Foe']) )
	  $Foe = Purify($_GET['Foe']);

  if ( isset($_GET['iFrame']) )
	  $_SESSION['EvoChroniclesRPG']['Battle']['Battle_Layout'] = 'iFrame';
  else
	  $_SESSION['EvoChroniclesRPG']['Battle']['Battle_Layout'] = $User_Data['Battle_Theme'];

	$_SESSION['EvoChroniclesRPG']['Battle']['Battle_Type'] = $Battle_Type;

	switch ($Battle_Type)
	{
		case 'trainer':
      $Foe = (int) $Foe;
			$Battle = new Trainer($User_Data['ID'], $Foe);
			break;

    case 'wild':
      $Battle = new Wild($User_Data['ID']);
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
		unset($_SESSION['EvoChroniclesRPG']['Battle']);
		header("Location: /battle_search.php");

		exit;
	}
