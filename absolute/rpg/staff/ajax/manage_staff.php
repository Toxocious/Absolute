<?php
  require_once $_SERVER['DOCUMENT_ROOT'] . '/core/required/session.php';
  require_once $_SERVER['DOCUMENT_ROOT'] . '/staff/functions/manage_staff.php';

  if ( !empty($_GET['User_ID']) )
    $User_ID = Purify($_GET['User_ID']);

  try
  {
    $Check_User_Existence = $PDO->prepare("
      SELECT `ID`
      FROM `users`
      WHERE `ID` = ? OR `Username` = ?
      LIMIT 1
    ");
    $Check_User_Existence->execute([
      $User_ID,
      $User_ID
    ]);
    $Check_User_Existence->setFetchMode(PDO::FETCH_ASSOC);
    $User_Existence = $Check_User_Existence->fetch();
  }
  catch ( PDOException $e )
  {
    HandleError($e);
  }

  if ( empty($User_ID) || empty($User_Existence) )
  {
    echo json_encode([
      'Success' => false,
      'Message' => 'The user you are trying to modify doesn\'t exist.',
    ]);

    exit;
  }

  if ( !empty($_GET['Action']) && in_array($_GET['Action'], ['Show_User_Perms', 'Update_User_Perms']) )
    $Action = Purify($_GET['Action']);

  if ( empty($Action) )
  {
    echo json_encode([
      'Success' => false,
      'Message' => 'An invalid action was selected.',
    ]);

    exit;
  }


  $Staff_Perm = null;
  if ( !empty($_GET['Staff_Perm']) )
    $Staff_Perm = Purify($_GET['Staff_Perm']);

  $Graphics_Perm = null;
  if ( !empty($_GET['Graphics_Perm']) )
      $Graphics_Perm = Purify($_GET['Graphics_Perm']);

  $Logs_Perm = null;
  if ( !empty($_GET['Logs_Perm']) )
      $Logs_Perm = Purify($_GET['Logs_Perm']);

  $Reports_Perm = null;
  if ( !empty($_GET['Reports_Perm']) )
      $Reports_Perm = Purify($_GET['Reports_Perm']);

  $Bans_Perm = null;
  if ( !empty($_GET['Bans_Perm']) )
      $Bans_Perm = Purify($_GET['Bans_Perm']);

  $User_Management_Perm = null;
  if ( !empty($_GET['User_Management_Perm']) )
      $User_Management_Perm = Purify($_GET['User_Management_Perm']);

  $Pokemon_Management_Perm = null;
  if ( !empty($_GET['Pokemon_Management_Perm']) )
      $Pokemon_Management_Perm = Purify($_GET['Pokemon_Management_Perm']);

  $Transer_Pokemon_Perm = null;
  if ( !empty($_GET['Transer_Pokemon_Perm']) )
      $Transer_Pokemon_Perm = Purify($_GET['Transer_Pokemon_Perm']);

  $Maintenance_Perm = null;
  if ( !empty($_GET['Maintenance_Perm']) )
      $Maintenance_Perm = Purify($_GET['Maintenance_Perm']);

  $Set_Obtainables_Perm = null;
  if ( !empty($_GET['Set_Obtainables_Perm']) )
      $Set_Obtainables_Perm = Purify($_GET['Set_Obtainables_Perm']);

  $Database_Perm = null;
  if ( !empty($_GET['Database_Perm']) )
      $Database_Perm = Purify($_GET['Database_Perm']);

  $Spawn_Perm = null;
  if ( !empty($_GET['Spawn_Perm']) )
      $Spawn_Perm = Purify($_GET['Spawn_Perm']);

  $Staff_Management_Perm = null;
  if ( !empty($_GET['Staff_Management_Perm']) )
      $Staff_Management_Perm = Purify($_GET['Staff_Management_Perm']);

  switch ( $Action )
  {
    case 'Show_User_Perms':
      $User_Perm_Table = ShowStaffMemberPermissionsTable($User_Existence['ID']);

      echo json_encode([
        'Success' => $User_Perm_Table['Success'] ?? null,
        'Message' => $User_Perm_Table['Message'] ?? null,
        'User_Perm_Table' => $User_Perm_Table,
      ]);
      break;

    case 'Update_User_Perms':
      $Update_User_Perms = UpdateStaffMemberPermissions(
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
      );

      echo json_encode([
        'Success' => $Update_User_Perms['Success'],
        'Message' => $Update_User_Perms['Message'],
        'User_Perm_Table' => ShowStaffMemberPermissionsTable($User_Existence['ID']),
      ]);
      break;
  }
