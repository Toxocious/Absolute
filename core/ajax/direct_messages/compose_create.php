<?php
  require_once '../../required/session.php';

  $Error = false;

  if ( !isset($_POST['Title']) )
    $_POST['Title'] = '';
  
  if ( !isset($_POST['Message']) )
  {
    $Error = true;

    $Text = "
      You may not send empty messages to other players.
    ";
  }

  if
  (
    !isset($_SESSION['direct_message']['users']) ||
    count($_SESSION['direct_message']['users']) < 1
  )
  {
    $Error = true;

    $Text = "
      You must select at least <b>1</b> player to send a direct message to.
    ";
  }

  if ( !$Error )
  {
    $Title = Purify($_POST['Title']);
    $Message = Purify($_POST['Message']);

    $Direct_Message = new DirectMessage();
    $Group_ID = $Direct_Message->ComposeMessage($Title, $Message, $_SESSION['direct_message']['users']);

    if ( $Group_ID )
      $Text = "You have successfully started a new direct message.";
    else
      $Text = "An error occurred while composing a new direct message.";
  }
  
  $Output = [
    'Text' => (isset($Text) ? $Text : null),
    'Group_ID' => (isset($Group_ID) ? $Group_ID : 0),
  ];

  header('Content-Type: application/json');
  echo json_encode($Output);
