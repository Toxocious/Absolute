/**
 * Add the specified user as a staff member.
 *
 * @param User_ID
 */
function ShowSelectedUsersPermissions(User_ID)
{
  let Form_Data = new FormData();
  Form_Data.append('Action', 'Show_User_Perms');
  Form_Data.append('User_ID', User_ID);

  SendRequest('manage_staff', Form_Data)
    .then((Manage_Staff) => {
      const Manage_Staff_Data = JSON.parse(Manage_Staff);

      console.log(Manage_Staff_Data);
      if ( Manage_Staff_Data.Message )
      {
        document.getElementById('Manage_Staff_AJAX').className = Manage_Staff_Data.Success ? 'success' : 'error';
        document.getElementById('Manage_Staff_AJAX').innerHTML = Manage_Staff_Data.Message;
      }

      if ( typeof Manage_Staff_Data.User_Perm_Table !== 'undefined' )
        document.getElementById('Manage_Staff_Table').innerHTML = Manage_Staff_Data.User_Perm_Table;
    })
    .catch((Error) => console.error('[ECRPG] An error occurred while displaying this user\'s staff permissions:', Error));
}

/**
 * Update the specified user's staff permissions.
 *
 * @param User_ID
 */
function UpdateUserStaffPerms(User_ID)
{
  let Form_Data = new FormData();
  Form_Data.append('Action', 'Update_User_Perms');
  Form_Data.append('User_ID', User_ID);

  const Staff_Perm = document.getElementsByName('Staff_Permission')[0].checked;
  const Graphics_Perm = document.getElementsByName('Graphics_Permission')[0].checked;
  const Logs_Perm = document.getElementsByName('Logs_Permission')[0].checked;
  const Reports_Perm = document.getElementsByName('Reports_Permission')[0].checked;
  const Bans_Perm = document.getElementsByName('Bans_Permission')[0].checked;
  const User_Management_Perm = document.getElementsByName('User_Management_Permission')[0].checked;
  const Pokemon_Management_Perm = document.getElementsByName('Pokemon_Management_Permission')[0].checked;
  const Transer_Pokemon_Perm = document.getElementsByName('Transfer_Pokemon_Permission')[0].checked;
  const Maintenance_Perm = document.getElementsByName('Maintenance_Permission')[0].checked;
  const Set_Obtainables_Perm = document.getElementsByName('Set_Obtainables_Permission')[0].checked;
  const Database_Perm = document.getElementsByName('Database_Edits_Permission')[0].checked;
  const Spawn_Perm = document.getElementsByName('Spawn_Permission')[0].checked;
  const Staff_Management_Perm = document.getElementsByName('Staff_Management_Permission')[0].checked;

  Form_Data.append('Staff_Perm',Staff_Perm);
  Form_Data.append('Graphics_Perm', Graphics_Perm);
  Form_Data.append('Logs_Perm', Logs_Perm);
  Form_Data.append('Reports_Perm', Reports_Perm);
  Form_Data.append('Bans_Perm', Bans_Perm);
  Form_Data.append('User_Management_Perm', User_Management_Perm);
  Form_Data.append('Pokemon_Management_Perm', Pokemon_Management_Perm);
  Form_Data.append('Transer_Pokemon_Perm', Transer_Pokemon_Perm);
  Form_Data.append('Maintenance_Perm', Maintenance_Perm);
  Form_Data.append('Set_Obtainables_Perm', Set_Obtainables_Perm);
  Form_Data.append('Database_Perm', Database_Perm);
  Form_Data.append('Spawn_Perm', Spawn_Perm);
  Form_Data.append('Staff_Management_Perm', Staff_Management_Perm);

  SendRequest('manage_staff', Form_Data)
    .then((Manage_Staff) => {
      const Manage_Staff_Data = JSON.parse(Manage_Staff);

      if ( Manage_Staff_Data.Message )
      {
        document.getElementById('Manage_Staff_AJAX').className = Manage_Staff_Data.Success ? 'success' : 'error';
        document.getElementById('Manage_Staff_AJAX').innerHTML = Manage_Staff_Data.Message;
      }

      if ( typeof Manage_Staff_Data.User_Perm_Table !== 'undefined' )
        document.getElementById('Manage_Staff_Table').innerHTML = Manage_Staff_Data.User_Perm_Table;
    })
    .catch((Error) => console.error('[ECRPG] An error occurred while updating this user\'s staff permissions:', Error));
}
