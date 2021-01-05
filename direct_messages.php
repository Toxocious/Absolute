<?php
  require_once 'core/required/layout_top.php';

  $DirectMessages = new DirectMessage();
  $Messages = $DirectMessages->FetchMessageList();
?>

<div class='panel content'>
  <div class='head'>Direct Messages</div>
  <div class='body flex' style='padding: 5px;'>
    <table class='border-gradient' style='flex-basis: 200px; margin-left: 0; max-height: 660px; width: 208px;'>
      <tbody>
        <tr>
          <td colspan='2'>
            <a href='javascript:void(0);' onclick='ComposeMessage();'>
              <h3>Compose New Message</h3>
            </a>
          </td>
        </tr>
      </tbody>
      <tbody>
        <?php
          if ( !$Messages )
          {
            echo "
              <tr>
                <td colspan=2' style='padding: 10px 0px;'>
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
                <tr style='cursor: pointer;' onclick='DisplayMessage({$Msg['Message_ID']});' data-msg-id='{$Msg['Message_ID']}'>
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
      <div id='direct-messages'>
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

  const DisplayMessage = (Message_ID) =>
  {
    $.ajax({
      type: 'POST',
      url: '<?= DOMAIN_ROOT; ?>/core/ajax/direct_messages/conversation.php',
      data: { Message_ID: Message_ID },
      success: (data) =>
      {
        $('#direct-messages').html(data);
        SmoothScrollToBottom('message-container');
      },
      error: (data) =>
      {
        $('#direct-messages').html(data);
        SmoothScrollToBottom('message-container');
      },
    });
  }
</script>

<?php
  require_once 'core/required/layout_bottom.php';
