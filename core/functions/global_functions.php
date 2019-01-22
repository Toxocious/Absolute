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
   * Janky Pagination handler.
   * !! Fix this later, whenever necessary or bored.
   */
  function pagination($Query_Text, $User_ID, $inputs, $page, $link, $limit = 30)
  {
    global $PDO;

    try
    {
      $Query_Total = $PDO->prepare($Query_Text);
      $Query_Total->execute($inputs);
      $Total = $Query_Total->fetchColumn();
    }
    catch ( PDOException $e )
    {
      HandleError( $e->getMessage() );
    }

    $adjacents = 3;
    $text = '';

    // How many pages will there be
    $pages = ceil($Total / $limit);

    // What page are we currently on?
    if ( $page == 0 )
    {
      $page = 1;
    }

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
        {
          $text .= "<div style='width: calc(60% / $pages);'><b style='display: block;'>$x</b></div>";
        }
        else
        {
          $text .= "<div style='width: calc(60% / $pages);'><a style='display: block;' href='javascript:void(0);' onclick=\"updateBox('$x');\">$x</a></div>";
        }
      }
    }

    echo "
      <div class='pagi'>
        $link_first $link_previous $text $link_next $link_last
      </div>
    ";
  }