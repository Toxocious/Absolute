<?php
    require_once $_SERVER['DOCUMENT_ROOT'] . '/backend/data/move_data.php';
    require_once $_SERVER['DOCUMENT_ROOT'] . '/backend/data/pokedex_data.php';
    require_once $_SERVER['DOCUMENT_ROOT'] . '/backend/data/pokemon_data.php';

    if ( $Pokemon_ID === -1 )
    {
        $Pokemon_Type = 'Normal';
        $Pokemon_Images = [
            'Sprite' => [
                'Sprite_Path' => '/assets/images/Pokemon/Sprites/Empty.png',
                'Alt_Text' => 'Empty Slot',
            ],
            'Icon' => [
                'Sprite_Path' => '/assets/images/Pokemon/Sprites/Empty.png',
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

<div class='pokemon-center-moves-slot' data-pokemon-id='<?= $Pokemon_ID === -1 ? 'empty' : $Pokemon_ID; ?>'>
    <img src='<?= $Pokemon_Images['Sprite']['Sprite_Path']; ?>' alt='<?= $Pokemon_Images['Sprite']['Alt_Text']; ?>' class='pokemon-image' /><br />
    <div class='pokemon-name'>
        <?php
            echo ($Pokemon_Type == 'Shiny') ? 'Shiny' : '';
            echo $Pokemon_Name;
            echo ($Pokemon_Forme) ? " ($Pokemon_Forme)" : '';
        ?>
        <?php if ( isset($Pokemon_Nickname) && $Pokemon_Nickname && $Pokemon_Nickname !== $Pokemon_Name ): ?>
            <div class='pokemon-nickname'>
                (<?= $Pokemon_Nickname ?>)
            </div>
        <?php endif; ?>
    </div>

    <?php if ( $Pokemon_ID !== -1 ): ?>
        <div class='move-dropdowns'>
            <?php for ( $Move_Slot = 1; $Move_Slot <= 4; $Move_Slot++ ): ?>
                <select name='move-<?= $Move_Slot ?>' class='move-dropdown' data-pokemon-id='<?= $Pokemon_ID; ?>' data-move-slot='<?= $Move_Slot; ?>'>
                    <option value=''>Select A Move</option>
                    <?php
                        $Pokemon_Move = ${"Pokemon_Move_{$Move_Slot}"} ?? '';
                        foreach ( $Move_Dropdown_Options as $option ) {
                            echo "<option value='{$option['id']}'" . ($Pokemon_Move === $option['id'] ? " selected" : "") . ">{$option['name']}</option>";
                        }
                    ?>
                </select>
            <?php endfor; ?>
        </div>
    <?php endif; ?>
</div>
