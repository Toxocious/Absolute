<?php
  require_once '../../required/session.php';

  $Clan_Data = $Clan_Class->FetchClanData($User_Data['Clan']);
  if ( !$Clan_Data )
  {
    echo "
      <tr>
        <td colspan='4'>
          <div class='error'>
            You're not currently in a clan.
          </div>
        </td>
      </tr>
    ";

    return;
  }

  if ( $User_Data['Clan_Rank'] != 'Administrator' )
  {
    echo "
      <tr>
        <td colspan='4'>
          <div class='error'>
            You must be a Clan Administrator to use this feature.
          </div>
        </td>
      </tr>
    ";

    return;
  }

  if
  (
    isset($_POST['User_ID']) &&
    isset($_POST['Position'])
  )
  {
    $User_ID = Purify($_POST['User_ID']);
    $Position = Purify($_POST['Position']);

    if ( in_array($Position, ['Member', 'Moderator']) )
    {
      $Selected_User = $User_Class->FetchUserData($User_ID);
      if ( $Selected_User )
      {
        $Update_Rank = $Clan_Class->UpdateRank($Selected_User['Clan'], $Selected_User['ID'], $Position);
        if ( $Update_Rank )
        {
          echo "
            <tr>
              <td colspan='2'>
                <div style='margin-bottom: 0; padding: 5px;'>
                  You have successfully updated {$Selected_User['Username']}'s position.
                </div>
              </td>
            </tr>
          ";
        }
        else
        {
          echo "
            <tr>
              <td colspan='2'>
                <div style='margin-bottom: 0; padding: 5px;'>
                  An error occurred while updating {$Selected_User['Username']}'s position.
                </div>
              </td>
            </tr>
          ";
        }
      }
      else
      {
        echo "
          <tr>
            <td colspan='2'>
              <div style='margin-bottom: 0; padding: 5px;'>
                An error occurred while fetching the selected user's data.
              </div>
            </td>
          </tr>
        ";
      }
    }
    else
    {
      echo "
        <tr>
          <td colspan='2'>
            <div style='margin-bottom: 0; padding: 5px;'>
              An invalid clan rank was selected.
            </div>
          </td>
        </tr>
      ";
    }
  }
  else
  {
    echo "
      <tr>
        <td colspan='2'>
          <div style='margin-bottom: 0; padding: 5px;'>
            Please select a valid user.
          </div>
        </td>
      </tr>
    ";
  }
