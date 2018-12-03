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
  
  /* ==========================================================================================================================================
                                                      HANDLE DISPLAYING SPRITES AND ICONS
  ========================================================================================================================================== */
  function showImage($type, $id, $table, $option)
  {
    $con = new mysqli("localhost", "root", "DvkDcU44QPsMnVsxDDKdcW", "absolute");
    
    $Pokemon = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM $table WHERE `ID` = '" . $id . "' LIMIT 1"));

    if ( $table !== 'pokedex' )
    {
      $Pokedex_Data = mysqli_fetch_assoc(mysqli_query($con, "SELECT `Name` FROM `pokedex` WHERE `ID` = '" . $Pokemon['Pokedex_ID'] . "' LIMIT 1 "));

      if ( $Pokemon['Type'] == 'Normal' )
        $Dir_Type = "1 - {$Pokemon['Type']}";
      else if ( $Pokemon['Type'] == 'Shiny' )
        $Dir_Type = "2 - {$Pokemon['Type']}";
      else if ( $Pokemon['Type'] == 'Sunset' )
        $Dir_Type = "3 - {$Pokemon['Type']}";
      else if ( $Pokemon['Type'] == 'Shiny Sunset' )
        $Dir_Type = "4 - {$Pokemon['Type']}";
      else
        $Dir_Type = "5 - {$Pokemon['Type']}";

      if ( $Pokemon['Pokedex_ID'] <= 151 )
        $Slot_Gen = 'Generation 1';
      else if ( $Pokemon['Pokedex_ID'] <= 251 && $Pokemon['Pokedex_ID'] >= 152 )
        $Slot_Gen = 'Generation 2';
      else if ( $Pokemon['Pokedex_ID'] <= 386 && $Pokemon['Pokedex_ID'] >= 252 )
        $Slot_Gen = 'Generation 3';
      else if ( $Pokemon['Pokedex_ID'] <= 493 && $Pokemon['Pokedex_ID'] >= 387 )
        $Slot_Gen = 'Generation 4';
      else if ( $Pokemon['Pokedex_ID'] <= 649 && $Pokemon['Pokedex_ID'] >= 494 )
        $Slot_Gen = 'Generation 5';
      else if ( $Pokemon['Pokedex_ID'] <= 721 && $Pokemon['Pokedex_ID'] >= 650 )
        $Slot_Gen = 'Generation 6';
      else
        $Slot_Gen = 'Generation 7';

      if ( $Pokemon['Pokedex_ID'] <= 151 )
        $Slot_Gen = 'Generation 1';
      else if ( $Pokemon['Pokedex_ID'] <= 251 && $Pokemon['Pokedex_ID'] >= 152 )
        $Slot_Gen = 'Generation 2';
      else if ( $Pokemon['Pokedex_ID'] <= 386 && $Pokemon['Pokedex_ID'] >= 252 )
        $Slot_Gen = 'Generation 3';
      else if ( $Pokemon['Pokedex_ID'] <= 493 && $Pokemon['Pokedex_ID'] >= 387 )
        $Slot_Gen = 'Generation 4';
      else if ( $Pokemon['Pokedex_ID'] <= 649 && $Pokemon['Pokedex_ID'] >= 494 )
        $Slot_Gen = 'Generation 5';
      else if ( $Pokemon['Pokedex_ID'] <= 721 && $Pokemon['Pokedex_ID'] >= 650 )
        $Slot_Gen = 'Generation 6';
      else
        $Slot_Gen = 'Generation 7';

      if ( strpos($Pokedex_Data['Name'], '(Mega)') )
      {
        $Slot_Gen = 'Mega';
        $Slot_pID = substr($Pokemon['Pokedex_ID'], 0, -1);
        $Slot_pID .= '-mega';
      }
      else
      {
        $Slot_pID = $Pokemon['Pokedex_ID'];
      }
    }

    else if ( $table === 'pokedex' )
    {
      $Dir_Type = "1 - Normal";

      if ( $Pokemon['ID'] <= 151 )
        $Slot_Gen = 'Generation 1';
      else if ( $Pokemon['ID'] <= 251 && $Pokemon['ID'] >= 152 )
        $Slot_Gen = 'Generation 2';
      else if ( $Pokemon['ID'] <= 386 && $Pokemon['ID'] >= 252 )
        $Slot_Gen = 'Generation 3';
      else if ( $Pokemon['ID'] <= 493 && $Pokemon['ID'] >= 387 )
        $Slot_Gen = 'Generation 4';
      else if ( $Pokemon['ID'] <= 649 && $Pokemon['ID'] >= 494 )
        $Slot_Gen = 'Generation 5';
      else if ( $Pokemon['ID'] <= 721 && $Pokemon['ID'] >= 650 )
        $Slot_Gen = 'Generation 6';
      else
        $Slot_Gen = 'Generation 7';

      if ( strpos($Pokemon['Name'], '(Mega)') )
      {
        $Slot_Gen = 'Mega';
        $Slot_pID = substr($Pokemon['ID'], 0, -1);
        $Slot_pID .= '-mega';
      }
      else
      {
        $Slot_pID = $Pokemon['ID'];
      }
    }

    else if ( $table === 'promo' )
    {
      if ( $Pokemon['Type'] == 'Normal' )
        $Dir_Type = "1 - {$Pokemon['Type']}";
      else if ( $Pokemon['Type'] == 'Shiny' )
        $Dir_Type = "2 - {$Pokemon['Type']}";
      else if ( $Pokemon['Type'] == 'Sunset' )
        $Dir_Type = "3 - {$Pokemon['Type']}";
      else if ( $Pokemon['Type'] == 'Shiny Sunset' )
        $Dir_Type = "4 - {$Pokemon['Type']}";
      else
        $Dir_Type = "5 - {$Pokemon['Type']}";

      if ( $Pokemon['ID'] <= 151 )
        $Slot_Gen = 'Generation 1';
      else if ( $Pokemon['ID'] <= 251 && $Pokemon['ID'] >= 152 )
        $Slot_Gen = 'Generation 2';
      else if ( $Pokemon['ID'] <= 386 && $Pokemon['ID'] >= 252 )
        $Slot_Gen = 'Generation 3';
      else if ( $Pokemon['ID'] <= 493 && $Pokemon['ID'] >= 387 )
        $Slot_Gen = 'Generation 4';
      else if ( $Pokemon['ID'] <= 649 && $Pokemon['ID'] >= 494 )
        $Slot_Gen = 'Generation 5';
      else if ( $Pokemon['ID'] <= 721 && $Pokemon['ID'] >= 650 )
        $Slot_Gen = 'Generation 6';
      else
        $Slot_Gen = 'Generation 7';

      if ( strpos($Pokemon['Name'], '(Mega)') )
      {
        $Slot_Gen = 'Mega';
        $Slot_pID = substr($Pokemon['ID'], 0, -1);
        $Slot_pID .= '-mega';
      }
      else
      {
        $Slot_pID = $Pokemon['ID'];
      }
    }

    switch ($table)
    {
      case 'pokemon':
        switch ($type)
        {
          case 'icon':
            if ( $option === 'Stats' )
            {
              $icon = "<img src='" . Domain(1) . "/images/Icons/{$Dir_Type}/{$Slot_Gen}/{$Slot_pID}.png' onclick='showData(\"pokecenter\", \"Stats\", \"{$Pokemon['ID']}\");' />";
            }
            else if ( $option === 'blank' )
            {
              $icon = "<img src='" . Domain(1) . "/images/Icons/{$Dir_Type}/{$Slot_Gen}/{$Slot_pID}.png' />";
            }
            else
            {
              $icon = "<img class='popup cboxElement' src='" . Domain(1) . "/images/Icons/{$Dir_Type}/{$Slot_Gen}/{$Slot_pID}.png' href='ajax/ajax_pokemon.php?id={$Pokemon['ID']}' />";
            }

            if ( $Pokemon['Name'] === 'Egg' )
              $icon = "<img src='" . Domain(1) . "/images/Pokemon/egg.png' style='height: 40px; width: 40px;' />";

            echo $icon;
            break;

          case 'sprite':
            $sprite = "<img src='" . Domain(1) . "/images/Pokemon/Version {$Pokemon['Sprite_Version']}/{$Dir_Type}/{$Slot_Gen}/{$Slot_pID}.gif' />";
            return $sprite;
            break;
        }

      case 'promo':
        switch ($type)
        {
          case 'sprite':
            $sprite = "<img src='" . Domain(1) . "/images/Pokemon/Version {$Pokemon['Sprite_Version']}/{$Dir_Type}/{$Slot_Gen}/{$Slot_pID}.gif' />";
            echo $sprite;
            return $sprite;
            break;
        }

      case 'pokedex':
        switch ($type)
        {
          case 'sprite':
            $sprite = "<img src='" . Domain(1) . "/images/Pokemon/Version 6/1 - Normal/{$Slot_Gen}/{$Slot_pID}.gif' />";
            echo $sprite;
            break;
        }
    }
  }

  /* ==========================================================================================================================================
                                                              SHOW ROSTER
  ========================================================================================================================================== */
  function showRoster($User_ID, $Display, $Option)
  {
    global $PDO;

    $con = new mysqli("localhost", "root", "DvkDcU44QPsMnVsxDDKdcW", "absolute");

    switch ($Display)
    {
      case 'Icons':
        for ($i = 1 ; $i <= 6 ; $i++) {
          $Pokemon_Data = mysqli_query($con, "SELECT * FROM pokemon WHERE Owner_Current = '" . $User_ID . "' AND Slot = $i");
          $Slot[$i] = mysqli_fetch_assoc($Pokemon_Data);
          
          if ( $Slot[$i] ) {
            $Pokedex_Data = mysqli_fetch_assoc(mysqli_query($con, "SELECT `Name` FROM `pokedex` WHERE `ID` = '" . $Slot[$i]["Pokedex_ID"] . "'"));
            $Item_Data = mysqli_fetch_assoc(mysqli_query($con, "SELECT Item_Name FROM items_owned WHERE Equipped_To = '" . $Slot[$i]['ID'] . "'"));
            $Slot[$i]['Name'] = $Pokedex_Data['Name'];
          } else {
            $Slot[$i] = 'Empty';
          }

          if ( $Slot[$i] != 'Empty' ) {
            if ( $Slot[$i]['Item'] != '0' )
              $Equipped_Item = "<img class='item' src='" . Domain(1) . "/images/Items/{$Item_Data['Item_Name']}.png' />";
            else
              $Equipped_Item = null;

            showImage('icon', $Slot[$i]['ID'], 'pokemon', 'item');
          } else {
            echo "
              <img src='" . Domain(1) . "/images/Assets/pokeball.png' style='width: 30px; margin-left: 8px; height: 30px;' />
            ";
          }
        }
        break;

      case 'Userbar':
        for ($i = 1 ; $i <= 6 ; $i++) {
          $Pokemon_Data = mysqli_query($con, "SELECT * FROM pokemon WHERE Owner_Current = '" . $User_ID . "' AND Slot = $i");
          $Slot[$i] = mysqli_fetch_assoc($Pokemon_Data);
          
          if ( $Slot[$i] ) {
            $Pokedex_Data = mysqli_fetch_assoc(mysqli_query($con, "SELECT `Name` FROM `pokedex` WHERE `ID` = '" . $Slot[$i]["Pokedex_ID"] . "'"));
            $Item_Data = mysqli_fetch_assoc(mysqli_query($con, "SELECT Item_Name FROM items_owned WHERE Equipped_To = '" . $Slot[$i]['ID'] . "'"));
            $Slot[$i]['Name'] = $Pokedex_Data['Name'];
          } else {
            $Slot[$i] = 'Empty';
          }

          if ( $Slot[$i] != 'Empty' ) {
            if ( $Slot[$i]['Item'] != '0' )
              $Equipped_Item = "<img class='item' src='" . Domain(1) . "/images/Items/{$Item_Data['Item_Name']}.png' />";
            else
              $Equipped_Item = null;

            echo "
              <div class='roster_slot' onmouseover='showSlot({$i});' onmouseout='hideSlot({$i});'>
                <div class='roster_mini'>
            ";

            showImage('icon', $Slot[$i]['ID'], 'pokemon', null);

            echo "
                </div>
                <div class='roster_tooltip' id='rosterTooltip{$i}'>
                  " . $Equipped_Item . "
                  " . showImage('sprite', $Slot[$i]['ID'], 'pokemon', null) . "
                  <img class='gender' style='margin-top: 3px;' src='" . Domain(1) . "/images/Assets/{$Slot[$i]['Gender']}.svg' />
                  <br />
                  <b>
            ";

            if ( $Slot[$i]['Type'] !== "Normal" )
              echo $Slot[$i]['Type'];

            echo "{$Slot[$i]['Name']}</b>
                  <br />
                  <div class='info'>
                    <div>Level</div>
                    <div>" . number_format($Slot[$i]['Level']) . "</div>
                  </div>
                  <div class='info'>
                    <div>Experience</div>
                    <div>" . number_format($Slot[$i]['Experience']) . "</div>
                  </div>
                </div>
              </div>
            ";
          } else {
            echo "
              <div class='roster_slot' onmouseover='showSlot({$i});' onmouseout='hideSlot({$i});'>
                <div class='roster_mini' style='padding: 0px 5px;'>
                  <img src='" . Domain(1) . "/images/Assets/pokeball.png' style='width: 30px; height: 30px;' />
                </div>
                <div class='roster_tooltip' style='padding: 38px 0px;' id='rosterTooltip{$i}'>
                  <img src='" . Domain(1) . "/images/Assets/pokeball.png' /><br />
                  <b>Empty</b><br />
                </div>
              </div>
            ";
          }
        }
        break;

      case 'Profile':
        $con = new mysqli("localhost", "root", "DvkDcU44QPsMnVsxDDKdcW", "absolute");

        for ( $i = 1; $i <= 6; $i++ )
        {
          $Roster_Data = mysqli_query($con, "SELECT * FROM pokemon WHERE Owner_Current = '" . $User_ID . "' AND slot = $i");
          $Slot_Data[$i] = mysqli_fetch_assoc($Roster_Data);

          if ($Slot_Data[$i]) {
            $name = mysqli_fetch_assoc(mysqli_query($con, "SELECT `Name` FROM `pokedex` WHERE `ID` = '" . $Slot_Data[$i]["Pokedex_ID"] . "'"));
            $Slot_Data[$i]["Name"] = $name["Name"];
            $item = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM `items_owned` WHERE `ID` = '" . $Slot_Data[$i]["Item"] . "'"));
            $Slot_Data[$i]["Item"] = $item["Item_Name"];
          } else {
            $Slot_Data[$i] = "Empty" ;
          }

          if ( $Slot_Data[$i] != "Empty" ) {
            echo 	"<div class='roster_slot' style='min-height: 200px;'>";

            echo  "<div>";
            echo	"<img class='gender' src='" . Domain(1) . "/images/Assets/{$Slot_Data[$i]['Gender']}.svg' style='float: left; height: 20px; margin-top: 5px; width: 20px;' />";
            if ( $Slot_Data[$i]['Item'] != '' ) {
              echo		"<img class='item' src='" . Domain(1) . "/images/Items/" . $Slot_Data[$i]['Item'] . ".png' style='float: left;/*position: absolute; margin-left: -45px; margin-top: 5px;*/' />";
            }
            echo  "</div>";

            echo  "<div style='height: 90px; margin-bottom: 5px; margin-right: 20px;'>";
            echo showImage('sprite', $Slot_Data[$i]['ID'], 'pokemon', null);
            echo  "</div>";
            
            if ( $Slot_Data[$i]['Type'] != "Normal" ) {
              echo "<b>" . $Slot_Data[$i]['Type'] . $Slot_Data[$i]['Name'] . "</b><br />";								
            }
            else {
              echo "<b>" . $Slot_Data[$i]['Name'] . "</b><br />";
            }

            echo	"<div class='info'>";
            echo 		"<div><b>Level</b></div>";
            echo 		"<div>" . number_format($Slot_Data[$i]['Level']) . "</div>";
            echo	"</div>";
            echo	"<div class='info'>";
            echo 		"<div><b>Experience</b></div>";
            echo 		"<div>" . number_format($Slot_Data[$i]['Experience']) . "</div>";
            echo	"</div>";

            echo 	"</div>";
          } else {
            echo "<div class='roster_slot' style='float: left; min-height: 200px; padding: 40px;'>";
            echo		"<img src='" . Domain(1) . "/images/Assets/pokeball.png' /><br />";
            echo		"Empty";
            echo "</div>";
          }
        }
        break;

      case 'Pokecenter':
        //$con = new mysqli("localhost", "root", "DvkDcU44QPsMnVsxDDKdcW", "absolute");
        try {
          $Roster_Query = $PDO->prepare("SELECT * FROM `pokemon` WHERE `Owner_Current` = ? AND `Slot` < 7 ORDER BY `Slot`");
          $Roster_Query->execute([$User_ID]);
          $Roster_Query->setFetchMode(PDO::FETCH_ASSOC);
          $Roster_Pokemon = $Roster_Query->fetchAll();
        } catch (PDOException $e) {
          echo $e->getMessage();
        }

        //foreach ( $Roster_Pokemon as $Index => $Pokemon )
        //{
        //  echo "
        //    <div class='rslot'>
        //  ";
        //  //var_dump($Pokemon);
        //
        //  showImage('icon', $Pokemon['ID'], 'pokemon', null);
        //  
        //  echo "
        //    </div>
        //  ";
        //}

        for ( $i = 1; $i <= 6; $i++ )
        {
          $Slot[$i] = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM pokemon WHERE Owner_Current = '" . $User_ID . "' AND Slot = $i"));
          
          # Determine whether or not the slot is empty.
          if ( $Slot[$i] ) {
            $Name = mysqli_fetch_assoc(mysqli_query($con, "SELECT Name FROM pokedex WHERE ID = '" . $Slot[$i]["Pokedex_ID"] . "'"));
            $Slot[$i]['Name'] = $Name['Name'];
          }
          else {
            $Slot[$i] = "Empty";
          }
          
          # If the slot IS NOT empty.
          if ( $Slot[$i] !== "Empty" )
          {
            if ( $Option == 'Box' )
            {
              $Onclick = "onclick=\"showData('pokecenter', 'Stats', '{$Slot[$i]['ID']}');\"";
            }
            else if ( $Option == 'Bag' )
            {
              $Onclick = "";
            }
            else if ( $Option == 'Nickname' )
            {
              $Onclick = "onclick=\"showData('pokecenter', 'Nick', '{$Slot[$i]['ID']}');\"";
            }
        
            $Item = mysqli_fetch_assoc(mysqli_query($con, "SELECT Item_Name FROM items_owned WHERE Equipped_To = '" . $Slot[$i]['ID'] . "'"));
            if ( $Slot[$i]['Type'] != "Normal" )
            {
              $Pokemon = "<b>" . $Slot[$i]['Type'] . $Slot[$i]['Name'] . "</b><br />";								
            }
            else
            {
              $Pokemon = "<b>" . $Slot[$i]['Name'] . "</b><br />";
            }
            
            #echo  "<div class='rslot' style='border-bottom: 1px solid #4A618F; border-right: 1px solid #4A618F; float: left; width: 50%;' " . $Onclick . ">";
            echo  "<div class='rslot' style='float: left; min-height: 200px; width: calc(100% / 3);' " . $Onclick . ">";
            echo    "<div style='padding-top: 5px; height: 99px;'>";
            // testing
            echo      "
                        <div style='float: left;'>
                          <div style='background: #334364; border-right: 1px solid #4A618F; height: calc(129px / 3); margin-top: -5px;'><a href='javascript:void(0);' onclick='changeSlot();' style='display: block; padding: 10px;'>1</a></div>
                          <div style='background: #425780; border-right: 1px solid #4A618F; height: calc(129px / 3); margin-top: -5px;'><a href='javascript:void(0);' onclick='changeSlot();' style='display: block; padding: 10px;'>2</a></div>
                          <div style='background: #334364; border-right: 1px solid #4A618F; height: calc(129px / 3); margin-top: -5px;'><a href='javascript:void(0);' onclick='changeSlot();' style='display: block; padding: 10px;'>3</a></div>
                        </div>
                      ";
            // -testing
            echo      showImage('sprite', $Slot[$i]['ID'], 'pokemon', null);
            // testing
            echo      "
                        <div style='float: right;'>
                        <div style='background: #334364; border-left: 1px solid #4A618F; height: calc(129px / 3); margin-top: -5px;'><a href='javascript:void(0);' onclick='changeSlot();' style='display: block; padding: 10px;'>4</a></div>
                        <div style='background: #425780; border-left: 1px solid #4A618F; height: calc(129px / 3); margin-top: -5px;'><a href='javascript:void(0);' onclick='changeSlot();' style='display: block; padding: 10px;'>5</a></div>
                        <div style='background: #334364; border-left: 1px solid #4A618F; height: calc(129px / 3); margin-top: -5px;'><a href='javascript:void(0);' onclick='changeSlot();' style='display: block; padding: 10px;'>6</a></div>
                        </div>
                      ";
            // -testing
            echo    "</div>";
            echo    "<div>";
            echo      "{$Pokemon}";
            echo    "</div>";
            echo    "<div style='background: #4A618F;'>";
            echo      "<b>Level</b>";
            echo    "</div>";
            echo    "<div>";
            echo      number_format($Slot[$i]['Level']);
            echo    "</div>";
            echo    "<div style='background: #4A618F;'>";
            echo      "<b>Experience</b>";
            echo    "</div>";
            echo    "<div>";
            echo      number_format($Slot[$i]['Experience']);
            echo    "</div>";
            echo  "</div>";
          } 
          # Else the slot IS empty.
          else {
            echo  "<div class='rslot' style='float: left; width: 50%;'>";
            echo    "<span style='float: left;'>";
            echo		  "<img src='" . Domain(1) . "/images/Assets/pokeball.png' style='height: 40px; width: 40px;' />";
            echo    "</span>";
            echo    "<div style='padding-top: 10px;'>";
            echo      "<b>Empty</b>";
            echo    "</div>";
            echo  "</div>";
          }
        }
        break;
    }
  }

  /* ==========================================================================================================================================
                                                        GENERATE EGGS FOR THE LAB
  ========================================================================================================================================== */
  function generateEgg()
  {
    //require 'db.php';
    $con = new mysqli("localhost", "root", "DvkDcU44QPsMnVsxDDKdcW", "absolute");
    $con2 = new mysqli("localhost", "root", "DvkDcU44QPsMnVsxDDKdcW", "absolute");

    $Commons = array('Wishiwashi (Solo)', 'Sunkern', 'Kricketot', 'Caterpie', 'Weedle', 'Wurmple', 'Magikarp', 'Wooper', 'Bounsweet', 'Sentret', 'Poochyena', 'Lotad', 'Seedot', 'Makuhita', 'Bunnelby', 'Zigzagoon', 'Whismur', 'Combee', 'Zubat', 'Starly', 'Spinarak', 'Hoppip', 'Swinub', 'Slugma', 'Bidoof', 'Pidgey', 'Rattata', 'Patrat', 'Yungoos', 'Venipede', 'Spearow', 'Hoothoot', 'Diglett', 'Ledyba', 'Pikipek', 'Dewpider', 'Taillow', 'Wingull', 'Lillipup', 'Tynamo', 'Litwick', 'Mareep', 'Fletchling', 'Slakoth', 'Meditite', 'Roggenrola', 'Purrloin', 'Paras', 'Morelull', 'Ekans', 'Barboach', 'Inkay', 'Helioptile', 'Meowth', 'Pineco', 'Solosis', 'Munna', 'Sandile', 'Tympole', 'Foongus', 'Duskull', 'Blitzle', 'Bellsprout', 'Geodude', 'Remoraid', 'Baltoy', 'Croagunk', 'Klink', 'Grubbin', 'Gulpin', 'Bergmite', 'Venonat', 'Mankey', 'Machop', 'Shellder', 'Timburr', 'Ducklett', 'Vanillite', 'Ferroseed', 'Binacle', 'Corphish', 'Doduo', 'Glameow', 'Sewaddle', 'Psyduck', 'Goldeen', 'Natu', 'Skrelp', 'Sandygast', 'Magnemite', 'Seel', 'Grimer', 'Exeggcute', 'Krabby', 'Dwebble', 'Drowzee', 'Drilbur', 'Stunky', 'Trubbish', 'Voltorb', 'Chinchou', 'Teddiursa', 'Phanpy', 'Spoink', 'Hippopotas', 'Clauncher', 'Snover', 'Tentacool', 'Cacnea', 'Elgyem', 'Crabrawler', 'Koffing', 'Staryu', 'Mienfoo', 'Skiddo', 'Tirtouga');

    $Rares = array('Feebas', 'Burmy', 'Scatterbug', 'Wimpod', 'Togepi', 'Skitty', 'Pidove', 'Nincada', 'Surskit', 'NidoranM', 'NidoranF', 'Cherubi', 'Cottonee', 'Spheal', 'Gothita', 'Horsea', 'Shroomish', 'Electrike', 'Shuppet', 'Sandshrew', 'Poliwag', 'Snubbull', 'Bronzor', 'Yamask', 'Golett', 'Carvanha', 'Numel', 'Cubchoo', 'Shelmet', 'Phantump', 'Abra', 'Gastly', 'Slowpoke', 'Darumaka', 'Karrablast', 'Pansage', 'Pansear', 'Panpour', 'Joltik', 'Oddish', 'Cubone', 'Woobat', 'Delibird', 'Aron', 'Buizel', 'Skorupi', 'Rhyhorn', 'Scraggy', 'Cranidos', 'Shieldon', 'Omanyte', 'Kabuto', 'Lileep', 'Anorith', 'Rufflet', 'Aipom', 'Nosepass', 'Onix', 'Lickitung', 'Mudbray', 'Yanma', 'Wailmer', 'Charjabug', 'Archen', 'Murkrow', 'Eelektrik', 'Ponyta', 'Gligar', 'Togedemaru', 'Tangela', 'Qwilfish', 'Carnivine', 'Girafarig', 'Basculin', 'Maractus', 'Stantler', 'Throh', 'Sawk', 'Torkoal', 'Alomomola', 'Stunfisk', 'Heatmor', 'Durant', 'Relicanth', 'Tauros', 'Miltank', 'Bouffalant', 'Oranguru', 'Passimian', 'Cryogonal');

    $Epics = array('Ralts', 'Fomantis', 'Shinx', 'Petilil', 'Rockruff', 'Riolu', 'Trapinch', 'Vulpix', 'Snorunt', 'Minccino', 'Flabebe', 'Cutiefly', 'Mareanie', 'Swablu', 'Salandit', 'Shellos', 'Honedge', 'Houndour', 'Luvdisc', 'Finneon', 'Zorua', 'Frillish', 'Pumpkaboo', 'Pawniard', 'Stufful', 'Spritzee', 'Swirlix', 'Clamperl', 'Drifloon', 'Pancham', 'Growlithe', 'Buneary', 'Espurr', 'Tyrunt', 'Amaura', 'Litleo', "Farfetch'd", 'Sableye', 'Mawile', 'Porygon', 'Pachirisu', 'Plusle', 'Minun', 'Corsola', 'Pyukumuku', 'Chatot', 'Dunsparce', 'Emolga', 'Sneasel', 'Volbeat', 'Illumise', 'Dedenne', 'Misdreavus', 'Kecleon', 'Audino', 'Zangoose', 'Seviper', 'Lunatone', 'Solrock', 'Tropius', 'Skarmory', 'Klefki', 'Oricorio', 'Bruxish', 'Komala', 'Spiritomb', 'Druddigon', 'Comfey', 'Turtonator', 'Drampa', 'Kangaskhan', 'Sigilyph', 'Scyther', 'Pinsir', 'Heracross', 'Hawlucha', 'Carbink', 'Shuckle', 'Aerodactyl', 'Dhelmise', 'Lapras');

    $Uniques = array("Pichu", "Azurill", "Igglybuff", "Tyrogue", "Cleffa", "Happiny", "Noibat", "Smeargle", "Rattata (Alolan)", "Wynaut", "Diglett (Alolan)", "Budew", "Chingling", "Meowth (Alolan)", "Bonsly", "Vulpix (Alolan)", "Sandshrew (Alolan)", "Geodude (Alolan)", "Dratini", "Larvitar", "Bagon", "Beldum", "Gible", "Deino", "Goomy", "Jangmo-o", "Smoochum", "Mime Jr.", "Axew", "Grimer (Alolan)", "Eevee", "Deerling", "Unown", "Mantyke", "Elekid", "Spinda", "Larvesta", "Magby", "Munchlax", "Castform", "Rotom", "Minior", "Furfrou", "Mimikyu", "Type: Null");

    $Starters = array("Fennekin", "Snivy", "Tepig", "Oshawott", "Charmander", "Cyndaquil", "Chimchar", "Treecko", "Torchic", "Mudkip", "Chespin", "Squirtle", "Totodile", "Piplup", "Froakie", "Bulbasaur", "Chikorita", "Turtwig", "Rowlet", "Litten", "Popplio");

    $Legendary = array("Absol", "Jirachi", "Poipole", "Phione", "Tapu Koko", "Tapu Lele", "Tapu Bulu", "Tapu Fini", "Nihilego", "Buzzwole", "Pheromosa", "Xurkitree", "Celesteela", "Kartana", "Guzzlord", "Stakataka", "Blacephalon", "Articuno", "Zapdos", "Moltres", "Raikou", "Entei", "Suicune", "Regirock", "Regice", "Registeel", "Uxie", "Mesprit", "Azelf", "Cobalion", "Terrakion", "Virizion", "Mew", "Celebi", "Heatran", "Cresselia", "Manaphy", "Darkrai", "Victini", "Volcanion", "Magearna", "Marshadow", "Zeraora", "Regigigas", "Lugia", "Ho-Oh", "Dialga", "Palkia", "Reshiram", "Zekrom", "Xerneas", "Yveltal");

    $Mythic = array("Zygarde (10%)", "Tornadus", "Thundurus", "Landorus", "Keldeo", "Latias", "Latios", "Deoxys", "Shaymin (Land)", "Meloetta", "Genesect", "Diancie", "Hoopa", "Necrozma", "Kyurem", "Kyogre", "Groudon", "Mewtwo", "Rayquaza", "Giratina (Altered)", "Arceus");

    $Special = array("Cosmog");

    $Ditto = array("Ditto");

    $Random = mt_rand(1, 8192);
    
    if ( $Random < 2098 )
    {
      $Selected_Egg = array_rand($Commons, 1);
      $Fetch_Dex = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM pokedex_testing WHERE Name = \"{$Commons[$Selected_Egg]}\""));
      $Fetch_Steps = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM pokedex WHERE Name = \"{$Commons[$Selected_Egg]}\""));
      mysqli_query($con2, "INSERT INTO `lab` (`Pokedex_ID`, `Sprite_Version`, `Steps`) VALUES('{$Fetch_Dex['ID']}', '6', '{$Fetch_Steps['Steps_Gen7']}')");
      //echo "<font style='color: black;'>Common Egg Selected: {$Commons[$Selected_Egg]}</font></br />";
    }
    else if ( $Random < 4096 && $Random >= 2098 )
    {
      $Selected_Egg = array_rand($Rares, 1);
      $Fetch_Dex = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM pokedex_testing WHERE Name = \"{$Rares[$Selected_Egg]}\""));
      $Fetch_Steps = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM pokedex WHERE Name = \"{$Rares[$Selected_Egg]}\""));
      mysqli_query($con2, "INSERT INTO `lab` (`Pokedex_ID`, `Sprite_Version`, `Steps`) VALUES('{$Fetch_Dex['ID']}', '6', '{$Fetch_Steps['Steps_Gen7']}')"); 
      //echo "<font style='color: lightblue;'>Rare Egg Selected: {$Rares[$Selected_Egg]}</font></br />";
    }
    else if ( $Random < 6144 && $Random >= 4096 )
    {
      $Selected_Egg = array_rand($Epics, 1);
      $Fetch_Dex = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM pokedex_testing WHERE Name = \"{$Epics[$Selected_Egg]}\""));
      $Fetch_Steps = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM pokedex WHERE Name = \"{$Epics[$Selected_Egg]}\""));
      mysqli_query($con2, "INSERT INTO `lab` (`Pokedex_ID`, `Sprite_Version`, `Steps`) VALUES('{$Fetch_Dex['ID']}', '6', '{$Fetch_Steps['Steps_Gen7']}')"); 
      //echo "<font style='color: purple;'>Epic Egg Selected: {$Epics[$Selected_Egg]}</font></br />";
    }
    else if ( $Random < 7168 && $Random >= 6144 )
    {
      $Selected_Egg = array_rand($Uniques, 1);
      $Fetch_Dex = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM pokedex_testing WHERE Name = \"{$Uniques[$Selected_Egg]}\""));
      $Fetch_Steps = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM pokedex WHERE Name = \"{$Uniques[$Selected_Egg]}\""));
      mysqli_query($con2, "INSERT INTO `lab` (`Pokedex_ID`, `Sprite_Version`, `Steps`) VALUES('{$Fetch_Dex['ID']}', '6', '{$Fetch_Steps['Steps_Gen7']}')"); 
      //echo "<font style='color: yellow;'>Unique Egg Selected: {$Uniques[$Selected_Egg]}</font></br />";
    }
    else if ( $Random < 7918 && $Random >= 7168 )
    {
      $Selected_Egg = array_rand($Starters, 1);
      $Fetch_Dex = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM pokedex_testing WHERE Name = \"{$Starters[$Selected_Egg]}\""));
      $Fetch_Steps = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM pokedex WHERE Name = \"{$Starters[$Selected_Egg]}\""));
      mysqli_query($con2, "INSERT INTO `lab` (`Pokedex_ID`, `Sprite_Version`, `Steps`) VALUES('{$Fetch_Dex['ID']}', '6', '{$Fetch_Steps['Steps_Gen7']}')"); 
      //echo "<font style='color: orange;'>Unique Egg Selected: {$Starters[$Selected_Egg]}</font></br />";
    }
    else if ( $Random < 8018 && $Random >= 7918 )
    {
      $Selected_Egg = array_rand($Legendary, 1);
      $Fetch_Dex = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM pokedex_testing WHERE Name = \"{$Legendary[$Selected_Egg]}\""));
      $Fetch_Steps = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM pokedex WHERE Name = \"{$Legendary[$Selected_Egg]}\""));
      mysqli_query($con2, "INSERT INTO `lab` (`Pokedex_ID`, `Sprite_Version`, `Steps`) VALUES('{$Fetch_Dex['ID']}', '6', '{$Fetch_Steps['Steps_Gen7']}')");
      //echo "<font style='color: lime;'>Legendary Egg Selected: {$Legendary[$Selected_Egg]}</font></br />";
    }
    else if ( $Random < 8118 && $Random >= 8018 )
    {
      $Selected_Egg = array_rand($Mythic, 1);
      $Fetch_Dex = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM pokedex_testing WHERE Name = \"{$Mythic[$Selected_Egg]}\""));
      $Fetch_Steps = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM pokedex WHERE Name = \"{$Mythic[$Selected_Egg]}\""));
      mysqli_query($con2, "INSERT INTO `lab` (`Pokedex_ID`, `Sprite_Version`, `Steps`) VALUES('{$Fetch_Dex['ID']}', '6', '{$Fetch_Steps['Steps_Gen7']}')");
      //echo "<font style='color: lavender;'>Mythic Egg Selected: {$Mythic[$Selected_Egg]}</font></br />";
    }
    else if ( $Random < 8168 && $Random >= 8118 )
    {
      $Selected_Egg = array_rand($Special, 1);
      $Fetch_Dex = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM pokedex_testing WHERE Name = \"{$Special[$Selected_Egg]}\""));
      $Fetch_Steps = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM pokedex WHERE Name = \"{$Special[$Selected_Egg]}\""));
      mysqli_query($con2, "INSERT INTO `lab` (`Pokedex_ID`, `Sprite_Version`, `Steps`) VALUES('{$Fetch_Dex['ID']}', '6', '{$Fetch_Steps['Steps_Gen7']}')");
      //echo "<font style='color: red;'>Special Egg Selected: {$Special[$Selected_Egg]}</font></br />";
    }
    else if ( $Random < 8193 && $Random >= 8168 )
    {
      $Selected_Egg = array_rand($Ditto, 1);
      $Fetch_Dex = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM pokedex_testing WHERE Name = \"{$Ditto[$Selected_Egg]}\""));
      $Fetch_Steps = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM pokedex WHERE Name = \"{$Ditto[$Selected_Egg]}\""));
      mysqli_query($con2, "INSERT INTO `lab` (`Pokedex_ID`, `Sprite_Version`, `Steps`) VALUES('{$Fetch_Dex['ID']}', '6', '{$Fetch_Steps['Steps_Gen7']}')");
      //echo "<font style='color: pink;'>Ditto Egg Selected: {$Ditto[$Selected_Egg]}</font></font></br />";
    }
    else
    {
      echo "Invalid integer check.";
    }
  }

function lastseen($ts, $totimestamp = '')
{
  $getseconds = time() - $ts;

  if ($totimestamp == 'hour' && $getseconds > 3600) {
    $lastseen = date("F j, Y, g:i a", $ts);
  } elseif ($totimestamp == 'day' && $getseconds > 86400) {
    $lastseen = date("F j, Y, g:i a", $ts);
  } elseif ($totimestamp == 'week' && $getseconds > 604800) {
    $lastseen = date("F j, Y, g:i a", $ts);
  } elseif ($totimestamp == 'month' && $getseconds > 2419200) {
    $lastseen = date("F j, Y, g:i a", $ts);
  } elseif ($totimestamp == 'year' && $getseconds > 29030400) {
    $lastseen = date("F j, Y, g:i a", $ts);
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