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

  if ( !$Clan_Data['ID'] )
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

  if ( !isset($_POST['Title']) )
  {
    echo "
      <tr>
        <td colspan='4' style='padding: 5px;'>
          You must input a valid title.
        </td>
      </tr>
    ";

    return;
  }

  $Title = Purify($_POST['Title']);
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

  if ( $User_Data['Clan_Rank'] === 'Member' )
  {
    echo "
      <tr>
        <td colspan='2'>
          <b style='color: #ff0000;'>
            Regular clan members do not have the power to modify clan titles.
          </b>
        </td>
      </tr>
    ";

    return;
  }

  $Update_Title = $Clan_Class->UpdateTitle($User_Clan['ID'], $Fetched_User['ID'], $Title);

  if ( !$Update_Title )
  {
    echo "
      <tr>
        <td colspan='2'>
          <b style='color: #ff0000;'>
            An error occurred while setting {$Fetched_User['Username']}'s clan title.
          </b>
        </td>
      </tr>
    ";
  }
  else
  {
    echo "
      <tr>
        <td colspan='2'>
          <img src='{$Fetched_User['Avatar']}' />
        </td>
      </tr>
      <tr>
        <td colspan='2'>
          <b style='color: #00ff00;'>
            {$Fetched_User['Username']} has had their clan title updated.
          </b>
        </td>
      </tr>
    ";
  }
