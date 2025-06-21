<?php
  require_once '../../required/session.php';

  $Error = false;

  /**
   * Check to see if an empty message is being sent.
   */
  if ( !isset($_POST['Message']) )
  {
    $Error = true;

    $Text = "
      You may not send empty messages to other players.
    ";
  }

  /**
   * If a clan message is being sent, don't check to see if the message is being sent to anyone.
   */
  if ( !isset($_SESSION['EvoChroniclesRPG']['Direct_Message']['clan_data']) )
  {
    /**
     * Check to see if the message is being sent to at least one user.
     */
    if
    (
      !isset($_SESSION['EvoChroniclesRPG']['Direct_Message']['users']) ||
      count($_SESSION['EvoChroniclesRPG']['Direct_Message']['users']) < 1
    )
    {
      $Error = true;

      $Text = "
        You must select at least <b>1</b> player to send a direct message to.
      ";
    }
  }

  /**
   * Handle processing of the title.
   * Clan Messages will default to "<Clan Name> Announcement".
   * If no title is specified, the title shall remain blank.
   */
  if ( !isset($_POST['Title']) )
  {
    if ( isset($_SESSION['EvoChroniclesRPG']['Direct_Message']['clan_data']) )
    {
      $Clan_ID = $User_Data['Clan'];
      $_POST['Title'] = $_SESSION['EvoChroniclesRPG']['Direct_Message']['clan_data']['Name'] . ": Clan Announcement";
    }
    else
    {
      $Clan_ID = null;
      $_POST['Title'] = '';
    }
  }

  if ( !$Error )
  {
    $Title = Purify($_POST['Title']);
    $Message = Purify($_POST['Message']);

    $Direct_Message = new DirectMessage();
    $Group_ID = $Direct_Message->ComposeMessage($Title, $Message, $_SESSION['EvoChroniclesRPG']['Direct_Message']['users'], $Clan_ID);

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
