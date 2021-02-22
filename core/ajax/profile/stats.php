<?php
  require_once '../../required/session.php';

  if ( isset($_GET['User_ID']) )
  {
    $User_ID = Purify($_GET['User_ID']);    
    $User_Info = $User_Class->FetchUserData($User_ID);

    if ( $User_Info )
    {
      echo "
        <tr>
          <td colspan='2'>
            <b>Trainer Level</b>
          </td>
          <td colspan='2'>
            {$User_Info['Trainer_Level']}
            <br />
            (<i>{$User_Info['Trainer_Exp']} Exp.</i>)
          </td>
        </tr>
      ";
    }
    else
    {
      echo "
        <tbody>
          <tr>
            <td style='padding: 5px;'>
              An invalid user has been selected.
            </td>
          </tr>
        </tbody>
      ";
    }
	}
  else
  {
		echo "
      <tbody>
        <tr>
          <td style='padding: 5px;'>
            An invalid user has been selected.
          </td>
        </tr>
      </tbody>
    ";
  }
