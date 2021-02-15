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

  $Output = [
    'Money' => $Clan_Data['Money'],
    'Abso_Coins' => $Clan_Data['Abso_Coins'],
    'Clan_Points' => $Clan_Data['Clan_Points'],
  ];

  header('Content-Type: application/json');
  echo json_encode($Output);
