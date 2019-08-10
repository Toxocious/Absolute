<?php
	require 'core/required/session.php';
	require 'battles/battle.php';

	if ( isset($_SESSION['abso_user']) )
	{
		// Check to see if the user is banned.
		// Banned users may not battle.
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

		// The user is banned; redirect them to the login page.
		if ( !isset($User['id']) )
		{
			header('Location: /login.php');
			exit;
		}
		// The user has nothing in their roster; redirect them to the Pokemon Center.
		else if ( $User['Roster'] == 0 )
		{
			header('Location: /pokemon_center.php');
			exit;
		}
		// The battle and foe has been set.
		else if ( !isset($_GET['Battle']) && !isset($_GET['Foe']) )
		{
			header('Location: /battle_search.php');
			exit;
		}
		// Everything is good; start the battle.
		else
		{
			$Battle_Mode = $_GET['Battle'];

			$Battle = new $Battle_Mode();
			$Create = $Battle->Create_Battle( $_GET['Foe'] );

			//echo "<pre>";var_dump($_SESSION['Battle']);echo "</pre>";

			if ( $Create )
			{
				header('Location: /battle.php');
				exit;
			}
			else
			{
				header("Location: /battle_search.php");
    		exit;
			}
		}
	}
	else
	{
		header('Location: /index.php');
		exit;
	}