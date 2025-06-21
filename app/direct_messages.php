<?php
  require_once 'core/required/layout_top.php';

  $Direct_Message = new DirectMessage();
  $Messages = $Direct_Message->FetchMessageList();
?>

<div class='panel content'>
  <div class='head'>Direct Messages</div>
  <div class='body flex' style='padding: 5px;'>
    <table class='border-gradient' style='margin-left: 0; height: 120px; width: 208px;'>
      <tbody>
        <tr>
          <td colspan='2'>
            <a href='javascript:void(0);' onclick='DisplayComposeMessage();'>
              <h3>Compose New Message</h3>
            </a>
          </td>
        </tr>
      </tbody>
      <tbody id='direct-message-list'>
        <?php
          if ( !$Messages )
          {
            echo "
              <tr>
                <td colspan='2' style='padding: 10px 0px;'>
                  You have not participated in any Direct Messages.
                </td>
              </tr>
            ";
          }
          else
          {
            foreach ( $Messages as $Msg_Key => $Msg )
            {
              echo "
                <tr style='cursor: pointer;' onclick='DisplayDirectMessage({$Msg['Group_ID']});' data-msg-id='{$Msg['Group_ID']}'>
                  <td colspan='1' style='height: 50px; width: 50px;'>
                    <img
                      src='" . DOMAIN_SPRITES . "/Assets/pokeball.png'
                      style='height: 50px; width: 50px;'
                    />
                  </td>
                  <td colspan='1'>
                    {$Msg['Group_Name']}
                  </td>
                </tr>
              ";
            }
          }
        ?>
      </tbody>
    </table>

    <div class='border-gradient' style='flex-basis: 660px; height: 660px; width: 660px;'>
      <div id='direct-messages' style='display: inline-block;'>
        <div class='flex' style='align-items: center; height: -webkit-fill-available; justify-content: center;'>
          <div>
            <h2>Message History</h2>
            Select a message in order to view it's history.
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script type='text/javascript'>
  const SmoothScrollToBottom = (Element_ID) =>
  {
    const Element = document.getElementById(Element_ID);

    $('#' + Element_ID).animate({
        scrollTop: Element.scrollHeight - Element.clientHeight
    }, 300);
  }

  const FetchDirectMessages = () =>
  {
    $.ajax({
      type: 'GET',
      url: '<?= DOMAIN_ROOT; ?>/core/ajax/direct_messages/fetch_message_list.php',
      success: (data) =>
      {
        $('#direct-message-list').html(data);
      },
      error: (data) =>
      {
        $('#direct-message-list').html(data);
      }
    });
  }

  const DisplayDirectMessage = (Group_ID) =>
  {
    $.ajax({
      type: 'GET',
      url: '<?= DOMAIN_ROOT; ?>/core/ajax/direct_messages/conversation.php',
      data: { Group_ID: Group_ID },
      success: (data) =>
      {
        $('#direct-messages').html(data);
        $('#dm-message').focus();
        SmoothScrollToBottom('message-container');

        $('#dm-message').keydown(e =>
        {
          if ( e.keyCode == 13 )
            AddMessage();
        });
      },
      error: (data) =>
      {
        $('#direct-messages').html(data);
        $('#dm-message').focus();
        SmoothScrollToBottom('message-container');

        $('#dm-message').keydown(e =>
        {
          if ( e.keyCode == 13 )
            AddMessage();
        });
      },
    });
  }

  const DisplayComposeMessage = () =>
  {
    $.ajax({
      type: 'GET',
      url: '<?= DOMAIN_ROOT; ?>/core/ajax/direct_messages/compose_form.php',
      success: (data) =>
      {
        $('#direct-messages').html(data);
      },
      error: (data) =>
      {
        $('#direct-messages').html(data);
      },
    });
  }

  const AddUser = (e) =>
  {
    let User = $('#select-user').val();

    $.ajax({
      type: 'POST',
      url: '<?= DOMAIN_ROOT; ?>/core/ajax/direct_messages/compose_add.php',
      data: { User: User },
      success: (data) =>
      {
        $('#added-users').html(data);
        $('#select-user').val('');
      },
      error: (data) =>
      {
        $('#added-users').html(data);
        $('#select-user').val('');
      },
    });
  }

  const AddMessage = () =>
  {
    let Group_ID = $('#dm-message').attr('data-group-id');
    let Message = $('#dm-message').val();

    $.ajax({
      type: 'POST',
      url: '<?= DOMAIN_ROOT; ?>/core/ajax/direct_messages/add_message.php',
      data: { Group_ID: Group_ID, Message: Message },
      success: (data) =>
      {
        if ( data.indexOf('error') > -1)
          alert("There was an error while attempting to send your message.\n", data);

        $('#dm-message').val('');
        $('#dm-message').focus();
        DisplayDirectMessage(Group_ID);
      },
      error: (data) =>
      {
        if ( data.indexOf('error') > -1)
          alert("There was an error while attempting to send your message.\n", data);

        $('#dm-message').val('');
        $('#dm-message').focus();
        DisplayDirectMessage(Group_ID);
      },
    });
  }

  const ComposeMessage = () =>
  {
    let Title = $('#group-title').val();
    let Message = $('#message-content').val();

    $.ajax({
      type: 'POST',
      url: '<?= DOMAIN_ROOT; ?>/core/ajax/direct_messages/compose_create.php',
      data: { Title: Title, Message: Message },
      success: (json) =>
      {
        DisplayDirectMessage(json.Group_ID);
        FetchDirectMessages();
      },
      error: (json) =>
      {
        DisplayDirectMessage(json.Group_ID);
        FetchDirectMessages();
      }
    });
  }

  <?php
    if ( !empty($_GET['Message_Recipient']) )
    {
      $Recipient_ID = Purify($_GET['Message_Recipient']);
      $_SESSION['EvoChroniclesRPG']['Direct_Message']['Message_Recipient'] = $Recipient_ID;

      echo 'DisplayComposeMessage();';
    }
  ?>
</script>

<?php
  require_once 'core/required/layout_bottom.php';
