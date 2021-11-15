class Network
{
  constructor()
  {
    this.AJAX_URL = '/maps/ajax/handler.php';

    this.Network_Position = {
      Request_ID: 0,
      x: 0,
      y: 0,
      z: 0,
    };

    this.Processing = false;
    this.Request_Buffer = [];
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
            req.open('POST', this.AJAX_URL);
            req.send(Data);
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
