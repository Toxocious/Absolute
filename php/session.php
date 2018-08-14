<?php
  // Establishing Connection with Server by passing server_name, user_id and password as a parameter
  $con = mysqli_connect("localhost", "root", "DvkDcU44QPsMnVsxDDKdcW", "absolute");
  $db = mysqli_select_db($con, "absolute");
  
  // Starting Session
  session_start();

  // Storing Session
  $user_check = $_SESSION['login_user'];

  // SQL Query To Fetch Complete Information Of User
  $ses_sql = mysqli_query($con, "SELECT * FROM members WHERE Username = '" . $user_check . "' ");
  $row = mysqli_fetch_assoc($ses_sql);
  $login_session = $row['Username'];

  $User_Data = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM members WHERE id = '" . $row['id'] . "'"));

  #Fetch the current URL.
  $Fetch_URL = "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
  
	# If the $login_session variable isn't set..
  if ( !isset($login_session) ) {
		# Close the connection.
    mysqli_close( $con );
		
		# Redirect the user to 'index.php'.
    header( 'Location: index.php' );
  }
?>