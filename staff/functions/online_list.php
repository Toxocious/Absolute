<?php
  function GetOnlineUsers()
  {
    global $PDO, $User_Class;

    $Last_Active = time() - 60 * 15;

    try
    {
      $Fetch_Online_Users = $PDO->prepare("SELECT `ID`, `Avatar`, `Last_Page`, `Last_Active` FROM `users` WHERE `Last_Active` >= ? ORDER BY `Last_Active` ASC");
      $Fetch_Online_Users->execute([ $Last_Active ]);
      $Fetch_Online_Users->setFetchMode(PDO::FETCH_ASSOC);
      $Online_Users = $Fetch_Online_Users->fetchAll();
    }
    catch( PDOException $e )
    {
      HandleError( $e );
    }

    $Online_List_Text = "
      <div style='flex-basis: 100%; margin-top: 5px;'>
        <table class='border-gradient' style='width: 550px;'>
          <thead>
            <th colspan='3'>
              Online Users
            </th>
          </thead>
          <tbody>
            <tr>
              <td colspan='1' style='padding: 5px; width: calc(100% / 3);'>
                <b>Username</b>
              </td>
              <td colspan='1' style='padding: 5px; width: calc(100% / 3);'>
                <b>Last Active</b>
              </td>
              <td colspan='1' style='padding: 5px; width: calc(100% / 3);'>
                <b>Visiting Page</b>
              </td>
            </tr>
          </tbody>
          <tbody>
    ";

    if ( empty($Online_Users) )
    {
      $Online_List_Text .= "
        <tr>
          <td colspan='3' style='padding: 10px;'>
            There are currently no active users online.
          </td>
        </tr>
      ";
    }
    else
    {
      foreach ( $Online_Users as $User_Key => $User_Val )
      {
        $User_Username = $User_Class->DisplayUsername($User_Val['ID'], true, false, true);

        $Online_List_Text .= "
          <tr>
            <td colspan='1'>
              <b>{$User_Username}</b>
            </td>
            <td colspan='1'>
              " . lastseen($User_Val['Last_Active'], 'week') . "
            </td>
            <td colspan='1'>
              {$User_Val['Last_Page']}
            </td>
          </tr>
        ";
      }
    }

    $Online_List_Text .= "
          </tbody>
        </table>
      </div>
    ";

    return $Online_List_Text;
  }
