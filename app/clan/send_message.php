<?php
  require_once '../core/required/layout_top.php';

  /**
   * Reset the compose session data.
   */
  if ( isset($_SESSION['EvoChroniclesRPG']['Direct_Message']['users']) )
  {
    $_SESSION['EvoChroniclesRPG']['Direct_Message']['users'] = [];
    unset($_SESSION['EvoChroniclesRPG']['Direct_Message']['users']);
  }

  $Clan_Data = $Clan_Class->FetchClanData($User_Data['Clan']);

  $Error_Text = '';

  if ( !$Clan_Data )
  {
    $Error_Text = "
      <div class='error' style='margin-bottom: 0px;'>
        To access this page, you must currently be in a clan.
      </div>
    ";
  }

  if ( !$User_Data['Clan_Rank'] == 'Member' )
  {
    $Error_Text = "
      <div class='error' style='margin-bottom: 0px;'>
        To access this page, you must be at least a Clan Moderator.
      </div>
    ";
  }
?>

<div class='panel content'>
  <div class='head'>Send Clan Announcement</div>
  <div class='body' style='padding: 5px;'>
    <?php
      if ( $Error_Text !== '' )
      {
        echo $Error_Text;

        return;
      }

      $_SESSION['EvoChroniclesRPG']['Direct_Message']['clan_data'] = $Clan_Data;

      /**
       * Add all members of the clan to our session array.
       */
      $Clan_Members = $Clan_Class->FetchMembers($Clan_Data['ID']);
      foreach ( $Clan_Members as $Clan_Member )
      {
        $Member = $User_Class->FetchUserData($Clan_Member['id']);

        if ( $Member )
        {
          $_SESSION['EvoChroniclesRPG']['Direct_Message']['users'][] = [
            'User_ID' => $Member['ID'],
          ];
        }
      }
    ?>

    <div class='warning' id='ajax-result'>
      The results of your sent direct message will be displayed here.
    </div>

    <div class='description'>
      Here, you may send a direct message to everyone in your clan.
      <br />
      It is advised that you do not abuse this feature to spam your fellow clanmates.
    </div>

    <table class='border-gradient'>
      <thead>
        <tr>
          <th>
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
              Send Clan Message
            </button>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</div>

<script type='text/javascript'>
  const ComposeMessage = () =>
  {
    let Message = $('#message-content').val();

    $.ajax({
      type: 'POST',
      url: '<?= DOMAIN_ROOT; ?>/core/ajax/direct_messages/compose_create.php',
      data: { Message: Message },
      success: (json) =>
      {
        $('#ajax-result').html(json.Text);
      },
      error: (json) =>
      {
        $('#ajax-result').html(json.Text);
      }
    });
  }
</script>

<?php
  require_once '../core/required/layout_bottom.php';
