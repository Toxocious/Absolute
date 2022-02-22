<?php
  /**
   * Return an HTML select dropdown menu of all available moves.
   *
   * @param $Pokemon_ID
   * @param $Move_Slot
   */
  function GetMoveDropdown
  (
    $Pokemon_ID,
    $Move_Slot
  )
  {
    global $PDO;

    try
    {
      $Get_Moves = $PDO->prepare("SELECT `ID`, `Name` FROM `moves` WHERE `Usable` = 1 ORDER BY `Name` ASC");
      $Get_Moves->execute([ ]);
      $Get_Moves->setFetchMode(PDO::FETCH_ASSOC);
      $Move_List = $Get_Moves->fetchAll();
    }
    catch ( PDOException $e )
    {
      HandleError($e);
    }

    $Move_Options = "";
    foreach ( $Move_List as $Move_Data )
      $Move_Options .= "<option value='{$Move_Data['ID']}'>{$Move_Data['Name']}</i>";

    return "
      <select name='{$Pokemon_ID}_Move_{$Move_Slot}' onchange='UpdateMoveSlot({$Pokemon_ID}, {$Move_Slot});'>
        <option>Select A Move</option>
        {$Move_Options}
      </select>
    ";
  }

  /**
   * Update the specified Pokemon's move.
   *
   * @param $Pokemon_ID
   * @param $Move_Slot
   * @param $Move_ID
   */
  function UpdatePokemonMove
  (
    $Pokemon_ID,
    $Move_Slot,
    $Move_ID
  )
  {
    global $PDO, $User_Data;

    try
    {
      $Check_Pokemon_Ownership = $PDO->prepare("
        SELECT `ID`, `Move_1`, `Move_2`, `Move_3`, `Move_4`
        FROM `pokemon`
        WHERE `ID` = ? AND `Owner_Current` = ?
        LIMIT 1
      ");
      $Check_Pokemon_Ownership->execute([
        $Pokemon_ID,
        $User_Data['ID']
      ]);
      $Check_Pokemon_Ownership->setFetchMode(PDO::FETCH_ASSOC);
      $Pokemon_Ownership = $Check_Pokemon_Ownership->fetch();
    }
    catch ( PDOException $e )
    {
      HandleError($e);
    }

    if ( empty($Pokemon_Ownership) )
    {
      return [
        'Success' => false,
        'Message' => 'This Pok&eacute;mon does not belong to you'
      ];
    }

    $Pokemon_Moves = [
      $Pokemon_Ownership['Move_1'],
      $Pokemon_Ownership['Move_2'],
      $Pokemon_Ownership['Move_3'],
      $Pokemon_Ownership['Move_4']
    ];

    $Pokemon_Moves[$Move_Slot] = (int) $Move_ID;

    if ( count(array_unique($Pokemon_Moves)) !== 4 )
    {
      return [
        'Success' => false,
        'Message' => 'Pok&eacute;mon may not have multiple copies of the same move.'
      ];
    }

    try
    {
      $PDO->beginTransaction();

      $Update_Move = $PDO->prepare("
        UPDATE `pokemon`
        SET `Move_{$Move_Slot}` = ?
        WHERE `ID` = ?
        LIMIT 1
      ");
      $Update_Move->execute([
        $Move_ID,
        $Pokemon_ID
      ]);

      $PDO->commit();
    }
    catch ( PDOException $e )
    {
      $PDO->rollBack();

      HandleError($e);
    }

    return [
      'Success' => true,
      'Message' => 'You have updated this Pok&eacute;mon\'s moves.'
    ];
  }
