<?php
  require_once '/xampp/htdocs/core/required/session.php';

  try
  {
    $Fetch_Moves = $PDO->prepare("SELECT `Name` FROM `moves`");
    $Fetch_Moves->execute([ ]);
    $Fetch_Moves->setFetchMove(PDO::FETCH_ASSOC);
    $Moves = $Fetch_Moves->fetchAll();
  }
  catch ( PDOException $e )
  {
    HandleError($e);
  }

  if ( empty($Moves) )
    return 'Moves table is empty.';

  $ch = curl_init();
  curl_setopt( $ch, CURLOPT_URL, 'http://www.something.com');
  curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true);

  $content = curl_exec($ch);
