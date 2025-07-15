/**
 * Get the user's inventory.
 *
 * @param {int} Profile_ID - The ID of the profile to get the inventory for.
 * @param {string} Category - The category name to retrieve items for.
 */
async function GetInventory(Profile_ID, Category = 'Battle Item') {
    let Form_Data = new FormData();
    Form_Data.append('Action', 'Get_Inventory');
    Form_Data.append('User_ID', Profile_ID);
    Form_Data.append('Category', Category);

    const Inventory_Request = await SendRequest('profile', 'inventory', Form_Data);
    console.log(Inventory_Request);
    const Inventory_Data = JSON.parse(Inventory_Request)[0];

    let Inventory_HTML = '';

    if (!Inventory_Data || Inventory_Data.length === 0 || Inventory_Data.Items.length === 0) {
        Inventory_HTML = `
            <div style='padding: 0.5em; width: 100%; grid-column: span 3;'>
                No items were found in this user's inventory.
            </div>
        `;
    } else {
        for (const Item of Inventory_Data.Items) {
            Inventory_HTML += `
                <div class='pokemon-card compact' style='flex-basis: calc(calc(100% / 3) - 2.5em);'>
                    <img src='images/Items/${Item.Item_Name}.png' alt='${Item.Item_Name}' height='30' width='30' />
                    <div class='pokemon-info'>
                        <h3 class='pokemon-name'>${Item.Item_Name}</h3>
                        <div class='pokemon-details'>
                            <span class='pokemon-level'>x${Item.Quantity}</span>
                        </div>
                    </div>
                </div>
            `;
        }
    }

    document.getElementById('ProfileAJAX').innerHTML = `
        <table>
            <tbody>
                ${Inventory_Data.Category_Header}
            </tbody>

            <tbody>
                <tr>
                    <td colspan='21'>
                        <div style='display: flex; flex-direction: row; flex-wrap: wrap; gap: 1em; justify-content: center; padding: 0.5em 0;'>
                            ${Inventory_HTML}
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    `;
}
