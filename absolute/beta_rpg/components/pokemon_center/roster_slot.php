<?php
    /**
     * Renders a roster slot for a Pokemon Center page, displaying the Pokemon's name, image, and level.
     *
     * Props:
     * - Pokemon_ID: The unique identifier for the Pokemon instance.
     * - Pokemon_Pokedex_ID: The Pokedex ID of the Pokemon species.
     * - Pokemon_Pokedex_Alt_ID: The alternate form ID for the Pokemon (if applicable).
     * - Pokemon_Name: The name of the Pokemon.
     * - Pokemon_Forme: The forme of the Pokemon (if applicable).
     * - Pokemon_Type: The type of the Pokemon (e.g., 'Normal', 'Shiny').
     * - Pokemon_Gender: The gender of the Pokemon ('Male', 'Female', 'Genderless', '(?)').
     * - Pokemon_Experience: The experience points of the Pokemon.
     * - Pokemon_Slot: The slot number of the Pokemon in the roster.
     * - Pokemon_Nickname: The nickname of the Pokemon (if applicable).
     * - Pokemon_Held_Item: The item held by the Pokemon (if applicable).
     */
    require_once $_SERVER['DOCUMENT_ROOT'] . '/backend/data/pokedex_data.php';
    require_once $_SERVER['DOCUMENT_ROOT'] . '/backend/data/pokemon_data.php';

    if ( $Pokemon_ID === -1 )
    {
        $Pokemon_Type = 'Normal';
        $Pokemon_Images = [
            'Sprite' => [
                'Sprite_Path' => '/assets/images/Pokemon/Icons/Empty.png',
                'Alt_Text' => 'Empty Slot',
            ],
            'Icon' => [
                'Sprite_Path' => '/assets/images/Pokemon/Icons/Empty.png',
                'Alt_Text' => 'Empty Slot',
            ],
        ];
    }
    else
    {
        $Pokemon_Images = PokemonData::FetchPokemonImages(
            $Pokemon_Pokedex_ID,
            $Pokemon_Pokedex_Alt_ID,
            $Pokemon_Type,
            $Pokemon_Name,
            $Pokemon_Forme
        );
    }
?>

<div class='pokemon-center-roster-slot' data-pokemon-id='<?= $Pokemon_ID === -1 ? 'empty' : $Pokemon_ID; ?>' <?= $Pokemon_ID !== -1 ? "draggable='true'" : ''; ?>>
    <div class='pokemon-data'>
        <img src='<?= $Pokemon_Images['Icon']['Sprite_Path']; ?>' alt='<?= $Pokemon_Images['Icon']['Alt_Text']; ?>' class='pokemon-image' />
        <div class='pokemon-info'>
            <div class='pokemon-name'>
                <?php echo ($Pokemon_Type == 'Shiny') ? 'Shiny' : ''; echo $Pokemon_Name; echo ($Pokemon_Forme) ? " ($Pokemon_Forme)" : ''; ?>
            </div>

            <?php if ( $Pokemon_ID !== -1 && $Pokemon_Nickname && $Pokemon_Nickname !== $Pokemon_Name ): ?>
                <div class='pokemon-nickname'>
                    (<?= $Pokemon_Nickname ?>)
                </div>
            <?php endif; ?>

            <?php if ( $Pokemon_ID !== -1 ): ?>
                <div class='pokemon-level'>
                    Lv. <?= $Pokemon_Level ?>
                </div>
            <?php endif; ?>

            <?php if ( isset($Pokemon_Gender) && in_array($Pokemon_Gender, ['Female', 'Male', '(?)']) ): ?>
                <div class='pokemon-gender'>
                    <img src='/assets/images/Pokemon/Misc/<?= $Pokemon_Gender == '(?)' ? 'Ungendered' : $Pokemon_Gender; ?>.svg' alt='<?= $Pokemon_Gender ?>' />
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
