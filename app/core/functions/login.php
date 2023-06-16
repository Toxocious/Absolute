<?php
  /**
   * Check if a given user exists by their username or user id.
   *
   * @param $Log_In_Value
   */
  function CheckUserExistence
  (
    $Log_In_Value
  )
  {
    global $PDO;

    try
    {
      $Query_User = $PDO->prepare("
        SELECT `ID`
        FROM `users`
        WHERE (`Username` = ? or `ID` = ?)
        LIMIT 1
      ");
      $Query_User->execute([
        $Log_In_Value,
        $Log_In_Value
      ]);
      $Query_User->setFetchMode(PDO::FETCH_ASSOC);
      $User_Info = $Query_User->fetch();
    }
    catch ( PDOException $e )
    {
      HandleError($e);
    }

    if ( !$User_Info )
      return false;

    return $User_Info;
  }

  /**
   * Check the submitted password against the stored password hash.
   *
   * @param $User_ID
   * @param $Password
   */
  function CheckUserPasswordMatch
  (
    $User_ID,
    $Password
  )
  {
    global $PDO;

    try
    {
      $Get_User_Password_Hash = $PDO->prepare("
        SELECT `Password`
        FROM `user_passwords`
        WHERE `ID` = ?
        LIMIT 1
      ");
      $Get_User_Password_Hash->execute([
        $User_ID
      ]);
      $Get_User_Password_Hash->setFetchMode(PDO::FETCH_ASSOC);
      $User_Password_Hash = $Get_User_Password_Hash->fetch();
    }
    catch ( PDOException $e )
    {
      HandleError($e);
    }

    if ( !$User_Password_Hash )
      return false;

    $Password_Check = password_verify($Password, $User_Password_Hash['Password']);
    if ( !$Password_Check )
      return false;

    return true;
  }

  /**
   * Track all attempted account logins in the database.
   *
   * @param $Account_Attempted
   * @param $Attempted_By_IP
   * @param $Was_Attempt_Successful
   */
  function TrackLoginAttempt
  (
    $Account_Attempted,
    $Attempted_By_IP,
    $Was_Attempt_Successful
  )
  {
    global $PDO;

    try
    {
      $PDO->beginTransaction();

      $Log_Attempt = $PDO->prepare("
        INSERT INTO `user_login_attempts` (
          `User_Info`,
          `Attempted_By_IP`,
          `Attempted_On`,
          `Was_Attempt_Successful`
        )
        VALUES ( ?, ?, ?, ? )
      ");
      $Log_Attempt->execute([
        $Account_Attempted,
        $Attempted_By_IP,
        time(),
        $Was_Attempt_Successful
      ]);

      $PDO->commit();
    }
    catch ( PDOException $e )
    {
      $PDO->rollBack();

      HandleError($e);
    }
  }
