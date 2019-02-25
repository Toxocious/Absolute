<?php
	require '../../required/session.php';

  if ( isset($User_Data['id']) )
	{
    $Page = (isset($_POST['page'])) ? $_POST['page'] : 1;
    $Filter_Type = (isset($_POST['filter_type'])) ? $_POST['filter_type'] : '0';
    $Filter_Gender = (isset($_POST['filter_gender'])) ? $_POST['filter_gender'] : '0';
    $Filter_Dir = (isset($_POST['filter_search_order'])) ? $_POST['filter_search_order'] : 'ASC';

    $Begin = ($Page - 1) * 35;
    if ( $Begin < 0 )
    {
      $Begin = 1;
    }

    $Query = "SELECT `ID` FROM `pokemon` WHERE `Owner_Current` = ? AND `Location` = 'Box'";
    $Inputs = [$User_Data['id']];

    if ( $Filter_Type != '0' )
    {
      $Query .= " AND `type` = ?";
      $Inputs[] = $Filter_Type;
    }

    switch ($Filter_Gender)
    {
      case 'm': $Query .= " AND `gender` = 'Male'"; break;
      case 'f': $Query .= " AND `gender` = 'Female'"; break;
      case 'g': $Query .= " AND `gender` = 'Genderless'"; break;
      case '?': $Query .= " AND `gender` = '(?)'"; break;
    }

    if ( $Filter_Dir != 'ASC' )
    {
      $Filter_Dir = 'DESC';
    }
    else
    {
      $Filter_Dir = 'ASC';
    }

    $Query .= " ORDER BY `Pokedex_ID`, `ID` ASC";

    try
    {
      $Fetch_Pokemon = $PDO->prepare("SELECT `ID` FROM `pokemon` WHERE `Owner_Current` = ? AND `Location` = 'Roster' ORDER BY `Slot` ASC LIMIT 6");
      $Fetch_Pokemon->execute([$User_Data['id']]);
      $Fetch_Pokemon->setFetchMode(PDO::FETCH_ASSOC);
      $Fetch_Roster = $Fetch_Pokemon->fetchAll();

      $Box_Query = $PDO->prepare($Query . " LIMIT " . $Begin . ",35");
      $Box_Query->execute($Inputs);
      $Box_Query->setFetchMode(PDO::FETCH_ASSOC);
      $Box_Pokemon = $Box_Query->fetchAll();
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
            $Item = "<img src='{$Roster_Slot[$i]['Item_Icon']}' style='margin-top: 48px;' />";
          }
          else
          {
            $Item = "";
          }

          $Slots = '';
          for ( $x = 1; $x <= 7; ++$x )
          {
            if ( $x == 7 )
            {
              $Slots .= "
                <div>
                  <a href='javascript:void(0);' onclick=\"handlePokemon('Move', {$Roster_Slot[$i]['ID']}, $x);\" style='padding: 0px 13px; width: calc(100% / 7);'>X</a>
                </div>
              ";
            }
            else if ( $x == $i + 1 || $x > count($Roster) )
            {
              $Slots .= "
                <div>
                  <span style='color: #000; padding: 0px 13px; width: calc(100% / 7);'>$x</span>
                </div>
              ";
            }
            else
            {
              $Slots .= "
                <div>
                  <a href='javascript:void(0);' onclick=\"handlePokemon('Move', {$Roster_Slot[$i]['ID']}, $x);\" style='padding: 0px 13px; width: calc(100% / 7);'>$x</a>
                </div>
              ";
            }
          }
          
          echo "
            <div class='roster_slot full'>
              <div class='slots'>
                $Slots
              </div>

              <div style='float: left; padding-top: 3px; text-align: center; width: 30px;'>
                <img src='{$Roster_Slot[$i]['Gender_Icon']}' style='height: 20px; width: 20px;' /><br />
                $Item
              </div>

              <div style='float: left; margin-left: -30px; padding: 3px;'>
                <img class='spricon popup cboxElement' src='{$Roster_Slot[$i]['Sprite']}' href='" . Domain(1) . "/core/ajax/pokemon.php?id={$Roster_Slot[$i]['ID']}' />
              </div>

              <div class='info_cont' style='float: right; width: 189px;'>
                <div style='font-weight: bold; padding: 2px;'>
                  {$Roster_Slot[$i]['Display_Name']}
                </div>
                <div class='info'>Level</div>
                <div>{$Roster_Slot[$i]['Level']}</div>
                <div class='info'>Experience</div>
                <div>" . number_format($Roster_Slot[$i]['Experience']) . "</div>
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
            <div class='roster_slot full' style='height: 131px; padding: 0px;'>
              <div style='float: left; padding: 18px 3px 3px;'>
                <img class='spricon' src='{$Roster_Slot[$i]['Sprite']}' />
              </div>

              <div class='info_cont' style='float: right; height: 131px; padding-top: 15px; width: 189px;'>
                <div style='font-weight: bold; padding: 2px;'>
                  {$Roster_Slot[$i]['Display_Name']}
                </div>
                <div class='info'>Level</div>
                <div>{$Roster_Slot[$i]['Level']}</div>
                <div class='info'>Experience</div>
                <div>{$Roster_Slot[$i]['Experience']}</div>
              </div>
            </div>
          ";
        }
      }
    ?>
  </div>
