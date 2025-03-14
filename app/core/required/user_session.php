<?php
  $Time = time();

  /**
   * Handle active session logic at the start of page loads.
   *  - Get active user data
   *  - Update active user page info, playtime, and last page active on
   */
  if ( isset($_SESSION['Absolute']) )
  {
    $User_Data = $User_Class->FetchUserData($_SESSION['Absolute']['Logged_In_As']);

    if ( !isset($_SESSION['Absolute']['Playtime']) )
    {
      $_SESSION['Absolute']['Playtime'] = $Time;
    }

    $Playtime = $Time - $_SESSION['Absolute']['Playtime'];
    $Playtime = $Playtime > 20 ? 20 : $Playtime;
    $_SESSION['Absolute']['Playtime'] = $Time;

    try
    {
      $Update_Activity = $PDO->prepare("INSERT INTO `logs` (`Type`, `Page`, `Data`, `User_ID`) VALUES ('pageview', ?, ?, ?)");
      $Update_Activity->execute([ $Current_Page['Name'], $Parse_URL['path'], $User_Data['ID'] ]);

      $Update_User = $PDO->prepare("UPDATE `users` SET `Last_Active` = ?, `Last_Page` = ?, `Playtime` = `Playtime` + ? WHERE `ID` = ? LIMIT 1");
      $Update_User->execute([ $Time, $Current_Page['Name'], $Playtime, $User_Data['ID'] ]);
    }
    catch ( PDOException $e )
    {
      HandleError($e);
    }
  }
