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

    let Roster_HTML = '';
    for (const Pokemon of Roster_Data) {
        let Gender_Icon = '';
        if (Pokemon.Gender === 'Male' || Pokemon.Gender === 'Female') {
            Gender_Icon = `<img src='${Pokemon.Gender_Icon}' class='gender-icon' alt='${Pokemon.Gender}' />`;
        }

        Roster_HTML += `
            <div class='pokemon-card' onclick='PokemonViewer.open("${Pokemon.ID}")'>
                <img src='${Pokemon.Sprite}' class='pokemon-sprite' alt='${Pokemon.Display_Name}' />
                <div class='pokemon-info'>
                    <h3 class='pokemon-name'>${Pokemon.Display_Name}</h3>
                    <div class='pokemon-details'>
                        ${Gender_Icon}
                        <span class='pokemon-level'>Lv. ${Pokemon.Level}</span>
                    </div>
                </div>
            </div>
        `;
    }

    Roster_Container.innerHTML = Roster_HTML;
}
