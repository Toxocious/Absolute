function LoadPage(Page_URL)
{
  return new Promise((resolve, reject) =>
  {
    const req = new XMLHttpRequest();

    req.open('GET', Page_URL);
    req.send();
    req.onerror = (error) => reject(Error(`Network Error: ${error}`));
    req.onload = () =>
    {
      if ( req.status === 200 )
      {
        document.getElementById('Staff_Content').innerHTML = req.response;
        resolve(req.response);
      }
      else
        reject(Error(req.statusText));
    };
  });
}

function SendRequest(Page, Data, HTTP_TYPE = 'GET')
{
  const AJAX_URL = `/staff/ajax/${Page}.php`;
  const URL_PARAMS = new URLSearchParams(Data).toString().replace(/\=$/, '');

  return new Promise((resolve, reject) =>
  {
    const req = new XMLHttpRequest();

    switch ( HTTP_TYPE )
    {
      case 'GET':
        req.open('GET', `${AJAX_URL}?${URL_PARAMS}`);
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

        req.open('POST', AJAX_URL);
        req.send(Data_Val);
        break;

      default:
        console.error(`Unable to process request of HTTP type '${HTTP_TYPE}'.`);
        break;
    }

    req.onerror = (error) => reject(Error(`Network Error: ${error}`));
    req.onload = () =>
    {
      if ( req.status === 200 )
      {
        setTimeout(() =>
        {
          [].forEach.call(document.getElementsByName("iFrame_Handler"), function(el)
          {
            console.log('setting iframelightbox on', el);

            el.lightbox = new IframeLightbox(el, {
              scrolling: true,
              rate: 500,
              touch: true,
            });
          });
        }, 250);

        resolve(req.response);
      }
      else
        reject(Error(req.statusText));
    };
  });
}
