<?php
  /**
   * Add a new user report to the database.
   *
   * @param $Reported_User_ID
   * @param $Reported_Reason
   */
  function AddNewReport
  (
    $Reported_User_ID,
    $Reported_Reason
  )
  {
    global $PDO, $User_Data;

    try
    {
      $PDO->beginTransaction();

      $Add_Report_Entry = $PDO->prepare("
        INSERT INTO `user_reports` (
          `Reported_User_ID`,
          `Reported_By`,
          `Reported_Reason`,
          `Report_Date`
        ) VALUES ( ?, ?, ?, ? )
      ");
      $Add_Report_Entry->execute([
        $Reported_User_ID,
        $User_Data['ID'],
        $Reported_Reason,
        time()
      ]);

      $PDO->commit();
    }
    catch ( PDOException $e )
    {
      $PDO->rollBack();

      HandleError($e);
    }
  }
