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
    global $User_Class;

    $Pokemon_Info = GetPokemonData($Pokemon_ID);
    $Current_Owner = $User_Class->DisplayUserName($Pokemon_Info['Owner_Current'], true, true, true);

    return "
      <input type='hidden' name='Pokemon_ID_To_Transfer' value='{$Pokemon_ID}}' />

      <table class='border-gradient' style='width: 500px;'>
        <thead>
          <tr>
            <th colspan='4'>
              Transferring Pok&eacute;mon #{$Pokemon_Info['ID']}
            </th>
          </tr>
        </thead>

        <tbody>
          <tr>
            <td colspan='4' style='width: 100%;'>
              <img src='{$Pokemon_Info['Sprite']}' />
              <br />
              <b>{$Pokemon_Info['Display_Name']}</b>
            </td>
          </tr>

          <tr>
            <td colspan='2' style='width: 50%;'>
              <h3>Current Owner</h3>
            </td>
            <td colspan='2' style='width: 50%;'>
              {$Current_Owner}
            </td>
          </tr>

          <tr>
            <td colspan='2' style='width: 50%;'>
              <h3>Transfer To</h3>
            </td>
            <td colspan='2' style='width: 50%;'>
              <input type='number' name='Transfer_To_User_ID' onkeydown='return event.keyCode !== 69' />
            </td>
          </tr>
        </tbody>

        <tbody>
          <tr>
            <td colspan='4' style='padding: 5px;'>
              <button onclick='TransferPokemon();'>
                Transfer Pok&eacute;mon
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    ";
  }

  /**
   * Transfer the selected Pokemon to the specified user.
   *
   * @param $Pokemon_ID
   * @param $User_ID
   */
  function TransferPokemon
  (
    $Pokemon_ID,
    $User_ID
  )
  {
    global $PDO, $User_Class, $User_Data;

    $User_Existence = $User_Class->FetchUserData($User_ID);
    if ( !$User_Existence )
    {
      return [
        'Success' => false,
        'Message' => 'The user you are trying to transfer this Pok&eacute;mon to does not exist.',
      ];
    }

    if ( $User_Existence['ID'] == $User_ID )
    {
      return [
        'Success' => false,
        'Message' => 'You may not transfer a Pok&eacute;mon to its current owner.',
      ];
    }

    try
    {
      $PDO->beginTransaction();

      $Transfer_Pokemon = $PDO->prepare("
        UPDATE `pokemon`
        SET `Owner_Current` = ?
        WHERE `ID` = ?
        LIMIT 1
      ");
      $Transfer_Pokemon->execute([
        $User_ID,
        $Pokemon_ID
      ]);

      $PDO->commit();

      LogStaffAction('Pokemon Transferred', $User_Data['ID']);
    }
    catch ( PDOException $e )
    {
      $PDO->rollBack();

      HandleError($e);
    }

    return [
      'Success' => true,
      'Message' => "This Pok&eacute;mon has been transferred to {$User_Existence['Username']}.",
    ];
  }
