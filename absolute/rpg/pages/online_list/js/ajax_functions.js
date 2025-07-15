/**
 * Refresh the online list's online users.
 */
async function RefreshOnlineList() {
    let Form_Data = new FormData();
    Form_Data.append('Action', 'Get_Online_Users');

    await SendRequest('online_list', 'online_list', Form_Data)
        .then((Online_Users) => {
            Online_Users = JSON.parse(Online_Users);

            document.getElementById('Online_List_Container').innerHTML = Online_Users.Online_List;
        })
        .catch((Error) => console.error('Error:', Error));
}
