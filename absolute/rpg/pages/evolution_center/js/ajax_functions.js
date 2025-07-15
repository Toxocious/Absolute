/**
 * Fetch and display the user's roster.
 */
async function UpdateRoster() {
    let Form_Data = new FormData();
    Form_Data.append('Action', 'Display_Pokemon');

    await SendRequest('evolution_center', 'evolution_center', Form_Data)
        .then((Roster) => {
            Roster = JSON.parse(Roster);

            document.getElementById('Evolution_Page_Roster').innerHTML = Roster.Roster_Pokemon;
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
        .then((Possible_Evolutions) => {
            Possible_Evolutions = JSON.parse(Possible_Evolutions);

            document.getElementById('Evo_Data').innerHTML = Possible_Evolutions.Evolution_Data;
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
        .then((Evolve_Pokemon) => {
            Evolve_Pokemon = JSON.parse(Evolve_Pokemon);

            document.getElementById('Evo_Data').innerHTML = Evolve_Pokemon.Evolution_Status;

            UpdateRoster();
        })
        .catch((Error) => console.error('Error:', Error));
}
