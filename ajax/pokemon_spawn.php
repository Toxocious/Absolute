<?php
  if ( isset($_POST['req']) )
  {
    require '../db.php';

    echo "
        <style>
          #popup .popup_content .description { background: #2c3a55; border: 1px solid #4A618F; border-radius: 4px; margin-bottom: 3px; margin-top: 0px; padding: 3px; text-align: center; width: 100%; }
          #popup .popup_content .user_cont { background: #2c3a55; border: 1px solid #4A618F; border-radius: 4px; float: left; margin-bottom: 3px; width: 24.4869%; }
          #popup .popup_content .user_cont:not(:last-child) { margin-right: 3px; }
          #popup .popup_content .user_cont:hover { background: #3b4d72; cursor: pointer; }
          #popup .popup_content .user_cont .user_name { border-bottom: 1px solid #4A618F; font-weight: bold; text-align: center; }
          #popup .popup_content .user_cont .user_avi { min-height: 106px; padding: 3px; text-align: center; }
        </style>
      ";

    if ( $_POST['req'] == 'user_list' )
    {
      echo "
        <script type='text/javascript'>
          function giftPokemon(id)
          {
            let owner = id;
            let type = $('#selectedPokemon > div:nth-child(2) > img').attr('src').split('/')[3].split(' - ')[1];
		        let version = $('#selectedPokemon > div:nth-child(2) > img').attr('src').split('/')[2].split(' ')[1];
            let pokemon = $.trim( $('#selectedPokemon > div:nth-child(1)').text().split('(#')[1].split(')')[0] );
            let stats_base = { HP: $('#base_sHP').val(), Attack: $('#base_sATT').val(), Defense: $('#base_sDEF').val(), SpAttack: $('#base_sSPATT').val(), SpDefense: $('#base_sSPDEF').val(), Speed: $('#base_sSPEED').val() };
            let stats_iv = { IV_HP: $('#iv_sHP').val(), IV_Attack: $('#iv_sATT').val(), IV_Defense: $('#iv_sDEF').val(), IV_SpAttack: $('#iv_sSPATT').val(), IV_SpDefense: $('#iv_sSPDEF').val(), IV_Speed: $('#iv_sSPEED').val() };
            let stats_ev = { EV_HP: $('#ev_sHP').val(), EV_Attack: $('#ev_sATT').val(), EV_Defense: $('#ev_sDEF').val(), EV_SpAttack: $('#ev_sSPATT').val(), EV_SpDefense: $('#ev_sSPDEF').val(), EV_Speed: $('#ev_sSPEED').val() };

            for ( i = 0; i < 6; i++ )
            {
              if ( stats_base[i] == '' )
                stats_base[i] == '0'
              
              if ( stats_iv[i] == null )
                stats_iv[i] == '0'

              if ( stats_ev[i] == null )
                stats_ev[i] == '0'
            }

            $.ajax({
              type: 'post',
              url: 'ajax/pokemon_spawn.php',
              data: { req: 'spawn', owner: owner, type: type, version: version, pokemon_id: pokemon, stats_base: stats_base, stats_iv: stats_iv, stats_ev: stats_ev },
              success: function(data)
              {
                $('#popup > .popup_content').html(data);
              },
              error: function(data)
              {
                $('#popup > .popup_content').html(data);
              }
            });
          }
        </script>
      ";

      echo "<div class='description'>Clicking on a user below will automatically send the selected Pokemon to their boxes.</div>";

      $Fetch_Users = mysqli_query($con, "SELECT * FROM members");
      while ( $List_Users = mysqli_fetch_assoc($Fetch_Users) )
      {
        echo "
          <div class='user_cont' onclick='giftPokemon({$List_Users['id']});'>
            <div class='user_name'>{$List_Users['Username']} - (#{$List_Users['id']})</div>
            <div class='user_avi'><img src='{$List_Users['Avatar']}' /></div>
          </div>
        ";
      }
    }

    if ( $_POST['req'] == 'spawn' )
    {
      date_default_timezone_set('America/Los_Angeles');
      $Date = date("M dS, Y g:i:s A");
  
      $Fetch_Pokedex = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM pokedex WHERE ID = {$_POST['pokemon_id']}"));
      $Fetch_Player = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM members WHERE ID = {$_POST['owner']}"));

      $stats_base = '';
      $stats_iv = '';
      $stats_ev = '';
      
      foreach ( $_POST['stats_base'] as $stat => $val )
      {
        if ( $val == '' )
        {
          $val = $Fetch_Pokedex[$stat];
          $stats_base .= $Fetch_Pokedex[$stat] . ",";
        }
      }

      $get_base = explode(',', $stats_base);
      array_pop($get_base);

      foreach ( $_POST['stats_iv'] as $stat => $val )
      {
        if ( $val == '' )
        {
          $val = '0';
          $stats_iv .= $val . ",";
        }
      }

      $get_iv = explode(',', $stats_iv);
      array_pop($get_iv);

      foreach ( $_POST['stats_ev'] as $stat => $val )
      {
        if ( $val == '' )
        {
          $val = '0';
          $stats_ev .= $val . ",";
        }
      }

      $get_ev = explode(',', $stats_ev);
      array_pop($get_ev);

      if ( $Fetch_Pokedex['ID'] <= 151 )
				$Slot_Gen = 'Generation 1';
			else if ( $Fetch_Pokedex['ID'] <= 251 && $Fetch_Pokedex['ID'] >= 152 )
				$Slot_Gen = 'Generation 2';
			else if ( $Fetch_Pokedex['ID'] <= 386 && $Fetch_Pokedex['ID'] >= 252 )
				$Slot_Gen = 'Generation 3';
			else if ( $Fetch_Pokedex['ID'] <= 493 && $Fetch_Pokedex['ID'] >= 387 )
				$Slot_Gen = 'Generation 4';
			else if ( $Fetch_Pokedex['ID'] <= 649 && $Fetch_Pokedex['ID'] >= 494 )
				$Slot_Gen = 'Generation 5';
			else if ( $Fetch_Pokedex['ID'] <= 721 && $Fetch_Pokedex['ID'] >= 650 )
				$Slot_Gen = 'Generation 6';
			else
        $Slot_Gen = 'Generation 7';

      if ( $_POST['type'] == 'Normal' )
        $Type = '1 - Normal';
      else if ( $_POST['type'] == 'Shiny' )
        $Type = '2 - Shiny';
      else if ( $_POST['type'] == 'Sunset' )
        $Type = '3 - Sunset';
      else if ( $_POST['type'] == 'Shiny Sunset' )
        $Type = '4 - Shiny Sunset';
        
      mysqli_query($con, "INSERT INTO pokemon (`owner_current`, `owner_original`, `pokedex_id`, `type`, `sprite_version`, `hp`, `attack`, `defense`, `spattack`, `spdefense`, `speed`, `iv_hp`, `iv_attack`, `iv_defense`, `iv_spattack`, `iv_spdefense`, `iv_speed`, `ev_hp`, `ev_attack`, `ev_defense`, `ev_spattack`, `ev_spdefense`, `ev_speed`) VALUES ({$Fetch_Player['id']}, {$Fetch_Player['id']}, {$Fetch_Pokedex['ID']}, '{$_POST['type']}', '{$_POST['version']}', {$get_base[0]}, {$get_base[1]}, {$get_base[2]}, {$get_base[3]}, {$get_base[4]}, {$get_base[5]}, {$get_iv[0]}, {$get_iv[1]}, {$get_iv[2]}, {$get_iv[3]}, {$get_iv[4]}, {$get_iv[5]}, {$get_ev[0]}, {$get_ev[1]}, {$get_ev[2]}, {$get_ev[3]}, {$get_ev[4]}, {$get_ev[5]})");

      echo "
        <div class='description' style='border-color: #00ff00;'>
          <img src='images/Pokemon/Version {$_POST['version']}/$Type/$Slot_Gen/{$Fetch_Pokedex['ID']}.gif' style='padding: 5px;' />
          <br />
          <b>{$Fetch_Pokedex['Name']}</b> has been sent to <b>{$Fetch_Player['Username']} - (#{$Fetch_Player['id']})</b>!
        </div>
      ";
      echo "<div class='description'>Clicking on a user below will automatically send the selected Pokemon to their boxes.</div>";

      $Fetch_Users = mysqli_query($con, "SELECT * FROM members");
      while ( $List_Users = mysqli_fetch_assoc($Fetch_Users) )
      {
        echo "
          <div class='user_cont' onclick='giftPokemon({$List_Users['id']});'>
            <div class='user_name'>{$List_Users['Username']} - (#{$List_Users['id']})</div>
            <div class='user_avi'><img src='{$List_Users['Avatar']}' /></div>
          </div>
        ";
      }
    }
  }