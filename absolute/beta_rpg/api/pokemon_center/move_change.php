<?php
    declare(strict_types=1);

    require_once $_SERVER['DOCUMENT_ROOT'] . '/backend/http/api_bootstrap.php';

    require_once $_SERVER['DOCUMENT_ROOT'] . '/backend/api/services/pokemon_center_service.php';

    require_once $_SERVER['DOCUMENT_ROOT'] . '/backend/data/pokemon_data.php';
    require_once $_SERVER['DOCUMENT_ROOT'] . '/backend/data/move_data.php';

    api_require_method('GET');

    $userId = api_require_auth_user_id();

    $pokemonId = (int) htmlspecialchars(trim($_GET['pokemon_id'] ?? ''));
    $pokemonMoveSlot = (int) htmlspecialchars(trim($_GET['move_slot'] ?? ''));
    $moveId = (string) htmlspecialchars(trim($_GET['move_id'] ?? ''));

    try {
        PokemonCenterService::ChangeMove($userId, $pokemonId, $moveId, $pokemonMoveSlot);

        $Pokemon_Name = PokemonData::FetchPokemonNameById($pokemonId);
        $Move_Name = MoveData::fetchMoveNameById($moveId);

        api_success([
            'text' => "{$Pokemon_Name['display_name']} has learned {$Move_Name}.",
        ]);
    } catch (Throwable $e) {
        if ( function_exists('HandleError') ) {
            HandleError($e);
        }

        api_error('pokemon_center_move_change_failed', 'Failed to change this Pokemon\'s move. ' . $e->getMessage(), 500);
    }
