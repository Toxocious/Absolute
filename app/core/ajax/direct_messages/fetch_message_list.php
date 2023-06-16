<?php
  require_once '../../required/session.php';

  $Direct_Message = new DirectMessage();
  $Messages = $Direct_Message->FetchMessageList();

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
  