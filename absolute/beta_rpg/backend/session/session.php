<?php
    $Page_Start_Time = microtime(true);

    date_default_timezone_set('America/Phoenix');
    $Date = date("M dS, Y g:i:s A");
    $Absolute_Time = [
        'Date' => date('m/d/y h:i A'),
        'Time' => date('h:i A'),
        'Timestamp' => time(),
    ];

    if ( session_status() !== PHP_SESSION_ACTIVE )
        {
        $Request_Host = strtolower($_SERVER['HTTP_HOST'] ?? '');
        $Request_Host = explode(':', $Request_Host)[0];

        $Is_Local = in_array($Request_Host, ['localhost', 'beta.localhost', '127.0.0.1'], true);

        if ($Is_Local) {
            // Host-only cookie for local dev (no domain key)
            session_set_cookie_params([
                'lifetime' => 0,
                'path'     => '/',
                'secure'   => false,
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
        } else {
            $Is_Https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';

            session_set_cookie_params([
                'lifetime' => 0,
                'path'     => '/',
                'domain'   => '.absoluterpg.com',
                'secure'   => $Is_Https,
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
        }
    }

    // No cache.
    header("Content-Type: text/html; charset=UTF-8");
    header("Expires: Tue, 03 Jul 2001 06:00:00 GMT");
    header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
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
     * Get all necessary classes, functions, and constants.
     */
    require_once $Dir_Root . '/backend/session/domain.php';
    require_once $Dir_Root . '/backend/session/database.php';
    // require_once $Dir_Root . '/backend/session/user_agent.php';

    // require_once $Dir_Root . '/backend/functions/pokemon.php';

    /**
     * Database initialization and connection.
     */
    try {
        Database::init([
            'host' => getenv('MYSQL_HOST') ?: 'localhost',
            'user' => getenv('MYSQL_USER') ?: 'root',
            'password' => getenv('MYSQL_PASSWORD') ?: '',
            // 'database' => getenv('MYSQL_GAME_DATABASE') ?: 'absolute_beta',
            'database' => 'absolute_beta',
            'charset' => getenv('MYSQL_CHARSET') ?: 'utf8mb4'
        ]);
    }
    catch (Exception $e)
    {
        HandleError($e);

        header("Location: /503.php");
        exit;
    }

    $db = Database::get();

    // var_dump(isset($_SESSION['Absolute_Beta']));

    /**
     * Get the client's IP address.
     */
    if ( in_array($_SERVER['REMOTE_ADDR'], []) )
    {
        $IP_List = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);

        if ( $IP_List[0] != '127.0.0.1' )
        {
            // The first proxy in the list is the client IP.
            $_SERVER['REMOTE_ADDR'] = $IP_List[0];
        }
    }
