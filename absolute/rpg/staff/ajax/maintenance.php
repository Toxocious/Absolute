<?php
  require_once $_SERVER['DOCUMENT_ROOT'] . '/core/required/session.php';
  require_once $_SERVER['DOCUMENT_ROOT'] . '/staff/functions/maintenance.php';

  if ( !empty($_GET['Page_ID']) )
    $Page_ID = Purify($_GET['Page_ID']);

  if ( empty($Page_ID) )
  {
    echo json_encode([
      'Success' => false,
      'Message' => 'The page you are trying to modify doesn\'t exist.',
    ]);

    exit;
  }

  if ( !empty($_GET['Page_Action']) && in_array($_GET['Page_Action'], ['Toggle']) )
    $Page_Action = Purify($_GET['Page_Action']);

  if ( empty($Page_Action) )
  {
    echo json_encode([
      'Success' => false,
      'Message' => 'An invalid action was selected.',
    ]);

    exit;
  }

  try
  {
    $Check_Page_Existence = $PDO->prepare("
      SELECT `ID`
      FROM `pages`
      WHERE `ID` = ?
      LIMIT 1
    ");
    $Check_Page_Existence->execute([
      $Page_ID
    ]);
    $Check_Page_Existence->setFetchMode(PDO::FETCH_ASSOC);
    $Page_Existence = $Check_Page_Existence->fetch();
  }
  catch ( PDOException $e )
  {
    HandleError($e);
  }

  if ( empty($Page_Existence) )
  {
    echo json_encode([
      'Success' => false,
      'Message' => 'This page does not exist.',
    ]);

    exit;
  }

  switch ( $Page_Action )
  {
    case 'Toggle':
      $Toggle_Page = TogglePageMaintenance($Page_Existence['ID']);

      echo json_encode([
        'Success' => $Toggle_Page['Success'],
        'Message' => $Toggle_Page['Message'],
        'Maintenance_Table' => $Toggle_Page['Maintenance_Table'],
      ]);
      break;
  }
