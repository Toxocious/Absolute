<?php
  define('IS_AJAX_REQUEST', isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');

  require_once $_SERVER['DOCUMENT_ROOT'] . '/core/required/session.php';

  if ( !isset($_SESSION['Absolute']) )
  {
    echo "<div>Please login again.</div>";
    session_destroy();
    exit;
  }

  require_once $_SERVER['DOCUMENT_ROOT'] . '/core/required/user_session.php';
