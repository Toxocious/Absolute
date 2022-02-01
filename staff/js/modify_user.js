function ShowUser()
{
  const User_Value = document.getElementsByName('Modify_User_Param')[0].value;
  if ( typeof User_Value === 'undefined' )
    return;

  let Form_Data = new FormData();
  Form_Data.append('User_Value', User_Value);
  Form_Data.append('User_Action', 'Show');

  SendRequest('modify_user', Form_Data)
    .then((User_Data) => {
      User_Data = JSON.parse(User_Data);

      document.getElementById('Modify_User_Table').innerHTML = User_Data.Modify_User_Table;
    })
    .catch((Error) => console.error('Error:', Error));
}

function UpdateUser()
{
  const User_Value = document.getElementsByName('User_ID_To_Update')[0].value;
  if ( typeof User_Value === 'undefined' )
    return;

  let Form_Data = new FormData();
  Form_Data.append('User_Value', User_Value);
  Form_Data.append('User_Action', 'Update');

  SendRequest('modify_user', Form_Data)
    .then((Update_User) => {
      Update_User = JSON.parse(Update_User);

      document.getElementById('Modify_User_AJAX').className = Update_User.Success ? 'success' : 'error';
      document.getElementById('Modify_User_AJAX').innerHTML = Update_User.Message;
    })
    .catch((Error) => console.error('Error:', Error));
}
