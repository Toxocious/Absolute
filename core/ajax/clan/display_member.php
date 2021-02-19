<?php
  require_once '../../required/session.php';

  if ( !isset($_POST['User_ID']) )
  {
    echo "
      <tr>
        <td colspan='4' style='padding: 5px;'>
          You must select a valid member to view this content.
        </td>
      </tr>
    ";

    return;
  }

  $Clan_Data = $Clan_Class->FetchClanData($User_Data['Clan']);
  if ( !$Clan_Data )
  {
    echo "
      <tr>
        <td colspan='4' style='padding: 5px;'>
          You must be in a clan to view this content.
        </td>
      </tr>
    ";

    return;
  }

  $User_ID = Purify($_POST['User_ID']);
  $Fetched_User = $User_Class->FetchUserData($User_ID);
  $User_Clan = $Clan_Class->FetchClanData($Fetched_User['Clan']);

  if ( $Clan_Data['ID'] !== $User_Clan['ID'] )
  {
    echo "
      <tr>
        <td colspan='4' style='padding: 5px;'>
          You can not view other clan's members.
        </td>
      </tr>
    ";

    return;
  }

  if ( $Fetched_User['ID'] == $User_Data['id'] )
  {
    $Kick_Button = '';

    $Position_Button = '';
  }
  else
  {
    $Kick_Button = "
      <tr>
        <td colspan='2'>
          <button onclick='kickMember({$Fetched_User['ID']});'>
            Kick Member
          </button>
        </td>
      </tr>
    ";

    if ( $User_Data['Clan_Rank'] == 'Administrator' )
    {
      if ( $Fetched_User['Clan_Rank'] == 'Member' )
        $Position = 'Moderator';
      else
        $Position = 'Member'; 

      $Position_Button = "
        <tr>
          <td colspan='2'>
            <button onclick='UpdatePosition({$Fetched_User['ID']}, \"{$Position}\");'>
              Set Position to {$Position}
            </button>
          </td>
        </tr>
      ";
    }
  }

  echo "
    <thead>
      <tr>
        <td colspan='2'>
          <b>Manage {$Fetched_User['Username']}</b>
        </td>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td colspan='2' style='width: 100px;'>
          <img src='{$Fetched_User['Avatar']}' />
        </td>
      </tr>
      <tr>
        <td colspan='2' style='width: 200px;'>
          <input type='text' name='title' id='title' placeholder='Insert Clan Title' />
          <br />
          <button onclick='changeTitle({$Fetched_User['ID']});' style='margin-top: 5px;'>
            Bestow Title
          </button>
        </td>
      </tr>
      {$Position_Button}
      {$Kick_Button}
    </tbody>
  ";
  