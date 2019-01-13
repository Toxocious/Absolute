<?php
  /**
   * If the client isn't on HTTPS, redirect them to HTTPS.
   */
  //{
  //  if ( $_SERVER['HTTPS'] != 'on' )
  //  {
	//		$redirect = "https://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	//		header("Location: $redirect"); 
	//	} 
  //}
  
  /**
   * Determine the current power level of the user.
   * !! Should move this to the user class file once it's made. (/core/classes/user.php)
   */
  function checkUserPower($User_Power, $Required_Power)
  {
    if ( $User_Power < $Required_Power )
    {
      exit("<div class='content'><div class='head'>Unauthorized Access</div><div class='box'>You do not have the appropriate power to access this page.</div></div>");
    }
  }

  /**
   * Janky Pagination handler.
   * !! Fix this later, whenever necessary or bored.
   */
  function pagination($QueryText, $User_ID, $inputs, $page, $link, $limit = 30)
  {
    global $PDO;

    try {
      $TotalQuery = $PDO->prepare($QueryText);
      $TotalQuery->execute($inputs);
      $Total = $TotalQuery->fetchColumn();
    } catch (PDOException $e) {
      echo $e->getMessage();
    }

    $adjacents = 3;
    $text = '';

    // How many pages will there be
    $pages = ceil($Total / $limit);

    // What page are we currently on?
    if ( $page == 0 )
      $page = 1;

    // First Page, Previous Page, Next Page, and Last Page Links.
    $link_first = ($page != 1) ? "<div style='width: 10%;'><a href='javascript:void(0);' onclick=\"updateBox(1);\">First</a></div>": "<div style='width: 10%;'><span class='disabled'>First</span></div>";
    $link_previous = ($page > 1) ? "<div style='width: 10%;'><a href='javascript:void(0);' onclick=\"updateBox(" . ($page - 1) . ");\">Previous</a></div>": "<div style='width: 10%;'><span class='disabled'>Previous</span></div>";
    $link_next = ($page < $pages) ? "<div style='width: 10%;'><a href='javascript:void(0);' onclick=\"updateBox(" . ($page + 1) . ");\">Next</a></div>" : "<div style='width: 10%;'><span class='disabled'>Next</span></div>";
    $link_last = ($page != $pages) ? "<div style='width: 10%;'><a href='javascript:void(0);' onclick=\"updateBox(" . $pages . ");\">Last</a></div>" : "<div style='width: 10%;'><span class='disabled'>Last</span></div>";

    for ( $x = ($page - $adjacents); $x < (($page + $adjacents) + 1); $x++ )
    {
      if ( ($x > 0) && ($x <= $pages) )
      {
        if ($x == $page)
            $text .= "<div style='width: calc(60% / $pages);'><b style='display: block;'>$x</b></div>";
        else
            $text .= "<div style='width: calc(60% / $pages);'><a style='display: block;' href='javascript:void(0);' onclick=\"updateBox('$x');\">$x</a></div>";
      }
    }

    echo "
      <div class='pagi'>
        $link_first $link_previous $text $link_next $link_last
      </div>
    ";
  }

  /* ==========================================================================================================================================
                                                       Deal with usernames ending in 's'.
  ========================================================================================================================================== */
  function formatName($name)
  {
    $lastchar = substr($name, -1);
    if ( $lastchar == 's' )
      str_replace;
  }

  /**
   * Last seen functions.
   * Converts unix timestamp to a readable format.
   */
  function lastseen($ts, $totimestamp = '')
  {
    $getseconds = time() - $ts;

    if ($totimestamp == 'hour' && $getseconds > 3600) {
      $lastseen = date("F j, Y (g:i A)", $ts);
    } elseif ($totimestamp == 'day' && $getseconds > 86400) {
      $lastseen = date("F j, Y (g:i A)", $ts);
    } elseif ($totimestamp == 'week' && $getseconds > 604800) {
      $lastseen = date("F j, Y (g:i A)", $ts);
    } elseif ($totimestamp == 'month' && $getseconds > 2419200) {
      $lastseen = date("F j, Y (g:i A)", $ts);
    } elseif ($totimestamp == 'year' && $getseconds > 29030400) {
      $lastseen = date("F j, Y (g:i A)", $ts);
    } else {
      if ($getseconds <= 59) {
        $lastseen = "".$getseconds." Second(s) Ago";
      } elseif ($getseconds >= 60 && $getseconds <= 3599) {
        $minutes = floor($getseconds / 60);
        $lastseen = "".$minutes." Minute(s) Ago";
      } elseif ($getseconds >= 3600 && $getseconds <= 86399) {
        $hours = floor($getseconds / 3600);
        $lastseen = "".$hours." Hour(s) Ago";
      } elseif ($getseconds >= 86400 && $getseconds <= 604799) {
        $days = floor($getseconds / 86400);
        $lastseen = "".$days." Day(s) Ago";
      } elseif ($getseconds >= 604800 && $getseconds <= 2419199) {
        $weeks = floor($getseconds / 604800);
        $lastseen = "".$weeks." Week(s) Ago";
      } elseif ($getseconds >= 2419200 && $getseconds <= 29030399) {
        $months = floor($getseconds / 2419200);
        $lastseen = "".$months." Month(s) Ago";
      } elseif ($getseconds > 365 * 86400 * 10) {
        $years = floor($getseconds / 29030400);
        $lastseen = "".$years." Year(s) Ago";
      } else {
        $lastseen = "Never";
      }
    }

    return $lastseen;
  }