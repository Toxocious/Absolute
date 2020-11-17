<?php
	require '../../required/session.php';

  if ( isset($User_Data['id']) )
	{
    /**
     * Set the search query for the user's box.
     */
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

    /**
     * Perform the necessary PDO queries.
     */
    try
    {
      $Fetch_Roster = $PDO->prepare("SELECT `ID` FROM `pokemon` WHERE `Owner_Current` = ? AND `Location` = 'Roster' ORDER BY `Slot` ASC LIMIT 6");
      $Fetch_Roster->execute([$User_Data['id']]);
      $Fetch_Roster->setFetchMode(PDO::FETCH_ASSOC);
      $Roster = $Fetch_Roster->fetchAll();

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

<table class='border-gradient' style='flex-basis: 100%;'>
  <thead>
    <th colspan='21'>
      Roster
    </th>
  </thead>

  <tbody>
    <?php
      $Items = '';
      $Slots = '';
      $Sprites = '';

      for ( $Slot = 0; $Slot < 6; $Slot++ )
      {
        if ( isset($Roster[$Slot]) )
        {
          $Pokemon = $Poke_Class->FetchPokemonData($Roster[$Slot]['ID']);

          $Sprites .= "
            <td colspan='3'>
              <img src='{$Pokemon['Sprite']}' />
            </td>
            <td colspan='4'>
              <b>{$Pokemon['Display_Name']}</b><br />
              <b>Level</b><br />
              {$Pokemon['Level']}<br />
              <b>Experience</b><br />
              {$Pokemon['Experience']}
            </td>
          ";

          $Items .= "<img src='{$Pokemon['Item_Icon']}' style='margin-top: 48px;' />";

          for ( $x = 1; $x <= 7; ++$x )
          {
            if ( $x == 7 )
            {
              $Slots .= "
                <td>
                  <a href='javascript:void(0);' onclick=\"handlePokemon('Move', {$Pokemon['ID']}, $x);\" style='padding: 0px 13px;'>X</a>
                </td>
              ";
            }
            else if ( $x == $Slot + 1 || $x > count($Roster) )
            {
              $Slots .= "
                <td>
                  <span style='color: #000; padding: 0px 13px;'>$x</span>
                </td>
              ";
            }
            else
            {
              $Slots .= "
                <td>
                  <a href='javascript:void(0);' onclick=\"handlePokemon('Move', {$Pokemon['ID']}, $x);\" style='padding: 0px 13px;'>$x</a>
                </td>
              ";
            }
          }
        }
        else
        {
          $Sprites .= "
            <td colspan='7'>
              <img src='" . DOMAIN_SPRITES . "/Pokemon/Sprites/0.png' />
            </td>
          ";

          $Items .= "ITEM";

          for ( $x = 1; $x <= 7; $x++ )
          {
            $Slots .= "
              <td>
                <span style='color: #000; padding: 0px 13px;'>$x</span>
              </td>
            ";
          }
        }

        if ( ($Slot + 1) % 3 === 0 )
        {
          echo "
            <tr>
              {$Slots}
            </tr>
            <tr>
              {$Sprites}
            </tr>
          ";

          $Items = '';
          $Slots = '';
          $Sprites = '';
        }
      }
    ?>
  </tbody>
</table>

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
?>

<div class='panel' style='flex-basis: calc(100% / 3 - 10px); margin: 5px 3px 5px 10px;'>
  <div class='head'>Box</div>
  <div class='body' id='Pokebox'>
    <?php
      if ( count($Box_Pokemon) == 0 )
      {
        echo "
          <div class='flex' style='align-items: center; justify-content: center; height: 209px;'>
            <div style='flex-basis: 100%'>
              No Pok&eacute;mon were found in your box.
            </div>
          </div>
        ";
      }
      else
      {
        $Pagination = Pagi(str_replace('SELECT `ID`', 'SELECT COUNT(*)', $Query), $User_Data['id'], $Inputs, $Page, 'onclick="updateBox(\'' . $Page . '\'); return false;"', 35);

        echo "
          {$Pagination}
          <div style='height: 172px; padding: 0px 0px 5px;'>
        ";
        
        foreach ( $Box_Pokemon as $Index => $Pokemon )
        {
          $Pokemon = $Poke_Class->FetchPokemonData($Pokemon['ID']);
          echo "<img class='spricon' src='{$Pokemon['Icon']}' onclick='displayPokeData({$Pokemon['ID']});'/>";
        }

        echo "
          </div>
        ";
      }
    ?>
  </div>
</div>

<div class='panel' style='flex-basis: calc(100% / 3 * 2 - 30px); margin: 5px 3px 5px 8px;'>
  <div class='head'>Selected Pokemon</div>
  <div class='body' style='height: 203px; padding: 3px;'>
      <div class='flex' id='pokeData' style='align-items: center; justify-content: center; height: inherit;'>
        <div style='flex-basis: 100%;'>Please select a Pokemon to view it's statistics.</div>
    </div>
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