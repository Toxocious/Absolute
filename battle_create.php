<?php
	require 'core/required/session.php';
	require 'core/classes/battle.php';

	if ( isset($_SESSION['abso_user']) )
	{
		unset($_SESSION['Battle']);

		try
		{
			$Query_User = $PDO->prepare("SELECT * FROM `users` WHERE `RPG_Ban` = 'no' AND `id` = ? LIMIT 1");
			$Query_User->execute([ $_SESSION['abso_user'] ]);
			$Query_User->setFetchMode(PDO::FETCH_ASSOC);
			$User = $Query_User->fetch();
		}
		catch ( PDOException $e )
		{
			HandleError( $e->getMessage() );
		}

		if ( !isset($User['id']) )
		{
			Header('Location: /login.php');
		}
		else if ( $User['Roster'] == 0 )
		{
			Header('Location: /pokemon_center.php');
		}
		else if ( !isset($_GET['Battle']) && !isset($_GET['Foe']) )
		{
			Header('Location: /battle_search.php');
		}
		else
		{
			$Battle = new Battle();
			$Battle->CreateBattle( $_GET['Battle'], $_GET['Foe'] );
			Header('Location: /battle.php');
		}
	}
	else
	{
		Header('Location: /index.php');
	}