/**
 * Get the user's currently rostered Pokemon.
 *
 * @param {int} Profile_ID - The ID of the profile to get the roster for.
 */
async function GetRoster(Profile_ID) {
    let Form_Data = new FormData();
    Form_Data.append('Action', 'Get_Roster');
    Form_Data.append('User_ID', Profile_ID);

    const Roster_Request = await SendRequest('profile', 'roster', Form_Data);
    const Roster_Data = JSON.parse(Roster_Request)[0];

    const Roster_Container = document.getElementById('team-container');

    if (!Roster_Data || Roster_Data.length === 0) {
        Roster_Container.innerHTML = `
            <div style='padding: 0.5em; width: 100%; grid-column: span 3;'>
                No Pok&eacute;mon were found in this user's roster.
            </div>
        `;

        return;
    }

    Roster_Container.innerHTML = Roster_Data;
}
