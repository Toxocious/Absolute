<?php
  define("PATH_ROOT", dirname(__DIR__));

  // Localhost domains.
  if ( $_SERVER['HTTP_HOST'] === 'localhost' )
  {
    define("DOMAIN_ROOT", "https://localhost");
    define("DOMAIN_SPRITES", "https://localhost/images");
  }
  // Live server domains.
  else
  {
    if ( isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' )
    {
      define("DOMAIN_ROOT", "https://absoluterpg.com");
      define("DOMAIN_SPRITES", "https://absoluterpg.com/images");
    }
    else
    {
      define("DOMAIN_ROOT", "http://absoluterpg.com");
      define("DOMAIN_SPRITES", "http://absoluterpg.com/images");
    }
  }