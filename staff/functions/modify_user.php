<?php
  /**
   * Display a table that allows modification of a given user.
   *
   * @param $User_Value
   */
  function ShowModifyUserTable
  (
    $User_Value
  )
  {
    global $User_Class, $User_Data;

    $User_Info = $User_Class->FetchUserData($User_Value);

    $Admin_Modification_Options = '';
    if ( CheckUserPermission('Staff Management') )
    {
      $Admin_Modification_Options = "
        <tbody>
          <tr>
            <td colspan='2' style='100%;'>
              <h3>Change Password</h3>
            </td>
          </tr>
        </tbody>

        <tbody>
          <tr>
            <td colspan='2'>
              <input type='password' name='New_User_Password' />
              <br />
              <i>Enter the new password</i>
            </td>
          </tr>
        </tbody>
      ";
    }

    $Preset_Avatars = glob($_SERVER['DOCUMENT_ROOT'] . "/images/Avatars/Sprites/*.png");
    $Avatar_Options = '';
    foreach ( $Preset_Avatars as $Avatar_ID => $Avatar )
    {
      $Avatar_ID++;
      $Avatar_Options .= "<option value='{$Avatar_ID}'>Avatar #{$Avatar_ID}</option>";
    }

    return "
      <input type='hidden' name='User_ID_To_Update' value='{$User_Info['ID']}' />
      <table class='border-gradient' style='width: 600px;'>
        <thead>
          <tr>
            <th colspan='2'>
              Modifying {$User_Info['Username']}
            </th>
          </tr>
        </thead>

        <tbody>
          <tr>
            <td colspan='2' style='100%;'>
              <h3>Change Avatar</h3>
            </td>
          </tr>
        </tbody>
        <tbody>
          <tr>
            <td colspan='1' style='width: 50%;'>
              <b>Current Avatar</b>
              <br />
              <img src='{$User_Info['Avatar']}' />
            </td>
            <td colspan='1' style='width: 50%;'>
              <b>New Avatar</b>
              <br />
              <select name='New_User_Avatar'>
                {$Avatar_Options}
              </select>
            </td>
          </tr>
        </tbody>

        {$Admin_Modification_Options}
      </table>

      <br />

      <button onclick='UpdateUser();'>
        Update User
      </button>
    ";
  }

  /**
   * Process updating a user.
   *
   * @param $User_Value
   * @param $New_User_Avatar
   * @param $New_User_Password
   */
  function UpdateUser
  (
    $User_Value,
    $New_User_Avatar = null,
    $New_User_Password = null
  )
  {
    if ( empty($New_User_Avatar) && empty($New_User_Password) )
    {
      return [
        'Success' => false,
        'Message' => 'You did not modify any of the user\'s information.'
      ];
    }

    if ( !empty($New_User_Avatar) )
    {
      $Avatar_Source = "/Avatars/Sprites/{$New_User_Avatar}.png";
      UpdateAvatar($User_Value, $Avatar_Source);
    }

    if ( !empty($New_User_Password) && CheckUserPermission('Staff Management') )
    {
      UpdatePassword($User_Value, $New_User_Password);
    }

    return [
      'Success' => true,
      'Message' => 'You have modified this user\'s information.',
      'New_Table_HTML' => ShowModifyUserTable($User_Value)
    ];
  }

  /**
   * Update the user's avatar.
   *
   * @param $User_Value
   * @param $Avatar_Source
   */
  function UpdateAvatar
  (
    $User_Value,
    $Avatar_Source
  )
  {
    global $PDO;

    try
    {
      $PDO->beginTransaction();

      $Update_Avatar = $PDO->prepare("
        UPDATE `users`
        SET `Avatar` = ?
        WHERE `ID` = ? OR `Username` = ?
        LIMIT 1
      ");
      $Update_Avatar->execute([
        $Avatar_Source,
        $User_Value,
        $User_Value
      ]);

      $PDO->commit();
    }
    catch ( PDOException $e )
    {
      $PDO->rollBack();

      HandleError($e);
    }
  }

  /**
   * Update the user's password.
   *
   * @param $User_Value
   * @param $New_Password
   */
  function UpdatePassword
  (
    $User_Value,
    $New_Password
  )
  {
    global $PDO;

    $New_Password_Hash = password_hash($New_Password, PASSWORD_DEFAULT);

    try
    {
      $PDO->beginTransaction();

      $Create_User_Password = $PDO->prepare("
        UPDATE `user_passwords`
        SET `Password` = ?
        WHERE `ID` = ? OR `Username` = ?
        LIMIT 1
      ");
      $Create_User_Password->execute([
        $New_Password_Hash,
        $User_Value,
        $User_Value
      ]);

      $PDO->commit();
    }
    catch ( PDOException $e )
    {
      $PDO->rollBack();

      HandleError($e);
    }
  }
