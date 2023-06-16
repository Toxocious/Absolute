<?php
  require_once 'core/required/session.php';

  if ( session_status() === PHP_SESSION_ACTIVE )
  {
    $params = session_get_cookie_params();

    setcookie(session_name(), '', time() - 42069,
      $params["path"], $params["domain"],
      $params["secure"], $params["httponly"]
    );

    session_destroy();

    unset($_SESSION);
  }

  header("Location: login.php");
  exit;
