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

            const Total_IVs = Purchase_Data.Object_Data.IVs.reduce(
                (a, b) => Number(a) + Number(b),
                0
            );
            const Total_Base_Stats = Purchase_Data.Object_Data.Stats.reduce((a, b) => a + b, 0);

            document.getElementById('ShopAJAX').innerHTML = `
                <table class='border-gradient' style='width: 475px; margin: 1em auto;'>
                    <tbody>
                        <tr>
                            <td colspan='2' rowspan='3'>
                                <img src='${Purchase_Data.Object_Data.Sprite}' />
                            </td>
                            <td></td>
                            <td style='width: 39px;'>
                                <b>HP</b>
                            </td>
                            <td style='width: 39px;'>
                                <b>Att</b>
                            </td>
                            <td style='width: 39px;'>
                                <b>Def</b>
                            </td>
                            <td style='width: 39px;'>
                                <b>Sp.A</b>
                            </td>
                            <td style='width: 39px;'>
                                <b>Sp.D</b>
                            </td>
                            <td style='width: 39px;'>
                                <b>Spe</b>
                            </td>
                            <td style='width: 39px;'>
                                <b>Total</b>
                            </td>
                        </tr>
                        <tr>
                            <td><b>Base</b></td>
                            <td>${Purchase_Data.Object_Data.Stats[0]}</td>
                            <td>${Purchase_Data.Object_Data.Stats[1]}</td>
                            <td>${Purchase_Data.Object_Data.Stats[2]}</td>
                            <td>${Purchase_Data.Object_Data.Stats[3]}</td>
                            <td>${Purchase_Data.Object_Data.Stats[4]}</td>
                            <td>${Purchase_Data.Object_Data.Stats[5]}</td>
                            <td>${Total_Base_Stats}</td>
                        </tr>
                        <tr>
                            <td><b>IVs</b></td>
                            <td>${Purchase_Data.Object_Data.IVs[0]}</td>
                            <td>${Purchase_Data.Object_Data.IVs[1]}</td>
                            <td>${Purchase_Data.Object_Data.IVs[2]}</td>
                            <td>${Purchase_Data.Object_Data.IVs[3]}</td>
                            <td>${Purchase_Data.Object_Data.IVs[4]}</td>
                            <td>${Purchase_Data.Object_Data.IVs[5]}</td>
                            <td>${Total_IVs}</td>
                        </tr>
                        <tr>
                            <td colspan='10' style='padding: 0.5em;'>
                                <b>You have successfully purchased a(n) ${Purchase_Data.Object_Data.Display_Name}.</b>
                            </td>
                        </tr>
                    <tbody>
                </table>
            `;
        })
        .catch((Error) => console.error('Error:', Error));
}
