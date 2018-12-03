<?php
  // Set the timezone that Absolute is based on.
	date_default_timezone_set('America/Los_Angeles');
	$Date = date("M dS, Y g:i:s A");
	$Time = time();

	// Deal with the $_SERVER const.
	if ( isset($_SERVER['HTTP_HOST']) )
	{
		if ( $_SERVER['HTTP_HOST'] == "localhost" )
		{
			session_set_cookie_params(0, '/', 'localhost');
		}
		
		session_cache_limiter('private');
		$cache_limiter = session_cache_limiter();

		session_cache_expire(180);
		$cache_expire = session_cache_expire();
	}

	header("Content-Type: text/html; charset=UTF-8");
  header("Expires: Tue, 03 Jul 2001 06:00:00 GMT");
  header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");
  header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
  header("Cache-Control: post-check=0, pre-check=0", false);
  header("Pragma: no-cache");


	// Start the session before doing anything else.
	if ( !isset($_SESSION) )
	{
		session_start();
	}

	// Directory Root
	if ( !isset($Dir_Root) )
	{
		$Dir_Root = realpath($_SERVER["DOCUMENT_ROOT"]);
	}

	// Require some files.
	require_once $Dir_Root . '/core/functions/formulas.php';
	require_once $Dir_Root . '/core/functions/main_functions.php';
	require_once $Dir_Root . '/core/functions/global_functions.php';

	// Require Pokemon and User classes.
	require_once $Dir_Root . '/core/classes/user.php';
	$User_Class = new User();
	require_once $Dir_Root . '/core/classes/pokemon.php';
	$PokeClass = new Pokemon();

	// Proxies sometimes send the X-Forwarded-For header to indicate the actual
	// IP address of the client. This cannot really be trusted, because the header
	// could potentially be forged. As such, we use a whitelist of proxy IPs that
	// we can trust.
	if ( in_array($_SERVER['REMOTE_ADDR'], []) )
	{
	  $IP_List = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);

		if ( $IP_List[0] && $IP_List[0] != '127.0.0.1' )
		{
	    $_SERVER['REMOTE_ADDR'] = $IP_List[0]; // The first proxy in the list is the client IP.
	  }
	}

	$Parse_URL = parse_url((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");
	
	$Fetch_Page = $PDO->prepare("SELECT * FROM `pages` WHERE `URL` = ? LIMIT 1");
	$Fetch_Page->execute([$Parse_URL['path']]);
	$Fetch_Page->setFetchMode(PDO::FETCH_ASSOC);
	$Current_Page = $Fetch_Page->fetch();
  
  /**
	 * If the user is currently in a session, run the following code at the start of every page load.
	 * Fetch their database information.
	 * Update their overall playtime.
	 * Fetch their roster.
	 */
	if ( isset($_SESSION['abso_user']) )
	{
		$Fetch_User = $PDO->prepare("SELECT * FROM `users` WHERE `id` = ? LIMIT 1");
		$Fetch_User->execute([$_SESSION['abso_user']]);
		$Fetch_User->setFetchMode(PDO::FETCH_ASSOC);
		$User_Data = $Fetch_User->fetch();

		if ( !isset($_SESSION['Playtime']) )
		{
			$_SESSION['Playtime'] = $Time;
		}
		
		$Playtime = $Time - $_SESSION['Playtime'];
		$Playtime = $Playtime > 20 ? 20 : $Playtime;
		$_SESSION['Playtime'] = $Time;

		try
		{
			$Update_User = $PDO->prepare("UPDATE `users` SET `Last_Active` = ?, `Playtime` = `Playtime` + ?,  `Last_Page` = ? WHERE `id` = ? LIMIT 1");
			$Update_User->execute([$Time, $Playtime, $Current_Page['Name'], $User_Data['id']]);

			$Fetch_Roster = $PDO->prepare("SELECT `ID` FROM `pokemon` WHERE `Owner_Current` = ? AND `Location` = 'Roster' ORDER BY `Slot` ASC LIMIT 6");
			$Fetch_Roster->execute([$User_Data['id']]);
			$Fetch_Roster->setFetchMode(PDO::FETCH_ASSOC);
			$Roster = $Fetch_Roster->fetchAll();
			
			$User_Data['Playtime'] += $Playtime;
		}
		catch ( PDOException $e )
		{
			echo $e->getMessage();
		}
	}