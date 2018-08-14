<?php
  require_once '../session.php';
  require_once '../global_functions.php';

  if ( $_SERVER['REQUEST_METHOD'] === 'GET' )
  {
    if ( isset($_GET['request']) )
    {
      if ( $_GET['request'] === 'refresh' )
      {
        $Fetch_Egg = mysqli_query($con, "SELECT `ID` FROM `lab` ORDER BY RAND() LIMIT 5;");
          
        foreach ( $Fetch_Egg as $ID )
        {
          $Egg_Data = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM `lab` WHERE `ID` = {$ID['ID']}"));

          echo "
            <div class='lab-egg' onclick='getEgg({$ID['ID']});'>
              <img src='images/Pokemon/egg.png' />
            </div>
          ";
        }
      }
      else
      {
        echo "Nice try, fella.";
      }
    }
  }

  else if ( $_SERVER['REQUEST_METHOD'] === 'POST' )
  {
    if ( isset($_POST['request']) )
    {
      if ( $_POST['request'] === 'get' )
      {
        if ( isset($_POST['id']) )
        {
          // give the user the egg
          mysqli_query($con, "INSERT INTO pokemon (Pokedex_ID, `Name`, Sprite_Version, Steps, Owner_Current, Owner_Original) SELECT Pokedex_ID, `Name`, Sprite_Version, Steps, '{$User_Data['id']}', '{$User_Data['id']}' FROM lab WHERE ID = '" . $_POST['id'] . "'");
          mysqli_query($con, "DELETE FROM lab WHERE ID = '" . $_POST['id'] . "'");

          // generate a new egg to replace the old one
          generateEgg();

          // Reload the eggs in the lab.
          $Fetch_Egg = mysqli_query($con, "SELECT `ID` FROM `lab` ORDER BY RAND() LIMIT 5;");
          $Egg_Count = mysqli_num_rows(mysqli_query($con, "SELECT * FROM lab"));

          echo "<div class='description' style='border-color: #00ff00; margin: 0px 0px 3px; width: 100%;'>You have taken an egg from the Laboratory. >:D</div>";

          echo "
            <div class='description' style='margin-bottom: 3px; margin-top: 0px; width: 100%;'>
              Welcome to the Pokemon Lab!<br />
              We find some eggs on occasion, and you'll be able to pick some up here.
            </div>
          ";

          echo "
            <div class='panel panel-default'>
              <div class='panel-heading'>
                <div style='float: left; font-style: normal !important;'>
                  There are " . number_format($Egg_Count) . " eggs in the lab.
                </div>
                <div style='margin-right: 190px !important;'>
                  Available Eggs
                </div>
                <div id='refresh' style='float: left; margin-left: 97.5%; margin-top: -20px;'>
                  <img src='images/Assets/options.png' onclick='refreshLab();' style='height: 22px; width: 22px;' />
                </div>
              </div>
              <div class='panel-body' id='lab_eggs'>
          ";
            
          foreach ( $Fetch_Egg as $ID )
          {
            $Egg_Data = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM `lab` WHERE `ID` = {$ID['ID']}"));

            echo "
              <div class='lab-egg' onclick='getEgg({$ID['ID']});'>
                <img src='images/Pokemon/egg.png' />
              </div>
            ";
          }

          echo "
              </div>
            </div>
          ";
        }
        else
        {
          // Reload the eggs in the lab.
          $Fetch_Egg = mysqli_query($con, "SELECT `ID` FROM `lab` ORDER BY RAND() LIMIT 5;");
          $Egg_Count = mysqli_num_rows(mysqli_query($con, "SELECT * FROM lab"));

          echo "<div class='description' style='border-color: #ff0000; margin: 0px 0px 3px; width: 100%;'>You're not bamboozling me, motherfucker.</div>";

          echo "
            <div class='description' style='margin-bottom: 3px; margin-top: 0px; width: 100%;'>
              Welcome to the Pokemon Lab!<br />
              We find some eggs on occasion, and you'll be able to pick some up here.
            </div>
          ";

          echo "
            <div class='panel panel-default'>
              <div class='panel-heading'>
                <div style='float: left; font-style: normal !important;'>
                  There are " . number_format($Egg_Count) . " eggs in the lab.
                </div>
                <div style='margin-right: 190px !important;'>
                  Available Eggs
                </div>
                <div id='refresh' style='float: left; margin-left: 97.5%; margin-top: -20px;'>
                  <img src='images/Assets/options.png' onclick='refreshLab();' style='height: 22px; width: 22px;' />
                </div>
              </div>
              <div class='panel-body' id='lab_eggs'>
          ";
            
          foreach ( $Fetch_Egg as $ID )
          {
            $Egg_Data = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM `lab` WHERE `ID` = {$ID['ID']}"));

            echo "
              <div class='lab-egg' onclick='getEgg({$ID['ID']});'>
                <img src='images/Pokemon/egg.png' />
              </div>
            ";
          }

          echo "
              </div>
            </div>
          ";
        }
      }
      else
      {
        echo "Again, nice try fella.";
      }
    }
  }