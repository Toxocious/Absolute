<?php
  define("PATH_ROOT", dirname(__DIR__));

  // Localhost domains.
  if ( $_SERVER['HTTP_HOST'] === 'localhost' )
  {
    define("DOMAIN_ROOT", "https://localhost");
    define("DOMAIN_SPRITES", "https://localhost/images/Sprites");
  }
  // Live server domains.
  else
  {
    define("DOMAIN_ROOT", "https://absoluterpg.com");
    define("DOMAIN_SPRITES", "https://absoluterpg.com/images/Sprites");
  }