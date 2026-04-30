/**
 * Build roster HTML from data.
 */
function BuildRosterHTML(Roster) {
    let HTML = '';
    for (const Pokemon of Roster) {
        HTML += `
            <div style='width: calc(100% / 6);' onclick='DisplayPossibleEvolutions(${Pokemon.ID});'>
                <img class='spricon' src='${Pokemon.Sprite}' /><br />
                <b>${Pokemon.Display_Name}</b><br />
            </div>
        `;
    }
    return HTML;
}

/**
 * Fetch and display the user's roster.
 */
async function UpdateRoster() {
    let Form_Data = new FormData();
    Form_Data.append('Action', 'Display_Pokemon');

    await SendRequest('evolution_center', 'evolution_center', Form_Data)
        .then((Roster) => {
            Roster = JSON.parse(Roster);

            document.getElementById('Evolution_Page_Roster').innerHTML = BuildRosterHTML(
                Roster.Roster_Pokemon
            );
        })
        .catch((Error) => console.error('Error:', Error));
}

/**
 * Fetch and display all possible evolutions for the selected Pokemon.
 */
async function DisplayPossibleEvolutions(Pokemon_ID) {
    let Form_Data = new FormData();
    Form_Data.append('Action', 'Display_Evolutions');
    Form_Data.append('Pokemon_ID', Pokemon_ID);

    await SendRequest('evolution_center', 'evolution_center', Form_Data)
        .then((Response) => {
            const Data = JSON.parse(Response).Evolution_Data;

            if (!Data.Success) {
                document.getElementById('Evo_Data').innerHTML = `
                    <thead><tr><th colspan='7'>Selected Pok&eacute;mon</th></tr></thead>
                    <tbody><tr><td colspan='7' style='padding: 5px;'>${Data.Message}</td></tr></tbody>
                `;
                return;
            }

            const Pokemon = Data.Pokemon;
            let Evo_Rows = '';

            if (Data.Evolutions.length === 0) {
                Evo_Rows = `<tr><td colspan='7' style='padding: 5px;'>This Pok&eacute;mon may not evolve further.</td></tr>`;
            } else {
                for (const Evo of Data.Evolutions) {
                    const Req = Evo.Requirements;
                    const Button = Evo.Can_Evolve
                        ? `<button onclick='EvolvePokemon(${Pokemon.ID}, ${Evo.Pokedex_ID}, ${Evo.Alt_ID});'>Evolve into ${Evo.Display_Name}</button>`
                        : `<button class='disabled'>Requirements Not Met</button>`;

                    Evo_Rows += `
                        <tr>
                            <td style='width: 150px;'><img src='${Evo.Icon}' /></td>
                            <td style='width: 100px;'><b>Level</b></td>
                            <td style='width: 100px;'><b>Gender</b></td>
                            <td style='width: 100px;'><b>Held Item</b></td>
                            <td style='width: 100px;'><b>Use Item</b></td>
                            <td style='width: 100px;'><b>Time of Day</b></td>
                            <td style='width: 100px;'><b>Happiness</b></td>
                        </tr>
                        <tr>
                            <td><b>${Evo.Display_Name}</b></td>
                            <td>${Req.Level ?? 'N/A'}</td>
                            <td>${Req.Gender ?? 'N/A'}</td>
                            <td>${Req.Held_Item ?? 'N/A'}</td>
                            <td>${Req.Use_Item ?? 'N/A'}</td>
                            <td>${Req.Time ?? 'N/A'}</td>
                            <td>${Req.Happiness ?? 'N/A'}</td>
                        </tr>
                        <tr><td colspan='7'>${Button}</td></tr>
                    `;
                }
            }

            document.getElementById('Evo_Data').innerHTML = `
                <thead><tr><th colspan='7'>Selected Pok&eacute;mon</th></tr></thead>
                <tbody>
                    <tr>
                        <td colspan='1' style='width: 150px;'><img src='${Pokemon.Icon}' /></td>
                        <td colspan='1' style='width: 100px;'><b>Level</b></td>
                        <td colspan='1' style='width: 100px;'><b>Gender</b></td>
                        <td colspan='1' style='width: 100px;'><b>Held Item</b></td>
                        <td colspan='1' style='width: 100px;'><b>Use Item</b></td>
                        <td colspan='1' style='width: 100px;'><b>Time of Day</b></td>
                        <td colspan='1' style='width: 100px;'><b>Happiness</b></td>
                    </tr>
                    <tr>
                        <td><b>${Pokemon.Display_Name}</b></td>
                        <td>${Pokemon.Level}</td>
                        <td>${Pokemon.Gender}</td>
                        <td>${Pokemon.Item ?? 'No Item'}</td>
                        <td>N/A</td>
                        <td>${Data.Time_Of_Day}</td>
                        <td>${Pokemon.Happiness}</td>
                    </tr>
                </tbody>
                <thead><tr><th colspan='7'>Available Evolutions</th></tr></thead>
                <tbody>${Evo_Rows}</tbody>
            `;
        })
        .catch((Error) => console.error('Error:', Error));
}

/**
 * Evolve into the selected Pokemon.
 */
async function EvolvePokemon(Pokemon_ID, Evolution_ID, Evolution_Alt_ID) {
    let Form_Data = new FormData();
    Form_Data.append('Action', 'Handle_Evolution');
    Form_Data.append('Pokemon_ID', Pokemon_ID);
    Form_Data.append('Evolution_ID', Evolution_ID);
    Form_Data.append('Evolution_Alt_ID', Evolution_Alt_ID);

    await SendRequest('evolution_center', 'evolution_center', Form_Data)
        .then((Response) => {
            const Data = JSON.parse(Response).Evolution_Status;
            const Status_Class = Data.Success ? 'success' : 'error';

            let Content = `<b>${Data.Message}</b>`;
            if (Data.Success && Data.Evolution_Sprite) {
                Content = `<img src='${Data.Evolution_Sprite}' /><br />${Content}`;
            }

            document.getElementById('Evo_Data').innerHTML = `
                <thead><tr><th colspan='7'>Evolutions</th></tr></thead>
                <tbody>
                    <tr>
                        <td colspan='7'>
                            <div class='${Status_Class}' style='margin: 0 auto;'>${Content}</div>
                        </td>
                    </tr>
                    <tr>
                        <td colspan='7' style='padding: 5px;'>Please select the Pok&eacute;mon that you wish to evolve.</td>
                    </tr>
                </tbody>
            `;

            UpdateRoster();
        })
        .catch((Error) => console.error('Error:', Error));
}
