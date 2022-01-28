<?php
  require_once $_SERVER['DOCUMENT_ROOT'] . '/core/required/session.php';
  require_once $_SERVER['DOCUMENT_ROOT'] . '/staff/functions/ban_user.php';

  if ( !empty($_GET['User_Value']) && in_array(gettype($_GET['User_Value']), ['integer', 'string']) )
    $User_Value = Purify($_GET['User_Value']);

  if ( empty($User_Value) )
  {
    echo json_encode([
      'Success' => false,
      'Message' => 'The user you are trying to ban doesn\'t exist.',
    ]);

    exit;
  }

  $Ban_Type = 'RPG';
  if ( !empty($_GET['Ban_Type']) && in_array($_GET['Ban_Type'], ['RPG', 'Chat']) )
    $Ban_Type = Purify($_GET['Ban_Type']);

  $Unban_Date = null;
  if ( !empty($_GET['Unban_Date']) && gettype($_GET['Unban_Date']) === 'string' && strlen($_GET['Unban_Date']) == 8 )
    $Unban_Date = Purify($_GET['Unban_Date']);

  $Ban_Reason = 'No ban reason was set.';
  if ( !empty($_GET['Ban_Reason']) && gettype($_GET['Ban_Reason']) === 'string' )
    $Ban_Reason = Purify($_GET['Ban_Reason']);

  $Staff_Notes = 'No staff notes were set.';
  if ( !empty($_GET['Staff_Notes']) && gettype($_GET['Staff_Notes']) === 'string' )
    $Staff_Notes = Purify($_GET['Staff_Notes']);

  try
  {
    $Check_User_Existence = $PDO->prepare("
      SELECT `ID`, `Username`
      FROM `users`
      WHERE `ID` = ? OR `Username` = ?
      LIMIT 1
    ");
    $Check_User_Existence->execute([
      $User_Value,
      $User_Value
    ]);
    $Check_User_Existence->setFetchMode(PDO::FETCH_ASSOC);
    $User_Existence = $Check_User_Existence->fetch();
  }
  catch ( PDOException $e )
  {
    HandleError($e);
  }

  if ( !$User_Existence || count($User_Existence) === 0 )
  {
    echo json_encode([
      'Success' => false,
      'Message' => 'The user you are trying to ban doesn\'t exist.',
    ]);

    exit;
  }

  try
  {
    $Check_User_Ban = $PDO->prepare("
      SELECT *
      FROM `user_bans`
      WHERE `User_ID` = ?
      LIMIT 1
    ");
    $Check_User_Ban->execute([
      $User_Existence['ID']
    ]);
    $Check_User_Ban->setFetchMode(PDO::FETCH_ASSOC);
    $User_Ban = $Check_User_Ban->fetch();
  }
  catch ( PDOException $e )
  {
    HandleError($e);
  }

  if ( !empty($User_Ban) )
  {
    if ( $User_Ban['RPG_Ban'] )
    {
      echo json_encode([
        'Success' => false,
        'Message' => "{$User_Existence['Username']} is already banned.",
      ]);

      exit;
    }

    if ( $User_Ban['Chat_Ban'] && $Ban_Type == 'Chat' )
    {
      echo json_encode([
        'Success' => false,
        'Message' => "{$User_Existence['Username']} is already chat banned.",
      ]);

      exit;
    }
  }

  switch ( $Ban_Type )
  {
    case 'RPG':
      RPGBanUser($User_Existence['ID'], 1, $Ban_Reason, $Staff_Notes, $Unban_Date);
      break;

    case 'Chat':
      ChatBanUser($User_Existence['ID'], 1, $Ban_Reason, $Staff_Notes, $Unban_Date);
      break;
  }

  echo json_encode([
    'Success' => true,
    'Message' => "{$User_Existence['Username']} has been banned.",
  ]);
