<?php
    require_once $_SERVER['DOCUMENT_ROOT'] . '/backend/session/session.php';
    require_once $_SERVER['DOCUMENT_ROOT'] . '/backend/session/user_session.php';

    require_once $_SERVER['DOCUMENT_ROOT'] . '/components/_component.php';

    require_once $_SERVER['DOCUMENT_ROOT'] . '/backend/data/item_data.php';
?>

<section class='pokemon-center-inventory'>
    <div class='pokemon-center-inventory-selected-item'>
        selected item info<br />
        name, amount, description<br />
        roster slots
    </div>

    <div class='panel pokemon-center-inventory-container'>
        <div class='panel-nav with-icons'>
            <button data-inventory-nav="battle">
                <img src="/assets/images/Items/Categories/battle.png" alt="Battle Items">Battle Items
            </button>

            <button data-inventory-nav="medicine">
                <img src="/assets/images/Items/Categories/medicine.png" alt="Medicine">Medicine
            </button>

            <button data-inventory-nav="berries">
                <img src="/assets/images/Items/Categories/berries.png" alt="Berries">Berries
            </button>

            <button data-inventory-nav="pokeballs">
                <img src="/assets/images/Items/Categories/pokeballs.png" alt="Pokeballs">Pokeballs
            </button>

            <button data-inventory-nav="key">
                <img src="/assets/images/Items/Categories/key.png" alt="Key Items">Key Items
            </button>

            <button data-inventory-nav="misc">
                <img src="/assets/images/Items/Categories/misc.png" alt="Misc.">Misc.
            </button>
        </div>

        <div class='panel-content pokemon-center-inventory-bag'>

        </div>
    </div>
</section>
