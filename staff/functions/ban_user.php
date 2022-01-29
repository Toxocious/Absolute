<?php
  /**
   * RPGBanUser
   *
   * @param $User_ID
   * @param $RPG_Ban
   * @param $RPG_Ban_Reason
   * @param $RPG_Ban_Until
   *
   * @return void
   */
  function RPGBanUser
  (
    $User_ID,
    $RPG_Ban,
    $RPG_Ban_Reason,
    $RPG_Ban_Staff_Notes,
    $RPG_Ban_Until
  )
  {
    global $PDO, $User_Data;

    try
    {
      $PDO->beginTransaction();

      $RPG_Ban_User = $PDO->prepare("
        INSERT INTO `user_bans` (
          `User_ID`, `Banned_By`,
          `RPG_Ban`, `RPG_Ban_Reason`, `RPG_Ban_Staff_Notes`, `RPG_Ban_Until`
        )
        VALUES ( ?, ?, ?, ?, ?, ? )
        ON DUPLICATE KEY UPDATE `RPG_Ban` = ?, `RPG_Ban_Reason` = ?, `RPG_Ban_Staff_Notes` = ?, `RPG_Ban_Until` = ?
      ");
      $RPG_Ban_User->execute([
        $User_ID,
        $User_Data['ID'],
        $RPG_Ban,
        $RPG_Ban_Reason,
        $RPG_Ban_Staff_Notes,
        $RPG_Ban_Until,
        $RPG_Ban,
        $RPG_Ban_Reason,
        $RPG_Ban_Staff_Notes,
        $RPG_Ban_Until
      ]);

      $PDO->commit();
    }
    catch ( PDOException $e )
    {
      $PDO->rollBack();

      HandleError($e);
    }
  }

  /**
   * ChatBanUser
   *
   * @param $User_ID
   * @param $Chat_Ban
   * @param $Chat_Ban_Reason
   * @param $Chat_Ban_Until
   *
   * @return void
   */
  function ChatBanUser
  (
    $User_ID,
    $Chat_Ban,
    $Chat_Ban_Reason,
    $Chat_Ban_Staff_Notes,
    $Chat_Ban_Until
  )
  {
    global $PDO, $User_Data;

    try
    {
      $PDO->beginTransaction();

      $Chat_Ban_User = $PDO->prepare("
        INSERT INTO `user_bans` (
          `User_ID`, `Banned_By`,
          `Chat_Ban`, `Chat_Ban_Reason`, `Chat_Ban_Staff_Notes`, `Chat_Ban_Until`
        )
        VALUES ( ?, ?, ?, ?, ?, ? )
        ON DUPLICATE KEY UPDATE `Chat_Ban` = ?, `Chat_Ban_Reason` = ?, `Chat_Ban_Staff_Notes` = ?, `Chat_Ban_Until` = ?
      ");
      $Chat_Ban_User->execute([
        $User_ID,
        $User_Data['ID'],
        $Chat_Ban,
        $Chat_Ban_Reason,
        $Chat_Ban_Staff_Notes,
        $Chat_Ban_Until,
        $Chat_Ban,
        $Chat_Ban_Reason,
        $Chat_Ban_Staff_Notes,
        $Chat_Ban_Until,
      ]);

      $PDO->commit();
    }
    catch ( PDOException $e )
    {
      $PDO->rollBack();

      HandleError($e);
    }
  }
