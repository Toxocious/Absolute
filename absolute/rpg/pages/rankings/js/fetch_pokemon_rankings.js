async function UpdatePokemonRankings(Page = 1) {
    const speciesEl = document.getElementById('speciesFilter');
    const shinyEl = document.getElementById('shinyFilter');

    let species = speciesEl ? speciesEl.value : '';
    let shiny = shinyEl ? shinyEl.value : 'all';

    const params = new URLSearchParams({
        Action: 'Get_Pokemon',
        Page: Page,
        Species: species,
        Type: shiny,
    });

    console.log(`/pages/rankings/ajax/rankings.php?${params.toString()}`);

    try {
        const response = await fetch(`/pages/rankings/ajax/rankings.php?${params.toString()}`, {
            method: 'GET',
            credentials: 'same-origin',
        });
        const json = await response.json();
        const data = json[0];

        // Basic validation
        if (!data || !data.Pagination) {
            console.warn('Invalid data received.');
            return;
        }

        const pagination_element = document.getElementById('pagination-container');

        // Replace rankings table body (rank entries)
        const table = document.querySelector('#rankings-ajax table.border-gradient');
        if (!table) return;

        const tbodyList = document.querySelector('#ranked-list > tbody');
        tbodyList.innerHTML = '';

        if (data.Pagination.Data.length === 0) {
            tbodyList.innerHTML = `
                <tr><td colspan='21' style='padding: 1em;'>
                    No Pokémon match the selected filters.
                </td></tr>
            `;
        } else {
            const currentPage = Number(data.Pagination.Current_Page || 1);
            const perPage = Number(data.Pagination.Per_Page || 30);
            let placement = (currentPage - 1) * perPage + 1;

            for (const entry of data.Pagination.Data) {
                const nickname = entry.Nickname ? `<br /><i>${entry.Nickname}</i>` : '';
                const icon =
                    entry.Pokedex_ID && entry.Type
                        ? GetSpriteIcon(entry.Pokedex_ID, entry.Alt_ID, entry.Type, entry.Forme)
                        : '';

                tbodyList.innerHTML += `
                    <tr>
                        <td colspan='3'>#${placement}</td>
                        <td colspan='6' onclick='PokemonViewer.open("${entry.ID}");'>
                            <img src='${icon}' style='vertical-align: middle;' /><br />
                            <a href='javascript:void(0);'>
                                <b>${entry.Display_Name}</b>
                                ${nickname}
                            </a>
                        </td>
                        <td colspan='6'>
                            Level ${entry.Level}<br />
                            (${Number(entry.Experience).toLocaleString()} Exp.)
                        </td>
                        <td colspan='6'>
                            ${entry.Owner_Current}
                        </td>
                    </tr>
                `;
                placement++;
            }

            pagination_element.innerHTML = data.Pagination.Pagination;
        }

        console.log(data);
    } catch (e) {
        console.error('Fetch error:', e);
    }
}

// Helper to reconstruct icon path (adjust if you have a central helper in JS)
function GetSpriteIcon(Pokedex_ID, Alt_ID, Type, Forme) {
    let iconName = String(Pokedex_ID).padStart(3, '0');
    if (Forme) iconName += `-${Forme}`;
    const base = `/images/Pokemon/Icons/${Type || 'Normal'}`;
    return `${base}/${iconName}.png`;
}
