import { apiGet } from '/js/api/client.js';

const BOX_PER_PAGE = 21;

let ActiveTab = 'roster';

let FilterTypes = ['all', 'normal', 'shiny', 'event'];
let FilterGenders = ['all', 'female', 'male', 'genderless', 'ungendered'];
let BoxFilter = {
    type: 'all',
    species: null,
    gender: null,
    includeFormes: true,
};

function UpdateBoxFilter(changedField, newValue) {
    if (changedField === 'type') {
        if (!FilterTypes.includes(newValue)) {
            console.warn(`Invalid filter type: ${newValue}`);
            return;
        }

        document.querySelectorAll('#pokemon-center-box-filter-type a').forEach((type) => {
            type.classList.toggle('active', type.dataset.boxFilterType === newValue);
        });
        BoxFilter.type = newValue;
    }

    if (changedField == 'gender') {
        if (!FilterGenders.includes(newValue)) {
            console.warn(`Invalid filter gender: ${newValue}`);
            return;
        }

        if (newValue !== BoxFilter.gender) {
            BoxFilter.gender = newValue;
        } else {
            BoxFilter.gender = null;
        }

        document.querySelectorAll('#pokemon-center-box-filter-gender a').forEach((gender) => {
            gender.classList.toggle('active', gender.dataset.boxFilterGender === BoxFilter.gender);
        });
    }

    if (changedField === 'species') {
        BoxFilter.species = newValue && newValue !== 'null' ? newValue : null;
    }
}

function escapeHtml(value) {
    return String(value)
        .replaceAll('&', '&')
        .replaceAll('<', '<')
        .replaceAll('>', '>')
        .replaceAll('"', '"')
        .replaceAll("'", "'");
}

function getDragAfterElement(container, y) {
    const draggableElements = [
        ...container.querySelectorAll('.pokemon-center-roster-slot:not(.dragging)'),
    ];

    return draggableElements.reduce(
        (closest, child) => {
            const box = child.getBoundingClientRect();
            const offset = y - box.top - box.height / 2;

            if (offset < 0 && offset > closest.offset) {
                return { offset, element: child };
            }

            return closest;
        },
        { offset: Number.NEGATIVE_INFINITY, element: null }
    ).element;
}

function getSlotIndex(slotElement) {
    if (!slotElement) return -1;

    const slots = [...slotElement.parentElement.querySelectorAll('.pokemon-center-roster-slot')];
    return slots.indexOf(slotElement);
}

function getFirstEmptySlot(teamList) {
    return [...teamList.querySelectorAll('.pokemon-center-roster-slot')].find(
        (el) => el.getAttribute('data-pokemon-id') === 'empty'
    );
}

function isEmptyRosterSlot(el) {
    return el?.getAttribute('data-pokemon-id') === 'empty';
}

function moveBeforeFirstEmpty(teamList, draggingItem) {
    const firstEmpty = [
        ...teamList.querySelectorAll('.pokemon-center-roster-slot:not(.dragging)'),
    ].find((el) => isEmptyRosterSlot(el));

    if (firstEmpty) {
        teamList.insertBefore(draggingItem, firstEmpty);
    } else {
        teamList.appendChild(draggingItem);
    }
}

async function normalizeRosterOrder(teamList) {
    const slots = [...teamList.querySelectorAll('.pokemon-center-roster-slot')];
    const filled = slots.filter((el) => !isEmptyRosterSlot(el));
    const empty = slots.filter((el) => isEmptyRosterSlot(el));

    const ordered = [...filled, ...empty].slice(0, 6);
    teamList.replaceChildren(...ordered);
}

