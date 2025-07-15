function ShowLogs(Form_Instance)
{
  let Form_Data = new FormData();
  Form_Data.append('Log_User', document.getElementsByName('log_user')[0].value);
  Form_Data.append('Log_Limit', document.getElementsByName('log_limit')[0].value);
  Form_Data.append('Log_Type', document.getElementsByName('log_type')[0].value);

  SendRequest('log_system', Form_Data)
    .then((Log_Data) => {
      document.getElementById('Log_AJAX').innerHTML = Log_Data;
    })
    .catch((Error) => console.log('Error:', Error));
}
