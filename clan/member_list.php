<?php
  require_once '../../required/session.php';

  $Clan_Data = $Clan_Class->FetchClanData($User_Data['Clan']);

  if ( !$Clan_Data )
  {
    echo "
      <tr>
        <td colspan='4'>
          <b style='color: #ff0000;'>
            You're not currently in a clan.
          </b>
        </td>
      </tr>
    ";

    return;
  }

  try
  {
    $Member_Query = $PDO->prepare("SELECT `id` FROM `users` WHERE `Clan` = ? ORDER BY `Clan_Exp` DESC");
    $Member_Query->execute([ $Clan_Data['ID'] ]);
    $Member_Query->setFetchMode(PDO::FETCH_ASSOC);
    $Members = $Member_Query->fetchAll();
  }
  catch ( PDOException $e )
  {
    HandleError($e);
  }

  foreach ( $Members as $Index => $Member )
  {
    $Member = $User_Class->FetchUserData($Member['id']);
    
    echo "
      <tr>
        <td class='" . strtolower($Member['Clan_Rank']) . "'>
          {$Member['Username']}
        </td>
        <td>
          {$Member['Clan_Title']}
        </td>
        <td>
          {$Member['Clan_Exp']}
        </td>
        <td>
          <a href='javascript:void(0);' onclick='FetchUserData({$Member['ID']});'>
            Modify Member
          </a>
        </td>
      </tr>
    ";
  }
  