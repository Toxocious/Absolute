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

    for (let Slot = 0; Slot < Roster_Data.length; Slot++) {
        const Pokemon_Slot = Roster_Data[Slot];

        const Pokemon_Sprites = GetPokemonSprites(
            Pokemon_Slot?.Type ?? 'Normal',
            Pokemon_Slot.Pokedex_ID,
            Pokemon_Slot.Forme
        );

        let Gender_Icon = '';
        if (Pokemon_Slot.Gender == 'Female' || Pokemon_Slot.Gender == 'Male') {
            Gender_Icon = `<img src='/images/Assets/${Pokemon_Slot.Gender.toLowerCase()}.svg' class="gender-icon" alt='${
                Pokemon_Slot.Gender
            }' />`;
        }

        Roster_HTML += `
            <div class='pokemon-card' onclick='PokemonViewer.open(\"${Pokemon_Slot.ID}\")'>
                <img src='${Pokemon_Sprites.Sprite}' class='pokemon-sprite' alt='${Pokemon_Slot.Display_Name}' />
                <div class='pokemon-info'>
                    <h3 class='pokemon-name'>${Pokemon_Slot.Display_Name}</h3>
                    <div class='pokemon-details'>
                        ${Gender_Icon}
                        <span class='pokemon-level'>Lv. ${Pokemon_Slot.Level}</span>
                    </div>
                </div>
            </div>
        `;
    }

    Roster_Container.innerHTML = Roster_HTML;
}
