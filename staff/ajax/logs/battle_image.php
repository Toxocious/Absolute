<?php
  require_once $_SERVER['DOCUMENT_ROOT'] . '/core/required/session.php';
  require_once $_SERVER['DOCUMENT_ROOT'] . '/staff/functions/auth.php';

  if ( !AuthorizeUser() )
  {
    echo "
      <div class='panel content'>
        <div class='head'>Staff Panel</div>
        <div class='body' style='padding: 5px'>
          You aren't authorized to be here.
        </div>
      </div>
    ";

    exit;
  }

  require_once $_SERVER['DOCUMENT_ROOT'] . '/staff/functions/log_heatmap.php';
  require_once $_SERVER['DOCUMENT_ROOT'] . '/battles/classes/log.php';
  $Log_Instance = new Log();

  $Window_Height = 900;
  $Window_Width = 1600;

  $Logs_To_Show = 2000;
  if ( !empty($_GET['Logs_To_Show']) )
    $Logs_To_Show = Purify($_GET['Logs_To_Show']);

  $User_ID_To_Show = 1;
  if ( !empty($_GET['User_ID_To_Show']) )
    $User_ID_To_Show = Purify($_GET['User_ID_To_Show']);

  try
  {
    $Get_Logged_User_Data = $PDO->prepare("
      SELECT `ID`, `Username`
      FROM `users`
      WHERE `ID` = ?
      LIMIT 1
    ");
    $Get_Logged_User_Data->execute([ $User_ID_To_Show ]);
    $Get_Logged_User_Data->setFetchMode(PDO::FETCH_ASSOC);
    $Logged_User_Data = $Get_Logged_User_Data->fetch();

    $Get_Battle_Log_Data = $PDO->prepare("
      SELECT `Actions_Performed`, `User_ID`, `Time_Battle_Started`
      FROM `battle_logs`
      WHERE `User_ID` = ?
      ORDER BY `ID` DESC
      LIMIT ?
    ");
    $Get_Battle_Log_Data->execute([ $User_ID_To_Show, $Logs_To_Show ]);
    $Get_Battle_Log_Data->setFetchMode(PDO::FETCH_ASSOC);
    $Battle_Log_Data = $Get_Battle_Log_Data->fetchAll();
  }
  catch ( PDOException $e )
  {
    HandleError($e);
  }

  $Image_Data = [];

  $Start_Time = null;
  $Finish_Time = null;

  foreach ( $Battle_Log_Data as $Log_Data )
  {
    if ( empty($Start_Time) )
      $Start_Time = $Log_Data['Time_Battle_Started'];

    $Finish_Time = $Log_Data['Time_Battle_Started'];

    $Actions = unpack('l*', $Log_Data['Actions_Performed']);

    foreach ( $Actions as $Action )
    {
      $Action_Data = $Log_Instance->Parse($Action);

      if ( empty($Image_Data[$Action_Data['Coords']['x']][$Action_Data['Coords']['y']]) )
        $Image_Data[$Action_Data['Coords']['x']][$Action_Data['Coords']['y']] = 1;
      else
        $Image_Data[$Action_Data['Coords']['x']][$Action_Data['Coords']['y']]++;
    }
  }

  $Heatmap_Image = imagecreatetruecolor($Window_Width, $Window_Height);
  foreach ( $Image_Data as $x => $ys )
  {
    foreach ($ys as $y => $Inputs)
    {
      if ($Inputs > 25)
        $Inputs = 25;

      imagesetpixel($Heatmap_Image, $x, $y, SetHeatmapColor($Inputs));
    }
  }

  $Image_Text = "Battle Log Heatmap -- {$Logged_User_Data['Username']} (" . count($Battle_Log_Data) . " Logs)";

  $Image_Text_Color = imagecolorallocate($Heatmap_Image, 0, 127, 255);
  imagestring($Heatmap_Image, 5, 4, 2, $Image_Text, $Image_Text_Color);

  $Image_Date_Text = date('m/d/y h:i A', $Start_Time) . ' to ' . date('m/d/y h:i A', $Finish_Time);
  imagestring($Heatmap_Image, 5, 4, 20, $Image_Date_Text, $Image_Text_Color);

  if (!headers_sent())
  {
    header('Content-Type: image/png');
    imagepng($Heatmap_Image);
  }
