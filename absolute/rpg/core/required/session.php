<?php
    $page_script_start_time = microtime(true);

    // Set the timezone that Absolute is based on.
    date_default_timezone_set('America/Los_Angeles');
    $Date = date("M dS, Y g:i:s A");
    $Absolute_Time = date('m/d/y h:i A');

    // Deal with the $_SERVER const.
    if ( isset($_SERVER['HTTP_HOST']) && session_status() !== PHP_SESSION_ACTIVE )
    {
        if ( $_SERVER['HTTP_HOST'] == "localhost" )
        {
            session_set_cookie_params([
                'lifetime' => 0,
                'path'     => '/',
                'domain'   => 'localhost',
                'secure'   => false,
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
        }
        else
        {
            session_set_cookie_params([
                'lifetime' => 0,
                'path'     => '/',
                'domain'   => 'absoluterpg.com',
                'secure'   => true,
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
        }
    }

    // No cache.
    header("Content-Type: text/html; charset=UTF-8");
    header("Expires: Tue, 03 Jul 2001 06:00:00 GMT");
    header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");
    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    header("Pragma: no-cache");

    if ( session_status() !== PHP_SESSION_ACTIVE )
    {
        session_start();
    }

    if ( !isset($Dir_Root) )
    {
        $Dir_Root = realpath($_SERVER["DOCUMENT_ROOT"]);
    }

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

    require_once $Dir_Root . '/core/classes/navigation.php';
    $Navigation = new Navigation();

    require_once $Dir_Root . '/core/classes/notification.php';
    $Notification = new Notification();

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
    require_once $Dir_Root . '/core/functions/system_notification.php';
    require_once $Dir_Root . '/core/functions/user_agent.php';

    require_once $Dir_Root . '/core/functions/pokemon.php';

    try
    {
        $PDO = DatabaseConnectionPool::getConnection('absolute');
    }
    catch (Exception $e)
    {
        HandleError($e);
        header("Location: /503.php");
        exit;
    }

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

        if ( !strpos($Parse_URL['path'], '/staff/') )
        {
            $Parse_URL['path'] = TransformPath($Parse_URL['path']);
        }

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
