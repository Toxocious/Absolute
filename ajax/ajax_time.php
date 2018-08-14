<?php
  require '../session.php';

  if ( isset($_GET['request']) )
  {
    if ( $_GET['request'] === 'get_time' )
    {
      date_default_timezone_set('America/Los_Angeles');
      $Date = date("M dS, Y g:i:s A");

      echo $Date;
    }
    else
    {
      echo ":)";
    }
  }
?>