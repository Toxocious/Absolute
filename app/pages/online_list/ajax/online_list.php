<?php
  require_once $_SERVER['DOCUMENT_ROOT'] . '/pages/online_list/functions/online_list.php';

  if ( !empty($_GET['Action']) && in_array($_GET['Action'], ['Get_Online_Users']) )
    $Action = Purify($_GET['Action']);

  // Default to the 'Get_Online_Users' action as it's the only possible option.
  if ( empty($Action) )
  {
    $Action = 'Get_Online_Users';
  }

  switch ( $Action )
  {
    case 'Get_Online_Users':
    default:
      echo json_encode([
        'Online_List' => GetOnlineUsersTable()
      ]);
      break;
  }
