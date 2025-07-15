<?php
  /**
   * Add a log about what staff action was taken.
   *
   * @param $Action
   * @param $Performed_On
   */
  function LogStaffAction
  (
    $Action,
    $Performed_On
  )
  {
    global $PDO, $User_Data;

    try
    {
      $PDO->beginTransaction();

      $Add_Staff_Log = $PDO->prepare("
        INSERT INTO `staff_logs` (
          `Action`,
          `Performed_On`,
          `Performed_By`,
          `Timestamp`
        ) VALUES ( ?, ?, ?, ? )
      ");
      $Add_Staff_Log->execute([
        $Action,
        $Performed_On,
        $User_Data['ID'],
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
