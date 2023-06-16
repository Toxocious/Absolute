<?php
  function GetOnlineUsersTable()
  {
    global $PDO, $User_Class;

    $Last_Active = time() - 60 * 15;

    try
    {
      $Fetch_Online_Staff = $PDO->prepare("
        SELECT `ID`, `Avatar`, `Last_Page`, `Last_Active`
        FROM `users`
        WHERE `Is_Staff` = 1 AND `Last_Active` >= ?
      ");
      $Fetch_Online_Staff->execute([ $Last_Active ]);
      $Fetch_Online_Staff->setFetchMode(PDO::FETCH_ASSOC);
      $Online_Staff = $Fetch_Online_Staff->fetchAll();
    }
    catch( PDOException $e )
    {
      HandleError($e);
    }

    $Online_List_Text = '';

    if ( $Online_Staff )
    {
      $Online_List_Text .= "
        <div style='flex-basis: 100%;'>
          <h2>Online Staff</h2>
        </div>
      ";

      foreach ( $Online_Staff as $Staff_Key => $Staff_Val )
      {
        $Staff_Data = $User_Class->FetchUserData($Staff_Val['ID']);
        $Staff_Username = $User_Class->DisplayUsername($Staff_Val['ID'], true, true, true);

        $Online_List_Text .= "
          <table class='border-gradient' style='flex-basis: 280px; margin: 3px;'>
            <tbody>
              <tr>
                <td rowspan='2' style='width: 100px;'>
                  <img src='{$Staff_Data['Avatar']}' />
                </td>
                <td colspan='2'>
                  <b>
                  {$Staff_Username}
                  </b>
                </td>
              </tr>
              <tr>
                <td colspan='2'>
                  " . LastSeenDate($Staff_Val['Last_Active'], 'week') . "
                </td>
              </tr>
              <tr>
                <td colspan='3' style='padding: 5px;'>
                  {$Staff_Val['Last_Page']}
                </td>
              </tr>
            </tbody>
          </table>
        ";
      }
    }

    /**
     * Fetch non-staff members.
     */
    try
    {
      $Fetch_Online_Users = $PDO->prepare("
        SELECT `ID`, `Avatar`, `Last_Page`, `Last_Active`
        FROM `users`
        WHERE `Is_Staff` = 0 AND `Last_Active` >= ?
        ORDER BY `Last_Active` DESC, `ID` ASC
      ");
      $Fetch_Online_Users->execute([ $Last_Active ]);
      $Fetch_Online_Users->setFetchMode(PDO::FETCH_ASSOC);
      $Online_Users = $Fetch_Online_Users->fetchAll();
    }
    catch( PDOException $e )
    {
      HandleError($e);
    }

    $Online_List_Text .= "
      <div style='flex-basis: 100%; margin-top: 5px;'>
        <table class='border-gradient' style='width: 500px;'>
          <thead>
            <th colspan='3'>
              Online Trainers
            </th>
          </thead>
          <tbody>
            <tr>
              <td colspan='1' style='padding: 5px; width: calc(100% / 3);'>
                <b>Username/ID</b>
              </td>
              <td colspan='1' style='padding: 5px; width: calc(100% / 3);'>
                <b>Last Active</b>
              </td>
              <td colspan='1' style='padding: 5px; width: calc(100% / 3);'>
                <b>Page Visiting</b>
              </td>
            </tr>
          </tbody>
          <tbody>
    ";

    if ( $Online_Users )
    {
      foreach ( $Online_Users as $User_Key => $User_Val )
      {
        $User_Username = $User_Class->DisplayUsername($User_Val['ID'], true, true, true);

        $Online_List_Text .= "
          <tr>
            <td colspan='1'>
              {$User_Username}
            </td>
            <td colspan='1'>
              " . LastSeenDate($User_Val['Last_Active'], 'week') . "
            </td>
            <td colspan='1'>
              {$User_Val['Last_Page']}
            </td>
          </tr>
        ";
      }
    }
    else
    {
      $Online_List_Text .= "
        <tr>
          <td colspan='3' style='padding: 10px;'>
            There are currently no active trainers online.
          </td>
        </tr>
      ";
    }

    $Online_List_Text .= "
          </tbody>
        </table>
      </div>
    ";

    return $Online_List_Text;
  }
