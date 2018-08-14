<?php
  require 'layout_top.php';
?>

<style>
  .content .box .row .panel { float: left; width: 33%; }
  .content .box .row .panel:nth-child(3n+2) { margin-left: 0.5%; margin-right: 0.5%; }
  .content .box .row .panel .panel-body div:nth-child(4) { background: #44c184; cursor: pointer; float: left; font-weight: bold; width: 50%; }
  .content .box .row .panel .panel-body div:nth-child(5) { background: #fb757a; cursor: pointer; float: left; font-weight: bold; width: 50%; }
</style>

<div class='content'>
  <div class='head'>Trade Interest</div>
  <div class='box'>
    <div class='description' style='margin-bottom: 5px; margin-top: 0px; width: 100%;'>Are you tired of people trading for a Pokemon that you are determined to keep?<br />Set it's trade interest here!</div>
    
    <div class='row'>
      <?php
        $Fetch_Pokemon = mysqli_query($con, "SELECT * FROM `pokemon` WHERE `Owner_Current` = '" . $User_Data['id'] . "'");

        while ( $Pokemon = mysqli_fetch_assoc($Fetch_Pokemon) )
        {
          $Pokedex = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM `pokedex` WHERE `ID` = '" . $Pokemon['Pokedex_ID'] . "'"));

          echo  "<div class='panel'>";
          echo    "<div class='panel-heading'>";
          if ( $Pokemon['Type'] !== "Normal" )
          {
            echo $Pokemon['Type'] . $Pokedex['Name'];
          }
          else
          {
            echo $Pokedex['Name'];
          }
          echo    "</div>";
          echo    "<div class='panel-body'>";
          echo      "<div style='margin: 5px; position: absolute;'>X</div>";
          echo      "<img src='images/Pokemon/{$Pokemon['Type']}/{$Pokemon['Pokedex_ID']}.png' /><br />";
          echo      "<div>YES</div>";
          echo      "<div>NO</div>";
          echo    "</div>";
          echo  "</div>";
        }
      ?>
    </div>
  </div>
</div>

<?php
  require 'layout_bottom.php';
?>