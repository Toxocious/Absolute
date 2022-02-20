<?php
  /**
   * Display a table that allows modification of a given Pokemon.
   *
   * @param $Pokemon_ID
   */
  function ShowPokemonModTable
  (
    $Pokemon_ID
  )
  {
    global $Poke_Class;

    $Pokemon_Info = $Poke_Class->FetchPokemonData($Pokemon_ID);

    $Frozen_Status = false;
    $Frozen_Text = '';
    if ( $Pokemon_Info['Frozen'] )
    {
      $Frozen_Status = true;
      $Frozen_Text = '<div><i>This Pok&eacute;mon is <b>frozen</b> and bound to its owner\'s account.</i></div>';
    }

    $Pokemon_Moves = [
      '1' => $Poke_Class->FetchMoveData($Pokemon_Info['Move_1']),
      '2' => $Poke_Class->FetchMoveData($Pokemon_Info['Move_2']),
      '3' => $Poke_Class->FetchMoveData($Pokemon_Info['Move_3']),
      '4' => $Poke_Class->FetchMoveData($Pokemon_Info['Move_4']),
    ];

    $Nature_Keys = array_keys($Poke_Class->Natures());
    $Nature_Options = '';
    foreach ( $Nature_Keys as $Nature )
      $Nature_Options .= "<option value='{$Nature}'>{$Nature}</option>";

    $Abilities = $Poke_Class->FetchAbilities($Pokemon_Info['Pokedex_ID'], $Pokemon_Info['Alt_ID']);
    $Ability_Options = '';
    foreach ( $Abilities as $Ability )
      if ( !empty($Ability) )
        $Ability_Options .= "<option value='{$Ability}'>{$Ability}</option>";

    return "
      <input type='hidden' name='Pokemon_ID_To_Update' value='{$Pokemon_ID}}' />
      <input type='hidden' name='Pokemon_Freeze_Status' value='{$Frozen_Status}' />

      <table class='border-gradient' style='width: 500px;'>
        <thead>
          <tr>
            <th colspan='4'>
              Modifying Pok&eacute;mon #{$Pokemon_Info['ID']}
            </th>
          </tr>
        </thead>

        <tbody>
          <tr>
            <td colspan='4' style='width: 100%;'>
              <img src='{$Pokemon_Info['Sprite']}' />
              <br />
              <b>{$Pokemon_Info['Display_Name']}</b>
              {$Frozen_Text}
            </td>
          </tr>

          <tr>
            <td colspan='2' style='width: 50%;'>
              <h3>Level</h3>
            </td>
            <td colspan='2' style='width: 50%;'>
              <input type='text' name='Level' value='{$Pokemon_Info['Level_Raw']}' />
            </td>
          </tr>

          <tr>
            <td colspan='2' style='width: 50%;'>
              <h3>Gender</h3>
            </td>
            <td colspan='2' style='width: 50%;'>
              <select name='Gender' style='padding: 4px; text-align: center;'>
                <option value='Ungendered'>(?)</option>
                <option value='Genderless'>Genderless</option>
                <option value='Female'>Female</option>
                <option value='Male'>Male</option>
              </select>
            </td>
          </tr>

          <tr>
            <td colspan='2' style='width: 50%;'>
              <h3>Nature</h3>
            </td>
            <td colspan='2' style='width: 50%;'>
              <select name='Nature' style='padding: 4px; text-align: center;'>
                {$Nature_Options}
              </select>
            </td>
          </tr>

          <tr>
            <td colspan='2' style='width: 50%;'>
              <h3>Ability</h3>
            </td>
            <td colspan='2' style='width: 50%;'>
              <select name='Ability' style='padding: 4px; text-align: center;'>
                {$Ability_Options}
              </select>
            </td>
          </tr>
        </tbody>

        <tbody>
          <tr>
            <td colspan='4' style='padding: 5px; width: 100%;'>
              <button onclick='UpdatePokemon();'>
                Update Pok&eacute;mon
              </button>
            </td>
          </tr>
        </tbody>

        <tbody>
          <tr>
            <td colspan='4' style='width: 50%;'>
              <h3>Moves</h3>
            </td>
          </tr>
          <tr>
            <td colspan='2' style='width: 50%;'>
              <div id='{$Pokemon_ID}_Move_1' onclick='SelectMove(\"{$Pokemon_ID}\", 1);' style='padding: 3px 0px;'>
                {$Pokemon_Moves['1']['Name']}
              </div>
            </td>
            <td colspan='2' style='width: 50%;'>
              <div id='{$Pokemon_ID}_Move_2' onclick='SelectMove(\"{$Pokemon_ID}\", 2);' style='padding: 3px 0px;'>
                {$Pokemon_Moves['2']['Name']}
              </div>
            </td>
          </tr>
          <tr>
            <td colspan='2' style='width: 50%;'>
              <div id='{$Pokemon_ID}_Move_3' onclick='SelectMove(\"{$Pokemon_ID}\", 3);' style='padding: 3px 0px;'>
                {$Pokemon_Moves['3']['Name']}
              </div>
            </td>
            <td colspan='2' style='width: 50%;'>
              <div id='{$Pokemon_ID}_Move_4' onclick='SelectMove(\"{$Pokemon_ID}\", 4);' style='padding: 3px 0px;'>
                {$Pokemon_Moves['4']['Name']}
              </div>
            </td>
          </tr>
        </tbody>
      </table>

      <br />

      <table style='width: 600px;'>
        <tbody>
          <tr>
            <td colspan='1' style='padding: 0px 10px; width: 50%;'>
              <button onclick='DeletePokemon();'>
                Delete Pok&eacute;mon
              </button>

              <br /><br />

              <i>
                This effectively releases the Pok&eacute;mon from the owner's account.
              </i>
            </td>

            <td colspan='1' style='padding: 0px 10px; width: 50%;'>
              <button onclick='TogglePokemonFreeze();'>
                Freeze Pok&eacute;mon
              </button>

              <br /><br />

              <i>
                This prevents the Pok&eacute;mon from leaving the owner's account.
              </i>
            </td>
          </tr>
        </tbody>
      </table>
    ";
  }

  /**
   * Display a list of selectable moves that a Pokemon can learn.
   *
   * @param $Pokemon_ID
   * @param $Move_Slot
   */
  function ShowMoveList
  (
    $Pokemon_ID,
    $Move_Slot
  )
  {
    global $PDO;

    try
    {
      $Fetch_Moves = $PDO->prepare("
        SELECT *
        FROM `moves`
        WHERE `usable` = 1
      ");
      $Fetch_Moves->execute([ ]);
      $Fetch_Moves->setFetchMode(PDO::FETCH_ASSOC);
      $Move_List = $Fetch_Moves->fetchAll();
    }
    catch ( PDOException $e )
    {
      HandleError($e);
    }

    if ( !$Move_List )
      return '';

    $Move_Options = '';
    foreach ( $Move_List as $Key => $Value )
      $Move_Options .= "<option value='{$Value['ID']}'>{$Value['Name']}</i>";

    return "
      <select name='{$Pokemon_ID}_Move_{$Move_Slot}' onchange='UpdateMove({$Pokemon_ID}, {$Move_Slot}, this);'>
        <option>Select A Move</option>
        <option value>---</option>
        {$Move_Options}
      </select>
    ";
  }

  /**
   * Update the selected move of a Pokemon.
   *
   * @param $Pokemon_ID
   * @param $Move_Slot
   * @param $Move_ID
   */
  function UpdateMove
  (
    $Pokemon_ID,
    $Move_Slot,
    $Move_ID
  )
  {
    global $PDO, $Poke_Class, $User_Data;

    $Pokemon_Data = $Poke_Class->FetchPokemonData($Pokemon_ID);

    $Current_Moves = [
      $Pokemon_Data['Move_1'],
      $Pokemon_Data['Move_2'],
      $Pokemon_Data['Move_3'],
      $Pokemon_Data['Move_4'],
    ];

    if ( count(array_unique($Current_Moves)) != 4 )
    {
      return [
        'Success' => false,
        'Message' => "<div class='error'>You may not have the same move more than once.</div>",
      ];
    }
    else
    {
      try
      {
        $PDO->beginTransaction();

        $Update_Moves = $PDO->prepare("
          UPDATE `pokemon`
          SET `Move_{$Move_Slot}` = ?
          WHERE `ID` = ?
          LIMIT 1
        ");
        $Update_Moves->execute([
          $Move_ID,
          $Pokemon_ID
        ]);

        $PDO->commit();

        LogStaffAction('Pokemon Move Update', $User_Data['ID']);
      }
      catch( PDOException $e )
      {
        $PDO->rollBack();

        HandleError($e);
      }

      return [
        'Success' => true,
        'Message' => "<b>{$Pokemon_Data['Display_Name']}'s</b> moves have been updated successfully.",
      ];
    }
  }

  /**
   * Update the selected Pokemon.
   *
   * @param $Pokemon_ID
   * @param $Pokemon_Level
   * @param $Pokemon_Gender
   * @param $Pokemon_Nature
   * @param $Pokemon_Ability
   */
  function UpdatePokemon
  (
    $Pokemon_ID,
    $Pokemon_Level,
    $Pokemon_Gender,
    $Pokemon_Nature,
    $Pokemon_Ability
  )
  {
    global $PDO, $Poke_Class, $User_Data;

    $Pokemon_Info = $Poke_Class->FetchPokemonData($Pokemon_ID);
    if ( !$Pokemon_Info )
    {
      return [
        'Success' => false,
        'Message' => "The Pok&eacute;mon that you attempted to update doesn't exist.",
        'Modification_Table' => ShowPokemonModTable($Pokemon_ID)
      ];
    }

    $Update_Query = 'UPDATE `pokemon` SET ';

    if ( $Pokemon_Level != $Pokemon_Info['Level_Raw'] )
    {
      $Pokemon_Exp = FetchExperience($Pokemon_Level, 'Pokemon');
      $Update_Query .= '`Experience` = ?, ';
      $Update_Params[] = $Pokemon_Exp;
    }

    if ( $Pokemon_Gender != $Pokemon_Info['Gender'] )
    {
      $Update_Query .= '`Gender` = ?, ';
      $Update_Params[] = $Pokemon_Gender;
    }

    if ( $Pokemon_Nature != $Pokemon_Info['Nature'] )
    {
      $Update_Query .= '`Nature` = ?, ';
      $Update_Params[] = $Pokemon_Nature;
    }

    if ( $Pokemon_Ability != $Pokemon_Info['Ability'] )
    {
      $Update_Query .= '`Ability` = ?, ';
      $Update_Params[] = $Pokemon_Ability;
    }

    $Update_Query = trim($Update_Query, ', ');
    $Update_Query .= ' WHERE `ID` = ? LIMIT 1';
    $Update_Params[] = $Pokemon_ID;

    try
    {
      $PDO->beginTransaction();

      $Update_Pokemon = $PDO->prepare($Update_Query);
      $Update_Pokemon->execute( $Update_Params );

      $PDO->commit();

      LogStaffAction('Pokemon Update', $User_Data['ID']);
    }
    catch ( PDOException $e )
    {
      $PDO->rollBack();

      HandleError($e);
    }

    return [
      'Success' => true,
      'Message' => "You have successfully updated {$Pokemon_Info['Display_Name']}",
      'Modification_Table' => ShowPokemonModTable($Pokemon_ID)
    ];
  }

  /**
   * Toggle whether the Pokemon is frozen.
   *
   * @param $Pokemon_ID
   * @param $Frozen_Status
   */
  function ToggleFreeze
  (
    $Pokemon_ID,
    $Frozen_Status
  )
  {
    global $PDO, $User_Data;

    $Opposite_Status = $Frozen_Status ? 0 : 1;
    if ( $Opposite_Status )
      $Frozen_Message = 'This Pok&eacute;mon has been frozen.';
    else
      $Frozen_Message = 'This Pok&eacute;mon has been unfrozen.';

    try
    {
      $PDO->beginTransaction();

      $Update_Frozen_Status = $PDO->prepare("
        UPDATE `pokemon`
        SET `Frozen` = ?
        WHERE `ID` = ?
        LIMIT 1
      ");
      $Update_Frozen_Status->execute([
        $Opposite_Status,
        $Pokemon_ID
      ]);

      $PDO->commit();

      LogStaffAction('Pokemon Freeze Toggle', $User_Data['ID']);

      return [
        'Success' => true,
        'Message' => $Frozen_Message,
        'Modification_Table' => ShowPokemonModTable($Pokemon_ID)
      ];
    }
    catch ( PDOException $e )
    {
      $PDO->rollBack();

      HandleError($e);

      return [
        'Success' => false,
        'Message' => 'There was an error while toggling the frozen status of this Pok&eacute;mon.',
        'Modification_Table' => ShowPokemonModTable($Pokemon_ID)
      ];
    }
  }

  /**
   * Delete the selected Pokemon.
   *
   * @param $Pokemon_ID
   */
  function DeletePokemon
  (
    $Pokemon_ID
  )
  {
    global $Poke_Class, $User_Data;

    $Pokemon_Info = $Poke_Class->FetchPokemonData($Pokemon_ID);
    if ( !$Pokemon_Info )
    {
      return [
        'Success' => false,
        'Message' => "The Pok&eacute;mon you're trying to delete does not exist.",
      ];
    }

    $Release_Pokemon = $Poke_Class->ReleasePokemon($Pokemon_ID, $Pokemon_Info['Owner_Current'], true);

    LogStaffAction('Release Pokemon', $User_Data['ID']);

    return [
      'Success' => $Release_Pokemon['Type'] == 'success' ? true : false,
      'Message' => $Release_Pokemon['Message']
    ];
  }
