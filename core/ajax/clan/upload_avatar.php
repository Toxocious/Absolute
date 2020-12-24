<?php
  require_once '../../required/session.php';

  $Clan_Data = $Clan_Class->FetchClanData($User_Data['Clan']);

  if ( !$Clan_Data )
  {
    $Text = "
      <div>
        <b style='color: #ff0000;'>
          You must be in a clan to use this feature.
        </b>
      </div>
    ";

    return;
  }

  if ( $User_Data['Clan_Rank'] == 'Member' )
  {
    $Text = "
      <div>
        <b style='color: #ff0000;'>
          You must be at least a Clan Moderator to use this feature.
        </b>
      </div>
    ";

    return;
  }

  if ( isset($_FILES['avatar']) )
  {
    if ( file_exists($_FILES['avatar']['tmp_name']) && is_uploaded_file($_FILES['avatar']['tmp_name']) )
    {
      $Avatar = Purify($_FILES['avatar']);

      if ( $Avatar )
      {
        $Avatar_Metadata = getimagesize($Avatar["tmp_name"]);

        $Errors = null;

        if ( $Avatar_Metadata[0] > 200 || $Avatar_Metadata[1] > 200 )
        {
          $Errors .= "
            <div>
              <b style='color: #ff0000;'>
                Your sprite exceeds the allowed size dimensions.
              </b>
            </div>
          ";
        }

        if ( !in_array($Avatar['type'], ['image/png', 'image/jpeg']) )
        {
          $Errors .= "
            <div>
              <b style='color: #ff0000;'>
                You must submit either a file that has the .png or .jpg extension.
              </b>
            </div>
          ";
        }

        if ( $Avatar['size'] > 1024000 )
        {
          $Errors .= "
            <div>
              <b style='color: #ff0000;'>
                Submitted avatars must be less than 1MB in size.
              </b>
            </div>
          ";
        }

        if ( $Errors )
        {
          $Text = $Errors;
        }
        else
        {
          $New_Filepath = '/Avatars/Clan/' . $Clan_Data['ID'] . '.png';
  
          try
          {
            $Update_Avatar = $PDO->prepare("UPDATE `clans` SET `Avatar` = ? WHERE `ID` = ? LIMIT 1");
            $Update_Avatar->execute([ $New_Filepath, $Clan_Data['ID'] ]);
          }
          catch ( PDOException $e )
          {
            HandleError($e);
          }
  
          move_uploaded_file(
            $Avatar['tmp_name'],
            dirname(__FILE__, 4) . '/images' . $New_Filepath
          );
  
          $Text = "
            <div>
              <b style='color: #00ff00;'>
                The avatar that you have submitted has been uploaded!
              </b>
            </div>
          ";
        }
      }
    }
  }
  else
  {
    $Text = "
      <div>
        <b style='color: #ff0000;'>
          You must upload an image for it to be processed.
        </b>
      </div>
    ";
  }

  $Output = [
    'Text' => $Text,
    'Avatar' => ( isset($New_Filepath) ? DOMAIN_SPRITES . $New_Filepath : $Clan_Data['Avatar'] ),
  ];

  header('Content-Type: application/json');
  echo json_encode($Output);
