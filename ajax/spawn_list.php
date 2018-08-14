<?php
  require '../session.php';

  echo "<link href='/Absolute/css/required.css' rel='stylesheet'>";
  echo "<script src='/Absolute/js/libraries.js' type='text/javascript'></script>";

  echo  "
    <style>
      html, body { background: #111; color: #fff; }
      *::-webkit-scrollbar {width:3px!important;height:10px!important;background:var(--bg-color-first, #2a2a2a)!important;border:1px solid var(--border-color-left, #353535)!important; z-index:1000000000;}
			*::-webkit-scrollbar-thumb {min-height:28px !important;background:var(--bg-color-background, #373737)!important}
			*::-webkit-input-placeholder {color:var(--text-color-second, #979797)}
			::-webkit-scrollbar-track, ::-webkit-scrollbar-corner {background:var(--bg-color-first, #2a2a2a)!important}
			::-webkit-scrollbar-thumb:hover {background:var(--bg-color-hover, #3d3a3a)!important}
      button { background: #1d212b; border: none; height: 66px; outline: none; width: calc(100% / 1); }
      button:hover { background: #2c3a55; }
      button:not(:last-child) { border-right: 2px solid #4A618F; }
      .list { width: 100%; height: 425px; overflow: auto; border-bottom: 2px solid #4A618F; padding: 5px; }
      .list > div { background: #1d2639; border: 1px solid #4A618F; border-radius: 4px; float: left; margin-bottom: 4px; margin-left: 4.4px; padding: 2px 0.5px; position: relative; }
      .list > div.halign { display: flex; justify-content: center; }
      .list > div.halign > input {  position: absolute; margin-left: 12px; margin-top: 25px; }
      .list > div.halign > div.valign { display: flex; justify-content: center; flex-direction: column; }
      .list > div.halign > div.valign > img { display: block;  }
      .list > div.halign > div.valign > img:hover { cursor: pointer; }
      .options { width: 100%; height: 50px; text-align: center; }
    </style>
  ";

  echo	"<div class='list'>";

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

    if ( file_exists("../images/Icons/1 - Normal/{$Slot_Gen}/{$Slot_pID}.png") )
      echo "<img id='{$pokemon_fetch['ID']}' src='../images/Icons/1 - Normal/{$Slot_Gen}/{$Slot_pID}.png' />";
    else
      echo "<img id='{$pokemon_fetch['ID']}' src='../images/Icons/5 - Special/unknown.png' />";
    
    echo "
        </div>
      </div>
    ";
  
  }
  echo	"</div>";
  
  echo	"
    <div class='options'>
      <button onclick='selectedPokemon();'>Select Pokemon</button>
    </div>

    <script type='text/javascript'>
      $('img').click(function() {
        if ( $(this).hasClass('checked') )
        {
          $(this).removeClass('checked');
          $(this).css('background','#1d2639');
          $('input', this).prop('checked', false);
        }
        else
        {
          $(this).addClass('checked');
          $(this).css('background','#003300');
          $('input', this).prop('checked', true);
        }
      });

      function selectedPokemon()
      {
        console.log('attempting to spawn in selected pokemon');

        if ( $('.cboxElement > img').hasClass('checked') )
          $('#pokemonSpawner').html( $('.cboxElement > img').hasClass('checked').attr('src') )
        else
          $('#pokemonSpawner').html( 'you didn\'t select a pokemon' );

        parent.$.colorbox.close();
      }

      /*
      function selectedPokemon()
      {
        console.log('attempting to spawn in selected pokemon');

        parent.$.colorbox.close();

        
        $.ajax({
          type: 'post',
          url: '../spawn_pokemon.php',
          data: { poke_id: $('img').hasClass('checked') },
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
      */
    </script>
  ";