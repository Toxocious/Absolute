<?php
  require_once '../../required/layout_top.php';

  $Clan_Data = $Clan_Class->FetchClanData($User_Data['Clan']);
?>

<div class='panel content'>
  <div class='head'>Manage Clan Members</div>
  <div class='body' style='padding: 5px;'>
    <?php
      if ( !$Clan_Data )
      {
        echo "
          <div class='error' style='margin-bottom: 0px;'>
            To access this page, you must currently be in a clan.
          </div>
        ";
    
        return;
      }
    ?>

    <div class='description'>
      Here, you may manage your clan members clan status here.
    </div>

    <table class='border-gradient' style='margin-bottom: 5px; width: 300px;'>
      <tbody id='SelectedUser'>
        <tr>
          <td colspan='4' style='padding: 5px;'>
            Select a member from below to view available management options.
          </td>
        </tr>
      </tbody>
    </table>

    <table class='border-gradient' style='width: 550px;'>
      <thead>
        <tr>
          <th colspan='3'>
            <b>Clan Members</b>
          </th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td colspan='1' style='width: 25%;'>
            <b>Member</b>
          </td>
          <td colspan='1' style='width: 25%;'>
            <b>Clan Title</b>
          </td>
          <td colspan='1' style='width: 25%;'>
            <b>Clan Exp.</b>
          </td>
          <td colspan='1' style='width: 25%;'>
            <b>Modify</b>
          </td>
        </tr>
      </tbody>
      <tbody id='MemberList'>
        <?php
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
        ?>
      </tbody>
    </table>
  </div>
</div>

<script type='text/javascript'>
  FetchUserData = (User_ID) =>
  {
    $.ajax({
      type: 'POST',
      url: 'display_member.php',
      data: { User_ID: User_ID },
      success: (data) =>
      {
        $('#SelectedUser').html(data);
      },
      error: (data) =>
      {
        $('#SelectedUser').html(data);
      },
    });
  }

  memberList = () =>
  {
    $.ajax({
      type: 'GET',
      url: 'member_list.php',
      data: { },
      success: (data) =>
      {
        $('#MemberList').html(data);
      },
      error: (data) =>
      {
        $('#MemberList').html(data);
      },
    });
  }

  changeTitle = (User_ID) =>
  {
    let Title = $('#title').val();

    $.ajax({
      type: 'POST',
      url: 'manage_title.php',
      data: { User_ID: User_ID, Title: Title },
      success: (data) =>
      {
        $('#SelectedUser').html(data);
        memberList();
      },
      error: (data) =>
      {
        $('#SelectedUser').html(data);
      },
    });
  }

  kickMember = (User_ID) =>
  {
    $.ajax({
      type: 'POST',
      url: 'kick_member.php',
      data: { User_ID: User_ID },
      success: (data) =>
      {
        $('#SelectedUser').html(data);
        memberList();
      },
      error: (data) =>
      {
        $('#SelectedUser').html(data);
      },
    });
  }
</script>