async function setupBoxedPokemonToRosterDragDrop(root) {
    const boxList = document.querySelector('[data-box-list]');
    const teamList = document.querySelector('[data-team-list]');

    if (!boxList || !teamList) {
        return;
    }

    let draggingItem = null;

    boxList.addEventListener('dragstart', (e) => {
        const card = e.target.closest('.pokemon-center-box-card');
        if (!card) {
            return;
        }

        draggingItem = card;
        draggingItem.classList.add('dragging');
    });

    boxList.addEventListener('dragend', () => {
        if (draggingItem) {
            draggingItem.classList.remove('dragging');
        }

        document
            .querySelectorAll('.pokemon-center-roster-slot')
            .forEach((item) => item.classList.remove('over'));

        draggingItem = null;
    });

    teamList.addEventListener('dragover', (e) => {
        e.preventDefault();

        const draggingOverItem = getDragAfterElement(teamList, e.clientY);

        document
            .querySelectorAll('.pokemon-center-roster-slot')
            .forEach((item) => item.classList.remove('over'));

        if (draggingOverItem) {
            draggingOverItem.classList.add('over');
        }
    });

    teamList.addEventListener('drop', async (e) => {
        e.preventDefault();
        if (!draggingItem) return;

        const boxList = document.querySelector('[data-box-list]');
        const dropTargetSlot = e.target.closest('.pokemon-center-roster-slot');
        const firstEmptySlot = getFirstEmptySlot(teamList);

        draggingItem.classList.remove('pokemon-center-box-card', 'dragging');
        draggingItem.classList.add('pokemon-center-roster-slot');
        draggingItem.setAttribute('draggable', 'true');

        const droppedOnOccupiedSlot =
            dropTargetSlot && dropTargetSlot.getAttribute('data-pokemon-id') !== 'empty';

        if (droppedOnOccupiedSlot && boxList) {
            const displacedCard = rosterSlotToBoxCard(dropTargetSlot);
            teamList.replaceChild(draggingItem, dropTargetSlot);
            boxList.prepend(displacedCard);
        } else if (dropTargetSlot && dropTargetSlot.getAttribute('data-pokemon-id') === 'empty') {
            teamList.insertBefore(draggingItem, firstEmptySlot || dropTargetSlot);
        } else if (firstEmptySlot) {
            teamList.insertBefore(draggingItem, firstEmptySlot);
        } else {
            moveBeforeFirstEmpty(teamList, draggingItem);
        }

        const currentPage = Number(root?.dataset.currentPage || '1');

        try {
            await apiGet('/api/pokemon_center/move_pokemon.php', {
                pokemon_id: draggingItem.dataset.pokemonId,
                move_location: 'roster',
                slot: getSlotIndex(draggingItem) + 1,
            });
            await normalizeRosterOrder(teamList);
            await Promise.all([loadTeam(root), loadBox(root, currentPage)]);
        } catch (error) {
            console.error('Failed to move Pokemon to box:', error);
        }

        teamList
            .querySelectorAll('.pokemon-center-roster-slot')
            .forEach((item) => item.classList.remove('over'));

        draggingItem = null;
    });
}

async function setupRosterDragDrop(root) {
    const teamList = document.querySelector('[data-team-list]');

    if (!teamList) {
        return;
    }

    let draggingItem = null;

    teamList.addEventListener('dragstart', (e) => {
        const slot = e.target.closest('.pokemon-center-roster-slot');
        if (!slot || isEmptyRosterSlot(slot)) {
            return;
        }

        draggingItem = slot;
        draggingItem.classList.add('dragging');
    });

    teamList.addEventListener('dragend', () => {
        if (draggingItem) {
            draggingItem.classList.remove('dragging');
        }

        document
            .querySelectorAll('.pokemon-center-roster-slot')
            .forEach((item) => item.classList.remove('over'));

        draggingItem = null;
    });

    teamList.addEventListener('dragover', (e) => {
        e.preventDefault();

        if (!draggingItem) {
            return;
        }

        const hover = e.target.closest('.pokemon-center-roster-slot');
        teamList
            .querySelectorAll('.pokemon-center-roster-slot')
            .forEach((item) => item.classList.remove('over'));

        if (hover && hover !== draggingItem) {
            hover.classList.add('over');
        }
    });

    teamList.addEventListener('drop', async (e) => {
        e.preventDefault();
        if (!draggingItem) return;

        const target = e.target.closest('.pokemon-center-roster-slot');
        if (!target || target === draggingItem || isEmptyRosterSlot(target)) {
            return;
        }

        const sourceId = Number(draggingItem.dataset.pokemonId || 0);
        const targetId = Number(target.dataset.pokemonId || 0);
        if (!sourceId || !targetId) return;

        // optimistic UI swap
        const marker = document.createElement('div');
        draggingItem.before(marker);
        target.before(draggingItem);
        marker.replaceWith(target);

        try {
            await apiGet('/api/pokemon_center/move_pokemon.php', {
                pokemon_id: sourceId,
                move_location: 'roster',
                slot: getSlotIndex(target) + 1,
                swap_id: targetId,
            });

            await loadTeam(root);
        } catch (error) {
            console.error('Failed to swap roster Pokemon spots:', error);
            await loadTeam(root);
        } finally {
            normalizeRosterOrder(teamList);
        }
    });
}

