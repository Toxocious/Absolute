<?php
  require_once '../php/session.php';
  require_once '../php/global_functions.php';

  /* ==================================================================================================================================================== 
                                                              CHANGING TABS
  ==================================================================================================================================================== */
  if ( $_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['tab']) )
  {
    if ( $_GET['tab'] === 'Roster'  )
    {
      $Fetch_Box = mysqli_query($con, "SELECT * FROM pokemon WHERE Owner_Current = '" . $User_Data['id'] . "' AND Slot = 7 LIMIT 50");

      echo "
      <div class='panel' style='float: left; margin-right: 5px; width: calc(50% - 5px);'>
        <div class='panel-heading'>Roster</div>
        <div class='panel-body'>";
          showRoster("{$User_Data['id']}", 'Pokecenter', 'Box');
      echo "</div>
      </div>

      <div class='panel' style='float: left; margin-bottom: 5px; width: 50%;'>
        <div class='panel-heading'>Box</div>
        <div class='panel-body' style='padding: 3px;'>";              
            while ( $Query_Box = mysqli_fetch_assoc($Fetch_Box) )
            {
              showImage('icon', $Query_Box['ID'], 'pokemon', 'Stats');
            }

            if ( mysqli_num_rows($Fetch_Box) == 0 ) {
              echo	"<div style='padding: 5px;'>There are no Pokemon in your box.</div>";
            }
        echo "</div>
      </div>

      <div class='panel' style='float: right; width: 50%;'>
        <div class='panel-heading'>Selected Pokemon</div>
        <div class='panel-body' style='padding: 3px;' id='dataDiv'>
          Please select a Pokemon to view their statistics.
        </div>
      </div>";
    }

    else if ( $_GET['tab'] === 'Inventory' )
    {
      $Item_Types = ['Battle', 'Berry', 'General', 'Held', 'Key', 'Machine', 'Medicine', 'Pokeball'];
      $Fetch_Box = mysqli_query($con, "SELECT * FROM pokemon WHERE Owner_Current = '" . $User_Data['id'] . "' AND Slot = 7 LIMIT 50");
      $Check_Equipped = mysqli_query($con, "SELECT * FROM items_owned WHERE Owner_Current = '" . $User_Data['id'] . "' AND Equipped_To > 0");

      $Item_Type = 'Held';
      $Get_Items = mysqli_query($con, "SELECT * FROM items_owned WHERE Owner_Current = '" . $User_Data['id'] . "' AND Item_Type = '" . $Item_Type . "'");

      echo "
        <div class='panel' style='margin-bottom: 5px;'>
          <div class='panel-heading'>Inventory</div>
          <div class='panel-body inventory'>
            <div>
              <div style='float: left; padding: 5px; width: calc(100% / 6);' onclick=\"inventoryTab('pokecenter', 'showtab', 'Battle');\">
                Battle Items
              </div>
              <div style='float: left; padding: 5px; width: calc(100% / 6);' onclick=\"inventoryTab('pokecenter', 'showtab', 'General');\">
                General Items
              </div>
              <div style='float: left; padding: 5px; width: calc(100% / 6);' onclick=\"inventoryTab('pokecenter', 'showtab', 'Held');\">
                Hold Items
              </div>
              <div style='float: left; padding: 5px; width: calc(100% / 6);' onclick=\"inventoryTab('pokecenter', 'showtab', 'Evolutionary');\">
                Evolutionary Items
              </div>
              <div style='float: left; padding: 5px; width: calc(100% / 6);' onclick=\"inventoryTab('pokecenter', 'showtab', 'Key');\">
                Key Items
              </div>
              <div style='float: left; padding: 5px; width: calc(100% / 6);' onclick=\"inventoryTab('pokecenter', 'showtab', 'Misc');\">
                Misc. Items
              </div>
            </div>
        ";
            
        echo "
          <div id='activeTab'>";
          if ( mysqli_num_rows($Get_Items) === 0 )
          {
            echo	"<div style='padding: 2px;'>There are no items in your inventory.</div>";
          }
          else
          {
            foreach ($Get_Items as $key => $Item)
            {
              echo  "<div class='item_cont' onclick='selectItem(\"pokecenter\", \"item_show\", " . $Item['id'] . ");'>";
              echo    "<div style='float: left;'>";
              echo      "<img src='images/Items/" . $Item['Item_Name'] . ".png' />";
              echo    "</div>";
              echo    "<b>{$Item['Item_Name']}</b><br />";
              echo    "x" . number_format($Item['Quantity']);
              echo  "</div>";
            }
          }
        echo "</div>";

        echo "</div>
          </div>

          <div class='panel' style='float: left; margin-right: 0.5%; width: 49.75%;'>
            <div class='panel-heading'>Attached Items</div>
            <div class='panel-body attacheditems' style='padding: 0px 3px 3px;'>";

            if ( mysqli_num_rows($Check_Equipped) == 0 )
            {
              echo "<div style='padding: 5px;'>None of your Pokemon have an item equipped.</div>";
            }
            else
            {
              while ( $Query = mysqli_fetch_assoc($Check_Equipped) )
              {
                $Items_Ref = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM items_owned WHERE id = '" . $Query['id'] . "' AND Owner_Current = '" . $User_Data['id'] . "'"));
                $Pokemon = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM pokemon WHERE ID = '" . $Items_Ref['Equipped_To'] . "'"));

                if ( $Pokemon['Type'] !== "Normal" ) $Pokemon['Type'] = $Pokemon['Type'];
                else                                 $Pokemon['Type'] = '';
                
                echo "
                  <div class='panel' style='float: left; margin-top: 3px; width: 49.75%;'>
                    <div class='panel-heading'>{$Pokemon['Type']}{$Pokemon['Name']}</div>
                    <!--<div class='panel-body'>their icon plus the icon of w/e item they have equipped plus a remove item button</div>-->
                    <div class='panel-body'>
                      <div style='float: left; padding-top: 2px;'>
                ";

                showImage('icon', $Pokemon['ID'], 'pokemon', 'blank');

                echo "</div>
                      <div style='float: left; padding-top: 3px;'>
                        <img src='images/Items/{$Items_Ref['Item_Name']}.png' />
                      </div>
                      <div style='float: left; height: 30px; padding-top: 7px; text-align: center; width: calc(100% - 70px);'>
                        <a href='javascript:void(0);' onclick='removeItem()'>Remove Item</a>
                      </div>
                    </div>
                  </div>
                ";
              }
            }

        echo "
            </div>
          </div>

          <div class='panel' style='float: left; width: 49.75%;'>
            <div class='panel-heading'>Selected Item Data</div>
            <div class='panel-body' id='dataDiv'>
              <div style='padding: 5px;'>Please select an item to use it.</div>
            </div>
          </div>

          <script type='text/javascript'>
            let tabDivs = $('.pokecenter .inventory div:nth-child(1) div');
            for ( let i = 0; i < tabDivs.length; i++ )
            {
              tabDivs[i].addEventListener('click', function()
              {
                let current = document.getElementsByClassName('itemtab, active');
                current[0].className = current[0].className.replace('active', '');
                this.className += 'active';
              });
            }
          </script>
        ";
    }

    else if ( $_GET['tab'] === 'Nickname' )
    {
      $Fetch_Box = mysqli_query($con, "SELECT * FROM pokemon WHERE Owner_Current = '" . $User_Data['id'] . "' AND Slot = 7 LIMIT 50");

      echo "
      <div class='panel' style='float: left; margin-right: 5px; width: calc(50% - 5px);'>
        <div class='panel-heading'>Roster</div>
        <div class='panel-body'>";
          showRoster("{$User_Data['id']}", 'Pokecenter', 'Nickname');
      echo "</div>
      </div>

      <div class='panel' style='float: right; width: 50%;'>
        <div class='panel-heading'>Nickname A Pokemon</div>
        <div class='panel-body' id='dataDiv' style='padding: 3px;'>
          Select the Pokemon that you would like to nickname.
        </div>
      </div>";
    }

    else if ( $_GET['tab'] === 'Release' )
    {
      /*
      $Fetch_Box = mysqli_query($con, "SELECT * FROM pokemon WHERE Owner_Current = '" . $User_Data['id'] . "' AND Slot = 7 LIMIT 50");

      echo "
      <div class='panel' style='float: left; width: calc(50% - 5px);'>
        <div class='panel-heading'>Box</div>
        <div class='panel-body' style='padding: 3px;'>";              
            while ( $Query_Box = mysqli_fetch_assoc($Fetch_Box) )
            {
              showImage('icon', $Query_Box['ID'], 'pokemon', 'Stats');
            }

            if ( mysqli_num_rows($Fetch_Box) == 0 ) {
              echo	"<div style='padding: 5px;'>There are no Pokemon in your box.</div>";
            }
        echo "</div>
      </div>";

      echo "
      <div class='panel' style='float: right; width: 50%;'>
        <div class='panel-heading'>Release A Pokemon</div>
        <div class='panel-body' id='releasePanel' style='padding: 3px;'>
          Select the Pokemon that you would like to release.
        </div>
      </div>";
      */
        echo  "
          <script type='text/javascript'>
            function countSelector(el) {
              var countID = document.getElementById(el.id + '_count');
            
              if (!countID)
              {
                return false;
              }
            
              var options = el.getElementsByTagName('OPTION');
              var selected = 0;
              
              for (var i = 0; i < options.length; i++){
                if (options[i].selected) selected++;
              }
              
              if (selected > 0)
              {
                $('#releaseButton').removeAttr('disabled');
                countID.style.color = 'green';
                countID.innerHTML = selected;
              }
              else
              {
                $('#releaseButton').attr('disabled', 'disabled');
              }
            }
  
            function releasePokemon(req, phase, event) {
              if ( confirm('Are you sure you want to release these Pokemon?') )
              {
                if ( $('#release :selected').length > 0 )
                {
                  var selectedPokemon = [];
                  $('#release :selected').each(function(i, selected) {
                      selectedPokemon[i] = $(selected).val();
                  });
                  console.log(selectedPokemon);
                  
                  $.post('ajax/pokecenter.php', { req: req, pokemon: JSON.stringify(selectedPokemon), phase: phase }, function(data)
                  {
                    console.log('Request: ' + req + '\\nPokemon: ' + JSON.stringify(selectedPokemon) + '\\nPhase: ' + phase);
                    $('#releaseDiv').html(data);
                  });
                }
              }
      
              //event.preventDefault();
              return false;
            }
          </script>
        ";
  
        echo		"<div class='panel panel-default'>";
        echo			"<div class='panel-heading'>Release Pokemon</div>";
        echo				"<div class='panel-body' style='padding: 3px' id='releaseDiv'>";
        echo          "<div>";
        echo              "<button id='releaseButton' style=\"background: #2c3a55 !important; border: 2px solid #000; border-radius: 4px; margin-bottom: 0px; padding: 3px; width: 100%;\" onclick=\" releasePokemon('release', 0); \">Release Pokemon</button>";
        echo          "</div>";
  
        /*
        echo            "<div class='panel' style='margin-bottom: 5px;'>";
        echo              "<div class='panel-heading'>Box Filters</div>";
        echo                "<div class='panel-body'>";
  
        echo                  "<div style='border-right: 1px solid #4A618F; float: left; padding: 29px 5px;'>";
        echo                    "<b>Search for a Pokemon<br />";
        echo                    "<input type='text' name='pokemonSearch' value='' />";
        echo                  "</div>";
  
        echo                  "<div style='border-right: 1px solid #4A618F; float: left;'>";
        echo                    "<table>";
        echo                      "<tr style='border-bottom: 1px solid #4A618F;'>";
        echo                        "<td colspan='2'><b>Types</b></td>";
        echo                      "</tr>";
        echo                      "<tr style='border-bottom: 1px solid #4A618F;'>";
        echo                        "<td style='border-right: 1px solid #4A618F;'><a style='display: block; padding: 5px 0px;' href='javascript:void(0);' onclick='filterBox(normal);'>Normal</a></td>";
        echo                        "<td style='width: 80px;'><a style='display: block; padding: 5px 0px;' href='javascript:void(0);' onclick='filterBox(shiny);'>Shiny</a></td>";
        echo                      "</tr>";
        echo                      "<tr style='border-bottom: 1px solid #4A618F;'>";
        echo                        "<td style='border-right: 1px solid #4A618F; width: 80px;'><a style='display: block; padding: 5px 0px;' href='javascript:void(0);' onclick='filterBox(normal);'>Nature</a></td>";
        echo                        "<td><a style='display: block; padding: 5px 0px;' href='javascript:void(0);' onclick='filterBox(shiny);'>Sunset</a></td>";
        echo                      "</tr>";
        echo                      "<tr>";
        echo                        "<td style='border-right: 1px solid #4A618F; width: 80px;'><a style='display: block; padding: 5px 0px;' href='javascript:void(0);' onclick='filterBox(normal);'>???</a></td>";
        echo                        "<td><a style='display: block; padding: 5px 0px;' href='javascript:void(0);' onclick='filterBox(shiny);'>???</a></td>";
        echo                      "</tr>";
        echo                    "</table>";
        echo                  "</div>";
  
        echo                  "<div style='border-right: 1px solid #4A618F; float: left;'>";
        echo                    "<table style='width: 162px;'>";
        echo                      "<tr style='border-bottom: 1px solid #4A618F;'>";
        echo                        "<td colspan='3'><b>IV Sorting</b></td>";
        echo                      "</tr>";
        echo                      "<tr style='border-bottom: 1px solid #4A618F;'>";
        echo                        "<td style='border-right: 1px solid #4A618F; width: 80px;'><a style='display: block; padding: 5px 0px;' href='javascript:void(0);' onclick='filterBox(aIV)'>Ascending</a></td>";
        echo                        "<td style='width: 80px;'><a style='display: block; padding: 5px 0px;' href='javascript:void(0);' onclick='filterBox(dIV)'>Descending</a></td>";
        echo                      "</tr>";
        echo                    "</table>";
        echo                    "<table>";
        echo                      "<tr style='border-bottom: 1px solid #4A618F;'>";
        echo                        "<td style='width: calc(160px / 3);'><input style='border: none; margin: 0px; padding: 5px; text-align: center; width: 100%;' type='text' name='attackIV' placeholder='HP' value='' /></td>";
        echo                        "<td style='border-left: 1px solid #4A618F; border-right: 1px solid #4A618F; width: calc(160px / 3);'><input style='border: none; margin: 0px; padding: 5px; text-align: center; width: 100%;' type='text' name='attackIV' placeholder='ATT' value='' /></td>";
        echo                        "<td style='width: calc(160px / 3);'><input style='border: none; margin: 0px; padding: 5px; text-align: center; width: 100%;' type='text' name='attackIV' placeholder='DEF' value='' /></td>";
        echo                      "</tr>";
        echo                      "<tr>";
        echo                        "<td style='width: calc(160px / 3);'><input style='border: none; margin: 0px; padding: 5px; text-align: center; width: 100%;' type='text' name='attackIV' placeholder='S.ATT' value='' /></td>";
        echo                        "<td style='border-left: 1px solid #4A618F; border-right: 1px solid #4A618F; width: calc(160px / 3);'><input style='border: none; margin: 0px; padding: 5px; text-align: center; width: 100%;' type='text' name='attackIV' placeholder='S.DEF' value='' /></td>";
        echo                        "<td style='width: calc(160px / 3);'><input style='border: none; margin: 0px; padding: 5px; text-align: center; width: 100%;' type='text' name='attackIV' placeholder='SPEED' value='' /></td>";
        echo                      "</tr>";
        echo                    "</table>";
        echo                  "</div>";
  
        echo                  "<div style='border-right: 1px solid #4A618F; float: left;'>";
        echo                    "<table style='width: 162px;'>";
        echo                      "<tr style='border-bottom: 1px solid #4A618F;'>";
        echo                        "<td colspan='3'><b>EV Sorting</b></td>";
        echo                      "</tr>";
        echo                      "<tr style='border-bottom: 1px solid #4A618F;'>";
        echo                        "<td style='border-right: 1px solid #4A618F; width: 80px;'><a style='display: block; padding: 5px 0px;' href='javascript:void(0);' onclick='filterBox(aIV)'>Ascending</a></td>";
        echo                        "<td style='width: 80px;'><a style='display: block; padding: 5px 0px;' href='javascript:void(0);' onclick='filterBox(dIV)'>Descending</a></td>";
        echo                      "</tr>";
        echo                    "</table>";
        echo                    "<table>";
        echo                      "<tr style='border-bottom: 1px solid #4A618F;'>";
        echo                        "<td style='width: calc(160px / 3);'><input style='border: none; margin: 0px; padding: 5px; text-align: center; width: 100%;' type='text' name='attackIV' placeholder='HP' value='' /></td>";
        echo                        "<td style='border-left: 1px solid #4A618F; border-right: 1px solid #4A618F; width: calc(160px / 3);'><input style='border: none; margin: 0px; padding: 5px; text-align: center; width: 100%;' type='text' name='attackIV' placeholder='ATT' value='' /></td>";
        echo                        "<td style='width: calc(160px / 3);'><input style='border: none; margin: 0px; padding: 5px; text-align: center; width: 100%;' type='text' name='attackIV' placeholder='DEF' value='' /></td>";
        echo                      "</tr>";
        echo                      "<tr>";
        echo                        "<td style='width: calc(160px / 3);'><input style='border: none; margin: 0px; padding: 5px; text-align: center; width: 100%;' type='text' name='attackIV' placeholder='S.ATT' value='' /></td>";
        echo                        "<td style='border-left: 1px solid #4A618F; border-right: 1px solid #4A618F; width: calc(160px / 3);'><input style='border: none; margin: 0px; padding: 5px; text-align: center; width: 100%;' type='text' name='attackIV' placeholder='S.DEF' value='' /></td>";
        echo                        "<td style='width: calc(160px / 3);'><input style='border: none; margin: 0px; padding: 5px; text-align: center; width: 100%;' type='text' name='attackIV' placeholder='SPEED' value='' /></td>";
        echo                      "</tr>";
        echo                    "</table>";
        echo                  "</div>";
  
        echo                  "<input style='margin-top: 40px; width: 150px;' type='submit' value='Filter Box' onclick='aFilterBox(); return false;' />";
        echo              "</div>";
        echo           "</div>";
        */
  
        echo            "<div class='description' style='margin-bottom: 5px; width: 100%;'>";
        echo              "You may select multiple Pokemon by holding down SHIFT.<br />";
        echo              "You can select multiple individual Pokemon by holding down CTRL.";
        echo            "</div>";
  
        echo           "<div class='panel' style='float: right; width: 100%;'>";
        echo             "<div class='panel-heading'>Release List</div>";
        echo             "<div class='panel-body' id='release'>";
        echo               "<select style='border: none; border-bottom: 1px solid #4A618F; border-radius: 0px; height: 300px; outline: none; width: 100%;' id='release' name='release[]' multiple='multiple' onchange='countSelector(this)'>";
  
        $Query_Box = mysqli_query($con, "SELECT * FROM `pokemon` WHERE `slot` = '7' AND `Owner_Current` = '" . $User_Data['id'] . "'");
  
        while ( $Box_Data = mysqli_fetch_assoc($Query_Box) )
        {
          $Pokedex_Data = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM `pokedex` WHERE id = '" . $Box_Data['Pokedex_ID'] . "'"));
  
          $Gender = substr($Box_Data['Gender'], 0, 1);
  
          if ( $Box_Data['Type'] !== "Normal" )
          {
            $Type = $Box_Data['Type'];
          }
          else
          {
            $Type = "";
          }
  
          echo  "<option value='{$Box_Data['ID']}'>{$Type}{$Pokedex_Data['Name']} {$Gender} (Level: " . number_format($Box_Data['Level']) . ")</option>";
  
          if ( $Box_Data['Item'] != '0' )
          {
            $Item_Data = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM `items_owned` WHERE id = '" . $Box_Data['Item'] . "'"));
  
            echo  "<option value disabled='disabled' style='font-size: 12px;'>^ Item: {$Item_Data['Item_Name']}</option>";
          }
        }
  
        echo               "</select>";
        echo               "<div style='background: #111; margin-top: -1px; padding: 3px;'>";
        echo                "You have selected <b><span id='release_count'>0</span></b> Pokemon to release.";
        echo               "</div>";
        echo             "</div>";
        echo           "</div>";
  
        /*
        echo            "<div class='panel' style='float: left; margin-bottom: 5px; width: 65%;'>";
        echo              "<div class='panel-heading'>Selected Pokemon</div>";
        echo              "<div class='panel-body' id='selectedPokemon'>";
        echo                "<div style='padding: 10px;'>Please select a Pokemon to view it's stats.</div>";
        echo              "</div>";
        echo            "</div>";
  
        echo		  			"<div class='panel' style='float: left; width: 65%;'>";
        echo             "<div class='panel-heading'>Box</div>";
        echo             "<div class='panel-body boxed_pokemon'>";
        
        $Query_Box = mysqli_query($con, "SELECT * FROM `pokemon` WHERE `slot` = '7' AND `Owner_Current` = '" . $User_Data['id'] . "'");
  
        while ( $rows = mysqli_fetch_assoc($Query_Box) ) {
          echo "<img src='images/Icons/{$rows['Type']}/" . $rows['Pokedex_ID'] . ".png' onclick='showPokemon(" . $rows['ID'] . ");' />";
        }
        
        if ( mysqli_num_rows($Query_Box) == 0 ) {
          echo	"<div style='padding: 5px;'>There are no Pokemon in your box.</div>";
        }
  
        echo              "</div>";
        echo           "</div>";
        */
        echo		  	"</div>";
        echo		  "</div>";
        echo	  "</div>";
    }

    else
    {
      echo "Bigger scam than bitconnect.";
    }
  }

  /* ==================================================================================================================================================== 
                                                            TAB/STATS/WHATEVER DATA
  ==================================================================================================================================================== */
  else if ( $_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['req']) )
  {
    //echo "GET SET";
    if ( $_GET['page'] == 'pokecenter' )
    {
      //echo "PAGE SET";
      if ( $_GET['req'] == 'Stats' )
      {
        //echo "STATS SET";

        $Pokemon_Data = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM `pokemon` WHERE `id` = '" . $_GET['id'] . "' AND `Owner_Current` = '" . $User_Data['id'] . "'"));
        $Pokemon_Item = mysqli_fetch_assoc(mysqli_query($con, "SELECT `Item_Name` FROM `items_owned` WHERE `Equipped_To` = '" . $Pokemon_Data['ID'] . "'"));
        $Pokedex_Data = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM `pokedex` WHERE `id` = '" . $Pokemon_Data['Pokedex_ID'] . "'"));
        
        echo	"<div class='row'>";
        echo		"<div>";
                  # Echo the Held Item.
                  if ( $Pokemon_Item['Item_Name'] != '' ) {
                    echo	"<img class='item' src='images/Items/" . $Pokemon_Item['Item_Name'] . ".png' />";
                  }
        
                  # Echo the gender icon.
                  if ( $Pokemon_Data['Gender'] === 'Female' ) {
                    echo	"<img class='gender' src='images/Assets/female.svg' />";
                  }
                  elseif ( $Pokemon_Data['Gender'] === 'Male' ) {
                    echo	"<img class='gender' src='images/Assets/male.svg' />";
                  }
                  else {
                    echo $Pokemon_Data['Gender'];
                  }
        
        echo      showImage('sprite', $Pokemon_Data['ID'], 'pokemon', null);

        echo 			"<br />";
        echo			"<b>";
                  if ( $Pokemon_Data['Type'] != "Normal" ) {
                    echo $Pokemon_Data['Type'] . $Pokedex_Data['Name'] . "<br />";
                  } else {
                    echo $Pokedex_Data['Name'] . "<br />";
                  }
        echo			"</b>";
        echo 			"<b>Level:</b> " . number_format($Pokemon_Data['Level']) . "<br />";
        echo      "<b>Exp:</b> " . number_format($Pokemon_Data['Experience']);
        
        echo			"<div style='padding-bottom: 5px; padding-top: 5px'>";
      
        for ( $i = 1; $i <= 6; $i++ )
        {
          $Slot[$i] = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM pokemon WHERE Owner_Current = '" . $User_Data['id'] . "' AND Slot = $i"));
  
          if ( $Slot[$i] ) 
          {
            $Name = mysqli_fetch_assoc(mysqli_query($con, "SELECT Name FROM pokedex WHERE ID = '" . $Slot[$i]["Pokedex_ID"] . "'"));
            $Slot[$i]['Name'] = $Name['Name'];
          }
          else 
          {
            $Slot[$i] = "Empty";
          }
  
          if ( $Slot[$i] !== "Empty" )
          {
            //showImage('icon', $Slot[$i]['ID'], 'pokemon', 'slot', $i);

            $Pokemon = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM pokemon WHERE `ID` = '" . $Slot[$i]['ID'] . "'"));
            $Pokedex_Data = mysqli_fetch_assoc(mysqli_query($con, "SELECT `Name` FROM `pokedex` WHERE `ID` = '" . $Pokemon['Pokedex_ID'] . "'"));

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

            echo "<img class='pokemonSlot' src='images/Icons/{$Dir_Type}/{$Slot_Gen}/{$Slot_pID}.png' onclick='changeSlot(\"slot_change\", {$Pokemon_Data['ID']}, $i);' />";
          }
          else
          {
            echo "<img class='pokemonSlot' src='images/Assets/Pokeball.png' style='height: 32px; margin-left: 8px; width: 32px;' onclick='changeSlot(\"slot_change\", {$Pokemon_Data['ID']}, $i);' />";
          }
        }

        echo			"</div>";
        echo		"</div>";

        echo    "<div>";
        echo      "<table class='special' style='width: 100%;'>";
        echo        "<thead>";
        echo          "<td colspan='1' style='width: 40%;'>Stat</td>";
        echo          "<td colspan='1' style='width: 20%;'>Base</td>";
        echo          "<td colspan='1' style='width: 20%;'>IV</td>";
        echo          "<td colspan='1' style='width: 20%;'>EV</td>";
        echo        "</thead>";
        echo        "<tbody>";
        echo          "<tr>";
        echo            "<td>HP</td>";
        echo            "<td style='text-align: center'>" . $Pokemon_Data['HP'] . "</td>";
        echo            "<td style='text-align: center'>" . $Pokemon_Data['IV_HP'] . "</td>";
        echo            "<td style='text-align: center'>" . $Pokemon_Data['EV_HP'] . "</td>";
        echo          "</tr>";
        echo          "<tr>";
        echo            "<td>Attack</td>";
        echo            "<td style='text-align: center'>" . $Pokemon_Data['Attack'] . "</td>";
        echo            "<td style='text-align: center'>" . $Pokemon_Data['IV_Attack'] . "</td>";
        echo            "<td style='text-align: center'>" . $Pokemon_Data['EV_Attack'] . "</td>";
        echo          "</tr>";
        echo          "<tr>";
        echo            "<td>Defense</td>";
        echo            "<td style='text-align: center'>" . $Pokemon_Data['Defense'] . "</td>";
        echo            "<td style='text-align: center'>" . $Pokemon_Data['IV_Defense'] . "</td>";
        echo            "<td style='text-align: center'>" . $Pokemon_Data['EV_Defense'] . "</td>";
        echo          "</tr>";
        echo          "<tr>";
        echo            "<td>Special Attack</td>";
        echo            "<td style='text-align: center'>" . $Pokemon_Data['SpAttack'] . "</td>";
        echo            "<td style='text-align: center'>" . $Pokemon_Data['IV_SpAttack'] . "</td>";
        echo            "<td style='text-align: center'>" . $Pokemon_Data['EV_SpDefense'] . "</td>";
        echo          "</tr>";
        echo          "<tr>";
        echo            "<td>Special Defense</td>";
        echo            "<td style='text-align: center'>" . $Pokemon_Data['SpDefense'] . "</td>";
        echo            "<td style='text-align: center'>" . $Pokemon_Data['IV_SpDefense'] . "</td>";
        echo            "<td style='text-align: center'>" . $Pokemon_Data['EV_SpDefense'] . "</td>";
        echo          "</tr>";
        echo          "<tr>";
        echo            "<td>Speed</td>";
        echo            "<td style='text-align: center'>" . $Pokemon_Data['Speed'] . "</td>";
        echo            "<td style='text-align: center'>" . $Pokemon_Data['IV_Speed'] . "</td>";
        echo            "<td style='text-align: center'>" . $Pokemon_Data['EV_Speed'] . "</td>";
        echo          "</tr>";
        echo        "</tbody>";
        echo      "</table>";
        echo    "</div>";
        echo	"</div>";
      }

      else if ( $_GET['req'] == 'showtab' )
      {
        $Item_Type = $_GET['item_tab'];
        $Get_Items = mysqli_query($con, "SELECT * FROM items_owned WHERE Owner_Current = '" . $User_Data['id'] . "' AND Item_Type = '" . $Item_Type . "'");

        if ( $Get_Items !== null )
        {
          if ( mysqli_num_rows($Get_Items) === 0 )
          {
            echo	"<div style='padding: 46px 5px 12px;'>There are no items in your inventory.</div>";
          }
          else
          {
            foreach ($Get_Items as $key => $Item)
            {
              echo  "<div class='item_cont' onclick='selectItem(\"pokecenter\", \"item_show\", " . $Item['id'] . ");'>";
              echo    "<div style='float: left;'>";
              echo      "<img src='images/Items/" . $Item['Item_Name'] . ".png' />";
              echo    "</div>";
              echo    "<b>{$Item['Item_Name']}</b><br />";
              echo    "x" . number_format($Item['Quantity']);
              echo  "</div>";
            }
          }
        }
      }

      else if ( $_GET['req'] == 'Nick' )
      {
        $Pokemon_Data = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM `pokemon` WHERE `id` = '" . $_GET['id'] . "' AND `Owner_Current` = '" . $User_Data['id'] . "'"));
        $Pokemon_Item = mysqli_fetch_assoc(mysqli_query($con, "SELECT `Item_Name` FROM `items_owned` WHERE `Equipped_To` = '" . $Pokemon_Data['ID'] . "'"));
        $Pokedex_Data = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM `pokedex` WHERE `id` = '" . $Pokemon_Data['Pokedex_ID'] . "'"));

        echo  "<div style='float: left; width: 50%;'>";
        echo    showImage('sprite', $Pokemon_Data['ID'], 'pokemon', null) . "<br />";
        echo		"<b>";
                if ( $Pokemon_Data['Type'] != "Normal" ) {
                  echo $Pokemon_Data['Type'] . $Pokedex_Data['Name'] . "<br />";
                } else {
                  echo $Pokedex_Data['Name'] . "<br />";
                }
        echo		"</b>";
        echo 		"<b>Level:</b> " . number_format($Pokemon_Data['Level']) . "<br />";
        echo    "<b>Exp:</b> " . number_format($Pokemon_Data['Experience']);
        echo  "</div>";

        echo  "<div style='float: left; padding-top: 12px; width: 50%;'>";
        echo    "<b>Current Nickname</b><br />";
        
        if ( $Pokemon_Data['Nickname'] != '' )
          echo  "<i>\"" . $Pokemon_Data['Nickname'] . "\"</i>";
        else
          echo  "<i>No nickname has been set.</i>";

        echo    "<br /><br />";
        echo    "<textarea id='nickname' maxlength='15' style='max-height: 30px; resize: none; width: 100%;'></textarea>";
        echo    "<button onclick='changeNick(\"nickname\", \"{$Pokemon_Data['ID']}\", \"{$Pokemon_Data['Slot']}\");' style='width: 100%;'>Change Nickname</button>";
        echo  "</div>";
      }

      else if ( $_GET['req'] == 'item_show' )
      {
        $Roster = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM `pokemon` WHERE `Owner_Current` = '" . $User_Data['id'] . "' AND Slot < 7"));
        $Item_Data = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM `items_owned` WHERE `id` = '" . $_GET['id'] . "'"));
        $Item_Description = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM `items` WHERE `Item_ID` = '" . $Item_Data['Item_ID'] . "'"));
        
        if ( $Item_Data['Owner_Current'] === $User_Data['id'] )
        {
          echo  "<div style='padding: 5px;'>";
          echo    "<img style='float: left; margin-top: 23px;' src='images/Items/{$Item_Data['Item_Name']}.png' />";
          echo	  "<b>{$Item_Data['Item_Name']}</b><br />";
          echo	  "<i>{$Item_Description['Item_Description']}</i><br />";
          echo  "</div>";
          echo  "<br />";

          for ($i = 1 ; $i <= 6 ; $i++) {
            $Pokemon_Data = mysqli_query($con, "SELECT * FROM pokemon WHERE Owner_Current = '" . $User_Data['id'] . "' AND Slot = $i");
            $Slot[$i] = mysqli_fetch_assoc($Pokemon_Data);
            
            if ( $Slot[$i] ) {
              $Pokedex_Data = mysqli_fetch_assoc(mysqli_query($con, "SELECT `Name` FROM `pokedex` WHERE `ID` = '" . $Slot[$i]["Pokedex_ID"] . "'"));
              $Fetch_Item = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM items_owned WHERE Equipped_To = '" . $Slot[$i]['ID'] . "'"));
              $Slot[$i]['Name'] = $Pokedex_Data['Name'];
            } else {
              $Slot[$i] = 'Empty';
            }
  
            if ( $Slot[$i] != 'Empty' ) {
              $Pokemon = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM `pokemon` WHERE `ID` = '" . $Slot[$i]['ID'] . "' LIMIT 1"));
              $Pokedex_Data = mysqli_fetch_assoc(mysqli_query($con, "SELECT `Name` FROM `pokedex` WHERE `ID` = '" . $Pokemon['Pokedex_ID'] . "' LIMIT 1 "));

              if ( $Pokemon['Type'] == 'Normal' ) $Dir_Type = "1 - {$Pokemon['Type']}";
              else if ( $Pokemon['Type'] == 'Shiny' ) $Dir_Type = "2 - {$Pokemon['Type']}";
              else if ( $Pokemon['Type'] == 'Sunset' ) $Dir_Type = "3 - {$Pokemon['Type']}";
              else if ( $Pokemon['Type'] == 'Shiny Sunset' ) $Dir_Type = "4 - {$Pokemon['Type']}";
              else $Dir_Type = "5 - {$Pokemon['Type']}";

              if ( $Pokemon['Pokedex_ID'] <= 151 ) $Slot_Gen = 'Generation 1';
              else if ( $Pokemon['Pokedex_ID'] <= 251 && $Pokemon['Pokedex_ID'] >= 152 ) $Slot_Gen = 'Generation 2';
              else if ( $Pokemon['Pokedex_ID'] <= 386 && $Pokemon['Pokedex_ID'] >= 252 ) $Slot_Gen = 'Generation 3';
              else if ( $Pokemon['Pokedex_ID'] <= 493 && $Pokemon['Pokedex_ID'] >= 387 ) $Slot_Gen = 'Generation 4';
              else if ( $Pokemon['Pokedex_ID'] <= 649 && $Pokemon['Pokedex_ID'] >= 494 ) $Slot_Gen = 'Generation 5';
              else if ( $Pokemon['Pokedex_ID'] <= 721 && $Pokemon['Pokedex_ID'] >= 650 ) $Slot_Gen = 'Generation 6';
              else $Slot_Gen = 'Generation 7';

              if ( $Pokemon['Pokedex_ID'] <= 151 ) $Slot_Gen = 'Generation 1';
              else if ( $Pokemon['Pokedex_ID'] <= 251 && $Pokemon['Pokedex_ID'] >= 152 ) $Slot_Gen = 'Generation 2';
              else if ( $Pokemon['Pokedex_ID'] <= 386 && $Pokemon['Pokedex_ID'] >= 252 ) $Slot_Gen = 'Generation 3';
              else if ( $Pokemon['Pokedex_ID'] <= 493 && $Pokemon['Pokedex_ID'] >= 387 ) $Slot_Gen = 'Generation 4';
              else if ( $Pokemon['Pokedex_ID'] <= 649 && $Pokemon['Pokedex_ID'] >= 494 ) $Slot_Gen = 'Generation 5';
              else if ( $Pokemon['Pokedex_ID'] <= 721 && $Pokemon['Pokedex_ID'] >= 650 ) $Slot_Gen = 'Generation 6';
              else $Slot_Gen = 'Generation 7';

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

              //var_dump($Item_Data);

              if ( $Slot[$i]['Item'] != '0' )
              {
                echo "<img src='images/Icons/{$Dir_Type}/{$Slot_Gen}/{$Slot_pID}.png' style='filter: grayscale(100%);' />";
              }
              else
              {
                echo "<img src='images/Icons/{$Dir_Type}/{$Slot_Gen}/{$Slot_pID}.png' onclick=\"attachItem('attachitem', {$Item_Data['id']}, $i);\" />";
              }
            } else {
              echo "
                <img src='images/Assets/pokeball.png' style='width: 30px; margin-left: 8px; height: 30px;' />
              ";
            }
          }

          echo "
            <script type='text/javascript'>
              function attachItem(req, id, slot) {
                $.post('ajax/pokecenter.php', { req: req, id: id, slot: slot }, function(data)
                {
                  $('#pokemon_center').html(data);
                });
              }
            </script>
          ";
        } else {
          echo "This item does not belong to you.";
        }
      }

      else
      {
        echo "I see you there, changing some JavaScript in order to find stuff out.";
      }
    }
  }

  /* ===================================================================================================================
                                          SERVER REQUEST METHOD = POST
  ----------------------------------------------------------------------------------------------------------------------
                                  i really need to merge the code below honestly
  =================================================================================================================== */
  else if ( $_SERVER['REQUEST_METHOD'] === 'POST' )
  {
    if ( $_POST['req'] === 'attachitem' && isset($_POST['id']) && isset($_POST['slot']) )
    {
      # Verify that the user requesting this data actually owns this item.
      $Item_Data = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM items_owned WHERE id = '" . $_POST['id'] . "' AND Owner_Current = '" . $User_Data['id'] . "'"));
            
      # Verify that the user owns the Pokemon he's attemping to attach the item to.
      $Pokemon_Data = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM pokemon WHERE Slot = '" . $_POST['slot'] . "' AND Owner_Current = '" . $User_Data['id'] . "'"));
      $Pokedex_Data = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM pokedex WHERE id = '" . $Pokemon_Data['Pokedex_ID'] . "'"));

      if ( $Item_Data['Owner_Current'] == $User_Data['id'] ) {
				if ( $Pokemon_Data['Owner_Current'] == $User_Data['id'] ) {
          mysqli_query($con, "UPDATE items_owned SET Equipped_To = '" . $Pokemon_Data['ID'] . "', Quantity = Quantity - 1 WHERE Owner_Current = '" . $User_Data['id'] . "' AND id = '" . $Item_Data['id'] . "'");
          mysqli_query($con, "UPDATE pokemon SET Item = '" . $Item_Data['id'] . "' WHERE id = '" . $Pokemon_Data['ID'] . "' AND Slot = '" . $_POST['slot'] . "'");
							
					if ( $Pokemon_Data['Type'] != "Normal" ) {
						echo "<div class='description' style='border-color: #00ff00; margin-bottom: 5px; margin-top: 0px; width: 100%;'>The <b>{$Item_Data['Item_Name']}</b> has been attached to your <b>{$Pokemon_Data['Type']}{$Pokedex_Data['Name']}</b>.</div>";
					} else {
						echo "<div class='description' style='border-color: #00ff00; margin-bottom: 5px; margin-top: 0px; width: 100%;'>The <b>{$Item_Data['Item_Name']}</b> has been attached to your <b>{$Pokedex_Data['Name']}</b>.</div>";
					}
				} else {
					echo "<div class='description' style='border-color: #ff0000; margin-bottom: 5px; margin-top: 0px; width: 100%;'>The Pokemon that you attemped to attach the item to doesn't belong to you.</div>";
				}
			} else {
					echo "<div class='description' style='border-color: #ff0000; margin-bottom: 5px; margin-top: 0px; width: 100%;'>The item that you attemped to attach the Pokemon to doesn't belong to you.</div>";
      }

      $Item_Types = ['Battle', 'Berry', 'General', 'Held', 'Key', 'Machine', 'Medicine', 'Pokeball'];
      $Fetch_Box = mysqli_query($con, "SELECT * FROM pokemon WHERE Owner_Current = '" . $User_Data['id'] . "' AND Slot = 7 LIMIT 50");
      $Check_Equipped = mysqli_query($con, "SELECT * FROM items_owned WHERE Owner_Current = '" . $User_Data['id'] . "' AND Equipped_To > 0");

      $Item_Type = 'Held';
      $Get_Items = mysqli_query($con, "SELECT * FROM items_owned WHERE Owner_Current = '" . $User_Data['id'] . "' AND Equipped_To = '0' AND Item_Type = '" . $Item_Type . "'");

      echo "
        <div class='panel' style='margin-bottom: 5px;'>
          <div class='panel-heading'>Inventory</div>
          <div class='panel-body inventory'>
            <div>
              <div style='float: left; padding: 5px; width: calc(100% / 6);' onclick=\"inventoryTab('pokecenter', 'showtab', 'Battle');\">
                Battle Items
              </div>
              <div style='float: left; padding: 5px; width: calc(100% / 6);' onclick=\"inventoryTab('pokecenter', 'showtab', 'General');\">
                General Items
              </div>
              <div style='float: left; padding: 5px; width: calc(100% / 6);' onclick=\"inventoryTab('pokecenter', 'showtab', 'Held');\">
                Hold Items
              </div>
              <div style='float: left; padding: 5px; width: calc(100% / 6);' onclick=\"inventoryTab('pokecenter', 'showtab', 'Evolutionary');\">
                Evolutionary Items
              </div>
              <div style='float: left; padding: 5px; width: calc(100% / 6);' onclick=\"inventoryTab('pokecenter', 'showtab', 'Key');\">
                Key Items
              </div>
              <div style='float: left; padding: 5px; width: calc(100% / 6);' onclick=\"inventoryTab('pokecenter', 'showtab', 'Misc');\">
                Misc. Items
              </div>
            </div>
        ";
            
        echo "
          <div id='activeTab'>";
          if ( mysqli_num_rows($Get_Items) === 0 )
          {
            echo	"<div style='padding: 2px;'>There are no items in your inventory.</div>";
          }
          else
          {
            foreach ($Get_Items as $key => $Item)
            {
              echo  "<div class='item_cont' onclick='selectItem(\"pokecenter\", \"item_show\", " . $Item['id'] . ");'>";
              echo    "<div style='float: left;'>";
              echo      "<img src='images/Items/" . $Item['Item_Name'] . ".png' />";
              echo    "</div>";
              echo    "<b>{$Item['Item_Name']}</b><br />";
              echo    "x" . number_format($Item['Quantity']);
              echo  "</div>";
            }
          }
        echo "</div>";

        echo "</div>
          </div>

          <div class='panel' style='float: left; margin-right: 0.5%; width: 49.75%;'>
            <div class='panel-heading'>Attached Items</div>
            <div class='panel-body attacheditems'>";

            if ( mysqli_num_rows($Check_Equipped) == 0 )
            {
              echo "<div style='padding: 5px;'>None of your Pokemon have an item equipped.</div>";
            }
            else
            {
              while ( $Query = mysqli_fetch_assoc($Check_Equipped) )
              {
                $Items_Ref = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM items_owned WHERE id = '" . $Query['id'] . "' AND Owner_Current = '" . $User_Data['id'] . "'"));
                $Pokemon = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM pokemon WHERE ID = '" . $Items_Ref['Equipped_To'] . "'"));

                if ( $Pokemon['Type'] !== "Normal" ) $Pokemon['Type'] = $Pokemon['Type'];
                else                                 $Pokemon['Type'] = '';
                
                echo "
                  <div class='panel' style='float: left; margin-top: 3px; width: 49.75%;'>
                    <div class='panel-heading'>{$Pokemon['Type']}{$Pokemon['Name']}</div>
                    <!--<div class='panel-body'>their icon plus the icon of w/e item they have equipped plus a remove item button</div>-->
                    <div class='panel-body'>
                      <div style='float: left; padding-top: 2px;'>
                ";

                showImage('icon', $Pokemon['ID'], 'pokemon', 'blank');

                echo "</div>
                      <div style='float: left; padding-top: 3px;'>
                        <img src='images/Items/{$Items_Ref['Item_Name']}.png' />
                      </div>
                      <div style='float: left; height: 30px; padding-top: 7px; text-align: center; width: calc(100% - 70px);'>
                        <a href='javascript:void(0);' onclick='removeItem()'>Remove Item</a>
                      </div>
                    </div>
                  </div>
                ";
              }
            }

        echo "
            </div>
          </div>

          <div class='panel' style='float: left; width: 49.75%;'>
            <div class='panel-heading'>Selected Item Data</div>
            <div class='panel-body' id='dataDiv'>
              <div style='padding: 5px;'>Please select an item to use it.</div>
            </div>
          </div>
        ";
    }

    if ( isset($_POST['req']) && isset($_POST['id']) && isset($_POST['slot']) )
    {
      //echo "All required variables have been set.<hr />";

      if ( $_POST['req'] === 'slot_change' )
      {
        # Pokemon data for the slot you're moving from.
        $Pokemon_One = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM `pokemon` WHERE `id` = '" .  $_POST['id'] . "' AND `Owner_Current` = '" . $User_Data['id'] . "'"));
        $Pokedex_One = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM `pokedex` WHERE `id` = '" . $Pokemon_One['Pokedex_ID'] . "'"));
        
        # Moving the Pokemon into the same slot.
        if ( $Pokemon_One['Slot'] === $_POST['slot'] )
        {
          if ( $Pokemon_One['Type'] !== "Normal" )
          {
            echo "<div class='description' style='border-color: #ff0000; margin-top: 0px; margin-bottom: 5px; padding: 5px; width: 100%;'>Your <b>{$Pokemon_One['Type']}{$Pokedex_One['Name']}</b> is already in slot {$_POST['slot']}.</div>";
          }
          else
          {
            echo "<div class='description' style='border-color: #ff0000; margin-top: 0px; margin-bottom: 5px; padding: 5px; width: 100%;'>Your <b>{$Pokedex_One['Name']}</b> is already in Slot {$_POST['slot']}.</div>";
          }
        }

        # Removing the Pokemon from your roster.
        else if ( $_POST['slot'] === '7' )
        {
          echo "<div class='description' style='border-color: #00ff00; margin-top: 0px; margin-bottom: 5px; padding: 5px; width: 100%;'>";
          if ( $Pokemon_One['Type'] !== "Normal" ) {
            echo "Your <b>{$Pokemon_One['Type']}{$Pokedex_One['Name']}</b> has been moved to your box.";
          } else {
            echo "Your <b>{$Pokedex_One['Name']}</b> has been moved to your box.";
          }
          echo "</div>";

          if ( $Pokemon_One['ID'] === null ) {
            # If the slot IS empty, loop through any previous slots to determine if they are empty as well.
            for ( $i = $_POST['slot']; $i >= 1; $i-- ) {
              $Check_Slots = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM `pokemon` WHERE `Slot` = '" . $i . "' AND `Owner_Current` = '" . $User_Data['id'] . "'"));

              # Looping through all previous slots to find out which ones are empty.
              if ( $Check_Slots['ID'] === null ) {
                # Get the very first empty slot.
                $Empty_Slot = $i;
              }
            }
          } else {
            # Update the `pokemon` database table with the updated slot change(s).
            mysqli_query($con, "UPDATE pokemon SET Slot = '" . $_POST['slot'] . "' WHERE id = '" . $Pokemon_One['ID'] . "' AND Owner_Current = '" . $User_Data['id'] . "'");

            # Check for empty slots in between Pokemon.
            for ( $i = $_POST['slot']; $i >= 1; $i-- ) {
              $Check_Slots = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM `pokemon` WHERE `Slot` = '" . $i . "' AND `Owner_Current` = '" . $User_Data['id'] . "'"));

              # Looping through all previous slots to find out which ones are empty.
              if ( $Check_Slots['ID'] === null ) {
                # Get the very first empty slot.
                $Empty_Slot = "Slot: " . $i . "<br />";

                # Move Pokemon over one space to the left.
              }
            }

            mysqli_query($con, "UPDATE pokemon SET Slot = '7' WHERE id = '" . $Pokemon_One['ID'] . "' AND Owner_Current = '" . $User_Data['id'] . "'");
          }
        }

        # Moving Pokemon to $_POST['slot'].
        else
        {
          # Pokemon data for the slot you're moving to.
          $Pokemon_Two = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM `pokemon` WHERE `Slot` = '" .  $_POST['slot'] . "' AND `Owner_Current` = '" . $User_Data['id'] . "'"));
          $Pokedex_Two = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM `pokedex` WHERE `id` = '" . $Pokemon_Two['Pokedex_ID'] . "'"));

          if ( $User_Data['id'] === $Pokemon_One['Owner_Current'] )
          {
            # Check to see if the slot you're moving the Pokemon to is empty.
            if ( $Pokemon_Two['ID'] === null && $Pokemon_Two['Owner_Current'] === $User_Data['id'] ) {
              # If the slot IS empty, loop through any previous slots to determine if they are empty as well.
              for ( $i = $_POST['slot']; $i >= 1; $i-- ) {
                $Check_Slots = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM `pokemon` WHERE `Slot` = '" . $i . "' AND `Owner_Current` = '" . $User_Data['id'] . "'"));

                # Looping through all previous slots to find out which ones are empty.
                if ( $Check_Slots['ID'] === null ) {
                  # Get the very first empty slot.
                  $Empty_Slot = $i;
                }
              }
              
              # Update the `pokemon` database table with the updated slot change(s).
              mysqli_query($con, "UPDATE pokemon SET Slot = '" . $Empty_Slot . "' WHERE id = '" . $Pokemon_One['ID'] . "' AND Owner_Current = '" . $User_Data['id'] . "'");
            } else {
              # Update the `pokemon` database table with the updated slot change(s).
              mysqli_query($con, "UPDATE pokemon SET Slot = '" . $_POST['slot'] . "' WHERE id = '" . $Pokemon_One['ID'] . "' AND Owner_Current = '" . $User_Data['id'] . "'");
              mysqli_query($con, "UPDATE pokemon SET Slot = '" . $Pokemon_One['Slot'] . "' WHERE id = '" . $Pokemon_Two['ID'] . "' AND Owner_Current = '" . $User_Data['id'] . "'");
            }

            # Echo some success dialog.
            echo 	"<div class='description' style='border-color: #00ff00; margin-top: 0px; margin-bottom: 5px; padding: 5px; width: 100%;'>";

            if ( $Pokemon_One['Type'] !== "Normal" ) {
              echo "Your <b>{$Pokemon_One['Type']}{$Pokedex_One['Name']}</b> has been moved to slot {$_POST['slot']}.";          
            } else {
              echo "Your <b>{$Pokedex_One['Name']}</b> has been moved to slot {$_POST['slot']}.";
            }
                      
            echo 	"</div>";
          }
          else
          {
            echo "<div class='description' style='border-color: #ff0000; margin-top: 0px; margin-bottom: 5px; padding: 5px; width: 100%;'>You don't own these Pokemon.</div>";
          }
        }

        $Fetch_Box = mysqli_query($con, "SELECT * FROM pokemon WHERE Owner_Current = '" . $User_Data['id'] . "' AND Slot = 7 LIMIT 50");

        echo "
        <div class='panel' style='float: left; margin-right: 5px; width: calc(50% - 5px);'>
          <div class='panel-heading'>Roster</div>
          <div class='panel-body'>";
            showRoster("{$User_Data['id']}", 'Pokecenter', 'Box');
        echo "</div>
        </div>

        <div class='panel' style='float: left; margin-bottom: 5px; width: 50%;'>
          <div class='panel-heading'>Box</div>
          <div class='panel-body' style='padding: 3px;'>";              
              while ( $Query_Box = mysqli_fetch_assoc($Fetch_Box) )
              {
                showImage('icon', $Query_Box['ID'], 'pokemon', 'Stats');
              }

              if ( mysqli_num_rows($Fetch_Box) == 0 )
              {
                echo	"<div style='padding: 5px;'>There are no Pokemon in your box.</div>";
              }
          echo "</div>
        </div>

        <div class='panel' style='float: right; width: 50%;'>
          <div class='panel-heading'>Selected Pokemon</div>
          <div class='panel-body' style='padding: 3px;' id='dataDiv'>
            Please select a Pokemon to view their statistics.
          </div>
        </div>";
      }

      else if ( $_POST['req'] === 'nickname' )
      {
        $Pokemon_Data = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM `pokemon` WHERE `id` = '" . $_POST['id'] . "' AND `Owner_Current` = '" . $User_Data['id'] . "'"));
        $Pokedex_Data = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM pokedex WHERE id = '{$Pokemon_Data['Pokedex_ID']}'"));

        if ( $Pokemon_Data['Owner_Current'] === $User_Data['id'] )
        {
          if ( isset($_POST['nickname']) )
          {
            if ( $_POST['nickname'] == '' )
            {
              mysqli_query($con, "UPDATE pokemon SET Nickname = '" . $_POST['nickname'] . "' WHERE id = {$Pokemon_Data['ID']}");

              echo "<div class='description' style='border-color: #00ff00; margin-top: 0px; margin-bottom: 5px; padding: 5px; width: 100%;'>";
              if ( $Pokemon_Data['Type'] !== "Normal" )
              {
                echo "Your <b>{$Pokemon_Data['Type']}{$Pokedex_Data['Name']}</b>'s nickname has been removed.";
              }
              else
              {
                echo "Your <b>{$Pokedex_Data['Name']}</b>'s nickname has been removed.";
              }
              echo showImage('sprite', $Pokemon_Data['ID'], 'pokemon', null) . "<br />";
              echo "</div>";
            }
            else
            {
              $Parse_Nickname = stripslashes(htmlentities($_POST['nickname']));
              mysqli_query($con, "UPDATE pokemon SET Nickname = '" . $Parse_Nickname . "' WHERE id = {$Pokemon_Data['ID']}");

              echo "<div class='description' style='border-color: #00ff00; margin-top: 0px; margin-bottom: 5px; padding: 5px; width: 100%;'>Your <b>{$Pokemon_Data['Type']}{$Pokedex_Data['Name']}</b>'s nickname has been changed to <b>{$Parse_Nickname}</b>.</div>";

              echo showImage('sprite', $Pokemon_Data['ID'], 'pokemon', null) . "<br />";
            }
            echo "</div>";
          }
          else
          {
            echo "<div class='description' style='border-color: #ff0000; margin-top: 0px; margin-bottom: 5px; padding: 5px; width: 100%;'>A variable isn't set.</div>";
          }
        }
        else
        {
          echo "<div class='description' style='border-color: #ff0000; margin-top: 0px; margin-bottom: 5px; padding: 5px; width: 100%;'>You can't change the nickname of Pokemon that you don't own.</div>";
        }
      }

      else
      {
        echo "What are you doing?";
      }
    }
  }

  else if ( $_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['req'] === 'release' )
  {
    if ( isset($_POST['pokemon']) && isset($_POST['phase']) )
    {
      if ( $_POST['phase'] == 0 )
      {
        echo "
          <script type='text/javascript'>
            function releasePokemon(req, phase, event) {
              if ( confirm('Are you sure you want to release these Pokemon? phase 1') )
              {
                var selectedPokemon = [];
                $('.panel').each(function(i) {
                  selectedPokemon[i] = $(this).attr('name');
                });
                console.log(selectedPokemon);
                  
                $.post('ajax/pokecenter.php', { req: req, pokemon: JSON.stringify(selectedPokemon), phase: phase }, function(data)
                {
                  console.log('Request: ' + req + '\\nPokemon: ' + JSON.stringify(selectedPokemon) + '\\nPhase: ' + phase);
                  $('#releaseDiv').html(data);
                });
              }
              return false;
            }
          </script>
        ";

        /*
        $Pokemon = JSON_DECODE($_POST['pokemon'], true);
        echo "<div class='description' style='border-color: #ff00ff;'>Are you sure you want to release the following Pokemon?</div>";
        echo "<input type='button' onclick='releasePokemon(1);' value='Release Pokemon' style='width: 100%;' />";

        echo "<div id='releaseList'>";
        var_dump($Pokemon);
        foreach ( $Pokemon as $num => $Database_ID )
        {
          $Pokemon_Data = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM `pokemon` WHERE id = '" . $Database_ID . "' AND Owner_Current = '" . $User_Data['id'] . "'"));
          $Pokedex_Data = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM `pokedex` WHERE id = '" . $Pokemon_Data['Pokedex_ID'] . "'"));
          $Item_Data = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM `items_owned` WHERE id = '" . $Pokemon_Data['Item'] . "' AND Owner_Current = '" . $User_Data['id'] . "'"));

          if ( $Pokemon_Data['Type'] !== "Normal" ) {
            $Pokemon_Type = $Pokemon_Data['Type'];
          } else {
            $Pokemon_Type = "";
          }

          echo "
            <div><b>{$Pokemon_Type}{$Pokedex_Data['Name']}</b> - {$Pokemon_Data['Gender']} (Level: " . number_format($Pokemon_Data['Level']) . ")</div>
          ";
        }
        echo "</div>";
        */

        $Pokemon = json_decode($_POST['pokemon'], true);

        echo  "<style>";
        echo    ".content .box.pokecenter .panel .panel-body.releaseList { padding: 0px 5px 5px 5px !important; }";
        echo    "#pokemon_center .releaseList > div { margin-top: 5px; }";
        echo    "#pokemon_center .releaseList > div:nth-child(3n+2) { margin-left: 1.25%; margin-right: 1.25%; }";
        echo  "</style>";

        echo  "<input type='button' onclick=\"releasePokemon('release', 1);\" value='Release Pokemon' style='width: 100%;' />";
        echo  "<div class='description' style='border-color: #FFA500; margin-bottom: 3px; margin-top: -1px; width: 100%;'>Are you sure you want to release the following Pokemon?</div>";

        echo  "<div class='panel'>";
        echo    "<div class='panel-heading'>Attempting To Release</div>";
        echo    "<div class='panel-body releaseList' style='padding: 5px;'>";

        foreach ( $Pokemon as $num => $Database_ID )
        {
          $Pokemon_Data = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM `pokemon` WHERE id = '" . $Database_ID . "' AND Owner_Current = '" . $User_Data['id'] . "'"));
          $Pokedex_Data = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM `pokedex` WHERE id = '" . $Pokemon_Data['Pokedex_ID'] . "'"));
          $Item_Data = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM `items_owned` WHERE id = '" . $Pokemon_Data['Item'] . "' AND Owner_Current = '" . $User_Data['id'] . "'"));

          if ( $Pokemon_Data['Type'] !== "Normal" ) {
            $Pokemon_Type = $Pokemon_Data['Type'];
          } else {
            $Pokemon_Type = "";
          }

          echo  "<div class='panel' name='{$Database_ID}' style='float: left; width: 32.5%;'>";
          echo    "<div class='panel-heading'>{$Pokemon_Type}{$Pokedex_Data['Name']}</div>";
          echo    "<div class='panel-body' style='background: rgba(29, 33, 43, 1); text-align: left;'>";

          showImage('icon', $Database_ID, 'pokemon', null);

          echo      "<img src='images/Assets/{$Pokemon_Data['Gender']}.svg' style='border-right: 1px solid #4A618F; height: 30px; padding: 3px; width: 28px;' />";

          if ( $Pokemon_Data['Item'] !== '0' )
          {
            echo    "<img src='images/Items/" . $Item_Data['Item_Name'] . ".png' style='border-right: 1px solid #4A618F; padding: 3px;' />";
          }

          echo      " Level: " . number_format($Pokemon_Data['Level']);

          echo    "</div>";
          echo  "</div>";
        }

        echo    "</div>";
        echo  "</div>";
      }

      else if ( $_POST['phase'] == 1 )
      {
        echo "
          <script type='text/javascript'>
            function releasePokemon(req, phase, event) {
              if ( confirm('Are you sure you want to release these Pokemon?') )
              {
                if ( $('#releaseList div').length > 0 )
                {
                  var selectedPokemon = [];
                  $('#releaseList div').each(function(i, selected) {
                      selectedPokemon[i] = $(selected).val();
                  });
                  console.log(selectedPokemon);
                  
                  $.post('ajax/pokecenter.php', { req: 'release', pokemon: JSON.stringify(selectedPokemon), phase: phase }, function(data)
                  {
                    console.log('Request: ' + req + '\\nPokemon: ' + JSON.stringify(selectedPokemon) + '\\nPhase: ' + phase);
                    $('#releaseDiv').html(data);
                  });
                }
              }
              return false;
            }
          </script>

          <!--<input type='button' onclick=\"releasePokemon('release', 0);\" value='Release Pokemon' style='width: 100%;' />-->
          <div class='description' style='border-color: #00ff00; margin-bottom: 3px; margin-top: -1px; width: 100%;'>You have successfully released your Pokemon.</div>
        ";

        $Pokemon = json_decode($_POST['pokemon'], true);

        foreach ( $Pokemon as $num => $Database_ID )
        {
          if ( $Database_ID !== null )
          {
            $Pokemon_Data = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM `pokemon` WHERE id = '" . $Database_ID . "' AND Owner_Current = '" . $User_Data['id'] . "'"));
            $Pokedex_Data = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM `pokedex` WHERE id = '" . $Pokemon_Data['Pokedex_ID'] . "'"));
            $Item_Data = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM `items_owned` WHERE id = '" . $Pokemon_Data['Item'] . "' AND Owner_Current = '" . $User_Data['id'] . "'"));

            # Remove the item from the Pokemon.
            mysqli_query($con, "UPDATE `pokemon` SET Item = '0' WHERE id = '" . $Database_ID . "' WHERE Owner_Current = '" . $User_Data['id'] . "'");
            mysqli_query($con, "UPDATE `items_owned` SET Equipped_To = '0' WHERE Equipped_To = '" . $Database_ID . "'");

            # Copy the Pokemon into the `released` database table.
            mysqli_query($con, "INSERT INTO `released` SELECT * FROM `pokemon` WHERE id = '" . $Database_ID . "'");

            # Delete the copy that exists within the `pokemon` table.
            mysqli_query($con, "DELETE FROM `pokemon` WHERE id = '" . $Database_ID . "'");

            if ( $Pokemon_Data['Type'] !== "Normal" ) {
              $Pokemon_Type = $Pokemon_Data['Type'];
            } else {
              $Pokemon_Type = "";
            }

            echo "
              <div><b>{$Pokemon_Type}{$Pokedex_Data['Name']}</b> - {$Pokemon_Data['Gender']} (Level: " . number_format($Pokemon_Data['Level']) . ")</div>
            ";
          }
        }
      }
    }
    else
    {
      echo "Please select some Pokemon to release.";
    }
  }