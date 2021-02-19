<?php
  require_once '../core/required/layout_top.php';

  $Clan_Data = $Clan_Class->FetchClanData($User_Data['Clan']);

  $Disbanded = false;
?>

<div class='panel content'>
  <div class='head'>Disband Clan</div>
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
      As the owner of this Clan, you have the power to disband this clan.
      <br />
      <b>Disbanding a clan is <u>permanent</u>, and can not be undone.</b>
      <br /><br />
      Upon disbanding this clan, the following will occur:
      <br />
      &bull; All clan statistics and donations are wiped.<br />
      &bull; All members will be removed from the clan.<br />
    </div>

    <?php
      if
      (
        isset($_POST['Disband']) &&
        isset($_POST['Clan_Name'])
      )
      {
        $Disband = Purify($_POST['Disband']);
        $Clan_Name = Purify($_POST['Clan_Name']);

        if ( $Clan_Name == "I wish to disband {$Clan_Data['Name']}" )
        {
          $Clan_Members = $Clan_Class->FetchMembers($Clan_Data['ID']);
          if ( $Clan_Members )
          {
            $Disband_Clan = $Clan_Class->DisbandClan($Clan_Data['ID']);
            if ( $Disband_Clan )
            {
              $Disbanded = true;

              echo "
                <div class='success'>
                  You have successfully disbanded your clan.
                </div>
              ";
            }
            else
            {
              echo "
                <div class='error'>
                  An error occurred while attempting to disband your clan.
                </div>
              ";
            }
          }
          else
          {
            echo "
              <div class='error'>
                An error occurred while attempting to disband your clan.
              </div>
            ";
          }
        }
        else
        {
          echo "
            <div class='warning'>
              Please correctly enter the required confirmation dialogue.
            </div>
          ";
        }
      }

      if ( $Disbanded )
      {
    ?>

    <form method='POST'>
      <table class='border-gradient' style='width: 400px;'>
        <tbody>
          <tr>
            <td colspan='2' style='height: 200px; width: 200px;'>
              <?= ( $Clan_Data['Avatar'] ? "<img src='{$Clan_Data['Avatar']}' />" : 'This clan has no avatar set.' ); ?>
            </td>
            <td colspan='2' style='height: 200px; width: 200px;'>
              <?= ( $Clan_Data['Signature'] ? $Clan_Data['Signature'] : 'This clan has no signature set.' ); ?>
            </td>
          </tr>
          <tr>
            <td colspan='4' style='padding: 10px;'>
              <b>Are you sure you wish to disband your clan?</b>
            </td>
          </tr>
          <tr>
            <td colspan='4' style='padding: 5px;'>
              Please type in "I wish to disband <?= $Clan_Data['Name']; ?>".
              <br />
              <input
                type='text'
                name='Clan_Name'
              />
            </td>
          </tr>
          <tr>
            <td colspan='4' style='padding: 5px;'>
              <input
                type='submit'
                name='Disband'
                value='Disband Clan'
              />
            </td>
          </tr>
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