async function setupRosterToBoxDragDrop(root) {
    const teamList = root.querySelector('[data-team-list]');
    const boxList = root.querySelector('[data-box-list]');

    if (!teamList || !boxList) return;

    let draggingRosterItem = null;

    teamList.addEventListener('dragstart', (e) => {
        const slot = e.target.closest('.pokemon-center-roster-slot');
        if (!slot) return;

        const pokemonId = Number(slot.dataset.pokemonId || '0');
        if (!pokemonId || slot.dataset.pokemonId === 'empty') return;

        draggingRosterItem = slot;
        draggingRosterItem.classList.add('dragging');
    });

    teamList.addEventListener('dragend', () => {
        if (draggingRosterItem) {
            draggingRosterItem.classList.remove('dragging');
        }

        draggingRosterItem = null;
    });

    boxList.addEventListener('dragover', (e) => {
        e.preventDefault();
    });

    boxList.addEventListener('drop', async (e) => {
        e.preventDefault();
        if (!draggingRosterItem) return;

        const pokemonId = Number(draggingRosterItem.dataset.pokemonId || '0');
        if (!pokemonId) return;

        const currentPage = Number(root?.dataset.currentPage || '1');

        try {
            await apiGet('/api/pokemon_center/move_pokemon.php', {
                pokemon_id: pokemonId,
                move_location: 'box',
            });
            await normalizeRosterOrder(teamList);
            await Promise.all([loadTeam(root), loadBox(root, currentPage)]);
        } catch (error) {
            console.error('Failed to move Pokemon to box:', error);
        } finally {
            draggingRosterItem = null;
        }
    });
}

function rosterSlotToBoxCard(slot) {
    const card = slot.cloneNode(true);

    card.classList.remove('pokemon-center-roster-slot', 'over', 'dragging');
    card.classList.add('pokemon-center-box-card');
    card.setAttribute('draggable', 'true');

    const name = card.querySelector('.pokemon-name');
    if (name) {
        name.classList.remove('pokemon-name');
        name.classList.add('pokemon-center-box-card-name');
    }

    const nickname = card.querySelector('.pokemon-nickname');
    if (nickname) {
        nickname.classList.remove('pokemon-nickname');
        nickname.classList.add('pokemon-center-box-card-nickname');
    }

    const image = card.querySelector('.pokemon-image');
    if (image) {
        image.classList.remove('pokemon-image');
        image.classList.add('pokemon-center-box-card-image');
    }

    const info = card.querySelector('.pokemon-info');
    if (info) {
        info.classList.remove('pokemon-info');
        info.classList.add('pokemon-center-box-card-info');
    }

    const level = card.querySelector('.pokemon-level');
    if (level) {
        level.classList.remove('pokemon-level');
        level.classList.add('pokemon-center-box-card-level');
    }

    const gender = card.querySelector('.pokemon-gender');
    if (gender) {
        gender.classList.remove('pokemon-gender');
        gender.classList.add('pokemon-center-box-card-gender');
    }

    return card;
}

