<?php
    define("PATH_ROOT", dirname(__DIR__));

    define("SERVER_ROOT",  $_SERVER['DOCUMENT_ROOT']);
    define("SERVER_SPRITES", $_SERVER['DOCUMENT_ROOT'] . "/assets/images");

    if ( $_SERVER['HTTP_HOST'] === 'beta.localhost' )
    {
        define('LOCAL', true);
        define("DOMAIN_ROOT", "https://beta.localhost");
        define("DOMAIN_SPRITES", "https://beta.localhost/assets/images");
    }
    else
    {
        define('LOCAL', false);

        if ( isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' )
        {
            define("DOMAIN_ROOT", "https://absoluterpg.com");
            define("DOMAIN_SPRITES", "https://absoluterpg.com/assets/images");
        }
        else
        {
            define("DOMAIN_ROOT", "http://absoluterpg.com");
            define("DOMAIN_SPRITES", "http://absoluterpg.com/assets/images");
        }
    }

