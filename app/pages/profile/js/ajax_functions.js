const Tab_List = ['roster', 'box', 'inventory', 'achievements', 'stats'];

/**
 * Load content from the specified tab.
 *
 * @param Tab_Name
 * @param Profile_ID
 */
async function ShowTab(Tab_Name, Profile_ID) {
    if (!Tab_Name) {
        Tab_Name = 'roster';
    }

    await FetchSubPage('profile', Tab_Name, '#ProfileAJAX');

    switch (Tab_Name) {
        case 'roster':
            GetRoster(Profile_ID);
            break;

        case 'box':
            GetBox(Profile_ID, 1);
            break;

        case 'inventory':
            GetInventory(Profile_ID);
            break;

        default:
            console.warn(`No specific action for tab: ${Tab_Name}`);
            break;
    }

    for (let i = 0; i < Tab_List.length; i++) {
        document.getElementById(`${Tab_List[i]}Button`).parentElement.classList.remove('active');
    }

    document.getElementById(`${Tab_Name}Button`).parentElement.classList.add('active');
}