function renderRosterSlot(pokemon) {
    if (!pokemon) {
        return `
            <div class='pokemon-center-roster-slot' data-pokemon-id='empty'>
                <div class='pokemon-data'>
                    <img src='/assets/images/Pokemon/Icons/Empty.png' alt='Empty Slot' class='pokemon-image' />
                    <div class='pokemon-info'>
                        <div class='pokemon-name'>Empty Slot</div>
                    </div>
                </div>
            </div>
        `;
    }

    const displayName = `${pokemon.type === 'Shiny' ? 'Shiny' : ''}${pokemon.name}${
        pokemon.forme ? ` (${pokemon.forme})` : ''
    }`.trim();

    const nickname =
        pokemon.nickname && pokemon.nickname !== pokemon.name
            ? `<div class='pokemon-nickname'>(${escapeHtml(pokemon.nickname)})</div>`
            : '';

    const genderIcon = ['Female', 'Male', '(?)'].includes(pokemon.gender)
        ? `<div class='pokemon-gender'>
                    <img src='/assets/images/Pokemon/Misc/${escapeHtml(
                        pokemon.gender == '(?)' ? 'Ungendered' : pokemon.gender
                    )}.svg' alt='${escapeHtml(pokemon.gender)}' />
                </div>`
        : '';

    return `
        <div class='pokemon-center-roster-slot' draggable='true' data-pokemon-id='${escapeHtml(
            pokemon.id
        )}'>
            <div class='pokemon-data'>
                <img src='${escapeHtml(pokemon.images.icon.path)}' alt='${escapeHtml(
        pokemon.images.icon.alt
    )}' class='pokemon-image' />
                <div class='pokemon-info'>
                    <div class='pokemon-name'>${escapeHtml(displayName)}</div>
                    ${nickname}
                    <div class='pokemon-level'>Lv. ${escapeHtml(pokemon.level)}</div>
                    ${genderIcon}
                </div>
            </div>
        </div>
    `;
}

function renderBoxCard(pokemon) {
    const displayName = `${pokemon.type === 'Shiny' ? 'Shiny' : ''}${pokemon.name}${
        pokemon.forme ? ` (${pokemon.forme})` : ''
    }`.trim();

    const nickname =
        pokemon.nickname && pokemon.nickname !== pokemon.name
            ? `<div class='pokemon-center-box-card-nickname'>(${escapeHtml(
                  pokemon.nickname
              )})</div>`
            : '';

    const genderIcon = ['Female', 'Male', '(?)'].includes(pokemon.gender)
        ? `<div class='pokemon-gender'>
                    <img src='/assets/images/Pokemon/Misc/${escapeHtml(
                        pokemon.gender == '(?)' ? 'Ungendered' : pokemon.gender
                    )}.svg' alt='${escapeHtml(pokemon.gender)}' />
                </div>`
        : '';

    const pokemonBoxCard = `
        <div class='pokemon-center-box-card' data-pokemon-id='${escapeHtml(pokemon.id)}' ${
        pokemon.id !== -1 ? "draggable='true'" : ''
    }>
            <div class='pokemon-data'>
                <img src='${escapeHtml(pokemon.images.icon.path)}' alt='${escapeHtml(
        pokemon.images.icon.alt
    )}' class='pokemon-center-box-card-image' />

                <div class='pokemon-center-box-card-info'>
                    <div class='pokemon-center-box-card-name'>${escapeHtml(displayName)}</div>
                    ${nickname}
                    <div class='pokemon-center-box-card-level'>Lv. ${escapeHtml(
                        pokemon.level
                    )}</div>
                    <div class='pokemon-center-box-card-gender'>
                        ${genderIcon}
                    </div>
                </div>
            </div>
        </div>
    `;

    return pokemonBoxCard;
}

async function loadTeam(root) {
    const endpoint = root.dataset.teamEndpoint;
    const list = root.querySelector('[data-team-list]');

    if (!endpoint || !list) {
        return;
    }

    const loadingRoster = Array.from({ length: 6 }, () => null);
    list.innerHTML = loadingRoster.map(renderRosterSlot).join('');

    try {
        const response = await apiGet(endpoint);
        const team = Array.isArray(response.data.team) ? response.data.team : [];

        const slotted = Array.from({ length: 6 }, (_, index) => {
            const slot = index + 1;
            return team.find((item) => Number(item.slot) === slot) || null;
        });

        list.innerHTML = slotted.map(renderRosterSlot).join('');

        await UpdateHeaderRoster(slotted);
    } catch (error) {
        list.innerHTML = `<p class='pokemon-center-status error'>${escapeHtml(error.message)}</p>`;
    }
}

