/**
 * Load content from the specified tab.
 *
 * @param Tab_Name
 */
async function RefreshOnlineList() {
    let Form_Data = new FormData();
    Form_Data.append('Action', 'Get_Online_Users');

    await SendRequest('online_list', Form_Data)
        .then((Online_Users) => {
            Online_Users = JSON.parse(Online_Users);
            console.log(Online_Users);

            document.getElementById('Online_List_Container').innerHTML = Online_Users.Online_List;
        })
        .catch((Error) => console.error('Error:', Error));
}

/**
 * Send an ajax request and handle the response.
 *
 * @param Page
 * @param Data
 * @param HTTP_TYPE
 */
async function SendRequest(Page, Data, HTTP_TYPE = 'GET') {
    const AJAX_URL = `/pages/online_list/ajax/${Page}.php`;
    const URL_PARAMS = new URLSearchParams(Data).toString().replace(/\=$/, '');

    return new Promise((resolve, reject) => {
        const req = new XMLHttpRequest();

        switch (HTTP_TYPE) {
            case 'GET':
                req.open('GET', `${AJAX_URL}?${URL_PARAMS}`);
                req.send();
                break;

            default:
                console.error(`Unable to process request of HTTP type '${HTTP_TYPE}'.`);
                break;
        }

        req.onerror = (error) => reject(Error(`Network Error: ${error}`));
        req.onload = () => {
            if (req.status === 200) resolve(req.response);
            else reject(Error(req.statusText));
        };
    });
}
