<?php
  require_once '../../required/session.php';

  $Clan_Data = $Clan_Class->FetchClanData($User_Data['Clan']);

  $Error = false;

  if ( !$Clan_Data )
  {
    $Error = true;

    $Text = "
      <div>
        <b style='color: #ff0000;'>
          You must be in a clan to use this feature.
        </b>
      </div>
    ";
  }

  if ( $User_Data['Clan_Rank'] == 'Member' )
  {
    $Error = true;

    $Text = "
      <div>
        <b style='color: #ff0000;'>
          You must be at least a Clan Moderator to use this feature.
        </b>
      </div>
    ";
  }

  if ( !$Error )
  {
    if ( isset($_POST['signature']) )
    {
      $Signature = Purify($_POST['signature']);

      if ( $Signature )
      {
        if ( strlen($Signature) > 1000 )
        {
          $Text = "
            Signature may not be greater than 1000 characters in length.
          ";
        }
        else
        {
          $Update_Signature = $Clan_Class->UpdateSignature($Clan_Data['ID'], $Signature);

          if ( $Update_Signature )
          {
            $Text = "
              You have sucessfully updated your clan's signature.
            ";
          }
          else
          {
            $Text = "
              An error occurred while updating your clan's signature.
            ";
          }
        }
      }
    }
  }

  $Output = [
    'Text' => $Text,
    'Signature' => ($Signature ? $Signature : 'No signature is currently set.'),
  ];

  header('Content-Type: application/json');
  echo json_encode($Output);
