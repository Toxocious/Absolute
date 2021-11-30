class Network
{
  constructor()
  {
    this.AJAX_URL = '/maps/ajax/handler.php';
  }

  /**
   * Send a request to the map handler.
   */
   SendRequest(Data, HTTP_TYPE = 'GET')
   {
     return new Promise((resolve, reject) =>
     {
        const req = new XMLHttpRequest();

        switch ( HTTP_TYPE )
        {
          case 'GET':
            req.open('GET', `${this.AJAX_URL}?${Data}`);
            req.send();
            break;

          case 'POST':
            const Data_Val = new FormData();
            Data_Val.append('Action', Data.Action);
            Data_Val.append('x', Data.x);
            Data_Val.append('y', Data.y);
            Data_Val.append('z', Data.z);

            req.open('POST', this.AJAX_URL);
            req.send(Data_Val);
            break;

          default:
            req.open('GET', `${this.AJAX_URL}?${Data}`);
            req.send();
            break;
        }

        req.onerror = (error) => reject(Error(`Network Error: ${error}`));
        req.onload = () =>
        {
          if ( req.status === 200 )
            resolve(req.response);
          else
            reject(Error(req.statusText));
        };
     });
   }
}
