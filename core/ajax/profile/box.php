<?php
  require '../../required/session.php';

  if ( isset($_POST['id']) )
    $User_ID = $_POST['id'];

  if ( isset($_GET['id']) )
    $User_ID = $_GET['id'];

  $Page = (isset($_POST['page'])) ? $_POST['page'] : 1;
  $Filter_Type = (isset($_POST['filter_type'])) ? $_POST['filter_type'] : '0';
  $Filter_Gender = (isset($_POST['filter_gender'])) ? $_POST['filter_gender'] : '0';
  $Filter_Dir = (isset($_POST['filter_search_order'])) ? $_POST['filter_search_order'] : 'ASC';

  $Begin = ($Page - 1) * 36;
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
    $Box_Query = $PDO->prepare($Query . " LIMIT " . $Begin . ",36");
    $Box_Query->execute($Inputs);
    $Box_Query->setFetchMode(PDO::FETCH_ASSOC);
    $Box_Pokemon = $Box_Query->fetchAll();
  }
  catch ( PDOException $e )
  {
    HandleError( $e->getMessage() );
  }
  
  if ( isset($User_ID) )
  {
?>

  <!--
  <div class='panel' style='margin-bottom: 5px;'>
    <div class='panel-heading'>Filter</div>
    <div class='panel-body toggle' style='cursor: pointer; padding: 3px;' onclick='toggleFilter();'>Toggle Filter Options</div>
    <div class='panel-body filter' style='display: none;'>
      <div class='p_search'>
        <input type='text' name='pokemon_search' placeholder='Search For a Pokemon' />
        <select name='pokemon_select'>
          <option>Please Select A Pokemon</option>
        </select>
      </div>

      <div class='p_type'>
        <div><a href='javascript:void(0);' id='1_normal' onclick="filterSelect(1, 'normal');">Normal</a></div>
        <div><a href='javascript:void(0);' id='1_shiny' onclick="filterSelect(1, 'shiny');">Shiny</a></div>
        <div><a href='javascript:void(0);' id='1_sunset' onclick="filterSelect(1, 'sunset');">Sunset</a></div>
        <div><a href='javascript:void(0);' id='1_shinysunset' onclick="filterSelect(1, 'shinysunset');">Shiny Sunset</a></div>
      </div>

      <div class='p_gender'>
        <div><a href='javascript:void(0);' id='2_female' onclick="filterSelect(2, 'f');">Female</a></div>
        <div><a href='javascript:void(0);' id='2_male' onclick="filterSelect(2, 'm');">Male</a></div>
        <div><a href='javascript:void(0);' id='2_genderless' onclick="filterSelect(2, 'g');">Genderless</a></div>
        <div><a href='javascript:void(0);' id='2_q' onclick="filterSelect(2, 'q');">(?)</a></div>
      </div>

      <div class='p_sortby'>
        <div><a href='javascript:void(0);' id='3_asc' onclick="filterSelect(3, 'asc');">Ascending</a></div>
        <div><a href='javascript:void(0);' id='3_desc' onclick="filterSelect(3, 'desc');">Descending</a></div>
      </div>

      <div class='search'>
        <div><a href='javascript:void(0);' onclick="updateBox();">Apply Filters</a></div>
      </div>
    </div>
  </div>
  -->
  
  <div class='panel'>
    <div class='panel-heading'>
      Box
      <div style='float: right;'>
        <a href='#'>Filter</a>
      </div>
    </div>
    <div class='panel-body'>
      <div class='page_nav'>
        <?php
          Pagi(str_replace('SELECT `ID`', 'SELECT COUNT(*)', $Query), $User_ID, $Inputs, $Page, 'onclick="updateBox(\'' . $Page . '\'); return false;"');
        ?>
      </div>
      <table class='box_cont' style='width: 100%;'>
        <?php                  
          $Pokemon_Count = 0;
          foreach ( $Box_Pokemon as $Index => $Pokemon )
          {
            $Poke_Data = $PokeClass->FetchPokemonData($Pokemon['ID']);

            if ( $Pokemon_Count % 3 == 0 )
            {
              echo "</tr><tr>";
            }
        ?>

            <td class='box_slot popup cboxElement' href='<?= Domain(1); ?>/core/ajax/pokemon.php?id=<?= $Poke_Data['ID']; ?>'>
              <img src='images/Assets/<?= $Poke_Data['Gender']; ?>.svg' style='float: left; height: 20px; margin-top: 5px; width: 20px;' />
              <!--<img src='images/Assets/Female.svg' style='float: left; height: 20px; margin-top: 5px; width: 20px;' />-->
              <span style='float: left;'>
                <img src='<?= $Poke_Data['Icon']; ?>' />
              </span>
              <div style='padding-top: 5px;'>
                <span style='font-size: 12px; padding-top: 0px;'>
                  <?= $Poke_Data['Display_Name']; ?>
                  (Level: <?= $Poke_Data['Level']; ?>)
                </span>
              </div>
            </td>

        <?php
            $Pokemon_Count++;
          }

          if ( $Pokemon_Count == 0 )
          {
            echo "<div style='padding: 5px;'>No Pokemon have been found given your search parameters.</div>";
          }

          if ( $Pokemon_Count % 3 == 1 )
          {
            echo "<td class='box_slot'></td>";
            echo "<td class='box_slot'></td>";
          }

          if ( $Pokemon_Count % 3 == 2 )
          {
            echo "<td class='box_slot'></td>";
          }
        ?>
      </table>
    </div>
	</div>

  <script type='text/javascript'>
    $("td.popup.cboxElement").colorbox({ iframe: true, innerWidth: 680, innerHeight: 491 });

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
      if (page == 'auto') page = currpage;
      else currpage = page;

      $.ajax({
        url:'core/ajax/profile/box.php',
        type: "POST",
        data: {
          id: '<?= $User_ID; ?>',
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
          $('#profileAJAX').html(data);
          $("td.popup.cboxElement").colorbox({ iframe: true, innerWidth: 680, innerHeight: 491 });
        },
        error: function(data)
        {
          $('#profileAJAX').html('<div class="error">An error has occurred. Please refresh the page.</div>');
        }
      });
    }
  </script>

<?php
  }

	exit();
?>