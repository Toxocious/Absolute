<?php
  require '../../required/session.php';

  if ( isset($_GET['User_ID']) )
  {
    $User_ID = Purify($_GET['User_ID']);

    $Current_Page = isset($_GET['Page']) ? Purify($_GET['Page']) : 1;
    $Filter_Type = (isset($_GET['filter_type'])) ? Purify($_GET['filter_type']) : '0';
    $Filter_Gender = (isset($_GET['filter_gender'])) ? Purify($_GET['filter_gender']) : '0';
    $Filter_Dir = (isset($_GET['filter_search_order'])) ? Purify($_GET['filter_search_order']) : 'ASC';

    $Display_Limit = 26;

    $Begin = ($Current_Page - 1) * $Display_Limit;
    if ( $Begin < 0 )
      $Begin = 1;

    $Query = "SELECT `ID` FROM `pokemon` WHERE `Owner_Current` = ?";
    $Inputs = [$User_ID];

    if ( $Filter_Type != '0' )
    {
      $Query .= " AND `type` = ?";
      $Inputs[] = $Filter_Type;
    }

    switch ($Filter_Gender)
    {
      case 'm':
        $Query .= " AND `gender` = 'Male'";
        break;
      case 'f':
        $Query .= " AND `gender` = 'Female'";
        break;
      case 'g':
        $Query .= " AND `gender` = 'Genderless'";
        break;
      case '?':
        $Query .= " AND `gender` = '(?)'";
        break;
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
      $Box_Query = $PDO->prepare($Query . " LIMIT " . $Begin . ",26");
      $Box_Query->execute($Inputs);
      $Box_Query->setFetchMode(PDO::FETCH_ASSOC);
      $Box_Pokemon = $Box_Query->fetchAll();
    }
    catch ( PDOException $e )
    {
      HandleError( $e->getMessage() );
    }
?>

<tbody>
  <?= Pagination(str_replace('SELECT `ID`', 'SELECT COUNT(*)', $Query), $Inputs, $User_ID, $Current_Page, $Display_Limit, 2, "onclick='UpdateBox([PAGE]);'"); ?>
</tbody>
<tbody>
  <?php
    if ( count($Box_Pokemon) == 0 )
    {
      echo "
        <tr>
          <td colspan='10' style='padding: 5px;'>
            No Pokemon have been found given your search parameters.
          </td>
        </tr>
      ";
    }
    else
    {
      $Pokemon_Count = 0;
      foreach ( $Box_Pokemon as $Index => $Pokemon )
      {
        $Poke_Data = $Poke_Class->FetchPokemonData($Pokemon['ID']);

        if ( $Pokemon_Count % 2 == 0 )
          echo "</tr><tr>";

        echo "
          <td colspan='7' class='popup cboxElement' href='" . DOMAIN_ROOT . "/core/ajax/pokemon.php?id={$Poke_Data['ID']}' style='width: 171px;'>
            <div style='float: left;'>
              <img src='{$Poke_Data['Icon']}' />
            </div>
            <div>
              <span style='font-size: 12px; padding-top: 0px;'>
                {$Poke_Data['Display_Name']}
                <br />
                (Level: {$Poke_Data['Level']})
              </span>
            </div>
          </td>
        ";

        $Pokemon_Count++;
      }

      if ( $Pokemon_Count % 2 == 1 )
      {
        echo "<td colspan='7'></td>";
      }
    }
  ?>
</tbody>

<script type='text/javascript'>
  $(".popup.cboxElement").colorbox({ iframe: true, innerWidth: 680, innerHeight: 491 });

  let CurrentSearch = [
    0, 0, 0
  ];

  function toggleFilter()
  {
    if ( $('div.panel-body.filter').css('display') == 'none' )
    {
      $('div.panel-body.filter').css('display', 'block');
    }
    else
    {
      $('div.panel-body.filter').css('display', 'none');
    }
  }

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

  const Update_Page = (page) =>
  {
    if (page == 'auto')
      page = currpage;
    else
      currpage = page;

    $.ajax({
      url:'core/ajax/profile/box.php',
      type: "POST",
      data: {
        id: <?= $User_ID; ?>,
        filter_type: CurrentSearch[0],
        filter_gender: CurrentSearch[1],
        filter_search_order: CurrentSearch[2],
        page: page
      },
      success: function(data)
      {
        $('#ProfileAJAX').html(data);
        $(".popup.cboxElement").colorbox({ iframe: true, innerWidth: 680, innerHeight: 491 });
      },
      error: function(data)
      {
        $('#ProfileAJAX').html('<div class="error">An error has occurred. Please refresh the page.</div>');
      }
    });
  }
</script>

<?php
  }
  else
  {
    echo "
      <tbody>
        <tr>
          <td>
            An invalid user has been selected.
          </td>
        </tr>
      </tbody>
    ";
  }
