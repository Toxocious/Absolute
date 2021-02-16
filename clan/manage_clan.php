<?php
  require_once '../core/required/layout_top.php';

  $Clan_Data = $Clan_Class->FetchClanData($User_Data['Clan']);
?>

<div class='panel content'>
  <div class='head'>Manage Clan</div>
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

      if ( !$User_Data['Clan_Rank'] == 'Member' )
      {
        echo "
          <div class='error' style='margin-bottom: 0px;'>
            To access this page, you must be at least a Clan Moderator.
          </div>
        ";
    
        return;
      }
    ?>

    <div class='description'>
      Spice up your clan by uploading a custom avatar, and by creating an epic signature.
    </div>

    <div class='warning' id='ajaxResult'>
      The results of your avatar upload or signature update will be displayed here.
    </div>

    <div class='flex'>
      <table class='border-gradient' style='margin-top: 5px; flex-basis: 46%;'>
        <thead>
          <tr>
            <th colspan='2'>
              <b>Update Avatar</b>
            </th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td colspan='2'>
              <h3>Current Avatar</h3>
            </td>
          </tr>
          <tr>
            <td colspan='2' style='height: 150px;'>
              <img src='<?= $Clan_Data['Avatar']; ?>' id='clanAvatar' />
            </td>
          </tr>
          <tr>
            <td colspan='2'>
              <h3>Upload a new avatar</h3>
            </td>
          </tr>
          <tr>
            <td colspan='2'>
              <i>Max image dimensions of 200x200.</i>
              <br />
              <i>Max image size of 1MB.</i>
              <br /><br />
              <form id='avatarUploadForm'>
                <input type='file' name='avatar' id='avatar' />
                <br />
                <input type='submit' value='Upload Avatar' style='margin-top: 5px;' />
              </form>
            </td>
          </tr>
        </tbody>
      </table>

      <table class='border-gradient' style='margin-top: 5px; flex-basis: 46%;'>
        <thead>
          <tr>
            <th colspan='2'>
              <b>Update Signature</b>
            </th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td colspan='2'>
              <h3>Current Signature</h3>
            </td>
          </tr>
          <tr>
            <td colspan='2' style='height: 150px;' id='clanSignature'>
              <?= $Clan_Data['Signature']; ?>
            </td>
          </tr>
          <tr>
            <td colspan='2'>
              <h3>Update Signature</h3>
            </td>
          </tr>
          <tr>
            <td colspan='2'>
              <form id='updateSignatureForm'>
                <textarea name='signature' rows='6' cols='50'></textarea>
                <br />
                <input type='submit' value='Update Signature' />
              </form>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</div>

<script type='text/javascript'>
  $('#avatarUploadForm').submit(e =>
  {
    e.preventDefault();

    let Avatar_Data = new FormData(document.getElementById("avatarUploadForm"));

    $.ajax({
      type: 'POST',
      url: 'upload_avatar.php',
      enctype: 'multipart/form-data',
      data: Avatar_Data,
      cache: false,
      contentType: false,
      processData: false,
      success: (json) =>
      {
        $('#ajaxResult').html(json.Text);
        $('#clanAvatar').attr('src', json.Avatar + '?' + Date.now());
      },
      error: (json) =>
      {
        $('#ajaxResult').html(json.Text);
        $('#clanAvatar').attr('src', json.Avatar + '?' + Date.now());
      }
    });
  });

  $('#updateSignatureForm').submit(e =>
  {
    e.preventDefault();

    let Signature_Data = new FormData(document.getElementById("updateSignatureForm"));

    $.ajax({
      type: 'POST',
      url: 'update_signature.php',
      enctype: 'multipart/form-data',
      data: Signature_Data,
      cache: false,
      contentType: false,
      processData: false,
      success: (json) =>
      {
        $('#ajaxResult').html(json.Text);
        $('#clanSignature').html(json.Signature);
      },
      error: (json) =>
      {
        $('#ajaxResult').html(json.Text);
        $('#clanSignature').html(json.Signature);
      }
    });
  });
</script>

<?php
  require_once '../core/required/layout_bottom.php';
