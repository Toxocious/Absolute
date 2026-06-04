<?php
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

<div class='pokemon-center-nicknames-slot' data-pokemon-id='<?= $Pokemon_ID === -1 ? 'empty' : $Pokemon_ID; ?>'>
    <img src='<?= $Pokemon_Images['Sprite']['Sprite_Path']; ?>' alt='<?= $Pokemon_Images['Sprite']['Alt_Text']; ?>' class='pokemon-image' /><br />
    <div class='pokemon-name'>
        <?php
            echo ($Pokemon_Type == 'Shiny') ? 'Shiny' : '';
            echo $Pokemon_Name;
            echo ($Pokemon_Forme) ? " ($Pokemon_Forme)" : '';
        ?>
        <div class='pokemon-nickname'>
            <?=  ( isset($Pokemon_Nickname) && $Pokemon_Nickname && $Pokemon_Nickname !== $Pokemon_Name ) ? "({$Pokemon_Nickname})" : '' ?>
        </div>
    </div>

    <?php if ( $Pokemon_ID !== -1 ): ?>
        <div class='nickname-input'>
            <input type='text' name='nickname' placeholder='Enter nickname...' value='<?= isset($Pokemon_Nickname) ? $Pokemon_Nickname : '' ?>' />
            <div class='nickname-buttons'>
                <button class='button button-primary' data-pokemon-id='<?= $Pokemon_ID; ?>' data-action='set'>Set Nickname</button>
                <button class='button button-primary' data-pokemon-id='<?= $Pokemon_ID; ?>' data-action='remove'>Remove Nickname</button>
            </div>
        </div>
    <?php endif; ?>
</div>
