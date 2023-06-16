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
    const URL_PARAMS = new URLSearchParams(Data).toString().replace(/\=$/, '');

    return new Promise((resolve, reject) =>
    {
      const req = new XMLHttpRequest();

      switch ( HTTP_TYPE )
      {
        case 'GET':
          req.open('GET', `${this.AJAX_URL}?${URL_PARAMS}`);
          req.send();
          break;

        case 'POST':
          const Data_Val = new FormData();
          Data_Val.append('Action', Data.Action);

          if ( typeof Data.x !== 'undefined' )
            Data_Val.append('x', Data.x);
          if ( typeof Data.y !== 'undefined' )
            Data_Val.append('y', Data.y);
          if ( typeof Data.z !== 'undefined' )
            Data_Val.append('z', Data.z);
          if ( typeof Data.Encounter_Tile !== 'undefined' )
            Data_Val.append('Encounter_Tile', Data.Encounter_Tile);
          if ( typeof Data.Warp_Tile !== 'undefined' )
            Data_Val.append('Warp_Tile', Data.Warp_Tile);

          req.open('POST', this.AJAX_URL);
          req.send(Data_Val);
          break;

        default:
          req.open('GET', `${this.AJAX_URL}?${URL_PARAMS}`);
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
