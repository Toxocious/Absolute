/**
 * Fetch all pages where maintenance may be toggled.
 *
 * @param Page_ID
 */
function TogglePageMaintenance(Page_ID)
{
  if ( typeof Page_ID === 'undefined' )
    return;

  let Form_Data = new FormData();
  Form_Data.append('Page_ID', Page_ID);
  Form_Data.append('Page_Action', 'Toggle');

  SendRequest('maintenance', Form_Data)
    .then((Maintenance_Data) => {
      Maintenance_Data = JSON.parse(Maintenance_Data);

      document.getElementById('Maintenance_AJAX').className = Maintenance_Data.Success ? 'success' : 'error';
      document.getElementById('Maintenance_AJAX').innerHTML = Maintenance_Data.Message;

      document.getElementById('Maintenance_Table').innerHTML = Maintenance_Data.Maintenance_Table;
    })
    .catch((Error) => console.error('Error:', Error));
}
