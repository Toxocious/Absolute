/**
 * Get the user's currently boxed Pokemon.
 *
 * @param {int} Profile_ID - The ID of the profile to get the box for.
 * @param {int} Page - The page number to retrieve.
 */
async function GetBox(Profile_ID, Page = 1) {
    let Form_Data = new FormData();
    Form_Data.append('Action', 'Get_Box');
    Form_Data.append('User_ID', Profile_ID);
    Form_Data.append('Page', Page);

    const Box_Request = await SendRequest('profile', 'box', Form_Data);
    const Box_Data = JSON.parse(Box_Request)[0];

    const Box_Container = document.getElementById('box-container');

    if (!Box_Data || Box_Data.length === 0 || Box_Data.Pagination.Data.length === 0) {
        Box_Container.innerHTML = `
            <div style='padding: 0.5em; width: 100%; grid-column: span 3;'>
                No Pok&eacute;mon were found in this user's box.
            </div>
        `;

        return;
    }

    let Boxed_Pokemon_HTML = '';

    for (const Pokemon of Box_Data.Pagination.Data) {
        const Pokemon_Sprites = GetPokemonSprites(
            Pokemon?.Type ?? 'Normal',
            Pokemon.Pokedex_ID,
            Pokemon.Forme
        );

        Boxed_Pokemon_HTML += `
            <div class='pokemon-card compact' onclick='PokemonViewer.open(\"${Pokemon.ID}\")' style='flex-basis: calc(calc(100% / 2) - 4em);'>
                <img src='${Pokemon_Sprites.Icon}' class='pokemon-icon' alt='${Pokemon.Display_Name}' />
                <div class='pokemon-info'>
                    <h3 class='pokemon-name'>${Pokemon.Display_Name}</h3>
                    <div class='pokemon-details'>
                        <span class='pokemon-level'>Lv. ${Pokemon.Level}</span>
                    </div>
                </div>
            </div>
        `;
    }

    document.getElementById('ProfileAJAX').innerHTML = `
        <table>
            <tbody>
                ${Box_Data.Pagination.Pagination}
            </tbody>

            <tbody>
                <tr>
                    <td colspan='21'>
                        <div style='display: flex; flex-direction: row; flex-wrap: wrap; gap: 1em; justify-content: center; padding: 0.5em 0;'>
                            ${Boxed_Pokemon_HTML}
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    `;
}