async function loadBox(root, page, filter) {
    const endpoint = root.dataset.boxEndpoint;
    const list = root.querySelector('[data-box-list]');
    const pageText = root.querySelector('[data-box-page]');
    const prevButton = root.querySelector('[data-box-prev]');
    const nextButton = root.querySelector('[data-box-next]');

    if (!endpoint || !list || !pageText || !prevButton || !nextButton) {
        return;
    }

    list.innerHTML = '<p class="pokemon-center-status">Loading boxed Pokemon...</p>';

    try {
        const response = await apiGet(endpoint, {
            page,
            per_page: BOX_PER_PAGE,
            filters: filter ? JSON.stringify(filter) : undefined,
        });

        const pokemon = Array.isArray(response.data.pokemon) ? response.data.pokemon : [];
        const meta = response.meta || {};

        const currentPage = Number(meta.page) || 1;
        const totalPages = Number(meta.total_pages) || 1;

        document.getElementById('pokemon-center-box-results').innerText = ` ${meta.total} Pokemon`;

        pageText.textContent = `Page ${currentPage} / ${totalPages}`;
        prevButton.disabled = !meta.has_prev;
        nextButton.disabled = !meta.has_next;
        root.dataset.currentPage = String(currentPage);

        if (pokemon.length === 0) {
            list.innerHTML = "<p class='pokemon-center-status'>You have no boxed Pokemon.</p>";
            return;
        }

        list.innerHTML = pokemon.map(renderBoxCard).join('');
    } catch (error) {
        list.innerHTML = `<p class='pokemon-center-status error'>${escapeHtml(error.message)}</p>`;
    }
}

function bindBoxSelection(root) {
    const list = root.querySelector('[data-box-list]');
    if (!list) return;

    list.addEventListener('click', (event) => {
        const card = event.target.closest('.pokemon-center-box-card');
        if (!card || !list.contains(card)) return;

        const pokemonId = Number(card.dataset.pokemonId || '0');
        if (pokemonId > 0) {
            PokemonViewer.open(pokemonId);
        }
    });
}

function bindBoxPagination(root) {
    const prevButton = root.querySelector('[data-box-prev]');
    const nextButton = root.querySelector('[data-box-next]');

    if (!prevButton || !nextButton) {
        return;
    }

    prevButton.addEventListener('click', () => {
        const currentPage = Number(root.dataset.currentPage || '1');
        const nextPage = Math.max(1, currentPage - 1);
        loadBox(root, nextPage);
    });

    nextButton.addEventListener('click', () => {
        const currentPage = Number(root.dataset.currentPage || '1');
        loadBox(root, currentPage + 1);
    });
}

function bindBoxFilterToggle() {
    const toggle = document.getElementById('pokemon-center-box-filter-toggle');
    if (!toggle) return;

    toggle.addEventListener('click', () => {
        const filterVisibility = document.querySelector('.pokemon-center-box-filter');
        if (!filterVisibility) {
            return;
        }

        filterVisibility.classList.toggle('display-none');
        filterVisibility.classList.toggle('display-block');
    });
}

function bindBoxFilterChange() {
    const filterType = document.querySelectorAll('#pokemon-center-box-filter-type a');

    if (filterType) {
        filterType.forEach((type) => {
            type.addEventListener('click', (e) => {
                UpdateBoxFilter('type', type.dataset.boxFilterType);
            });
        });
    }

    const filterGender = document.querySelectorAll('#pokemon-center-box-filter-gender a');

    if (filterGender) {
        filterGender.forEach((gender) => {
            gender.addEventListener('click', (e) => {
                UpdateBoxFilter('gender', gender.dataset.boxFilterGender);
            });
        });
    }

    const filterSpecies = document.querySelector('#pokemon-center-box-filter-species');
    if (filterSpecies) {
        filterSpecies.addEventListener('change', (e) => {
            const value = filterSpecies.value;
            UpdateBoxFilter('species', value);
        });
    }
}

function bindBoxFilterSearch(root) {
    const searchInput = document.querySelector('#pokemon-center-box-filter-search');
    if (!searchInput) return;

    searchInput.addEventListener('click', (e) => {
        loadBox(root, 1, BoxFilter);
    });
}

async function changeTab(tab) {
    await fetch('/components/pokemon_center/' + tab + '_tab.php')
        .then((response) => {
            return response.text();
        })
        .then((html) => {
            const contentContainer = document.querySelector('.pokemon-center-content');
            if (contentContainer) {
                contentContainer.innerHTML = html;
            } else {
                console.error('Content container not found for tab:', tab);
            }
        })
        .catch((error) => {
            console.error('Error loading tab content:', error);
        });

    // re-bind any dynamic elements in the newly loaded tab
    setTimeout(() => {
        setupBindings();
    }, 100);
}

