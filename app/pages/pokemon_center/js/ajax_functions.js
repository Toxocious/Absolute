/**
 * Load content from the specified tab.
 *
 * @param Tab_Name
 */
async function ShowTab(Tab_Name) {
    document.getElementById('Pokemon_Center_Page').innerHTML = `
      <div style='display: flex; align-items: center; justify-content: center; padding: 10px;'>
        <div class='loading-element'></div>
      </div>
    `;

    const tabList = ['roster', 'release', 'nickname', 'moves', 'inventory'];

    return new Promise((resolve, reject) => {
        const req = new XMLHttpRequest();

        req.open('GET', `/pages/pokemon_center/pages/${Tab_Name}.php`);
        req.send();
        req.onerror = (error) => reject(Error(`Network Error: ${error}`));
        req.onload = () => {
            if (req.status === 200) {
                document.getElementById('Pokemon_Center_Page').innerHTML = req.response;

                switch (Tab_Name) {
                    case 'roster':
                        GetRoster();
                        GetBoxedPokemon();
                        break;

                    case 'release':
                        GetReleasablePokemon();
                        break;

                    case 'nickname':
                        GetNicknameTabRoster();
                        break;

                    case 'moves':
                        GetMoveTabRoster();
                        break;

                    case 'inventory':
                        ShowInventoryTab('Held Item');
                        ShowEquippedItems();
                        break;
                }

                for (let i = 0; i < tabList.length; i++) {
                    document
                        .getElementById(`${tabList[i]}Button`)
                        .parentElement.classList.remove('active');
                }

                document.getElementById(`${Tab_Name}Button`).parentElement.classList.add('active');

                resolve(req.response);
            } else {
                reject(Error(req.statusText));
            }
        };
    });
}
