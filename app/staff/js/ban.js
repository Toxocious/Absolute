function BanUser()
{
  const User_Value = document.getElementsByName('User_Value')[0].value;
  const Ban_Type = document.getElementsByName('Ban_Type')[0].value;
  const Unban_Date = document.getElementsByName('Unban_Date')[0].value;
  const Ban_Reason = document.getElementsByName('Ban_Reason')[0].value;
  const Staff_Notes = document.getElementsByName('Staff_Notes')[0].value;

  let Form_Data = new FormData();
  Form_Data.append('User_Value', User_Value);
  Form_Data.append('Ban_Type', Ban_Type);
  Form_Data.append('Unban_Date', Unban_Date);
  Form_Data.append('Ban_Reason', Ban_Reason);
  Form_Data.append('Staff_Notes', Staff_Notes);

  SendRequest('ban_user', Form_Data)
    .then((Ban_Data) => {
      Ban_Data = JSON.parse(Ban_Data);

      document.getElementById('Ban_AJAX').className = Ban_Data.Success ? 'success' : 'error';
      document.getElementById('Ban_AJAX').innerHTML = Ban_Data.Message;
    })
    .catch((Error) => console.error('Error:', Error));
}

function UnbanUser(Banned_User_ID)
{
  let Form_Data = new FormData();
  Form_Data.append('Banned_User_ID', Banned_User_ID);
  Form_Data.append('Ban_Action', 'Unban');

  SendRequest('ban_list', Form_Data)
    .then((Unban_Data) => {
      Unban_Data = JSON.parse(Unban_Data);

      document.getElementById('Ban_AJAX').className = Unban_Data.Success ? 'success' : 'error';
      document.getElementById('Ban_AJAX').innerHTML = Unban_Data.Message;

      document.getElementById('Banned_User_List').innerHTML = Unban_Data.Banned_User_List;
    })
    .catch((Error) => console.error('Error:', Error));
}
