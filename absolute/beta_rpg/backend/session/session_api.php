<?php
    declare(strict_types=1);

    header('Expires: Tue, 03 Jul 2001 06:00:00 GMT');
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');

    if ( session_status() !== PHP_SESSION_ACTIVE )
    {
        $Request_Host = strtolower($_SERVER['HTTP_HOST'] ?? '');
        $Request_Host = explode(':', $Request_Host)[0];

        $Is_Local = in_array($Request_Host, ['localhost', 'beta.localhost', '127.0.0.1'], true);

        if ( $Is_Local ) {
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

        session_start();
    }

    if ( !isset($Dir_Root) )
    {
        $Dir_Root = realpath($_SERVER['DOCUMENT_ROOT']);
    }

    require_once $Dir_Root . '/backend/session/domain.php';
    require_once $Dir_Root . '/backend/session/database.php';

    try {
        Database::init([
            'host' => getenv('MYSQL_HOST') ?: 'localhost',
            'user' => getenv('MYSQL_USER') ?: 'root',
            'password' => getenv('MYSQL_PASSWORD') ?: '',
            'database' => 'absolute_beta',
            'charset' => getenv('MYSQL_CHARSET') ?: 'utf8mb4'
        ]);
    }
    catch (Exception $e)
    {
        if ( function_exists('HandleError') )
        {
            HandleError($e);
        }

        http_response_code(503);
        header('Content-Type: application/json; charset=UTF-8');

        echo json_encode([
            'ok' => false,
            'error' => [
                'code' => 'service_unavailable',
                'message' => 'Unable to connect to the database right now.'
            ]
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        exit;
    }