</div>

<div class='panel' style='float: left; width: calc(100% / 3);'>
  <div class='panel-heading'>Box</div>
  <div class='panel-body' id='Pokebox'>
    <div class='page_nav'>
      <?php
        Pagi(str_replace('SELECT `ID`', 'SELECT COUNT(*)', $Query), $User_Data['id'], $Inputs, $Page, 'onclick="updateBox(\'' . $Page . '\'); return false;"', 35);
      ?>
    </div>
    <?php
      try
      {
        $Box_Query = $PDO->prepare("SELECT * FROM `pokemon` WHERE `Owner_Current` = ? AND `Slot` = 7 ORDER BY `Pokedex_ID` ASC LIMIT 35");
        $Box_Query->execute([$User_Data['id']]);
        $Box_Query->setFetchMode(PDO::FETCH_ASSOC);
        $Box_Pokemon = $Box_Query->fetchAll();
      }
      catch (PDOException $e)
      {
        HandleError( $e->getMessage() );
      }

      echo "<div style='height: 156px; padding: 3px;'>";
      foreach ( $Box_Pokemon as $Index => $Pokemon )
      {
        $Pokemon = $PokeClass->FetchPokemonData($Pokemon['ID']);
        echo "<img class='spricon' src='{$Pokemon['Icon']}' onclick='displayPokeData({$Pokemon['ID']});'/>";
      }
      echo "</div>";

      if ( count($Box_Pokemon) == 0 )
      {
        echo "<div style='padding: 3px;'>No Pokemon were found in your box.</div>";
      }
    ?>
  </div>
</div>

<div class='panel' id='pokeData' style='float: right; width: calc(100% / 1.5 - 5px);'>
  <div class='panel-heading'>Selected Pokemon</div>
  <div class='panel-body' style='padding: 3px;'>
    <div style='padding: 5px;'>Please select a Pokemon to view it's statistics.</div>
  </div>
</div>

<script type='text/javascript'>
  $("img.popup.cboxElement").colorbox({ iframe: true, innerWidth: 680, innerHeight: 491 });

  var CurrentSearch = [
    0, 0, 0
  ];

  function filterSelect(row, type)
  {
    switch (row)
    {
      case 1: var Cells = ['normal', 'shiny', 'sunset', 'shinysunset']; break;
      //case 2: var Cells = ['A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','s3']; break; //thats Shift+3, noobs
      case 2: var Cells = ['female','male','genderless','q']; break;
      //case 3: var Cells = ['level','pokedex','id','abc','iv','item']; break;
      case 3: var Cells = ['ASC','DESC']; break;
    }

    for (var i = 0; i < Cells.length; ++i)
    {
      $('#'+row+'_'+Cells[i]).css({"color":"","cursor":"pointer"});
    }

    if (CurrentSearch[row-1] != type)
    {
      $('#'+row+'_'+type).css({"color":"black","cursor":"auto"});
      CurrentSearch[row-1] = type;
    }
    else
    {
      CurrentSearch[row-1] = 0;
    }
  }

  function updateBox(page)
  {
    if ( page == 'auto' )
    {
      page = currpage;
    }
    else
    {
      currpage = page;
    }

    $.ajax({
      url:'core/ajax/pokecenter/box.php',
      type: 'POST',
      data: {
        id: parseInt(<?= $User_Data['id']; ?>),
        filter_type: CurrentSearch[0],
        //filter_search: $('[name=pokemon_search]').val(),
        //filter_select: $('[name=pokemon_select]').val(),
        filter_gender: CurrentSearch[1],
        filter_search_order: CurrentSearch[2],
        //filter_order: CurrentSearch[2],
        page: page
      },
      success: function(data)
      {
        $('#Pokebox').html(data);
      },
      error: function(data)
      {
        $('#Pokebox').html('<div class="error">An error has occurred. Please refresh the page.</div>');
      }
    });
  }
</script>

<?php
  }