<?php
  /**
   * Used to handle the processing and displayment of content
   * that is loaded via AJAX across multiple pages.
   */
  function Pagination
  (
    string $SQL_Query,
    array $SQL_Parameters,
    int $User_ID,
    int $Current_Page,
    int $Display_Limit,
    int $Colspan = 3,
    string $Onclick_Link = null
  )
  {
    global $PDO;

    $SQL_Query = Purify($SQL_Query);
    $SQL_Parameters = Purify($SQL_Parameters);
    $User_ID = Purify($User_ID);
    $Current_Page = Purify($Current_Page);
    $Display_Limit = Purify($Display_Limit);
    $Colspan = $Colspan > 0 ? Purify($Colspan) : $Colspan = 3;

    try
    {
      $Page_Prepare = $PDO->prepare($SQL_Query);
      $Page_Prepare->execute($SQL_Parameters);
      $Total_Results = $Page_Prepare->fetchColumn();
    }
    catch ( PDOException $e )
    {
      HandleError( $e->getMessage() );
    }

    $Total_Pages = ceil($Total_Results / $Display_Limit);

    if ( $Current_Page < 1 )
      $Current_Page = 1;

    $Links = [
      'Next' => '',
      'Previous' => '',
      'Pages' => '',
    ];

    /**
     * Display the proper element to go back to page one.
     */
    if ( $Current_Page !== 1 )
    {
      $Links['Previous'] .= "
        <td colspan='{$Colspan}' style='width: calc(100% / 7);'>
          <a href='javascript:void(0);' onclick='Update_Box(1, {$User_ID});'>
            &lt;&lt;
          </a>
        </td>
      ";
    }
    else
    {
      $Links['Previous'] .= "
        <td colspan='{$Colspan}' style='width: calc(100% / 7);'>
          <span>
            &lt;&lt;
          </span>
        </td>
      ";
    }

    /**
     * Display the proper element to go back a single page.
     */
    if ( $Current_Page > 1 )
    {
      $Links['Previous'] .= "
        <td colspan='{$Colspan}' style='width: calc(100% / 7);'>
          <a href='javascript:void(0);' onclick='Update_Box(" . ( $Current_Page - 1 ) . ", {$User_ID});'>
            &lt;
          </a>
        </td>
      ";
    }
    else
    {
      $Links['Previous'] .= "
        <td colspan='{$Colspan}' style='width: calc(100% / 7);'>
          <span>
            &lt;
          </span>
        </td>
      ";
    }

    /**
     * Display the proper element to go forward a single page.
     */
    if ( $Current_Page < $Total_Pages )
    {
      $Links['Next'] .= "
        <td colspan='{$Colspan}' style='width: calc(100% / 7);'>
          <a href='javascript:void(0);' onclick='Update_Box(" . ( $Current_Page + 1 ) . ", {$User_ID});'>
            &gt;
          </a>
        </td>
      ";
    }
    else
    {
      $Links['Next'] .= "
        <td colspan='{$Colspan}' style='width: calc(100% / 7);'>
          <span>
            &gt;
          </span>
        </td>
      ";
    }

    /**
     * Display the proper element to go to the last page.
     */
    if ( $Current_Page != $Total_Pages )
    {
      $Links['Next'] .= "
        <td colspan='{$Colspan}' style='width: calc(100% / 7);'>
          <a href='javascript:void(0);' onclick='Update_Box({$Total_Pages}, {$User_ID});'>
            &gt;&gt;
          </a>
        </td>
      ";
    }
    else
    {
      $Links['Next'] .= "
        <td colspan='{$Colspan}' style='width: calc(100% / 7);'>
          <span>
            &gt;&gt;
          </span>
        </td>
      ";
    }

    /**
     * Determine which three page numbers to display to the user.
     */
    if ( $Total_Pages == 1 )
    {
      $Links['Pages'] .= "
        <td colspan='{$Colspan}' style='width: calc(100% / 7);'>

        </td>
        <td colspan='{$Colspan}' style='width: calc(100% / 7);'>
          <b>
            1
          </b>
        </td>
        <td colspan='{$Colspan}' style='width: calc(100% / 7);'>

        </td>
      ";
    }
    else
    {
      if ( $Current_Page == 1 )
      {
        $Links['Pages'] .= "
          <td colspan='{$Colspan}' style='width: calc(100% / 7);'>
            
          </td>
        ";
      }

      for ( $x = ( $Current_Page - 1 ); $x < ( ( $Current_Page + 1 ) + 1 ); $x++ )
      {
        if ( ( $x > 0 ) && ( $x <= $Total_Pages ) )
        {
          if ( $x == $Current_Page )
          {
            $Links['Pages'] .= "
              <td colspan='{$Colspan}' style='width: calc(100% / 7);'>
                <b>
                  {$x}
                </b>
              </td>
            ";
          }
          else
          {
            $Links['Pages'] .= "
              <td colspan='{$Colspan}' style='width: calc(100% / 7);'>
                <a href='javascript:void(0);' onclick='Update_Box({$x}, {$User_ID});'>
                  {$x}
                </a>
              </td>
            ";
          }
        }
      }

      if ( $Current_Page == $Total_Pages )
      {
        $Links['Pages'] .= "
          <td colspan='{$Colspan}' style='width: calc(100% / 7);'>
            
          </td>
        ";
      }
    }

    /**
     * Display the pages to the user.
     */
    echo "
      <tr data-current-page='{$Current_Page}' data-total-pages='{$Total_Pages}' >
        {$Links['Previous']}
        {$Links['Pages']}
        {$Links['Next']}
      </tr>
    ";
  }

  
  



  function Pagi($Query, $User, $Parameters, $Page, $Link, $Limit)
  {
    global $PDO;

    try
    {
      $Prepare = $PDO->prepare($Query);
      $Prepare->execute($Parameters);
      $Total = $Prepare->fetchColumn();
    }
    catch ( PDOException $e )
    {
      HandleError( $e->getMessage() );
		}

		$Pages = ceil($Total / $Limit);

    if ( $Page == 0 )
    {
      $Page = 1;
    }
    
    /**
     * Render pagination navigation links.
     */
		$Adjacent = 1;
    $Link_Previous = '';
    $Link_Next = '';
    $Text = '';

    if ( $Page != 1 )
    {
      $Link_Previous .= "<div style='flex-basis: 10%;'><a href='javascript:void(0);' onclick='Update_Box(1, " . $User . ");'> << </a></div>";
    }
    else
    {
      $Link_Previous .= "<div style='flex-basis: 10%;'><span> << </span></div>";
    }

    if ( $Page > 1 )
    {
      $Link_Previous .= "<div style='flex-basis: 10%;'><a href='javascript:void(0);' onclick='Update_Box(" . ( $Page - 1 ) . ", " . $User . ");'> < </a></div>";
    }
    else
    {
      $Link_Previous .= "<div style='flex-basis: 10%;'><span> < </span></div>";
    }

    if ( $Page < $Pages )
    {
      $Link_Next .= "<div style='flex-basis: 10%;'><a href='javascript:void(0);' onclick='Update_Box(" . ( $Page + 1 ) . ", " . $User . ");'> > </a></div>";
    }
    else
    {
      $Link_Next .= "<div style='flex-basis: 10%;'><span> > </span></div>";
    }

    if ( $Page != $Pages )
    {
      $Link_Next .= "<div style='flex-basis: 10%;'><a href='javascript:void(0);' onclick='Update_Box(" . $Pages . ", " . $User . ");'> >> </a></div>";
    }
    else
    {
      $Link_Next .= "<div style='flex-basis: 10%;'><span> >> </span></div>";
		}

    for ( $x = ( $Page - $Adjacent ); $x < ( ( $Page + $Adjacent ) + 1 ); $x++ )
    {
      if ( ( $x > 0 ) && ( $x <= $Pages ) )
      {
				if ( $Page == 1 && $Pages == 1 )
				{
					$Width = '60%';
				}
				else if ( $Page == 1 || $Page == $Pages )
				{
					$Width = '30%';
				}
				else
				{
					$Width = '20%;';
				}

        if ( $x == $Page )
        {
          $Text .= "<div style='flex-basis: {$Width}'><b style='display: block;'>$x</b></div>";
        }
        else
        {
          $Text .= "<div style='flex-basis: {$Width}'><a style='display: block;' href='javascript:void(0);' onclick=\"Update_Box($x, $User);\">$x</a></div>";
        }
			}
    }

    /**
     * Echo the pagination navigation bar.
     */
		echo "
      <div class='pagi flex'>
        {$Link_Previous} {$Text} {$Link_Next}
      </div>
    ";
  }