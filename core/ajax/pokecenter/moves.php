<?php
  require '../../required/session.php';

  /**
   * Update the moves of the given Pokemon.
   */
  if ( isset($_POST['poke_id']) && isset($_POST['move_1']) && isset($_POST['move_2']) && isset($_POST['move_3']) && isset($_POST['move_4']) )
  {
    $Pokemon_ID = Purify($_POST['poke_id']);
    $Pokemon = $Poke_Class->FetchPokemonData($Pokemon_ID);

    $Moves = Purify($_POST['move_1']) . ',' . Purify($_POST['move_2']) . ',' . Purify($_POST['move_3']) . ',' . Purify($_POST['move_4']);
    $Moves_Array = [
      Purify($_POST['move_1']),
      Purify($_POST['move_2']),
      Purify($_POST['move_3']),
      Purify($_POST['move_4']),
    ];

    if ( $Pokemon['Owner_Current'] !== $User_Data['id'] )
    {
      echo "<div class='error'>This Pokemon does not belong to you.</div>";
    }
    else if ( count(array_unique($Moves_Array)) != 4 )
    {
      echo "<div class='error'>You may not have the same move more than once.</div>";
    }
    else
    {
      try
      {
        $Update_Moves = $PDO->prepare("UPDATE `pokemon` SET `Moves` = ? WHERE `ID` = ? LIMIT 1");
        $Update_Moves->execute([ $Moves, $Pokemon_ID ]);
      }
      catch( PDOException $e )
      {
        HandleError( $e->getMessage() );
      }

      echo "
        <div class='success'>
          <b>{$Pokemon['Display_Name']}'s</b> moves have been updated successfully.
        </div>
      ";
    }
  }

  /**
   * Fetch an array of all of the moves in game.
   */
  try
  {
    $Fetch_Moves = $PDO->prepare("SELECT * FROM `moves` WHERE `programmed` = 1");
    $Fetch_Moves->execute([]);
    $Fetch_Moves->setFetchMode(PDO::FETCH_ASSOC);
    $Move_List = $Fetch_Moves->fetchAll();
  }
  catch ( PDOException $e )
  {
    HandleError( $e->getMessage() );
  }

  /**
   * Loop through the user's roster.
   */
  $Sprites = '';
  $Moves_Echo = '';
  $Moves_Roster = '';
  for ( $i = 0; $i < 6; $i++ )
  {
    if ( isset($Roster[$i]['ID']) )
    {
      $Pokemon = $Poke_Class->FetchPokemonData($Roster[$i]['ID']);
      $Moves = [
        '1' => $Poke_Class->FetchMoveData($Pokemon['Move_1']),
        '2' => $Poke_Class->FetchMoveData($Pokemon['Move_2']),
        '3' => $Poke_Class->FetchMoveData($Pokemon['Move_3']),
        '4' => $Poke_Class->FetchMoveData($Pokemon['Move_4']),
      ];

      /**
       * Set the moves list dropdown.
       */
      $Move_Dropdown = '';
      for ( $m = 1; $m <= 4; $m++ )
      {
        $Move_Dropdown .= "
          <select name='{$Pokemon['ID']}_move_{$m}' onchange='updateMoves({$Pokemon['ID']});'>
            <option value='{$Moves[$m]['ID']}'>" . $Moves[$m]['Name'] . "</option>
            <option value>---</option>
        ";
        foreach ( $Move_List as $Key => $Value )
        {
          $Move_Dropdown .= "<option value='{$Value['id']}'>{$Value['name']}</i>";
        }
        $Move_Dropdown .= "
          </select>
        ";
      }

      $Sprites .= "
        <td>
          <img src='{$Pokemon['Sprite']}' /><br />
          <b>{$Pokemon['Display_Name']}</b>
        </td>
      ";

      $Moves_Echo .= "
        <td>
          {$Move_Dropdown}
        </td>
      ";
    }
    else
    {
      $Pokemon['Sprite'] = DOMAIN_SPRITES . '/Pokemon/Sprites/0.png';
      $Pokemon['Icon'] = DOMAIN_SPRITES . '/Pokemon/Sprites/0_mini.png';
      $Pokemon['Display_Name'] = 'Empty';
      $Pokemon['Level'] = '0';
      $Pokemon['Experience'] = '0';

      $Sprites .= "
        <td style='width: 137px;'>
          <img src='{$Pokemon['Sprite']}' /><br />
          <b>Empty</b>
        </td>
      ";

      $Moves_Echo .= "
        <td>
          
        </td>
      ";
    }
  }

  $Moves_Roster .= "
    <tr>
      {$Sprites}
    </tr>
    <tr>
      {$Moves_Echo}
    </tr>
  ";

  /**
   * Display the user's roster and move dropdowns.
   */
  echo "
    <table class='border-gradient' style='flex-basis: 100%;'>
      <thead>
        <th colspan='6'>Roster</th>
      </thead>
      <tbody>
        {$Moves_Roster}
      </tbody>
    </table>
    
    <script type='text/javascript'>
      function updateMoves(id)
      {
        let poke_id = id;
        let move_1 = $('[name=\"'+poke_id+'_move_1\"]').val();
        let move_2 = $('[name=\"'+poke_id+'_move_2\"]').val();
        let move_3 = $('[name=\"'+poke_id+'_move_3\"]').val();
        let move_4 = $('[name=\"'+poke_id+'_move_4\"]').val();

        $.ajax({
          type: 'post',
          url: '" . DOMAIN_ROOT . "/core/ajax/pokecenter/moves.php',
          data: { poke_id: poke_id, move_1: move_1, move_2: move_2, move_3: move_3, move_4: move_4, },
          success: function(data)
          {
            $('#pokemon_center').html(data);
          },
          error: function(data)
          {
            $('#pokemon_center').html(data);
          },
        });
      }
    </script>
	";
?>