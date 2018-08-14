<?php
  $con = new mysqli("localhost", "root", "DvkDcU44QPsMnVsxDDKdcW", "absolute");

  /* check connection */
  if ( $con->connect_errno ) {
    die("<b>Connection failed:</b> \n" . $con->connect_error . "<br /><b>This is not good.</b>");
  }
?>
