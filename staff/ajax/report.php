<?php
  require_once $_SERVER['DOCUMENT_ROOT'] . '/core/required/session.php';
  require_once $_SERVER['DOCUMENT_ROOT'] . '/staff/functions/report.php';

  if ( !empty($_GET['Report_ID']) )
    $Report_ID = Purify($_GET['Report_ID']);

  if ( empty($Report_ID) )
  {
    echo json_encode([
      'Success' => false,
      'Message' => 'The report you are trying to delete doesn\'t exist.',
    ]);

    exit;
  }

  try
  {
    $Check_Report_Existence = $PDO->prepare("
      SELECT *
      FROM `user_reports`
      WHERE `ID` = ?
      LIMIT 1
    ");
    $Check_Report_Existence->execute([
      $Report_ID
    ]);
    $Check_Report_Existence->setFetchMode(PDO::FETCH_ASSOC);
    $Report_Existence = $Check_Report_Existence->fetch();
  }
  catch ( PDOException $e )
  {
    HandleError($e);
  }

  if ( empty($Report_Existence) )
  {
    echo json_encode([
      'Success' => false,
      'Message' => 'This report no longer exists.',
    ]);

    exit;
  }

  DeleteReport($Report_ID);

  echo json_encode([
    'Success' => true,
    'Message' => "This report has been deleted.",
    'Active_Report_List' => ShowActiveReports(GetActiveReports()),
  ]);
