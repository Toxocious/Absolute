<?php
  require_once '../../required/session.php';

  /**
   * Reset the compose session data.
   */
  if ( isset($_SESSION['direct_message']['users']) )
  {
    $_SESSION['direct_message']['users'] = [];
    unset($_SESSION['direct_message']['users']);
  }

  $_SESSION['direct_message']['users'][] = [
    'User_ID' => $User_Data['ID'],
  ];
?>

<table class='border-gradient' style='width: 650px;'>
  <thead>
    <tr>
      <th colspan='1' style='font-size: 18px;'>
        Group Name
      </th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td>
        <input
          type='text'
          name='group'
          id='group-title'
          placeholder='Group Name'
          style='width: 300px;'
        />
      </td>
    </tr>
  </tbody>

  <thead>
    <tr>
      <th colspan='1' style='font-size: 18px;'>
        Message Content
      </th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td style='padding: 7px 5px 0px;'>
        <textarea
          id='message-content'
          style='width: 625px;'
          rows='10'
        ></textarea>
      </td>
    </tr>
  </tbody>

  <tbody>
    <tr>
      <td>
        <button onclick='ComposeMessage();'>
          Create Direct Message
        </button>
      </td>
    </tr>
  </tbody>
</table>

<br />

<div class='flex'>
  <div style='flex-basis: 50%; width: 50%;'>
    <h3>Add Users To Conversation</h3>

    <table class='border-gradient' style='width: 100%;'>
      <tbody>
        <tr>
          <td id='selected-user'>
            <img src='<?= DOMAIN_SPRITES . '/Pokemon/Sprites/0.png' ?>' />
            <br />
            <i>Enter a trainer's username or ID.</i>
          </td>
        </tr>
        <tr>
          <td>
            <input type='text' id='select-user' placeholder='Trainer ID/Username' />
            <button onclick='AddUser();' style='width: 100px;'>
              Add User
            </button>
          </td>
        </tr>
      </tbody>
    </table>
  </div>

  <div style='flex-basis: 50%; width: 50%;'>
    <h3>Added Users</h3>

    <div id='added-users'>
      <i>No users have been added.</i>
    </div>
  </div>
</div>
