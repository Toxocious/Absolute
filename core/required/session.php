<?php
	// Timer function.
	function timer()
	{
		static $Timer_Start;

		if ( is_null($Timer_Start) )
		{
			$Timer_Start = microtime(true);
		}
		else
		{
			$Difference = round((microtime(true) - $Timer_Start), 4);
			$Timer_Start = null;

			return $Difference;
		}
	}

	timer();

  // Set the timezone that Absolute is based on.
	date_default_timezone_set('America/Los_Angeles');
	$Date = date("M dS, Y g:i:s A");
	$Absolute_Time = date('m/d/y&\nb\sp;&\nb\sp;h:i A');
	$Time = time();

	// Deal with the $_SERVER const.
	if ( isset($_SERVER['HTTP_HOST']) && session_status() !== PHP_SESSION_ACTIVE )
	{
		if ( $_SERVER['HTTP_HOST'] == "localhost" )
		{
			session_set_cookie_params(0, '/', 'localhost');
		}
		else
		{
			session_set_cookie_params(0, '/', 'absoluterpg.com');
		}
	}

	// No cache.
	header("Content-Type: text/html; charset=UTF-8");
	header("Expires: Tue, 03 Jul 2001 06:00:00 GMT");
	header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");
	header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
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

	// Require all necessary classes.
	require_once $Dir_Root . '/core/classes/constants.php';
	$Constants = new Constants();
	require_once $Dir_Root . '/core/classes/pokemon.php';
	$Poke_Class = new Pokemon();
	require_once $Dir_Root . '/core/classes/user.php';
	$User_Class = new User();
	require_once $Dir_Root . '/core/classes/clan.php';
	$Clan_Class = new Clan();
	require_once $Dir_Root . '/core/classes/item.php';
	$Item_Class = new Item();
	require_once $Dir_Root . '/core/classes/purify.php';
	$Purify = new Purify();
	require_once $Dir_Root . '/core/classes/shop.php';
	$Shop_Class = new Shop();
	require_once $Dir_Root . '/core/classes/navigation.php';
	$Navigation = new Navigation();
	require_once $Dir_Root . '/core/classes/notification.php';
	$Notification = new Notification();
	require_once $Dir_Root . '/core/classes/weighter.php';
	require_once $Dir_Root . '/core/classes/direct_message.php';

	// Require some files.
	require_once $Dir_Root . '/core/required/domains.php';
	require_once $Dir_Root . '/core/functions/formulas.php';
	require_once $Dir_Root . '/core/functions/pagination.php';
	require_once $Dir_Root . '/core/functions/main_functions.php';

	// Proxies sometimes send the X-Forwarded-For header to indicate the actual
	// IP address of the client. This cannot really be trusted, because the header
	// could potentially be forged. As such, we use a whitelist of proxy IPs that
	// we can trust.
	if ( in_array($_SERVER['REMOTE_ADDR'], []) )
	{
	  $IP_List = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);

		if ( $IP_List[0] != '127.0.0.1' )
		{
	    $_SERVER['REMOTE_ADDR'] = $IP_List[0]; // The first proxy in the list is the client IP.
	  }
	}

	/**
	 * Get page data.
	 */
	$Parse_URL = parse_url((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");
	$Fetch_Page = $PDO->prepare("SELECT * FROM `pages` WHERE `URL` = ? LIMIT 1");
	$Fetch_Page->execute([$Parse_URL['path']]);
	$Fetch_Page->setFetchMode(PDO::FETCH_ASSOC);
	$Current_Page = $Fetch_Page->fetch();

	/**
	 * Handle session clearing here.
	 */
	if ( isset($_GET['Logout']) )
	{
		session_start();
		$params = session_get_cookie_params();
		setcookie(session_name(), '', time() - 42000,
			$params["path"], $params["domain"],
			$params["secure"], $params["httponly"]
		);
		session_destroy();
		unset($_SESSION);
		header("Location: login.php");
	}

  /**
	 * If the user is currently in a session, run the following code at the start of every page load.
	 */
	$is_User_Data_Fetched = false;
	if ( isset($_SESSION['abso_user']) )
	{
		// $Fetch_User = $PDO->prepare("SELECT * FROM `users` INNER JOIN `user_currency` ON `users`.`id`=`user_currency`.`User_ID` WHERE `id` = ? LIMIT 1");
		// $Fetch_User->execute([ $_SESSION['abso_user'] ]);
		// $Fetch_User->setFetchMode(PDO::FETCH_ASSOC);
		// $User_Data = $Fetch_User->fetch();

		if ( !$is_User_Data_Fetched )
		{
			$is_User_Data_Fetched = true;
			$User_Data = $User_Class->FetchUserData($_SESSION['abso_user']);
		}

		if ( !isset($_SESSION['Playtime']) )
		{
			$_SESSION['Playtime'] = $Time;
		}

		$Playtime = $Time - $_SESSION['Playtime'];
		$Playtime = $Playtime > 20 ? 20 : $Playtime;
		$_SESSION['Playtime'] = $Time;

		//$User_Data['Playtime'] += $Playtime;

		try
		{
			if ( $Current_Page )
			{
				$Update_Activity = $PDO->prepare("INSERT INTO `logs` (`Type`, `Page`, `Data`, `User_ID`) VALUES ('pageview', ?, ?, ?)");
				$Update_Activity->execute([ $Current_Page['Name'], $Parse_URL['path'], $User_Data['ID'] ]);
			}

			$Update_User = $PDO->prepare("UPDATE `users` SET `Last_Active` = ?, `Last_Page` = ?, `Playtime` = `Playtime` + ? WHERE `id` = ? LIMIT 1");
			$Update_User->execute([ $Time, $Current_Page['Name'], $Playtime, $User_Data['ID'] ]);
		}
		catch ( PDOException $e )
		{
			HandleError( $e->getMessage() );
		}
	}

	if ( !$Current_Page )
	{
		$Current_Page['Name'] = 'Index';
		$Current_Page['Maintenance'] = 'no';
		$Current_Page['Logged_In'] = 'no';
	}

  if
  (
    $Current_Page['Logged_In'] === 'yes' &&
    !isset($_SESSION['abso_user'])
  )
  {
    include_once $Dir_Root . '/index.php';
  }
