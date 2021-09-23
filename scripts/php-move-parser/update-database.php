<?php
  require_once '../../core/required/session.php';

  if ( empty($_POST['Move_Name']) )
  {
    echo "<div style='color: red;'>Failed to process move (Move_Name param is missing).</div>";
    return;
  }

  if ( empty($_POST['Move_Flags']) )
  {
    echo "<div style='color: orange;'>[{$_POST['Move_Name']}] : Skipping, as it has no flags.</div>";
    return;
  }

  $Move_Data = [
    'Name' => $_POST['Move_Name'],
    'Flags' => $_POST['Move_Flags']
  ];

  /**
   * fetch move data if it exists
   */
  try
  {
    $Fetch_Move = $PDO->prepare("
      SELECT `ID`, `Class_Name`, `Name`
      FROM `moves`
      WHERE `Name` LIKE ?
      LIMIT 1
    ");
    $Fetch_Move->execute([ $Move_Data['Name'] ]);
    $Fetch_Move->setFetchMode(PDO::FETCH_ASSOC);
    $Move = $Fetch_Move->fetch();
  }
  catch ( PDOException $e )
  {
    HandleError($e);
    return;
  }

  try
  {
    $Dupe_Check = $PDO->prepare("
      SELECT `ID`
      FROM `moves_flags`
      WHERE `ID` = ?
      LIMIT 1
    ");
    $Dupe_Check->execute([ $Move['ID'] ]);
    $Dupe_Check->setFetchMode(PDO::FETCH_ASSOC);
    $Has_Dupe = $Dupe_Check->fetch();
  }
  catch ( PDOException $e )
  {

  }

  if ( !empty($Has_Dupe) )
  {
    echo "<div style='color: pink;'>[{$Move['Name']}] : This move already has move flags set; skipping</div>";
    return;
  }

  /**
   * Create string of flags for insert
   */
  $Query = "INSERT INTO `moves_flags` (";
  $Flags = "`ID`, `Move_Name`, `Move_Class`, ";
  foreach ( $Move_Data['Flags'] as $Flag => $Value )
    $Flags .= "`{$Flag}`, ";

  $Flags = substr($Flags, 0, strlen($Flags) - 2);
  $Query .= $Flags . ') VALUES (';

  $Flag_Vals = [ $Move['ID'], $Move['Name'], $Move['Class_Name'] ];
  $Val_Text = '?, ?, ?, ';
  foreach ( $Move_Data['Flags'] as $Flag => $Value )
  {
    $Flag_Vals[] = (int) $Value;
    $Val_Text .= '?, ';
  }
  $Val_Text = substr($Val_Text, 0, strlen($Val_Text) - 2);
  $Query .= $Val_Text . ');';

  try
  {
    $PDO->beginTransaction();

    $Update_Flags = $PDO->prepare($Query);
    $Update_Flags->execute($Flag_Vals);

    $PDO->commit();
  }
  catch ( PDOException $e )
  {
    $PDO->rollback();

    echo "<div style='color: red;'>[{$Move['Name']}] : Error creating flag data (Possible duplicate).</div>";
    echo "<pre>";
    var_dump($_POST, $Move);
    echo "</pre>";
    return;
    // HandleError($e);
  }

  echo "<div style='color: green;'>Successfully created flags for {$Move_Data['Name']}.</div>";
