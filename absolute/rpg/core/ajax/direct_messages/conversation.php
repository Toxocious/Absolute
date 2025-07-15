<?php
  require_once '../../required/session.php';
  
  /**
   * No message ID has been set.
   */
  if ( !isset($_GET['Group_ID']) )
  {
    echo "
      <div class='flex' style='align-items: center; height: -webkit-fill-available; justify-content: center;'>
        <div>
          <h2>Message History</h2>
          <font style='color: #ff0000;'>
            Please select a valid Direct Message.
          </font>
        </div>
      </div>
    ";

    return;
  }

  $Group_ID = Purify(intval($_GET['Group_ID']));

  $Direct_Message = new DirectMessage();
  $Messages = $Direct_Message->FetchMessage($Group_ID, $User_Data['ID']);

  /**
   * Unable to fetch the message history of the selected direct message.
   */
  if ( !$Messages )
  {
    echo "
      <div class='flex' style='align-items: center; height: -webkit-fill-available; justify-content: center;'>
        <div>
          <h2>Message History</h2>
          <font style='color: #ff0000;'>
            Please select a valid Direct Message.
          </font>
        </div>
      </div>
    ";

    return;
  }

  $Direct_Message->ReadDirectMessage($Group_ID, $User_Data['ID']);

  $Total_Messages = count($Messages);

  echo "
    <div id='message-container' style='height: 610px; overflow: auto;'>
  ";
  foreach ( $Messages as $Key => $Message )
  {
    $Participant = $User_Class->FetchUserData($Message['User_ID']);
    $Username = $User_Class->DisplayUserName($Message['User_ID'], false, false, true);
    $Sent_On = date("M j, Y (g:i A)", $Message['Timestamp']);

    if ( $Key !== 0 )
      echo "<hr class='faded' />";

    echo "
      <div class='flex'>
        <div style='flex-basis: 100px; min-width: 100px;'>
          <img
            src='{$Participant['Avatar']}'
            style='border-radius: 50%; height: 50px; width: 50px;'
          />
        </div>
        <div style='flex-basis: auto; text-align: left;'>
          <b>{$Username}</b>
          &nbsp;&nbsp;
          <i style='color: #999; font-size: 11px;'>{$Sent_On}</i>
          <br />
          &nbsp;&nbsp;&nbsp;&nbsp;{$Message['Message']}
        </div>
      </div>
    ";
  }
  echo "</div>";

  /**
   * Display the client's textbox to submit messages.
   * Also displays a setting icon to adjust the chat's settings.
   */
  echo "
    <div style='bottom: 5px; position: absolute; width: 650px;'>
      <hr class='faded' style='width: 100%;' />
      <input
        type='text'
        name='message'
        id='dm-message'
        data-group-id='{$Group_ID}'
        placeholder='Message Group'
        style='width: 80%;'
      />
      <input
        type='button'
        name='send-message'
        onclick='AddMessage();'
        value='Send'
        style='width: 15%;'
      />
    </div>
  ";
