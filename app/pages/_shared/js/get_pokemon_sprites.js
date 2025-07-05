/**
 * Retrieve the sprite and icon URLs of a Pokemon.
 *
 * @param Pokedex_ID
 * @param Forme
 *
 * @return array
 */
function GetPokemonSprites(Type, Pokedex_ID, Forme = null) {
    if (!Pokedex_ID) {
        return {
            Sprite: '/images/Pokemon/Sprites/0.png',
            Icon: '/images/Pokemon/Sprites/0_mini.png',
        };
    }

    const Pokedex_ID_Padded = String(Pokedex_ID).padStart(3, '0');

    const Sprite_URL = `/images/Pokemon/Sprites/${Type}/${Pokedex_ID_Padded}${
        Forme ? `-${Forme}` : ''
    }.png`;

    const Icon_URL = `/images/Pokemon/Icons/Normal/${Pokedex_ID_Padded}${
        Forme ? `-${Forme}` : ''
    }.png`;

    return {
        Sprite: Sprite_URL,
        Icon: Icon_URL,
    };
}
