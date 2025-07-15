let Current_Boxed_Pokemon = [];

const Border_Color_Default = 'var(--border-color);';
const Border_Color_Yes = 'var(--border-color-success);';
const Border_Color_No = 'var(--border-color-error);';

/**
 * Sets the trade interest status of a Pokémon.
 *
 * @param Pokemon_ID
 * @param Trade_Interest
 */
async function SetPokemonInterestStatus(Pokemon_ID, Trade_Interest) {
    const Pokemon_Entry = Current_Boxed_Pokemon.find((p) => p.ID == Pokemon_ID);

    if (Pokemon_Entry.Trade_Interest == Trade_Interest) {
        return;
    }

    let Form_Data = new FormData();
    Form_Data.append('Action', 'Update_Pokemon_Interest');
    Form_Data.append('Pokemon_ID', Pokemon_ID);
    Form_Data.append('Trade_Interest', Trade_Interest);

    await SendRequest('trade_interest', 'trade_interest', Form_Data)
        .then((Updated_Pokemon) => {
            const Updated_Interest = JSON.parse(Updated_Pokemon);

            if (Updated_Interest.Success && Current_Boxed_Pokemon.length > 0) {
                if (Pokemon_Entry) {
                    Pokemon_Entry.Trade_Interest = Trade_Interest;

                    const pokemon_card = document.getElementById(`pokemon_card_${Pokemon_ID}`);
                    if (pokemon_card) {
                        let border_color;
                        switch (Trade_Interest) {
                            case 'Yes':
                                border_color = getComputedStyle(document.documentElement)
                                    .getPropertyValue('--border-color-success')
                                    .trim();
                                break;
                            case 'No':
                                border_color = getComputedStyle(document.documentElement)
                                    .getPropertyValue('--border-color-error')
                                    .trim();
                                break;
                            default:
                                border_color = getComputedStyle(document.documentElement)
                                    .getPropertyValue('--border-color')
                                    .trim();
                                break;
                        }
                        pokemon_card.style.border = `1px solid ${border_color}`;
                    }
                }
            }
        })
        .catch((Error) => console.error('Error:', Error));
}

/**
 * Get the user's currently boxed Pokemon.
 *
 * @param Pokemon_Type
 * @param Page
 */
async function GetBoxedPokemon(Pokemon_Type = 'Normal', Page = 1) {
    let Form_Data = new FormData();
    Form_Data.append('Action', 'Get_Box');
    Form_Data.append('Page', Page);
    Form_Data.append('Pokemon_Type', Pokemon_Type);

    await SendRequest('trade_interest', 'trade_interest', Form_Data)
        .then((Boxed_Pokemon) => {
            Boxed_Pokemon = JSON.parse(Boxed_Pokemon)[0];
            Current_Boxed_Pokemon = Boxed_Pokemon.Pagination.Data;

            if (Boxed_Pokemon.Pagination.Data.length === 0) {
                document.querySelector('#Box_Pagination > tr > td').innerHTML = 'No Pages';
                document.querySelector('#Boxed_Pokemon > tr > td').innerHTML =
                    'You have no Pok&eacute;mon in your box.';
            } else {
                document.getElementById('Box_Pagination').innerHTML =
                    Boxed_Pokemon.Pagination.Pagination;

                document.querySelector('#Boxed_Pokemon > tr > td > div').innerHTML = '';
                document.querySelector('#Boxed_Pokemon > tr > td > div').style.flexWrap = 'wrap';
                document.querySelector('#Boxed_Pokemon > tr > td > div').style.gap = '0.5em';
                document.querySelector('#Boxed_Pokemon > tr > td > div').style.padding = '0.5em 0';
                document.querySelector('#Boxed_Pokemon > tr > td > div').style.justifyContent =
                    'space-around';
                document.querySelector('#Boxed_Pokemon > tr > td > div').style.flexDirection =
                    'row';

                for (const Pokemon of Boxed_Pokemon.Pagination.Data) {
                    Icon_Path = `/images/Pokemon/Icons/Normal`;
                    Icon_Name = String(Pokemon.Pokedex_ID).padStart(3, '0');
                    if (Pokemon.Forme) Icon_Name += `-${Pokemon.Forme}`;

                    let Display_Name = Pokemon.Display_Name;
                    if (Pokemon_Type != 'Normal') {
                        Display_Name = `${Pokemon_Type}${Display_Name}`;
                    }

                    let [Interest_Yes, Interest_No, Interest_NA] = [
                        Pokemon.Trade_Interest == 'Yes',
                        Pokemon.Trade_Interest == 'No',
                        Pokemon.Trade_Interest == 'Undecided',
                    ];

                    let Border_Color = Border_Color_Default;
                    if (Interest_Yes) {
                        Border_Color = Border_Color_Yes;
                    } else if (Interest_No) {
                        Border_Color = Border_Color_No;
                    }

                    document.querySelector('#Boxed_Pokemon > tr > td > div').innerHTML += `
                        <div
                            id='pokemon_card_${Pokemon.ID}'
                            class='pokemon_trade_interest_card'
                            style='display: flex; flex-direction: row; gap: 0.25em; border: 1px solid ${Border_Color}'
                        >
                             <div><img src='${Icon_Path}/${Icon_Name}.png' alt='${Display_Name}' /></div>
                             <div>
                                <div>${Display_Name}</div>
                                <div>
                                    <input
                                        type='radio'
                                        id='Pokemon_Interest_No_${Pokemon.ID}'
                                        name='Pokemon_Interest_${Pokemon.ID}'
                                        value='${Pokemon.ID}'
                                        onclick='SetPokemonInterestStatus(
                                            ${Pokemon.ID}, "No", "${Pokemon.Trade_Interest}"
                                        );'
                                        ${Interest_No && 'checked'}
                                    />
                                    <label for='Pokemon_Interest_${Pokemon.ID}'>No</label>

                                    <input
                                        type='radio'
                                        id='Pokemon_Interest_Yes_${Pokemon.ID}'
                                        name='Pokemon_Interest_${Pokemon.ID}'
                                        value='${Pokemon.ID}'
                                        onclick='SetPokemonInterestStatus(
                                            ${Pokemon.ID}, "Yes", "${Pokemon.Trade_Interest}"
                                        );'
                                        ${Interest_Yes && 'checked'}
                                    />
                                    <label for='Pokemon_Interest_${Pokemon.ID}'>Yes</label>

                                    <input
                                        type='radio'
                                        id='Pokemon_Interest_NA_${Pokemon.ID}'
                                        name='Pokemon_Interest_${Pokemon.ID}'
                                        value='${Pokemon.ID}'
                                        onclick='SetPokemonInterestStatus(
                                            ${Pokemon.ID}, "Undecided", "${Pokemon.Trade_Interest}"
                                        );'
                                        ${Interest_NA && 'checked'}
                                    />
                                    <label for='Pokemon_Interest_${Pokemon.ID}'>N/A</label>
                                </div>
                            </div>
                        </div>
                    `;
                }
            }

            document.getElementById('interest_nav_normal').classList.remove('active');
            document.getElementById('interest_nav_shiny').classList.remove('active');

            document
                .getElementById(`interest_nav_${Pokemon_Type.toLowerCase()}`)
                .classList.add('active');
        })
        .catch((Error) => console.error('Error:', Error));
}
