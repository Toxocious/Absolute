<?php
  require '../../required/session.php';

  if ( isset($_POST['poke_id']) )
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
      echo "<div class='error' style='margin-bottom: 5px;'>This Pokemon does not belong to you.</div>";
    }
    else if ( count(array_unique($Moves_Array)) != 4 )
    {
      echo "<div class='error' style='margin-bottom: 5px;'>You may not have the same move more than once.</div>";
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
        <div class='success' style='margin-bottom: 5px;'>
          <b>{$Pokemon['Display_Name']}'s</b> moves have been updated successfully.
        </div>
      ";
    }
  }

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

	echo "
		<div class='panel'>
			<div class='panel-heading'>Roster</div>
			<div class='panel-body'>
  ";
  
	for ( $i = 0; $i <= 5; $i++ )
  {
    if ( isset($Roster[$i]['ID']) )
    {
      $Roster_Slot[$i] = $Poke_Class->FetchPokemonData($Roster[$i]['ID']);
      $Move_1 = $Poke_Class->FetchMoveData($Roster_Slot[$i]['Move_1']);
      $Move_2 = $Poke_Class->FetchMoveData($Roster_Slot[$i]['Move_2']);
      $Move_3 = $Poke_Class->FetchMoveData($Roster_Slot[$i]['Move_3']);
      $Move_4 = $Poke_Class->FetchMoveData($Roster_Slot[$i]['Move_4']);
    }
    else
    {
      $Roster_Slot[$i]['Sprite'] = Domain(3) . 'images/pokemon/0.png';
      $Roster_Slot[$i]['Icon'] = Domain(3) . 'images/pokemon/0_mini.png';
      $Roster_Slot[$i]['Display_Name'] = 'Empty';
      $Roster_Slot[$i]['Level'] = '0';
      $Roster_Slot[$i]['Experience'] = '0';
    }

    if ( $Roster_Slot[$i]['Display_Name'] !== "Empty" )
    {
      echo "
        <div class='roster_slot full' style='padding: 5px;'>
          <div style='width: 100%;'>
            <b>{$Roster_Slot[$i]['Display_Name']}</b>
          </div>
          <div style='float: left; width: 100px;'>
            <img class='spricon popup cboxElement' src='{$Roster_Slot[$i]['Sprite']}' href='" . Domain(1) . "/core/ajax/pokemon.php?id={$Roster_Slot[$i]['ID']}' /><br />
          </div>
          <div style='float: left; width: calc(100% - 105px);'>
            <select name='{$Roster_Slot[$i]['ID']}_move_1' onchange='updateMoves({$Roster_Slot[$i]['ID']});' style='margin: 2px; width: 100%;'>
              <option value='{$Move_1['ID']}'>" . $Move_1['Name'] . "</option>
              <option value>~~~~~~~~~~~~~~</option>
        ";

            foreach ( $Move_List as $Key => $Value )
            {
              echo "<option value='{$Value['id']}'>{$Value['name']}</i>";
            }

        echo "
            </select>
            <select name='{$Roster_Slot[$i]['ID']}_move_2' onchange='updateMoves({$Roster_Slot[$i]['ID']});' style='margin: 2px; width: 100%;'>
              <option value='{$Move_2['ID']}'>" . $Move_2['Name'] . "</option>
              <option value>~~~~~~~~~~~~~~</option>
            ";

            foreach ( $Move_List as $Key => $Value )
            {
              echo "<option value='{$Value['id']}'>{$Value['name']}</i>";
            }
            
        echo "
            </select>
            <select name='{$Roster_Slot[$i]['ID']}_move_3' onchange='updateMoves({$Roster_Slot[$i]['ID']});' style='margin: 2px; width: 100%;'>
              <option value='{$Move_3['ID']}'>" . $Move_3['Name'] . "</option>
              <option value>~~~~~~~~~~~~~~</option>
            ";

            foreach ( $Move_List as $Key => $Value )
            {
              echo "<option value='{$Value['id']}'>{$Value['name']}</i>";
            }
            
        echo "
            </select>
            <select name='{$Roster_Slot[$i]['ID']}_move_4' onchange='updateMoves({$Roster_Slot[$i]['ID']});' style='margin: 2px; width: 100%;'>
              <option value='{$Move_4['ID']}'>" . $Move_4['Name'] . "</option>
              <option value>~~~~~~~~~~~~~~</option>
            ";

            foreach ( $Move_List as $Key => $Value )
            {
              echo "<option value='{$Value['id']}'>{$Value['name']}</i>";
            }
            
        echo "
            </select>
          </div>
        </div>
      ";
    }
    else
    {
      echo "
        <div class='roster_slot full' style='height: 135px; padding-top: 20px;'>
          <div style='float: left;'>
            <img class='spricon' src='" . Domain(1) . "images/pokemon/0.png'>
          </div>
          <div style='float: left; padding-left: 50px; padding-top: 35px;'>
            <b>Empty</b>
          </div>
        </div>
      ";
    }
  }
  
	echo "
			</div>
    </div>
    
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
          url: '" . Domain(1) . "/core/ajax/pokecenter/moves.php',
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