/**
 * Update the user's roster in the header component when any roster change has happened.
 *
 * @param pokemon_payload - An array containing a JSON payload for each of the user's rostered Pokemon.
 */
async function UpdateHeaderRoster(pokemon_payload) {
    const UserRosterSlots = document.querySelectorAll(
        '.user-bar > .user-container > .user-info > .user-roster > .user-roster-slot'
    );

    UserRosterSlots.forEach((RosterSlot, Index) => {
        if (pokemon_payload[Index]) {
            RosterSlot.firstElementChild.setAttribute(
                'src',
                pokemon_payload[Index].images.icon.path
            );
            RosterSlot.firstElementChild.setAttribute(
                'alt',
                pokemon_payload[Index].images.icon.alt
            );
            RosterSlot.firstElementChild.setAttribute(
                'onclick',
                `PokemonViewer.open('${pokemon_payload[Index].id}')`
            );
        } else {
            RosterSlot.firstElementChild.setAttribute(
                'src',
                'assets/images/Pokemon/Icons/Empty.png'
            );
            RosterSlot.firstElementChild.setAttribute('alt', 'Empty Slot');
            RosterSlot.firstElementChild.removeAttribute('onclick');
        }
    });
}
