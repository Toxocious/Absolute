<?php
  /**
   * GetActiveReports()
   *
   * @return array
   */
  function GetActiveReports()
  {
    global $PDO;

    try
    {
      $Get_Active_Reports = $PDO->prepare("
        SELECT *
        FROM `user_reports`
      ");
      $Get_Active_Reports->execute([ ]);
      $Get_Active_Reports->setFetchMode(PDO::FETCH_ASSOC);
      $Active_Reports = $Get_Active_Reports->fetchAll();
    }
    catch ( PDOException $e )
    {
      HandleError($e);
    }

    return $Active_Reports;
  }

  /**
   * ShowActiveReports()
   *
   * @param $Active_Reports
   *
   * @return string
   */
  function ShowActiveReports
  (
    $Active_Reports
  )
  {
    global $User_Class;

    if ( empty($Active_Reports) )
    {
      return "
        <table class='border-gradient' style='width: 600px;'>
          <tbody>
            <tr>
              <td colspan='3' style='padding: 10px;'>
                There are no active reports.
              </td>
            </tr>
          </tbody>
        </table>
      ";
    }
    else
    {
      $Reported_User_Text = '';

      foreach ( $Active_Reports as $Reported_User )
      {
        $User_Info = $User_Class->FetchUserData($Reported_User['Reported_User_ID']);
        $User_Username = $User_Class->DisplayUsername($Reported_User['Reported_User_ID'], false, false, true);
        $Reported_By_Username = $User_Class->DisplayUserName($Reported_User['Reported_By'], false, false, true);

        $Reported_On = date('m/d/y', $Reported_User['Report_Date']);

        $Report_Reason = '';
        if ( !empty($Reported_User['Reported_Reason']) )
          $Report_Reason = "<div>{$Reported_User['Reported_Reason']}</div>";

        $Reported_User_Text .= "
          <table class='border-gradient' style='width: 600px;'>
            <thead>
              <tr>
                <th colspan='3'>
                  Report Submitted On {$Reported_On}
                </th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td colspan='1' rowspan='1' style='padding: 10px 0px; width: 150px;'>
                  <img src='{$User_Info['Avatar']}' />
                  <br />
                  {$User_Username}
                </td>
                <td colspan='2'>
                  <b>Report Reason</b>
                  <br />
                  {$Report_Reason}
                </td>
              </tr>

              <tr>
                <td colspan='1' style='padding: 5px;'>
                  <b>Reported By</b>
                  <br />
                  {$Reported_By_Username}
                </td>

                <td>
                  <button onclick='DeleteReport({$Reported_User['ID']});'>Delete Report</button>
                </td>
              </tr>
            </tbody>
          </table>
        ";
      }
    }

    return $Reported_User_Text;
  }

  /**
   * DeleteReport()
   *
   * @param $Report_ID
   *
   * @return void
   */
  function DeleteReport
  (
    $Report_ID
  )
  {
    global $PDO, $User_Data;

    try
    {
      $PDO->beginTransaction();

      $RPG_Ban_User = $PDO->prepare("
        DELETE FROM `user_reports`
        WHERE `ID` = ?
        LIMIT 1
      ");
      $RPG_Ban_User->execute([
        $Report_ID
      ]);

      $PDO->commit();

      LogStaffAction('Report Deletion', $User_Data['ID']);
    }
    catch ( PDOException $e )
    {
      $PDO->rollBack();

      HandleError($e);
    }
  }
