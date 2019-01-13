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
      HandleError( $e->getMessage() );
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
            <div class='roster_slot full' style='/*width: calc(100% / 3);*/'>
              <div style='float: left;' class='slots left'>
          ";

          for ($x = 1; $x <= 3; ++$x) {
            if ( $x == $i + 1 || $x > count($Fetch_Roster) )
            {
              echo "<div><span style='color: #000; display: block; padding: 13px;'>$x</span></div>";
            }
            else
            {
              echo "<div><a href='javascript:void(0);' onclick=\"handlePokemon('Move', {$Roster_Slot[$i]['ID']}, $x);\" style='display: block; padding: 13px;'>$x</a></div>";
            }
          }

          echo "
            </div>
            <img src='{$Roster_Slot[$i]['Gender_Icon']}' style='height: 20px; margin: 10px 0px 0px -20px; position: absolute; width: 20px;' />
            <img class='spricon popup cboxElement' src='{$Roster_Slot[$i]['Sprite']}' href='core/ajax/pokemon.php?id={$Roster_Slot[$i]['ID']}' />
            $Item
            <div style='float: right;' class='slots right'>
          ";

          for ($x = 4; $x <= 6; ++$x) {
            if ( $x == $i + 1 || $x > count($Fetch_Roster) )
            {
              echo "<div><span style='color: #000; display: block; padding: 13px;'>$x</span></div>";
            }
            else
            {
              echo "<div><a href='javascript:void(0);' onclick=\"handlePokemon('Move', {$Roster_Slot[$i]['ID']}, $x);\" style='display: block; padding: 13px;'>$x</a></div>";
            }
          }

          echo "
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
            <div class='roster_slot full' style='/*width: calc(100% / 3);*/'>
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
        echo "<img class='spricon' src='{$Pokemon['Icon']}' onclick='displayPokeData({$Pokemon['ID']});'/>";
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
  <div class='panel-body' style='padding: 3px;' id='pokeData'>
    <div style='padding: 5px;'>Please select a Pokemon to view it's statistics.</div>
  </div>
</div>

<script type='text/javascript'>
  $("img.popup.cboxElement").colorbox({ iframe: true, innerWidth: 680, innerHeight: 491 });
</script>

<?php
  }