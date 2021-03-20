<?php
	require_once '../core/required/layout_top.php';
	
	checkUserPower($User_Data['Power'], 7);
?>

<style>
	body > div.content > div.box > button { background: #1d2639 !important; border: 1px solid #4A618F !important; font-weight: bold !important; margin-bottom: 0px !important; margin-top: 3px !important; width: 100% !important; }
	body > div.content > div.box > button:hover { background: #3b4d72 !important; }

	#selectedPokemon > div:nth-child(1) { background: #3b4d72; font-weight: bold; }
	#selectedPokemon > div:nth-child(2) { padding: 12px 0px; } /* eventually this needs to auto H+V align the sprite */
	#selectedPokemon div.stats > div { background: #4A618F; font-weight: bold; }
	#selectedPokemon div.stats table { width: 100%; }
	#selectedPokemon div.stats table:not(:last-child) tr td:nth-child(even) { border-left: 2px solid #3b4d72; border-right: 2px solid #3b4d72; }
	#selectedPokemon div.stats table:not(:last-child) tr:nth-child(odd) td { background: #3b4d72; font-weight: bold; }
	#selectedPokemon div.stats table:not(:last-child) tr td { width: calc(100% / 3); }
	#selectedPokemon div.stats table:last-child tr:not(:last-child) { border-bottom: 2px solid #3b4d72; }
	#selectedPokemon div.stats table:last-child tr td:not(:last-child) { border-right: 2px solid #3b4d72; }

	#selectedStats div.stats > div { background: #4A618F; font-weight: bold; }
	#selectedStats div.stats table { width: 100%; }
	#selectedStats div.stats table tr td:nth-child(even) { border-left: 2px solid #3b4d72; border-right: 2px solid #3b4d72; }
	#selectedStats div.stats table tr:nth-child(odd) td { background: #3b4d72; font-weight: bold; }
	#selectedStats div.stats table tr td { width: calc(100% / 3); }
	#selectedStats div.stats table input { background: transparent; border: none; display: block; margin-bottom: 0px; padding: 1px; text-align: center; width: 100%; }

	.btnContainer button { background: #1d2639 !important; border: none; border-radius: 0px; }
	.btnContainer button:hover { background: #3b4d72 !important; }

  .btnContainer div:nth-child(1) { margin-left: -2px; }
	.btnContainer div:nth-child(1) button { height: 25px; margin: 0px; width: calc(100% / 4 + 1px); }
  .btnContainer div:nth-child(1) button:nth-child(-n+4) { border-bottom: 1px solid #4A618F; }
  .btnContainer div:nth-child(1) button:nth-child(-n+4) { margin-right: -5px; }
	.btnContainer div:nth-child(1) button:nth-last-child(-n+3) { margin-right: -5px; width: calc(100% / 3 + 1px); }

	.btnContainer div:nth-child(2) button { height: 25px; margin: 0; width: 50%; }
	.btnContainer div:nth-child(2) button:nth-child(-n+2) { border-bottom: 1px solid #4A618F; }
	.btnContainer div:nth-child(2) button:nth-child(odd) { border-left: 1px solid #4A618F; margin-right: -5px; }
	.btnContainer div:nth-child(2) button:nth-child(even) { border-left: 1px solid #4A618F; }

	#selectedMoves select { padding: 6px; text-align: center; width: 49%; }
	#selectedMoves select:nth-child(-n+2) { margin-bottom: 5px; }

  #selectContent { max-height: 125px; margin-top: 0px; overflow: hidden; }
  #selectContent > button { background: #1d212b; border: none; height: 66px; outline: none; width: calc(100% / 1); }
  #selectContent > button:hover { background: #2c3a55; }
  #selectContent > button:not(:last-child) { border-right: 2px solid #4A618F; }
  #selectContent > .list { float: left; width: calc(100% - 55px); margin-left: 55px; margin-top: -125px; height: 125px; overflow: auto; padding: 5px 5px 0px 5px; }
  #selectContent > .list > div { background: #1d2639; border: 1px solid #4A618F; border-radius: 4px; float: left; margin-bottom: 4px; margin-left: 4.4px; padding: 2px 0.5px; position: relative; }
  #selectContent > .list > div.halign { display: flex; justify-content: center; }
  #selectContent > .list > div.halign > input {  position: absolute; margin-left: 12px; margin-top: 25px; }
  #selectContent > .list > div.halign > div.valign { display: flex; justify-content: center; flex-direction: column; }
  #selectContent > .list > div.halign > div.valign > img { display: block;  }
  #selectContent > .list > div.halign > div.valign > img:hover { cursor: pointer; }
  #selectContent > .options { width: 100%; height: 50px; text-align: center; }

  #selectContent > .menu-slider { float: left; height: 125px; margin-top: 0px; width: 50px; }
  #selectContent > .menu-slider > .menu-toggle { background: #1d2639; border-right: 1px solid #4A618F; cursor: pointer; float: left; height: 125px; padding-top: 2px; width: 50px; }
  #selectContent > .menu-slider > .menu-content { background: #253047; border-right: 1px solid #4A618F; display: none; float: left; height: 125px; margin-left: 50px; margin-top: -125px; width: 200px; z-index: 100000; }

  .options { margin-bottom: -2px; margin-top: 3px; }
	.options button,
	.options input,
	.options select { border: 1px solid #4A618F; padding: 5px; width: 49.6%; }
  .options button:hover { background: #334364 !important; }
</style>

<div class='content'>
	<div class='head'>Pokemon Spawner</div>
	<div class='box'>

    <div class='panel' style='margin-bottom: 3px;'>
      <div class='panel-heading'>Pokemon Selector</div>
      <div class='panel-body' id='selectContent'>
        <div class='menu-slider'>
          <div class='menu-toggle'>F<br />I<br />L<br />T<br />E<br />R</div>
          <div class='menu-content'>
            filter and stuff
          </div>
        </div>

        <div class='list'>
        <?php
          try
          {
            $Fetch_Pokedex = $PDO->prepare("SELECT * FROM `pokedex` ORDER BY `ID` ASC");
            $Fetch_Pokedex->execute();
            $Fetch_Pokedex->setFetchMode(PDO::FETCH_ASSOC);
            $Pokedex = $Fetch_Pokedex->fetchAll();
          }
          catch ( PDOException $e )
          {
            HandleError( $e->getMessage() );
          }

          foreach ( $Pokedex as $Index => $Poke_Data )
          {
            $Pokemon = $PokeClass->FetchPokedexData($Poke_Data['id']);
            
            echo "
              <div class='halign' data-id='{$Pokemon['ID']}'>
                <div class='valign'>
            ";

            echo "<img src='{$Pokemon['Icon']}' onclick='selectPokemon({$Pokemon['ID']});' />";

            //if ( file_exists("{$Pokemon['Icon']}") )
            //{
            //  echo "<img src='{$Pokemon['Icon']}' onclick='selectPokemon({$Pokemon['ID']});' />";
            //}
            //else
            //{
            //  echo "<img src='images/Pokemon/0_mini.png' onclick='selectPokemon({$Pokemon['ID']});' />";
            //}

            echo "
                </div>
              </div>
            ";
          }

          /*
          $pokemon_list = mysqli_query($con, "SELECT * FROM pokedex");
          while ( $pokemon_fetch = mysqli_fetch_assoc($pokemon_list) )
          {
            if ( $pokemon_fetch['ID'] <= 151 ) $Slot_Gen = 'Generation 1';
            else if ( $pokemon_fetch['ID'] <= 251 && $pokemon_fetch['ID'] >= 152 ) $Slot_Gen = 'Generation 2';
            else if ( $pokemon_fetch['ID'] <= 386 && $pokemon_fetch['ID'] >= 252 ) $Slot_Gen = 'Generation 3';
            else if ( $pokemon_fetch['ID'] <= 493 && $pokemon_fetch['ID'] >= 387 ) $Slot_Gen = 'Generation 4';
            else if ( $pokemon_fetch['ID'] <= 649 && $pokemon_fetch['ID'] >= 494 ) $Slot_Gen = 'Generation 5';
            else if ( $pokemon_fetch['ID'] <= 721 && $pokemon_fetch['ID'] >= 650 ) $Slot_Gen = 'Generation 6';
            else $Slot_Gen = 'Generation 7';
            
            if ( strpos($pokemon_fetch['Name'], '(Mega)') )
            {
              $Slot_Gen = 'Mega';
              $Slot_pID = substr($pokemon_fetch['ID'], 0, -1);
              $Slot_pID .= '-mega';
            }
            else
            {
              $Slot_pID = $pokemon_fetch['ID'];
            }
                      
            echo "
              <div class='halign' data-id='{$pokemon_fetch['ID']}'>
                <div class='valign'>
            ";

            if ( file_exists("images/Icons/1 - Normal/{$Slot_Gen}/{$Slot_pID}.png") )
              echo "<img src='images/Icons/1 - Normal/{$Slot_Gen}/{$Slot_pID}.png' onclick='selectPokemon({$Slot_pID});' />";
            else
              echo "<img src='images/Icons/5 - Special/unknown.png' onclick='selectPokemon({$Slot_pID});' />";
            
            echo "
                </div>
              </div>
            ";
          }
          */
        ?>
        </div>
      </div>
    </div>

    <div class='options'>
      <div>
        <button style='width: 100%;' onclick='displayPopup();'>Spawn Pokemon</button>
      </div>
    </div>

    <div id='pokemonSpawner'>
      <div class='panel' style='float: left; margin-bottom: -1px; width: 33%;'>
        <div class='panel-heading'>Pokemon Preview</div>
        <div class='panel-body' id='selectedPokemon'>
          <div>
            ???
          </div>
          <div>
            <img src='images/Assets/pokeball.png' />
          </div>
          <div class='stats'>
            <div>Base Stats</div>
            <table>
              <tr>
                <td>HP</td>
                <td>Attack</td>
                <td>Defense</td>
              </tr>
              <tr>
                <td id='base_hp'>0</td>
                <td id='base_att'>0</td>
                <td id='base_def'>0</td>
              </tr>
              <tr>
                <td>Sp.Att</td>
                <td>Sp.Def</td>
                <td>Speed</td>
              </tr>
              <tr>
                <td id='base_spatt'>0</td>
                <td id='base_spdef'>0</td>
                <td id='base_speed'>0</td>
              </tr>
            </table>

            <div>Individual Values</div>
            <table>
              <tr>
                <td>HP</td>
                <td>Attack</td>
                <td>Defense</td>
              </tr>
              <tr>
                <td id='iv_hp'>0</td>
                <td id='iv_att'>0</td>
                <td id='iv_def'>0</td>
              </tr>
              <tr>
                <td>Sp.Att</td>
                <td>Sp.Def</td>
                <td>Speed</td>
              </tr>
              <tr>
                <td id='iv_spatt'>0</td>
                <td id='iv_spdef'>0</td>
                <td id='iv_speed'>0</td>
              </tr>
            </table>

            <div>Effort Values</div>
            <table>
              <tr>
                <td>HP</td>
                <td>Attack</td>
                <td>Defense</td>
              </tr>
              <tr>
                <td id='iv_hp'>0</td>
                <td id='iv_att'>0</td>
                <td id='iv_def'>0</td>
              </tr>
              <tr>
                <td>Sp.Att</td>
                <td>Sp.Def</td>
                <td>Speed</td>
              </tr>
              <tr>
                <td id='iv_spatt'>0</td>
                <td id='iv_spdef'>0</td>
                <td id='iv_speed'>0</td>
              </tr>
            </table>

            <div>Moves</div>
            <table>
              <tr>
                <td id='move_1'>null</td>
                <td id='move_2'>null</td>
              </tr>
              <tr>
                <td id='move_3'>null</td>
                <td id='move_4'>null</td>
              </tr>
            </table>
          </div>
        </div>
      </div>

      <div class='panel' style='height: 80px; margin-bottom: 3px; margin-left: 33.3%; width: 66.6%;'>
        <div class='panel-heading'>Type & Version</div>
        <div class='panel-body btnContainer'>
          <div style='float: left; width: 50%;'>
            <button>Version 1</button>
            <button>Version 2</button>
            <button>Version 3</button>
            <button>Version 4</button>
            <button>Version 5</button>
            <button>Version 6</button>
            <button>Version 7</button>
          </div>

          <div style='margin-left: 50%; width: 50%;'>
            <button>Normal</button>
            <button>Shiny</button>
            <button>Sunset</button>
            <button>Shiny Sunset</button>
          </div>
        </div>
      </div>

      <div class='panel' style='height: 340px; margin-bottom: 3px; margin-left: 33.3%; width: 66.6%;'>
        <div class='panel-heading'>Stats</div>
        <div class='panel-body' id='selectedStats'>
          <div class='stats'>
            <div>Base Stats</div>
            <table>
              <tr>
                <td>HP</td>
                <td>Attack</td>
                <td>Defense</td>
              </tr>
              <tr>
                <td><input type='text' placeholder='0' pattern="\d*" maxlength='9' id='base_sHP' /></td>
                <td><input type='text' placeholder='0' pattern="\d*" maxlength='9' id='base_sATT' /></td>
                <td><input type='text' placeholder='0' pattern="\d*" maxlength='9' id='base_sDEF' /></td>
              </tr>
              <tr>
                <td>Sp.Att</td>
                <td>Sp.Def</td>
                <td>Speed</td>
              </tr>
              <tr>
                <td><input type='text' placeholder='0' pattern="\d*" maxlength='9' id='base_sSPATT' /></td>
                <td><input type='text' placeholder='0' pattern="\d*" maxlength='9' id='base_sSPDEF' /></td>
                <td><input type='text' placeholder='0' pattern="\d*" maxlength='9' id='base_sSPEED' /></td>
              </tr>
            </table>

            <div>Individual Values</div>
            <table>
              <tr>
                <td>HP</td>
                <td>Attack</td>
                <td>Defense</td>
              </tr>
              <tr>
                <td><input type='text' placeholder='0' pattern="\d*" maxlength='2' id='iv_sHP' /></td>
                <td><input type='text' placeholder='0' pattern="\d*" maxlength='2' id='iv_sATT' /></td>
                <td><input type='text' placeholder='0' pattern="\d*" maxlength='2' id='iv_sDEF' /></td>
              </tr>
              <tr>
                <td>Sp.Att</td>
                <td>Sp.Def</td>
                <td>Speed</td>
              </tr>
              <tr>
                <td><input type='text' placeholder='0' pattern="\d*" maxlength='2' id='iv_sSPATT' /></td>
                <td><input type='text' placeholder='0' pattern="\d*" maxlength='2' id='iv_sSPDEF' /></td>
                <td><input type='text' placeholder='0' pattern="\d*" maxlength='2' id='iv_sSPEED' /></td>
              </tr>
            </table>

            <div>Effort Values</div>
            <table>
              <tr>
                <td>HP</td>
                <td>Attack</td>
                <td>Defense</td>
              </tr>
              <tr>
                <td><input type='text' placeholder='0' pattern="[0-9]+" maxlength='3' id='ev_sHP' /></td>
                <td><input type='text' placeholder='0' pattern="\d*" maxlength='3' id='ev_sATT' /></td>
                <td><input type='text' placeholder='0' pattern="\d*" maxlength='3' id='ev_sDEF' /></td>
              </tr>
              <tr>
                <td>Sp.Att</td>
                <td>Sp.Def</td>
                <td>Speed</td>
              </tr>
              <tr>
                <td><input type='text' placeholder='0' pattern="\d*" maxlength='3' id='ev_sSPATT' /></td>
                <td><input type='text' placeholder='0' pattern="\d*" maxlength='3' id='ev_sSPDEF' /></td>
                <td><input type='text' placeholder='0' pattern="\d*" maxlength='3' id='ev_sSPEED' /></td>
              </tr>
            </table>
          </div>
        </div>
      </div>

      <div class='panel' style='margin-left: 33.3%; width: 66.6%;'>
        <div class='panel-heading'>Moves</div>
        <div class='panel-body' id='selectedMoves'>
          <select>
            <option>Select Move #1</option>
          </select>

          <select>
            <option>Select Move #2</option>
          </select>

          <select>
            <option>Select Move #3</option>
          </select>

          <select>
            <option>Select Move #4</option>
          </select>
        </div>
      </div>
    </div>
	</div>	
</div>

<script type='text/javascript'>
	$(document).ready(function ()
	{
    $('.popup.cboxElement').colorbox({ iframe: true, innerWidth: 680, innerHeight: 491 });

    $( '.list' ).on( 'mousewheel DOMMouseScroll', function ( e ) {
      var e0 = e.originalEvent,
          delta = e0.wheelDelta || -e0.detail;

      this.scrollTop += ( delta < 0 ? 1 : -1 ) * 30;
      e.preventDefault();
    });

		$("input").keypress(function(e)
		{
			if ( e.which != 8 && e.which != 0 && (e.which < 48 || e.which > 57) )
			{
					return false;
			}
		});

    $('.menu-toggle').click(function()
    {
      if ( $('.menu-content').css('display') == 'none' )
      {
        console.log("Displaying the toggle menu.");
        $('.menu-content').css({ 'display': 'block' });
        $('.list').css({ 'width': 'calc(100% - 249px)', 'margin-left': '249px' })
      }
      else
      {
        console.log("Hiding the toggle menu.");
        $('.menu-content').css( 'display', 'none' );
		    $('.list').css({ 'width': 'calc(100% - 50px)', 'margin-left': '50px' });
      }
    });
	});

  $('.list > img[checked="checked"]').each(function()
  {
    if ( $(this).length > 1 )
    {
      $(this).attr('checked', 'unchecked');
    }
  });
	
	$("input[id^='iv']").change(function()
	{
		if ( parseInt(this.value) > 31 )
		{
        this.value = 31;
     } 
	});

	$("input[id^='ev']").change(function()
	{
		if ( parseInt(this.value) > 255 )
		{
        this.value = 255;
     } 
	});

  function selectPokemon(id)
  {
    console.log('selecting the pokemon :)');

    $.ajax({
      type: 'post',
      url: 'ajax/pokemon_stats.php',
      data: { req: 'select', id: id },
      success: function(data)
      {
        $('#pokemonSpawner').html(data);
      },
      error: function(data)
      {
        $('#pokemonSpawner').html(data);
      }
    });
  }
</script>

<?php
  require_once '../core/required/layout_bottom.php';
?>