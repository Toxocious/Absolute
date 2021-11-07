<?php
  require_once 'core/required/layout_top.php';

  try
  {
    $Fetch_Pokemon = $PDO->prepare("SELECT `ID` FROM `pokemon` WHERE `Owner_Current` = ? AND `Location` = 'Roster' ORDER BY `Slot` ASC LIMIT 6");
    $Fetch_Pokemon->execute([$User_Data['ID']]);
    $Fetch_Pokemon->setFetchMode(PDO::FETCH_ASSOC);
    $Fetch_Roster = $Fetch_Pokemon->fetchAll();
  }
  catch ( PDOException $e )
  {
    HandleError( $e->getMessage() );
  }
?>

<div class='panel content'>
  <div class='head'>Pokemon Center</div>
  <div class='body pokecenter'>
    <div class='nav'>
      <div>
        <a href='javascript:void(0);' onclick="showTab('roster');">
          Roster
        </a>
      </div>
      <div>
        <a href='javascript:void(0);' onclick="showTab('moves');">
          Moves
        </a>
      </div>
      <div>
        <a href='javascript:void(0);' onclick="showTab('inventory');">
          Inventory
        </a>
      </div>
      <div>
        <a href='javascript:void(0);' onclick="showTab('nickname');">
          Nickname
        </a>
      </div>
      <div>
        <a href='javascript:void(0);' onclick="showTab('release');">
          Release
        </a>
      </div>
    </div>

    <div class='flex wrap' id='pokemon_center' style='gap: 10px; justify-content: center;'>
      <div class='panel'>
        <div class='head'>Loading</div>
        <div class='body' style='padding: 5px;'>Loading</div>
      </div>
    </div>
  </div>
</div>

<script type='text/javascript'>
  $(function()
  {
    showTab('roster');
  });

   /**
     * Handle AJAX requests pertaining to moving Pokemon around, as well as displaying their stats if necessary.
    * Also updates both the Userbar roster, as well as the Pokemon Center roster.
    */
  function handlePokemon(Request, PokeID = null, Slot = null)
  {
    $.ajax({
      type: 'POST',
      url: 'core/ajax/functions/manage_pokemon.php',
      data: { Request: Request, PokeID: PokeID, Slot: Slot },
      success: function(data)
      {
        $('#pokemon_center').html(data);
        updateRoster('pokecenter');
        updateRoster('userbar');
      },
      error: function(data)
      {
        $('#pokemon_center').html(data);
      }
    });
  }

  /**
   * Update the Userbar and Pokemon Center rosters.
   */
  function updateRoster(Location)
  {
    $.ajax({
      type: 'POST',
      url: 'core/ajax/functions/manage_pokemon.php',
      data: { Request: 'Roster', Location: Location },
      success: function(data)
      {
        if ( $("#"+Location+"_roster").length > -1 )
        {
          $("#"+Location+"_roster").html(data);
        }
      },
      error: function(data)
      {
        $("#"+Location+"_roster").html(data);
      }
    });
  }

  /**
   * Display a Pokemon's data to the user.
   */
  function displayPokeData(PokeID)
  {
    $.ajax({
      type: 'POST',
      url: 'core/ajax/pokecenter/pokemon.php',
      data: { PokeID: PokeID },
      success: function(data)
      {
        $("#pokeData").html(data);
      },
      error: function(data)
      {
        $("#pokeData").html(data);
      }
    });
  }

  /**
   * Handle swapping between inventory tabs, as well as displaying item data.
   */
  function itemHandler(request, category = null, id = null, pokeid = null)
  {
    $.ajax({
      type: 'POST',
      url: 'core/ajax/pokecenter/inventory.php',
      data: { request: request, category: category, id: id, pokeid: pokeid },
      success: function(data)
      {
        switch (request)
        {
          case 'item_data':
            $('#itemData').html(data);
            break;
          case 'item_tab':
            $('#activeTab').html(data);
            break;
          case 'attach':
          case 'detach':
          case 'detachall':
            $('#pokemon_center').html(data);
            break;
        }
      },
      error: function(data)
      {
        $('#pokemon_center').html(data);
      }
    });
  }

  /**
   * Handle nicknaming Pokemon.
   */
  function Nickname(PokeID)
  {
    $.ajax({
      type: 'POST',
      url: 'core/ajax/pokecenter/nickname.php',
      data: { PokeID: PokeID, Nickname: $("[name='" + PokeID + "_nick']").val() },
      success: function(data)
      {
        $('#pokemon_center').html(data);
      },
      error: function(data)
      {
        $('#pokemon_center').html(data);
      }
    });
  }

  /**
   * Release Pokemon.
   */
  let Stage_Confirm;
  let Release_List;
  function ReleasePokemon(Release_Stage)
  {
    switch(Release_Stage)
    {
      case 2:
        Stage_Confirm = confirm("Are you sure you want to release these Pokemon?");
        Release_List = $('#ReleaseList').val();
        break;

      case 3:
        Stage_Confirm = confirm("Are you sure you want to release these Pokemon?\nThis is your final warning; this process is irreversible.");
        break;

      default:
        break;
    }

    /**
     * The user has confirmed that they want to proceed with releasing their selected Pokemon.
     */
    if ( Stage_Confirm )
    {
      $.ajax({
        type: 'POST',
        url: '<?= DOMAIN_ROOT; ?>/core/ajax/pokecenter/release.php',
        data: {
          Release_Stage: Release_Stage,
          Release_List: Release_List
        },
        success: (data) =>
        {
          $('#pokemon_center').html(data);

          [].forEach.call(document.getElementsByClassName("popup"), function(el) {
            el.lightbox = new IframeLightbox(el, {
              scrolling: false,
              rate: 500,
              touch: false,
            });
          });
        },
        error: (data) =>
        {
          $('#pokemon_center').html(data);

          [].forEach.call(document.getElementsByClassName("popup"), function(el) {
            el.lightbox = new IframeLightbox(el, {
              scrolling: false,
              rate: 500,
              touch: false,
            });
          });
        },
      });
    }
  }

  /**
   * Swap between tab content.
   */
  function showTab(tab)
  {
    $.get('core/ajax/pokecenter/' + tab + '.php', function(data)
    {
      $('#pokemon_center').html(data);

      [].forEach.call(document.getElementsByClassName("popup"), function(el) {
        el.lightbox = new IframeLightbox(el, {
          scrolling: false,
          rate: 500,
          touch: false,
        });
      });
    });
  }
</script>

<?php
  require_once 'core/required/layout_bottom.php';
