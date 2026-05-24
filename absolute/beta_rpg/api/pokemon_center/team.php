<?php
    declare(strict_types=1);

    require_once $_SERVER['DOCUMENT_ROOT'] . '/backend/http/api_bootstrap.php';
    require_once $_SERVER['DOCUMENT_ROOT'] . '/backend/api/services/pokemon_center_service.php';

    api_require_method('GET');
    $userId = api_require_auth_user_id();

    try {
        $team = PokemonCenterService::fetchTeam($userId);

        api_success([
            'team' => $team,
            'team_count' => count($team),
        ]);
    } catch (Throwable $e) {
        if ( function_exists('HandleError') ) {
            HandleError($e);
        }

        api_error('pokemon_center_team_failed', 'Failed to fetch team data.', 500);
    }
