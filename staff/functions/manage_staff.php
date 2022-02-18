<?php
  require_once $_SERVER['DOCUMENT_ROOT'] . '/staff/functions/permissions.php';

  /**
   * Show a list of all active staff members.
   */
  function ShowActiveStaff()
  {
    global $PDO, $User_Class;

    try
    {
      $Get_Staff_Members = $PDO->prepare("
        SELECT `ID`, `Rank`
        FROM `users`
        WHERE `Is_Staff` = 1
      ");
      $Get_Staff_Members->execute([ ]);
      $Get_Staff_Members->setFetchMode(PDO::FETCH_ASSOC);
      $Staff_Members = $Get_Staff_Members->fetchAll();
    }
    catch ( PDOException $e )
    {
      HandleError($e);
    }

    if ( empty($Staff_Members) )
    {
      return [
        'Success' => false,
        'Message' => 'There are no active staff members.',
      ];
    }

    $Staff_Member_Table = '';
    foreach ( $Staff_Members as $Staff )
    {
      $Staff_Username = $User_Class->DisplayUserName($Staff['ID'], false, true, true);

      $Staff_Member_Table .= "
        <tr>
          <td colspan='3' style='width: 70%;'>
            <h3>{$Staff_Username}</h3>
            <b>{$Staff['Rank']}</b>
          </td>

          <td colspan='1' style='width: 30%;'>
            <button onclick='ShowSelectedUsersPermissions({$Staff['ID']});' style='width: 100px;'>
              Manage
            </button>
          </td>
        </tr>
      ";
    }

    return "
      <table class='border-gradient' style='width: 400px;'>
        <thead>
          <tr>
            <th colspan='4'>
              Add New Staff Member
            </th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td colspan='3' style='padding: 10px;'>
              <input type='number' name='New_Staff_Member_User_ID' placeholder='User ID' />
            </td>
            <td colspan='1'>
              <button onclick='AddNewStaffMember();' style='width: 100px;'>
                Add
              </button>
            </td>
          </tr>
        </tbody>

        <thead>
          <tr>
            <th colspan='4'>
              Manage Existing Staff
            </th>
          </tr>
        </thead>
        <tbody>
          {$Staff_Member_Table}
        </tbody>
      </table>
    ";
  }

  /**
   * Manage the staff permissions of the selected staff member.
   *
   * @param $User_ID
   */
  function ShowStaffMemberPermissionsTable
  (
    $User_ID
  )
  {
    global $User_Class, $PDO;

    $Managing_User_Info = $User_Class->FetchUserData($User_ID);
    $Managing_Username = $User_Class->DisplayUserName($User_ID);

    try
    {
      $Get_User_Perms = $PDO->prepare("
        SELECT `Permission`
        FROM `user_permissions`
        WHERE `User_ID` = ?
      ");
      $Get_User_Perms->execute([ $User_ID ]);
      $Get_User_Perms->setFetchMode(PDO::FETCH_ASSOC);
      $User_Perms = $Get_User_Perms->fetchAll();
    }
    catch ( PDOException $e )
    {
      HandleError($e);
    }

    $Position_Options = '';
    $Positions = ['Member', 'Chat Moderator', 'Moderator', 'Super Moderator', 'Developer', 'Bot', 'Administrator'];
    foreach ( $Positions as $Position )
    {
      $Position_Options .= "
        <option
          value='{$Position}'
          " . ($Managing_User_Info['Rank'] == $Position ? 'selected' : '') . "
        >
          {$Position}
        </option>
      ";
    }

    $Permissions_Table_Rows = '';
    foreach ( PERMISSIONS as $Permission_Name => $Permission_Description )
    {
      $Does_User_Have_Perm = array_search($Permission_Name, array_column($User_Perms, 'Permission')) !== false ? true : false;
      $Permission_Input_Name = str_replace(' ', '_', $Permission_Name);

      $Permissions_Table_Rows .= "
        <tr>
          <td colspan='3' style='width: 75%;'>
            <h3>{$Permission_Name}</h3>
            <i>{$Permission_Description}</i>
          </td>
          <td colspan='1' style='width: 25%;'>
            <input
              type='checkbox'
              name='{$Permission_Input_Name}_Permission'
              " . ($Does_User_Have_Perm ? 'checked' : '' ) . "
            />
          </td>
        </tr>
      ";
    }

    return "
      <table class='border-gradient' style='width: 500px;'>
        <thead>
          <tr>
            <th colspan='4'>
              Managing {$Managing_Username}'s Permissions
            </th>
          </tr>
        </thead>

        <tbody>
          <tr>
            <td colspan='4'>
              <h3>Position</h3>
              <select name='position'>
                {$Position_Options}
              </select>
            </td>
          </tr>
        </tbody>

        <tbody>
          {$Permissions_Table_Rows}
        </tbody>

        <tbody>
          <tr>
            <td colspan='4' style='padding: 10px;'>
              <button onclick='UpdateUserStaffPerms({$User_ID});'>
                Update Permissions
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    ";
  }

  /**
   * Update the staff permissions of the specified staff member.
   *
   * @param $Staff_Perm,
   * @param $Graphics_Perm,
   * @param $Logs_Perm,
   * @param $Reports_Perm,
   * @param $Bans_Perm,
   * @param $User_Management_Perm,
   * @param $Pokemon_Management_Perm,
   * @param $Transer_Pokemon_Perm,
   * @param $Maintenance_Perm,
   * @param $Set_Obtainables_Perm,
   * @param $Database_Perm,
   * @param $Spawn_Perm,
   * @param $Staff_Management_Perm
   */
  function UpdateStaffMemberPermissions
  (
    $User_ID,
    $Staff_Perm,
    $Graphics_Perm,
    $Logs_Perm,
    $Reports_Perm,
    $Bans_Perm,
    $User_Management_Perm,
    $Pokemon_Management_Perm,
    $Transer_Pokemon_Perm,
    $Maintenance_Perm,
    $Set_Obtainables_Perm,
    $Database_Perm,
    $Spawn_Perm,
    $Staff_Management_Perm
  )
  {
    global $PDO;

    try
    {
      $Get_User_Perms = $PDO->prepare("
        SELECT `Permission`
        FROM `user_permissions`
        WHERE `User_ID` = ?
      ");
      $Get_User_Perms->execute([ $User_ID ]);
      $Get_User_Perms->setFetchMode(PDO::FETCH_ASSOC);
      $User_Perms = $Get_User_Perms->fetchAll();
    }
    catch ( PDOException $e )
    {
      HandleError($e);
    }

    foreach ( func_get_args() as $Index => $Giving_Permission )
    {
      if ( $Index === 0 )
        continue;

      $Permission_Name = array_keys(PERMISSIONS)[$Index - 1];
      $User_Has_Perm = array_search($Permission_Name, array_column($User_Perms, 'Permission')) !== false ? true : false;

      if
      (
        ( $User_Has_Perm && $Giving_Permission == 'true' ) ||
        ( !$User_Has_Perm && $Giving_Permission == 'false' )
      )
        continue;

      if ( $User_Has_Perm && $Giving_Permission == 'false' )
      {
        $Permission_Query = "DELETE FROM `user_permissions` WHERE `Permission` = ? AND `User_ID` = ? LIMIT 1";
        $Permission_Params = [ $Permission_Name, $User_ID ];
      }
      else if ( !$User_Has_Perm && $Giving_Permission == 'true' )
      {
        $Permission_Query = "INSERT INTO `user_permissions` ( `Permission`, `User_ID` ) VALUES ( ?, ? )";
        $Permission_Params = [ $Permission_Name, $User_ID ];
      }

      try
      {
        $PDO->beginTransaction();

        $Handle_Permission_Update = $PDO->prepare($Permission_Query);
        $Handle_Permission_Update->execute($Permission_Params);

        $PDO->commit();
      }
      catch ( PDOException $e )
      {
        $PDO->rollBack();

        HandleError($e);
      }
    }

    return [
      'Success' => true,
      'Message' => 'You have successfully updated this user\'s staff permissions.',
    ];
  }
