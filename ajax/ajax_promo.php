<?php
  require 'session.php';

  $genderless = array("Arceus", "Articuno", "Azelf", "Baltoy", "Beldum", "Blacephalon", "Bronzong", "Bronzor", "Buzzwole", "Carbink", "Celebi", "Celesteela", "Claydol", "Cobalion", "Cosmoem", "Cosmog", "Cryogonal", "Darkrai", "Deoxys", "Dhelmise", "Dialga", "Diancie", "Ditto", "Electrode", "Entei", "Genesect", "Giratina", "Golett", "Golurk", "Groudon", "Guzzlord", "Ho-Oh", "Hoopa", "Jirachi", "Kartana", "Keldeo", "Klang", "Klink", "Klinklang", "Kyogre", "Kyurem", "Lugia", "Lunala", "Lunatone", "Magearna", "Magnemite", "Magneton", "Magnezone", "Manaphy", "Marshadow", "Meloetta", "Mesprit", "Metagross", "Metang", "Mew", "Mewtwo", "Minior", "Moltres", "Naganadel", "Necrozma", "Nihilego", "Palkia", "Pheromosa", "Phione", "Poipole", "Porygon", "Porygon-Z", "Porygon2", "Raikou", "Rayquaza", "Regice", "Regigigas", "Regirock", "Registeel", "Reshiram", "Rotom", "Shaymin", "Shedinja", "Silvally", "Solgaleo", "Solrock", "Stakataka", "Starmie", "Staryu", "Suicune", "Tapu Bulu", "Tapu Fini", "Tapu Koko", "Tapu Lele", "Terrakion", "Type: Null", "Unown", "Uxie", "Victini", "Virizion", "Volcanion", "Voltorb", "Xerneas", "Xurkitree", "Yveltal", "Zapdos", "Zekrom", "Zeraora", "Zygarde");

  date_default_timezone_set('America/Los_Angeles');
	$Date = date("M dS, Y g:i:s A");

  $User_Data = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM members WHERE id = '" . $row['id'] . "'"));
  $Promo = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM promo WHERE Promo_Active = 'True'"));
  $Dex = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM pokedex WHERE id = '" . $Promo['Promo_Dex'] . "'"));

  if ( isset($_POST['request']) ) {
    # Requesting to obtain the promo.
    if ( $_POST['request'] === 'obtain' ) {
      # The user hasn't obtained the promo yet.
      if ( $User_Data['Promo_Claimed'] === 'False' ) {
        # The user meets all three of the requirements to obtain the promo.
        if ( $User_Data['Promo_Status_1'] >= 25 && $User_Data['Promo_Status_2'] >= 10 && $User_Data['Promo_Status_3'] >= 5 ) {
          # Aquire random gender.
          if ( in_array($Promo['Promo_Name'], $genderless) ) {
            $gender = 'Genderless';
          } else {
            $randInt = mt_rand(1, 10);

            if ( $randInt < 6 ) { 
              $gender = 'Female'; 
            } else { 
              $gender = 'Male'; 
            }
          }

          $randIV1 = mt_rand(1, 31);
          $randIV2 = mt_rand(1, 31);
          $randIV3 = mt_rand(1, 31);
          $randIV4 = mt_rand(1, 31);
          $randIV5 = mt_rand(1, 31);
          $randIV6 = mt_rand(1, 31);

          # Update the database.
          mysqli_query($con, "UPDATE members SET Promo_Claimed = 'True' WHERE id = '" . $User_Data['id'] . "'");
          
          mysqli_query($con, "INSERT INTO `pokemon`(`Pokedex_ID`, `Type`, `Slot`, `Owner_Current`, `Owner_Original`, `Gender`, `HP`, `Attack`, `Defense`, `SpAttack`, `SpDefense`, `Speed`, `IV_HP`, `IV_Attack`, `IV_Defense`, `IV_SpAttack`, `IV_SpDefense`, `IV_Speed`, `Creation_Date`) VALUES ('" . $Promo['Promo_Dex'] . "','" . $Promo['Promo_Type'] . "','7','" . $User_Data['id'] . "','" . $User_Data['id'] . "','" . $gender . "','" . $Dex['HP'] . "','" . $Dex['Attack'] . "','" . $Dex['Defense'] . "','" . $Dex['SpecialAttack'] . "','" . $Dex['SpecialDefense'] . "','" . $Dex['Speed'] . "','" . $randIV1 . "','" . $randIV2 . "','" . $randIV3 . "','" . $randIV4 . "','" . $randIV5 . "','" . $randIV6 . "','" . $Date . "')");

          # Echo to the user that they obtained the promo.
          echo "<div class='success'>You have successfully obtained the promotional Pokemon!</div>";
        }
        # The user doesn't meet all three of the requirements to obtain the promo.
        else {
          echo "<div class='error'>You do not meet the requirements to obtain the promotional Pokemon.</div>";
        }
      }
      # The user has already obtained the promo.
      else {
        echo "<div class='error'>You have already obtained the promotional Pokemon. Come back next week!</div>";
      }
    }

    # Toggle Promo History
    else if ( $_POST['request'] === 'history' ) {
      # Show previous promos.
      if ( $_POST['toggle'] === 'show' ) {
        echo 'showing previous promos';
      }

      # Hide previous promos.
      else if ( $_POST['toggle'] === 'hide' ) {
        echo `
          <div class='panel'>
            <div class='panel-heading'>Promo Information</div>
            <div class='panel-body'>
              Welcome to the Promotional Pokemon Center!<br /><br />
              Every Sunday at the stroke of midnight, the promo will change.<br /><br />
              In order to obtain the promo, you'll have to complete a set list of requirements, displayed to your right.<br /><br />
              Please note that the promo is only obtainable once per rotation, and that any attempts to exploit this will result in a ban.
            </div>
          </div>
      
          <div class='panel'>
            <div class='panel-heading'>Current Promo</div>
            <div class='panel-body'>
              <div class='left-col'>
                <div class='heading'>`;
                 
          if ( $Promo_Data['Promo_Type'] !== "Normal" ) {
            echo $Promo_Data['Promo_Type'];
          }
      
          echo $Promo_Data['Promo_Name'];

          echo `</div>
               <div style='padding-top: 15px;'>`;

          echo "<img src='images/Pokemon/" . $Promo_Data['Promo_Type'] . "/" . $Promo_Data['Promo_Dex'] . ".png' />";
          
          echo `</div>
              </div>
              <div class='right-col'>
                <div class='heading'>Requirements</div>
                <div style='padding: 0px 5px; text-align: left;'>
                  <b>Complete 25 Battles</b><br />`;
                  
          echo " ~ ";
          if ( $User_Data['Promo_Status_1'] > 25 ) {
            echo "25";
          } else {
            echo $User_Data['Promo_Status_1'];
          }
          echo " / 25<br />";
      
          echo `<b style='display: block; margin-top: 4px !important;'>Capture 10 Pokemon</b>`;
                  
          echo " ~ ";
          if ( $User_Data['Promo_Status_2'] > 10 ) {
            echo "10";
          } else {
            echo $User_Data['Promo_Status_2'];
          }
          echo " / 10<br />";
                  
      
          echo `<b style='display: block; margin-top: 4px !important;'>Hatch 5 Pokemon</b>`;
                  
          echo " ~ ";
          if ( $User_Data['Promo_Status_3'] > 5 ) {
            echo "5";
          } else {
            echo $User_Data['Promo_Status_3'];
          }
          echo " / 5<br />";
                  
          echo `</div>
              </div>`;
              
          echo `<div style='margin-top: 13px;'>
                <a href='javascript:void(0);' onclick='promoHistory("show");'>Show Previous Promos</a>`;
                
          if ( $User_Data['Promo_Claimed'] === 'True' ) {
            echo "<a href='javascript:void(0);'>Already Obtained</a>";
          } else {
            echo "<a href='javascript:void(0);' onclick='obtainPromo();'>Obtain Promo</a>";
          }
                
          echo `</div>
            </div>
          </div>`;
      }

      # The user has altered the JS; display an error.
      else {
        echo "<div class='error'>An error has occured when attempting to process your request.</div>";
      }
    }

    # The user has altered the JS; display an error.
    else {
      echo "<div class='error'>An error has occured when attempting to process your request.</div>";
    }
  }

  # The user has altered the JS; display an error.
  else {
    echo "<div class='error'>An error has occured when attempting to process your request.</div>";
  }
?>