function bindTabChange() {
    const tabButtons = document.querySelectorAll('[data-pokemon-center-api] .panel-nav button');
    tabButtons.forEach((button) => {
        const tabName = button.dataset.navSection;
        button.addEventListener('click', () => {
            if (tabName === ActiveTab) {
                return;
            }

            button.classList.add('active');
            tabButtons.forEach((btn) => {
                if (btn !== button) {
                    btn.classList.remove('active');
                }
            });

            ActiveTab = tabName;

            changeTab(tabName);
        });
    });
}

function bindMoveChangeDropdowns() {
    const dropdowns = document.querySelectorAll('.move-dropdown');

    dropdowns.forEach((dropdown) => {
        dropdown.addEventListener('change', (e) => {
            handleMoveChange(e.target);
        });
    });
}

function bindNicknameChangeInputs() {
    const buttons = document.querySelectorAll('.nickname-buttons > button');

    buttons.forEach((button) => {
        button.addEventListener('click', (e) => {
            handleNicknameChange(e.target);
        });
    });
}

async function handleNicknameChange(button) {
    const input = button.parentElement.parentElement.querySelector('input');
    if (!input) {
        return;
    }

    const newNickname = input.value.trim();
    if (newNickname.length == 0 && button.dataset.action === 'set') {
        SpawnToast(
            'Nickname cannot be empty!',
            'Please enter a nickname or click "Clear Nickname" to remove it.',
            'error',
            undefined,
            false
        );
        return;
    }

    const pokemonId = button.dataset.pokemonId;
    const nicknameAction = button.dataset.action;

    if (!pokemonId) {
        return;
    }

    const nicknameElement = document.querySelector(
        `.pokemon-center-nicknames-slot[data-pokemon-id="${pokemonId}"] > .pokemon-name > .pokemon-nickname`
    );

    try {
        const response = await apiGet('/api/pokemon_center/change_nickname.php', {
            pokemon_id: pokemonId,
            nickname: newNickname,
            nickname_action: nicknameAction,
        });

        if (nicknameAction === 'set') {
            SpawnToast('Nickname Changed!', response.data.text, 'success', undefined, false);
        } else if (nicknameAction === 'remove') {
            SpawnToast('Nickname Cleared!', response.data.text, 'success', undefined, false);
        }

        if (nicknameElement) {
            nicknameElement.textContent =
                response.data.new_nickname == null ? '' : `(${response.data.new_nickname})`;
        }
    } catch (error) {
        SpawnToast('Nickname Error', error, 'error', undefined, false);
    }
}

async function handleMoveChange(t) {
    const root = document.querySelector('[data-pokemon-center-api]');

    const endpoint = root.dataset.moveChangeEndpoint;

    if (!endpoint) {
        return;
    }

    try {
        const response = await apiGet(endpoint, {
            pokemon_id: t.dataset.pokemonId,
            move_slot: t.dataset.moveSlot,
            move_id: t.value,
        });

        SpawnToast('Move Changed!', response.data.text, 'success', undefined, false);
    } catch (error) {
        SpawnToast('Move Change Failed', error, 'error', undefined, false);
    }
}

function setupBindings() {
    const root = document.querySelector('[data-pokemon-center-api]');
    if (!root) {
        return;
    }

    bindTabChange();

    switch (ActiveTab) {
        case 'roster':
            bindBoxPagination(root);
            bindBoxSelection(root);
            bindBoxFilterToggle();
            bindBoxFilterChange();
            bindBoxFilterSearch(root);

            setupRosterDragDrop(root);
            setupBoxedPokemonToRosterDragDrop(root);
            setupRosterToBoxDragDrop(root);

            loadBox(root, 1);

            break;

        case 'moves':
            bindMoveChangeDropdowns();

            break;

        case 'nickname':
            bindNicknameChangeInputs();

            break;

        default:
            break;
    }

    if (ActiveTab === 'roster') {
        bindBoxPagination(root);
        bindBoxSelection(root);
        bindBoxFilterToggle();
        bindBoxFilterChange();
        bindBoxFilterSearch(root);

        setupRosterDragDrop(root);
        setupBoxedPokemonToRosterDragDrop(root);
        setupRosterToBoxDragDrop(root);

        loadBox(root, 1);
    }
}

window.addEventListener('DOMContentLoaded', () => {
    setupBindings(false);
});
