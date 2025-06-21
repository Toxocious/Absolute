<?php
  require_once '../../required/session.php';

  $Error = false;

  if ( !isset($_POST['User']) )
  {
    $Error = true;

    echo "
      <div class='error'>
        You must enter a valid user's ID or Username in order to add them to this direct message.
      </div>
    ";
  }

  $Added_User = Purify($_POST['User']);
  $Added_User_Data = $User_Class->FetchUserData($Added_User);

  if ( !$Added_User_Data )
  {
    $Error = true;

    echo "
      <div class='error'>
        You must enter a valid user's ID or Username in order to add them to this direct message.
      </div>
    ";
  }

  if ( $Added_User_Data['ID'] === $User_Data['ID'] )
  {
    $Error = true;

    echo "
      <div class='error'>
        You may not add yourself as a recipient to a direct message.
      </div>
    ";
  }

  if ( isset( $_SESSION['EvoChroniclesRPG']['Direct_Message']['users']) )
  {
    foreach ( $_SESSION['EvoChroniclesRPG']['Direct_Message']['users'] as $Key => $Value )
    {
      if ( $Value['User_ID'] == $Added_User_Data['ID'] )
      {
        $Error = true;

        echo "
          <div class='error'>
            You have already added <b>{$Added_User_Data['Username']}</b> as a recipient to this direct message.
          </div>
        ";
      }
    }
  }

  if ( !$Error )
  {
    $_SESSION['EvoChroniclesRPG']['Direct_Message']['users'][] = [
      'User_ID' => $Added_User_Data['ID'],
    ];

    echo "
      <div class='success'>
        You have successfully added <b>{$Added_User_Data['Username']}</b> as a recipient to this direct message.
      </div>
    ";
  }

  foreach ( $_SESSION['EvoChroniclesRPG']['Direct_Message']['users'] as $Key => $Included_User )
  {
    if ( $Included_User['User_ID'] == $User_Data['ID'] )
      continue;

    $Included_User_Name = $User_Class->DisplayUserName($Included_User['User_ID']);

    echo "&bull; {$Included_User_Name}<br />";
  }
