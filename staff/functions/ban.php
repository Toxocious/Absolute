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
          `User_ID`, `Banned_By`, `Banned_On`,
          `RPG_Ban`, `RPG_Ban_Reason`, `RPG_Ban_Staff_Notes`, `RPG_Ban_Until`
        )
        VALUES ( ?, ?, ?, ?, ?, ?, ? )
        ON DUPLICATE KEY UPDATE `RPG_Ban` = ?, `RPG_Ban_Reason` = ?, `RPG_Ban_Staff_Notes` = ?, `RPG_Ban_Until` = ?
      ");
      $RPG_Ban_User->execute([
        $User_ID,
        $User_Data['ID'],
        time(),
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
          `User_ID`, `Banned_By`, `Banned_On`,
          `Chat_Ban`, `Chat_Ban_Reason`, `Chat_Ban_Staff_Notes`, `Chat_Ban_Until`
        )
        VALUES ( ?, ?, ?, ?, ?, ?, ? )
        ON DUPLICATE KEY UPDATE `Chat_Ban` = ?, `Chat_Ban_Reason` = ?, `Chat_Ban_Staff_Notes` = ?, `Chat_Ban_Until` = ?
      ");
      $Chat_Ban_User->execute([
        $User_ID,
        $User_Data['ID'],
        time(),
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

  /**
   * UnbanUser
   *
   * @param $User_ID
   *
   * @return void
   */
  function UnbanUser
  (
    $User_ID
  )
  {
    global $PDO;

    try
    {
      $PDO->beginTransaction();

      $RPG_Ban_User = $PDO->prepare("
        DELETE FROM `user_bans`
        WHERE `User_ID` = ?
        LIMIT 1
      ");
      $RPG_Ban_User->execute([
        $User_ID
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
   * GetBannedUsers
   *
   * @return void
   */
  function GetBannedUsers()
  {
    global $PDO;

    try
    {
      $Fetch_Banned_Users = $PDO->prepare("
        SELECT *
        FROM `user_bans`
      ");
      $Fetch_Banned_Users->execute([ ]);
      $Fetch_Banned_Users->setFetchMode(PDO::FETCH_ASSOC);
      $Banned_Users = $Fetch_Banned_Users->fetchAll();
    }
    catch( PDOException $e )
    {
      HandleError( $e );
    }

    return $Banned_Users;
  }

  /**
   * ShowBannedUsers
   *
   * @param $Banned_Users
   *
   * @return string
   */
  function ShowBannedUsers
  (
    $Banned_Users
  )
  {
    global $User_Class;

    if ( empty($Banned_Users) )
    {
      return "
        <table class='border-gradient' style='width: 600px;'>
          <tbody>
            <tr>
              <td colspan='3' style='padding: 10px;'>
                There are no banned users.
              </td>
            </tr>
          </tbody>
        </table>
      ";
    }
    else
    {
      $Banned_User_Text = '';

      foreach ( $Banned_Users as $Banned_User )
      {
        $User_Info = $User_Class->FetchUserData($Banned_User['User_ID']);
        $User_Username = $User_Class->DisplayUsername($Banned_User['User_ID'], false, false, true);
        $Banned_By_Username = $User_Class->DisplayUserName($Banned_User['Banned_By'], false, false, true);

        $Unban_Date = '';
        if ( !empty($Banned_User['RPG_Ban_Until']) )
          $Unban_Date = ' &mdash; ' . date('m/d/y', $Banned_User['RPG_Ban_Until']);
        else if ( !empty($Banned_User['Chat_Ban_Until']) )
          $Unban_Date = ' &mdash; ' . date('m/d/y', $Banned_User['Chat_Ban_Until']);

        $Ban_Reason = '';
        if ( !empty($Banned_User['RPG_Ban_Reason']) )
        {
          $Ban_Type = 'RPG';
          $Ban_Reason .= "<div>{$Banned_User['RPG_Ban_Reason']}</div>";
        }
        if ( !empty($Banned_User['Chat_Ban_Reason']) )
        {
          if ( empty($Ban_Type) )
            $Ban_Type = 'Chat';

          $Ban_Reason .= "<div>{$Banned_User['Chat_Ban_Reason']}</div>";
        }

        $Staff_Notes = '';
        if ( !empty($Banned_User['RPG_Ban_Staff_Notes']) )
          $Staff_Notes .= "<div>{$Banned_User['RPG_Ban_Staff_Notes']}</div>";
        if ( !empty($Banned_User['Chat_Ban_Staff_Notes']) )
          $Staff_Notes .= "<div>{$Banned_User['Chat_Ban_Staff_Notes']}</div>";

        if ( $Staff_Notes === '' )
        {
          $Staff_Note_Rows = '';
        }
        else
        {
          $Staff_Note_Rows = "
            <tr>
              <td colspan='2'>
                <b>Staff Notes</b>
                <br />
                {$Staff_Notes}
              </td>
            </tr>
          ";
        }

        $Banned_User_Text .= "
          <table class='border-gradient' style='width: 600px;'>
            <thead>
              <tr>
                <th colspan='1'>
                  " . date('m/d/y', $Banned_User['Banned_On']) . "
                  {$Unban_Date}
                </th>
                <th colspan='2'>
                  {$User_Info['Username']} &mdash; {$Ban_Type} Ban
                </th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td colspan='1' rowspan='2' style='padding: 10px 0px; width: 150px;'>
                  <img src='{$User_Info['Avatar']}' />
                  <br />
                  {$User_Username}
                </td>
                <td colspan='2'>
                  <b>Ban Reason</b>
                  <br />
                  {$Ban_Reason}
                </td>
              </tr>

              {$Staff_Note_Rows}

              <tr>
                <td colspan='1' style='padding: 5px;'>
                  <b>Banned By</b>
                  <br />
                  {$Banned_By_Username}
                </td>

                <td>
                  <button onclick='UnbanUser({$Banned_User['User_ID']});'>Unban</button>
                </td>
              </tr>
            </tbody>
          </table>
        ";
      }
    }

    return $Banned_User_Text;
  }
