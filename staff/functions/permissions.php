<?php
  const PERMISSIONS = [
    'Staff',
    'Graphics',
    'Logs',
    'Reports',
    'Bans',
    'User Management',
    'Pokemon Management',
    'Transfer Pokemon',
    'Maintenance',
    'Set Obtainables',
    'Database Edits',
    'Spawn',
    'Staff Management'
  ];

  function CheckUserPermission()
  {
    global $Current_Page, $PDO, $User_Data;

    if ( empty($Current_Page['Required_Permission']) && $Current_Page['Staff_Only'] == 'No' )
      return true;

    if ( empty($Current_Page['Required_Permission']) && $Current_Page['Staff_Only'] == 'Yes' )
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
        $Current_Page['Required_Permission']
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
