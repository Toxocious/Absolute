<?php
  /**
   * Update the specified Pokemon's nickname.
   *
   * @param $Pokemon_ID
   * @param $Nickname
   */
  function UpdateNickname
  (
    $Pokemon_ID,
    $Nickname
  )
  {
    global $PDO, $User_Data;

    $Pokemon = GetPokemonData($Pokemon_ID);

    if ( $Pokemon['Owner_Current'] != $User_Data['ID'] )
    {
      return [
        'Success' => false,
        'Message' => 'You may not update the nickname of a Pok&eacute;mon that is not yours.'
      ];
    }

    if ( !empty($Nickname) && $Nickname != '' )
    {
      try
      {
        $PDO->beginTransaction();

        $Update_Pokemon = $PDO->prepare("
          UPDATE `pokemon`
          SET `Nickname` = ?
          WHERE `ID` = ?
          LIMIT 1
        ");
        $Update_Pokemon->execute([
          $Nickname,
          $Pokemon['ID']
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
        'Message' => "<b>{$Pokemon['Display_Name']}</b>'s new nickname is <b>{$Nickname}</b>."
      ];
    }

    try
    {
      $PDO->beginTransaction();

      $Update_Pokemon = $PDO->prepare("
        UPDATE `pokemon`
        SET `Nickname` = null
        WHERE `ID` = ?
        LIMIT 1
      ");
      $Update_Pokemon->execute([
        $Pokemon['ID']
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
      'Message' => "<b>{$Pokemon['Display_Name']}</b>'s nickname has been removed."
    ];
  }
