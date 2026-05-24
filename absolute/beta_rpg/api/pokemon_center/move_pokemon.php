<?php
    declare(strict_types=1);

    require_once $_SERVER['DOCUMENT_ROOT'] . '/backend/http/api_bootstrap.php';

    require_once $_SERVER['DOCUMENT_ROOT'] . '/backend/api/services/pokemon_center_service.php';

    api_require_method('GET');

    $userId = api_require_auth_user_id();

    $moveLocation = htmlspecialchars(trim($_GET['move_location'] ?? ''));
    $pokemonId = (int) htmlspecialchars(trim($_GET['pokemon_id'] ?? ''));
    $slot = isset($_GET['slot']) ? (int) htmlspecialchars(trim($_GET['slot'])) : null;
    $swapId = isset($_GET['swap_id']) ? (int) htmlspecialchars(trim($_GET['swap_id'])) : null;

    try {
        PokemonCenterService::movePokemon($userId, $pokemonId, $moveLocation, $slot, $swapId);
        $team = PokemonCenterService::fetchTeam($userId);

        api_success([
            'team' => $team,
            'team_count' => count($team),
        ]);
    } catch (Throwable $e) {
        if ( function_exists('HandleError') ) {
            HandleError($e);
        }

        api_error('pokemon_center_move_failed', 'Failed to move Pokemon.', 500);
    }
