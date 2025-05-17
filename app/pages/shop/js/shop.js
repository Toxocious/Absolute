/**
 * Show a preview of the selected item.
 *
 * @param Shop_ID
 * @param Object_ID
 * @param Object_Type
 */
async function PurchaseShopObject({ Shop_ID, Object_ID, Object_Type }) {
    let Form_Data = new FormData();
    Form_Data.append('Action', 'Purchase_Object');
    Form_Data.append('Shop_ID', Shop_ID);
    Form_Data.append('Object_ID', Object_ID);
    Form_Data.append('Object_Type', Object_Type);

    await SendRequest('shop', Form_Data)
        .then((Purchase_Data) => {
            Purchase_Data = JSON.parse(Purchase_Data);
            console.log(Purchase_Data);

            if (
                Purchase_Data.Object_Data.Shiny_Alert &&
                Purchase_Data.Object_Data.Ungendered_Alert
            ) {
                alert("Woah! You just bought a shiny Pokémon! And it's also ungendered!");
            } else if (Purchase_Data.Object_Data.Shiny_Alert) {
                alert('Woah! You just bought a shiny Pokémon!');
            } else if (Purchase_Data.Object_Data.Ungendered_Alert) {
                alert('Woah! You just bought an ungendered Pokémon!');
            }

            document.getElementById('ShopAJAX').style.display = 'flex';
            document.getElementById('ShopAJAX').style.flexDirection = 'column';
            document.getElementById('ShopAJAX').style.alignItems = 'center';
            document.getElementById('ShopAJAX').style.justifyContent = 'center';
            document.getElementById('ShopAJAX').style.marginBottom = '1em';

            document.getElementById('ShopAJAX').innerHTML = `
                <h3 class='obtained-pokemon-card-notice'>Thanks for your purchase!</h3>
                <div class='obtained-pokemon-card'>
                    <div class='obtained-pokemon-card-content'>
                        <div class='obtained-pokemon-card-image'>
                            <img src='${Purchase_Data.Object_Data.Sprite}' />
                        </div>
                        <div class='obtained-pokemon-card-info'>
                            <div class='obtained-pokemon-card-title'>
                                <div>
                                    ${Purchase_Data.Object_Data.Display_Name}
                                    <div class='obtained-pokemon-card-title-data'>
                                        ${Purchase_Data.Object_Data.Nature} &mdash;
                                        ${Purchase_Data.Object_Data.Gender} &mdash;
                                        ${Purchase_Data.Object_Data.Ability} &mdash;
                                        Lv. 5
                                    </div>
                                </div>
                                <div>
                                    ${
                                        Purchase_Data.Object_Data.Shiny_Alert
                                            ? "<img src='https://archives.bulbagarden.net/media/upload/8/82/ShinyLGPEStar.png' />"
                                            : ''
                                    }
                                </div>
                            </div>

                            <div class='obtained-pokemon-card-stats'>
                                <h3>IVs</h3>
                                <table class='border-gradient'>
                                    <tbody>
                                        <tr>
                                            <td style='width: calc(100% / 6);'><b>HP</b></td>
                                            <td style='width: calc(100% / 6);'><b>Attack</b></td>
                                            <td style='width: calc(100% / 6);'><b>Sp. Atk</b></td>
                                            <td style='width: calc(100% / 6);'><b>Defense</b></td>
                                            <td style='width: calc(100% / 6);'><b>Sp. Def</b></td>
                                            <td style='width: calc(100% / 6);'><b>Speed</b></td>
                                        </tr>
                                        <tr>
                                            <td style='${
                                                Purchase_Data.Object_Data.IVs[0] == 31
                                                    ? 'color: green; font-weight: bold;'
                                                    : ''
                                            }'>${Purchase_Data.Object_Data.IVs[0]}</td>
                                            <td style='${
                                                Purchase_Data.Object_Data.IVs[1] == 31
                                                    ? 'color: green; font-weight: bold;'
                                                    : ''
                                            }'>${Purchase_Data.Object_Data.IVs[1]}</td>
                                            <td style='${
                                                Purchase_Data.Object_Data.IVs[2] == 31
                                                    ? 'color: green; font-weight: bold;'
                                                    : ''
                                            }'>${Purchase_Data.Object_Data.IVs[2]}</td>
                                            <td style='${
                                                Purchase_Data.Object_Data.IVs[3] == 31
                                                    ? 'color: green; font-weight: bold;'
                                                    : ''
                                            }'>${Purchase_Data.Object_Data.IVs[3]}</td>
                                            <td style='${
                                                Purchase_Data.Object_Data.IVs[4] == 31
                                                    ? 'color: green; font-weight: bold;'
                                                    : ''
                                            }'>${Purchase_Data.Object_Data.IVs[4]}</td>
                                            <td style='${
                                                Purchase_Data.Object_Data.IVs[5] == 31
                                                    ? 'color: green; font-weight: bold;'
                                                    : ''
                                            }'>${Purchase_Data.Object_Data.IVs[5]}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        })
        .catch((Error) => console.error('Error:', Error));
}
