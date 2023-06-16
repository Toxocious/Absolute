<?php
  const PERMISSIONS = [
    'Staff' => 'Required to access the staff panel index page.',
    'Graphics' => 'Grants access to the sprite list.',
    'Logs' => 'Grants access to the log system.',
    'Reports' => 'Grants access to all reports.',
    'Bans' => 'Grants access to the ban system.',
    'User Management' => 'Grants access to modify users.',
    'Pokemon Management' => 'Grants access to modify user\'s Pok&eacute;mon.',
    'Transfer Pokemon' => 'Grants access to transfer Pok&eacute;mon.',
    'Maintenance' => 'Grants access to put pages in and out of maintenace.',
    'Set Obtainables' => 'Grants access to set all obtainable items and Pok&eacute;mon.',
    'Database Edits' => 'Grants access to edit base item, move, and Pok&eacute;dex values.',
    'Spawn' => 'Grants access to the item and Pok&eacute;mon spawners.',
    'Staff Management' => 'Grants access to the manage staff panel.',
  ];

  /**
   * Check if the user has the required permission for the page.
   *
   * @param $Permission_Name
   */
  function CheckUserPermission
  (
    $Permission_Name = null
  )
  {
    global $Current_Page, $PDO, $User_Data;

    if ( empty($Permission_Name) )
      $Permission_Name = $Current_Page['Required_Permission'];

    if ( empty($Permission_Name) && $Current_Page['Staff_Only'] == 'No' )
      return true;

    if ( empty($Permission_Name) && $Current_Page['Staff_Only'] == 'Yes' )
      return false;

    try
    {
      $Check_User_Permission = $PDO->prepare("
        SELECT `ID`
        FROM `user_permissions`
        WHERE `User_ID` = ? AND `Permission` = ?
        LIMIT 1
      ");
      $Check_User_Permission->execute([
        $User_Data['ID'],
        $Permission_Name
      ]);
      $Check_User_Permission->setFetchMode(PDO::FETCH_ASSOC);
      $User_Permission = $Check_User_Permission->rowCount();
    }
    catch ( PDOException $e )
    {
      HandleError($e);
    }

    if ( empty($User_Permission) || $User_Permission === 0 )
      return false;

    return true;
  }
