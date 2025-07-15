function DeleteReport(Report_ID)
{
  let Form_Data = new FormData();
  Form_Data.append('Report_ID', Report_ID);

  SendRequest('report', Form_Data)
    .then((Unban_Data) => {
      Unban_Data = JSON.parse(Unban_Data);

      document.getElementById('Report_AJAX').className = Unban_Data.Success ? 'success' : 'error';
      document.getElementById('Report_AJAX').innerHTML = Unban_Data.Message;

      document.getElementById('Active_Report_List').innerHTML = Unban_Data.Active_Report_List;
    })
    .catch((Error) => console.error('Error:', Error));
}
