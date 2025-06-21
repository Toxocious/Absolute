<?php
  $page_script_start_time = microtime(true);

  // Set the timezone that Evo-Chronicles RPG is based on.
  date_default_timezone_set('America/Los_Angeles');
  $Date = date("M dS, Y g:i:s A");
  $EvoChroniclesRPG_Time = date('m/d/y h:i A');
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

  if ( session_status() !== PHP_SESSION_ACTIVE )
    session_start();

  if ( !isset($Dir_Root) )
    $Dir_Root = realpath($_SERVER["DOCUMENT_ROOT"]);

  /**
   * Get all necessary classes.
   */
  require_once $Dir_Root . '/core/classes/constants.php';
  $Constants = new Constants();
  require_once $Dir_Root . '/core/classes/user.php';
  $User_Class = new User();
  require_once $Dir_Root . '/core/classes/clan.php';
  $Clan_Class = new Clan();
  require_once $Dir_Root . '/core/classes/item.php';
  $Item_Class = new Item();
  require_once $Dir_Root . '/core/classes/shop.php';
  $Shop_Class = new Shop();
  require_once $Dir_Root . '/core/classes/navigation.php';
  $Navigation = new Navigation();
  require_once $Dir_Root . '/core/classes/notification.php';
  $Notification = new Notification();
  require_once $Dir_Root . '/core/classes/timer.php';
  require_once $Dir_Root . '/core/classes/weighter.php';
  require_once $Dir_Root . '/core/classes/direct_message.php';

  /**
   * Get all necessary functions and constants.
   */
  require_once $Dir_Root . '/core/required/domains.php';
  require_once $Dir_Root . '/core/required/database.php';
  require_once $Dir_Root . '/core/functions/formulas.php';
  require_once $Dir_Root . '/core/functions/pagination.php';
  require_once $Dir_Root . '/core/functions/purify.php';
  require_once $Dir_Root . '/core/functions/last_seen.php';
  require_once $Dir_Root . '/core/functions/is_between_dates.php';
  require_once $Dir_Root . '/core/functions/user_agent.php';

  require_once $Dir_Root . '/core/functions/pokemon.php';

  $PDO = connect_database('evo_chronicles_rpg');

  /**
   * Get the client's IP address.
   */
  if ( in_array($_SERVER['REMOTE_ADDR'], []) )
  {
    $IP_List = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);

    if ( $IP_List[0] != '127.0.0.1' )
    {
      $_SERVER['REMOTE_ADDR'] = $IP_List[0]; // The first proxy in the list is the client IP.
    }
  }

  /**
   * Get data about the page the client is on.
   */
  try
  {
    $Parse_URL = parse_url((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");

    $Fetch_Page = $PDO->prepare("SELECT * FROM `pages` WHERE `URL` = ? LIMIT 1");
    $Fetch_Page->execute([ $Parse_URL['path'] ]);
    $Fetch_Page->setFetchMode(PDO::FETCH_ASSOC);
    $Current_Page = $Fetch_Page->fetch();
  }
  catch ( PDOException $e )
  {
    HandleError($e);
  }

  if ( !$Current_Page )
  {
    $Current_Page['Name'] = 'Index';
    $Current_Page['Maintenance'] = 'no';
    $Current_Page['Logged_In'] = 'no';
  }

  /**
   * Handle active session logic at the start of page loads.
   *  - Get active user data
   *  - Update active user page info, playtime, and last page active on
   */
  if ( isset($_SESSION['EvoChroniclesRPG']) )
  {
    $User_Data = $User_Class->FetchUserData($_SESSION['EvoChroniclesRPG']['Logged_In_As']);

    if ( !isset($_SESSION['EvoChroniclesRPG']['Playtime']) )
    {
      $_SESSION['EvoChroniclesRPG']['Playtime'] = $Time;
    }

    $Playtime = $Time - $_SESSION['EvoChroniclesRPG']['Playtime'];
    $Playtime = $Playtime > 20 ? 20 : $Playtime;
    $_SESSION['EvoChroniclesRPG']['Playtime'] = $Time;

    try
    {
      $Update_Activity = $PDO->prepare("INSERT INTO `logs` (`Type`, `Page`, `Data`, `User_ID`) VALUES ('pageview', ?, ?, ?)");
      $Update_Activity->execute([ $Current_Page['Name'], $Parse_URL['path'], $User_Data['ID'] ]);

      $Update_User = $PDO->prepare("UPDATE `users` SET `Last_Active` = ?, `Last_Page` = ?, `Playtime` = `Playtime` + ? WHERE `ID` = ? LIMIT 1");
      $Update_User->execute([ $Time, $Current_Page['Name'], $Playtime, $User_Data['ID'] ]);
    }
    catch ( PDOException $e )
    {
      HandleError($e);
    }
  }
