<?php
  require_once '../core/required/layout_top.php';

  $Clan_Data = $Clan_Class->FetchClanData($User_Data['Clan']);

  $Passed_Ownership = false;
?>

<div class='panel content'>
  <div class='head'>Transfer Clan Ownership</div>
  <div class='body' style='padding: 5px;'>
    <?php
      if ( !$Clan_Data )
      {
        echo "
          <div class='error' style='margin-bottom: 0px;'>
            To access this page, you must currently be in a clan.
          </div>
        ";
    
        return;
      }

      if ( $User_Data['Clan_Rank'] != 'Administrator' )
      {
        echo "
          <div class='error' style='margin-bottom: 0px;'>
            To access this page, you must be a clan Administrator.
          </div>
        ";
    
        return;
      }
    ?>

    <div class='description'>
      As the owner of this Clan, you have the power to transfer ownership of the clan to a fellow clanmate.
      <br />
      This is permanent, and upon transferring ownership, your clan rank will be set to that of Clan Moderator.
      <br />
      Only clan members who are Clan Moderators are eligible to be passed ownership.
    </div>

    <?php
      if
      (
        isset($_POST['Ownership']) &&
        isset($_POST['Member_ID'])
      )
      {
        $Ownership = Purify($_POST['Ownership']);
        $Member_ID = Purify($_POST['Member_ID']);

        $Member_Data = $User_Class->FetchUserData($Member_ID);
        if ( $Member_Data )
        {
          if ( $Member_Data['Clan'] == $Clan_Data['ID'] )
          {
            if ( $Member_Data['Clan_Rank'] == 'Moderator' )
            {
              $Demote_Self = $Clan_Class->UpdateRank($Clan_Data['ID'], $User_Data['ID'], 'Moderator');
              if ( $Demote_Self )
              {
                $Promote_User = $Clan_Class->UpdateRank($Clan_Data['ID'], $Member_Data['ID'], 'Administrator');
                if ( $Promote_User )
                {
                  $Passed_Ownership = true;

                  echo "
                    <div class='success'>
                      You have successfully passed ownership of {$Clan_Data['Name']} to {$Member_Data['Username']}.
                    </div>
                  ";
                }
                else
                {
                  echo "
                    <div class='error'>
                      An error occurred while transferring ownership of the clan.
                    </div>
                  ";
                }
              }
              else
              {
                echo "
                  <div class='error'>
                    An error occurred while transferring ownership of the clan.
                  </div>
                ";
              }
            }
            else
            {
              echo "
                <div class='error'>
                  You may not transfer ownership to a user who isn't a Clan Moderator.
                </div>
              ";
            }
          }
          else
          {
            echo "
              <div class='error'>
                You may not transfer ownership to a user who isn't in your clan.
              </div>
            ";
          }
        }
        else
        {
          echo "
            <div class='error'>
              An invalid user has been selected.
            </div>
          ";
        }
      }

      if ( !$Passed_Ownership )
      {
    ?>

    <form method='POST'>
      <table class='border-gradient' style='width: 500px;'>
        <thead>
          <tr>
            <th colspan='4'>
              Eligible Clan Members
            </th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td colspan='1'>
              <b>Username</b>
            </td>
            <td colspan='1'>
              <b>Clan Title</b>
            </td>
            <td colspan='1'>
              <b>Clan Exp.</b>
            </td>
            <td colspan='1'>
              <b>Promote</b>
            </td>
          </tr>
        </tbody>
        <tbody>
          <?php
            $Member_List = $Clan_Class->FetchMembers($Clan_Data['ID']);
            if ( $Member_List )
            {
              foreach ( $Member_List as $Member )
              {
                $Member = $User_Class->FetchUserData($Member['id']);

                if
                (
                  $User_Data['ID'] == $Member['ID'] ||
                  $Member['Clan_Rank'] != 'Moderator'
                )
                  continue;

                echo "
                  <tr>
                    <td style='width: 125px;'>
                      <a href='" . DOMAIN_ROOT . "/profiles.php?id={$Member['ID']}'>
                        <b class='" . strtolower($Member['Clan_Rank']) . "'>
                          {$Member['Username']}
                        </b>
                      </a>
                    </td>
                    <td style='width: 125px;'>
                      {$Member['Clan_Title']}
                    </td>
                    <td style='width: 125px;'>
                      {$Member['Clan_Exp']}
                    </td>
                    <td style='width: 125px;'>
                      <input
                        type='hidden'
                        name='Member_ID'
                        value='{$Member['ID']}'
                      />
                      <input
                        type='submit'
                        name='Ownership'
                        value='Pass Ownership'
                        style='width: 110px;'
                      />
                    </td>
                  </tr>
                ";
              }
            }
            else
            {
              echo "
                <tr>
                  <td colspan='3'>
                    <div class='error'>
                      An error occurred while fetching the members of your clan.
                    </div>
                  </td>
                </tr>
              ";
            }
          ?>
        </tbody>
      </table>
    </form>

    <?php
      }
    ?>
  </div>
</div>

<?php
  require_once '../core/required/layout_bottom.php';
