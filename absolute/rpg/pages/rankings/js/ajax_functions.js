const Tab_List = ['pokemon', 'trainers'];

/**
 * Load content from the specified tab.
 *
 * @param Tab_Name
 */
async function ShowTab(Tab_Name) {
    if (!Tab_Name) {
        Tab_Name = 'pokemon';
    }

    await FetchSubPage('rankings', Tab_Name, '#rankings-ajax');

    for (let i = 0; i < Tab_List.length; i++) {
        document.getElementById(`${Tab_List[i]}Button`).parentElement.classList.remove('active');
    }

    document.getElementById(`${Tab_Name}Button`).parentElement.classList.add('active');
}
