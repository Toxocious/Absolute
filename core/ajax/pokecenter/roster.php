<?php
	require '../../required/session.php';

  if ( isset($User_Data['id']) )
	{
    try
    {
      $Fetch_Pokemon = $PDO->prepare("SELECT `ID` FROM `pokemon` WHERE `Owner_Current` = ? AND `Location` = 'Roster' ORDER BY `Slot` ASC LIMIT 6");
      $Fetch_Pokemon->execute([$User_Data['id']]);
      $Fetch_Pokemon->setFetchMode(PDO::FETCH_ASSOC);
      $Fetch_Roster = $Fetch_Pokemon->fetchAll();
    }
    catch ( PDOException $e )
    {
      echo $e->getMessage();
    }
?>
<div class='panel' style='margin-bottom: 5px; width: 100%;'>
  <div class='panel-heading'>Roster</div>
  <div class='panel-body' id='pokecenter_roster'>
    <?php
      for ( $i = 0; $i <= 5; $i++ )
      {
        if ( isset($Fetch_Roster[$i]['ID']) )
        {
          $Roster_Slot[$i] = $PokeClass->FetchPokemonData($Fetch_Roster[$i]['ID']);

          if ( $Roster_Slot[$i]['Item'] != null )
          {
            $Item = "<img src='{$Roster_Slot[$i]['Item_Icon']}' style='margin: 5px 0px 0px -10px; position: absolute;' />";
          }
          else
          {
            $Item = "";
          }
          
          echo "
            <div class='roster_slot' style='width: calc(100% / 3);'>
              <div style='float: left;'>
                <div style='background: #334364; border-right: 1px solid #4A618F; height: calc(132px / 3); margin-top: -5px;'><a href='javascript:void(0);' onclick=\"handlePokemon('Move', {$Roster_Slot[$i]['ID']}, 1);\" style='display: block; padding: 10px;'>1</a></div>
                <div style='background: #425780; border-right: 1px solid #4A618F; height: calc(131px / 3); margin-top: -5px;'><a href='javascript:void(0);' onclick=\"handlePokemon('Move', {$Roster_Slot[$i]['ID']}, 2);\" style='display: block; padding: 10px;'>2</a></div>
                <div style='background: #334364; border-right: 1px solid #4A618F; height: calc(131px / 3); margin-top: -5px;'><a href='javascript:void(0);' onclick=\"handlePokemon('Move', {$Roster_Slot[$i]['ID']}, 3);\" style='display: block; padding: 10px;'>3</a></div>
              </div>
              <img src='{$Roster_Slot[$i]['Gender']}' style='height: 20px; margin: 10px 0px 0px -20px; position: absolute; width: 20px;' />
              <img src='{$Roster_Slot[$i]['Sprite']}' ?>
              $Item
              <div style='float: right;'>
                <div style='background: #334364; border-left: 1px solid #4A618F; height: calc(132px / 3); margin-top: -5px;'><a href='javascript:void(0);' onclick=\"handlePokemon('Move', {$Roster_Slot[$i]['ID']}, 4);\" style='display: block; padding: 10px;'>4</a></div>
                <div style='background: #425780; border-left: 1px solid #4A618F; height: calc(131px / 3); margin-top: -5px;'><a href='javascript:void(0);' onclick=\"handlePokemon('Move', {$Roster_Slot[$i]['ID']}, 5);\" style='display: block; padding: 10px;'>5</a></div>
                <div style='background: #334364; border-left: 1px solid #4A618F; height: calc(131px / 3); margin-top: -5px;'><a href='javascript:void(0);' onclick=\"handlePokemon('Move', {$Roster_Slot[$i]['ID']}, 6);\" style='display: block; padding: 10px;'>6</a></div>
              </div>
              <div><b>{$Roster_Slot[$i]['Display_Name']}</b></div>
              <div class='info'>
                <div>Level</div>
                <div>{$Roster_Slot[$i]['Level']}</div>
              </div>
              <div class='info'>
                <div>Experience</div>
                <div>{$Roster_Slot[$i]['Experience']}</div>
              </div>
          </div>
          ";
        }
        else
        {
          $Roster_Slot[$i]['Sprite'] = Domain(3) . 'images/pokemon/0.png';
          $Roster_Slot[$i]['Display_Name'] = 'Empty';
          $Roster_Slot[$i]['Level'] = '0';
          $Roster_Slot[$i]['Experience'] = '0';

          echo "
            <div class='roster_slot' style='width: calc(100% / 3);'>
              <img src='{$Roster_Slot[$i]['Sprite']}' />
              <div><b>{$Roster_Slot[$i]['Display_Name']}</b></div>
              <div class='info'>
                <div>Level</div>
                <div>{$Roster_Slot[$i]['Level']}</div>
              </div>
              <div class='info'>
                <div>Experience</div>
                <div>{$Roster_Slot[$i]['Experience']}</div>
              </div>
            </div>
          ";
        }
      }
    ?>
  </div>
</div>

<div class='panel' style='float: left; width: calc(100% / 2 - 2.5px);'>
  <div class='panel-heading'>Box</div>
  <div class='panel-body' style='padding: 3px;'>
    <?php
      try
      {
        $Box_Query = $PDO->prepare("SELECT * FROM `pokemon` WHERE `Owner_Current` = ? AND `Slot` = 7 ORDER BY `Pokedex_ID` ASC LIMIT 50");
        $Box_Query->execute([$User_Data['id']]);
        $Box_Query->setFetchMode(PDO::FETCH_ASSOC);
        $Box_Pokemon = $Box_Query->fetchAll();
      }
      catch (PDOException $e)
      {
        echo $e->getMessage();
      }

      foreach ( $Box_Pokemon as $Index => $Pokemon )
      {
        $Pokemon = $PokeClass->FetchPokemonData($Pokemon['ID']);
        echo "<img class='popup cboxElement' src='{$Pokemon['Icon']}' href='core/ajax/pokemon.php?id={$Pokemon['ID']}' />";
      }

      if ( count($Box_Pokemon) == 0 )
      {
        echo "No Pokemon were found in your box.";
      }
    ?>
  </div>
</div>

<div class='panel' style='float: right; width: calc(100% / 2 - 2.5px);'>
  <div class='panel-heading'>Selected Pokemon</div>
  <div class='panel-body' style='padding: 3px;' id='dataDiv'>
    <div style='padding: 5px;'>Please select a Pokemon to view it's statistics.</div>
  </div>
</div>

<script type='text/javascript'>
  $("img.popup.cboxElement").colorbox({ iframe: true, innerWidth: 680, innerHeight: 491 });
</script>

<?php
  }
  else
  {
    echo "Your session has expired, or an error has been thrown while attempting to retrieve your roster and boxed Pokemon.";
  }