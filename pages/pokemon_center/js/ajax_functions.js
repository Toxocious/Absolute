/**
 * Load content from the specified tab.
 *
 * @param Tab_Name
 */
async function ShowTab(Tab_Name)
{
  document.getElementById('Pokemon_Center_Page').innerHTML = `
    <div style='display: flex; align-items: center; justify-content: center; padding: 10px;'>
      <div class='loading-element'></div>
    </div>
  `;

  return new Promise((resolve, reject) =>
  {
    const req = new XMLHttpRequest();

    req.open('GET', `/pages/pokemon_center/pages/${Tab_Name}.php`);
    req.send();
    req.onerror = (error) => reject(Error(`Network Error: ${error}`));
    req.onload = () =>
    {
      if ( req.status === 200 )
      {
        document.getElementById('Pokemon_Center_Page').innerHTML = req.response;

        switch ( Tab_Name )
        {
          case 'roster':
            GetRoster();
            GetBoxedPokemon();
            break;

          case 'release':
            GetReleasablePokemon();
            break;

          case 'nickname':
            GetNicknameTabRoster();
            break;

          case 'moves':
            GetMoveTabRoster();
            break;

          case 'inventory':
            ShowInventoryTab('Held Item');
            ShowEquippedItems();
            break;
        }

        [].forEach.call(document.getElementsByClassName("popup"), function(el) {
          el.lightbox = new IframeLightbox(el, {
            scrolling: false,
            rate: 500,
            touch: false,
          });
        });

        resolve(req.response);
      }
      else
      {
        reject(Error(req.statusText));
      }
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
async function SendRequest(Page, Data, HTTP_TYPE = 'GET')
{
  const AJAX_URL = `/pages/pokemon_center/ajax/${Page}.php`;
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
