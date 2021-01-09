<?php
  require_once '../../required/session.php';

  $Error = false;

  if ( !isset($_POST['Group_ID']) )
    return;
  
  if ( !isset($_POST['Message']) )
    return;

  $Group_ID = Purify($_POST['Group_ID']);
  $Message = Purify($_POST['Message']);

  $Direct_Message = new DirectMessage();
  $Participant_Check = $Direct_Message->IsParticipating($Group_ID, $User_Data['id']);

  if ( !$Participant_Check )
    return;

  $Messages = $Direct_Message->CreateMessage($Group_ID, $Message, $User_Data['id']);

  if ( !$Message )
  {
    echo "
      An error occurred while attemping to send your message.
    ";
  }
