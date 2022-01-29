<?php
  require_once $_SERVER['DOCUMENT_ROOT'] . '/core/required/session.php';
  require_once $_SERVER['DOCUMENT_ROOT'] . '/staff/functions/ban.php';

  if ( !empty($_GET['Banned_User_ID']) )
    $Banned_User_ID = Purify($_GET['Banned_User_ID']);

  if ( empty($Banned_User_ID) )
  {
    echo json_encode([
      'Success' => false,
      'Message' => 'The user you are trying to unban doesn\'t exist.',
    ]);

    exit;
  }

  if ( !empty($_GET['Ban_Action']) && in_array($_GET['Ban_Action'], ['Unban']) )
    $Ban_Action = Purify($_GET['Ban_Action']);

  if ( empty($Ban_Action) )
  {
    echo json_encode([
      'Success' => false,
      'Message' => 'An invalid action was selected.',
    ]);

    exit;
  }

  try
  {
    $Check_Ban_Existence = $PDO->prepare("
      SELECT `User_ID`
      FROM `user_bans`
      WHERE `User_ID` = ?
      LIMIT 1
    ");
    $Check_Ban_Existence->execute([
      $Banned_User_ID
    ]);
    $Check_Ban_Existence->setFetchMode(PDO::FETCH_ASSOC);
    $Ban_Existence = $Check_Ban_Existence->fetch();
  }
  catch ( PDOException $e )
  {
    HandleError($e);
  }

  if ( empty($Ban_Existence) )
  {
    echo json_encode([
      'Success' => false,
      'Message' => 'This ban no longer exists.',
    ]);

    exit;
  }

  $User_Username = $User_Class->DisplayUsername($Ban_Existence['User_ID'], false, false, true);

  switch ( $Ban_Action )
  {
    case 'Unban':
      UnbanUser($Banned_User_ID);

      echo json_encode([
        'Success' => true,
        'Message' => "{$User_Username} has been unbanned.",
        'Banned_User_List' => GetBannedUsers(),
      ]);
      break;
  }
