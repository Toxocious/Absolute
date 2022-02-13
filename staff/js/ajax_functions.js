/**
 * Load a given page through an ajax request.
 *
 * @param Page_URL
 */
function LoadPage(Page_URL)
{
  document.getElementById('Staff_Content').innerHTML = `
    <div style='display: flex; align-items: center; justify-content: center; padding: 10px;'>
      <div class='loading-element'></div>
    </div>
  `;

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

/**
 * Send an ajax request and handle the response.
 *
 * @param Page
 * @param Data
 * @param HTTP_TYPE
 */
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

      default:
        console.error(`Unable to process request of HTTP type '${HTTP_TYPE}'.`);
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
