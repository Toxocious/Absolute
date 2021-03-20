<?php
  require '../../required/session.php';

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
   * Update the moves of the given Pokemon.
   */
  if ( isset($_GET['req']) )
  {
    $Request = Purify($_GET['req']);

    /**
     * Updating a given move of a Pokemon.
     */
    if ( $Request === 'update' )
    {
      if ( isset($_POST['Poke_ID']) && isset($_POST['Move_Slot']) && isset($_POST['Move']) )
      {
        $Pokemon_ID = Purify($_POST['Poke_ID']);
        $Pokemon = $Poke_Class->FetchPokemonData($Pokemon_ID);
        $Move_Slot = Purify($_POST['Move_Slot']);
        $Move = Purify($_POST['Move']);

        $Current_Moves = [
          $Pokemon['Move_1'],
          $Pokemon['Move_2'],
          $Pokemon['Move_3'],
          $Pokemon['Move_4'],
        ];

        if ( $Pokemon['Owner_Current'] !== $User_Data['ID'] )
        {
          echo "<div class='error'>This Pokemon does not belong to you.</div>";
        }
        else if ( count(array_unique($Current_Moves)) != 4 )
        {
          echo "<div class='error'>You may not have the same move more than once.</div>";
        }
        else
        {
          try
          {
            $Update_Moves = $PDO->prepare("UPDATE `pokemon` SET `Move_{$Move_Slot}` = ? WHERE `ID` = ? LIMIT 1");
            $Update_Moves->execute([ $Move, $Pokemon_ID ]);
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
    }

    /**
     * Selecting a move of a Pokemon.
     */
    else if ( $Request === 'select' )
    {
      $Pokemon_ID = Purify($_POST['Poke_ID']);
      $Move = Purify($_POST['Move']);

      $Move_Dropdown = "
        <select name='{$Pokemon_ID}_Move_{$Move}' onchange='updateMove({$Pokemon_ID}, {$Move}, this);'>
          <option>Select A Move</option>
          <option value>---</option>
      ";
      foreach ( $Move_List as $Key => $Value )
      {
        $Move_Dropdown .= "<option value='{$Value['id']}'>{$Value['name']}</i>";
      }
      $Move_Dropdown .= "
        </select>
      ";

      echo $Move_Dropdown;
      exit;
    }
  }

  /**
   * Loop through the user's roster.
   */
  $Sprites = '';
  $Moves_Echo = '';
  $Pokemon_Row = '';

  if ( $User_Data['Roster'] )
  {
    for ( $i = 0; $i < 6; $i++ )
    {
      if ( isset($User_Data['Roster'][$i]['ID']) )
      {
        $Pokemon = $Poke_Class->FetchPokemonData($User_Data['Roster'][$i]['ID']);
        $Moves = [
          '1' => $Poke_Class->FetchMoveData($Pokemon['Move_1']),
          '2' => $Poke_Class->FetchMoveData($Pokemon['Move_2']),
          '3' => $Poke_Class->FetchMoveData($Pokemon['Move_3']),
          '4' => $Poke_Class->FetchMoveData($Pokemon['Move_4']),
        ];
  
        $Moves_Echo .= "
          <td colspan='3' style='width: calc(100% / 6);'>
            <img src='{$Pokemon['Sprite']}' /><br />
            <b>{$Pokemon['Display_Name']}</b>
          </td>
          <td colspan='3' style='width: calc(100% / 6);'>
            <div id='{$Pokemon['ID']}_Move_1' onclick='selectMove(\"{$Pokemon['ID']}\", 1);' style='padding: 3px 0px; width: 133px;'>
              <b>{$Moves['1']['Name']}</b>
            </div>
            <div id='{$Pokemon['ID']}_Move_2' onclick='selectMove(\"{$Pokemon['ID']}\", 2);' style='padding: 3px 0px; width: 133px;'>
              <b>{$Moves['2']['Name']}</b>
            </div>
            <div id='{$Pokemon['ID']}_Move_3' onclick='selectMove(\"{$Pokemon['ID']}\", 3);' style='padding: 3px 0px; width: 133px;'>
              <b>{$Moves['3']['Name']}</b>
            </div>
            <div id='{$Pokemon['ID']}_Move_4' onclick='selectMove(\"{$Pokemon['ID']}\", 4);' style='padding: 3px 0px; width: 133px;'>
              <b>{$Moves['4']['Name']}</b>
            </div>
          </td>
        ";
      }
      else
      {
        $Pokemon['Sprite'] = DOMAIN_SPRITES . '/Pokemon/Sprites/0.png';
  
        $Moves_Echo .= "
          <td colspan='6' style='width: calc(100% / 3);'>
            <img src='{$Pokemon['Sprite']}' /><br />
            <b>Empty</b>
          </td>
        ";
      }
  
      if ( ($i + 1) % 3 === 0 )
      {
        $Pokemon_Row .= "
          <tr>
            {$Moves_Echo}
          </tr>
        ";
  
        $Sprites = '';
        $Moves_Echo = '';
      }
    }
  }
  else
  {
    $Pokemon_Row = "
      <td colspan='9' style='padding: 10px;'>
        Your roster is currently empty.
      </td>
    ";
  }
?>

  <div class='description'>
    To change the move of a Pokemon, simply click on the move that you wish to change, and a dropdown menu will appear in it's place, allowing you to select the move that you desire.
  </div>

  <table class='border-gradient' style='flex-basis: 85%;'>
    <thead>
      <th colspan='18'>Roster</th>
    </thead>
    <tbody>
      <?= $Pokemon_Row; ?>
    </tbody>
  </table>
  
  <script type='text/javascript'>
    let isChanging = false;

    function updateMove(Poke_ID, Move_Slot)
    {
      isChanging = false;

      let Move = $('[name="' + Poke_ID + '_Move_' + Move_Slot + '"]').val();
      let Move_Option = $('#' + Poke_ID + '_' + Move_Slot + ' option:selected').html();

      $.ajax({
        type: 'POST',
        url: '/core/ajax/pokecenter/moves.php?req=update',
        data: { Poke_ID: Poke_ID, Move_Slot: Move_Slot, Move: Move },
        success: function(data)
        {
          $('#pokemon_center').html(data);
        },
        error: function(data)
        {
          $('#pokemon_center').html(data);
        },
      });

      $('#' + Poke_ID + '_Move_' + Move_Slot).html(`<b>${Move_Option}</b>`);
    }

    function selectMove(Poke_ID, Move)
    {
      if ( isChanging )
        return;

      isChanging = true;

      $.ajax({
        type: 'POST',
        url: '/core/ajax/pokecenter/moves.php?req=select',
        data: { Poke_ID: Poke_ID, Move: Move },
        success: function(data)
        {
          $('#' + Poke_ID + '_Move_' + Move).html(data);
        },
        error: function(data)
        {
          console.log(data);
        }
      });
    }
  </script>