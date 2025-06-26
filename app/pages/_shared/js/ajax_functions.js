/**
 * Send an ajax request and handle the response.
 *
 * @param Page
 * @param Data
 * @param HTTP_TYPE
 */
async function SendRequest(Root, Page, Data, HTTP_TYPE = 'GET') {
    const AJAX_URL = `/pages/${Root}/ajax/${Page}.php`;

    return new Promise((resolve, reject) => {
        const req = new XMLHttpRequest();

        switch (HTTP_TYPE) {
            case 'GET':
                const URL_PARAMS = new URLSearchParams(Data).toString().replace(/\=$/, '');

                req.open('GET', `${AJAX_URL}?${URL_PARAMS}`);
                req.send();
                break;

            case 'POST':
                req.open('POST', AJAX_URL);
                req.send(Data);
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
