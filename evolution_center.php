<?php
  require 'php/layout_top.php';
?>

<div class='content'>
  <div class='head'>Evolution Center</div>
  <div class='box evolution_center'>
    <div class='description' style='margin: 0px 0px 5px; width: 100%;'>
      Welcome to the Evolution Center.
    </div>

    <div class='row'>
      <div class='panel' style='float: left; margin-right: 0.5%; width: 40%;'>
        <div class='panel-heading'>Roster</div>
        <div class='panel-body roster'>
          <?php
            for ( $i = 1; $i <= 6; $i++ ) {
              $Slot[$i] = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM pokemon WHERE Owner_Current = '" . $row['id'] . "' AND Slot = $i"));
                
              if ( $Slot[$i] ) {
                $Name = mysqli_fetch_assoc(mysqli_query($con, "SELECT `Name` FROM `pokedex` WHERE `ID` = '" . $Slot[$i]["Pokedex_ID"] . "'"));
                $Slot[$i]['Name'] = $Name['Name'];
              }
              else {
                $Slot[$i] = "Empty";
              }
                
              if ( $Slot[$i] !== "Empty" ) {
                $Item = mysqli_fetch_assoc(mysqli_query($con, "SELECT Item_Name FROM items_owned WHERE Equipped_To = '" . $Slot[$i]['ID'] . "'"));
                
                echo	"<div class='roster_slot' onclick='showEvolutions($i)'>";
                  
                if ( $Slot[$i]['Item'] != '0' ) {
                  echo	"<img class='item' style='margin-left: -24px; position: absolute;' src='images/Items/" . $Item['Item_Name'] . ".png' />";
                }
                    
                echo	  "<img class='gender' style='height: 28px; margin-left: 96px; position: absolute; width: 20px;' src='images/Assets/{$Slot[$i]['Gender']}.svg' />";
                    
                echo 		"<img src='images/Pokemon/" . $Slot[$i]['Type'] . "/" . $Slot[$i]['Pokedex_ID'] . ".png' /><br />";
                  
                if ( $Slot[$i]['Type'] !== "Normal" ) {
                  echo $Slot[$i]['Type'] . $Slot[$i]['Name'] . "<br />";
                }
                else {
                  echo $Slot[$i]['Name'] . "<br />";
                }
                  
                echo 	  "<b>Level:</b> " . number_format($Slot[$i]['Level']);
                echo	"</div>";
              } 
              else {
                echo "<div class='roster_slot' style='float: left; padding: 10px;'>";
                echo		"<img src='images/Assets/pokeball.png' /><br />";
                echo		"Empty";
                echo "</div>";
              }
            }
          ?>
        </div>
      </div>

      <div class='panel' style='float: left; margin-bottom: 3px; width: 59.502%;'>
        <div class='panel-heading'>Selected Pokemon</div>
        <div class='panel-body' id='selectedPokemon'>
          <div style='padding: 10px;'>Please select a Pokemon.</div>
        </div>
      </div>
    </div>
  </div>
</div>

<script type='text/javascript'>
  function showEvolutions(slot)
  {
    $('#selectedPokemon').html("<div style='padding: 10px;'>Loading Evolutions..</div>");

    $.ajax({
      type: 'post',
      url: 'ajax/ajax_evocenter.php',
      data: { request: 'display_evos', slot: slot },
      success: function(data) {
        $('#selectedPokemon').html(data);
      },
      error: function(data) {
        $('#selectedPokemon').html(data);
      }
    });
  }
</script>

<?php
  require 'php/layout_bottom.php';
?>