<?php
    declare(strict_types=1);

    require_once $_SERVER['DOCUMENT_ROOT'] . '/backend/http/api_bootstrap.php';

    require_once $_SERVER['DOCUMENT_ROOT'] . '/backend/api/services/pokemon_center_service.php';

    require_once $_SERVER['DOCUMENT_ROOT'] . '/backend/data/pokemon_data.php';

    api_require_method('GET');

    $userId = api_require_auth_user_id();

    $pokemonId = (int) htmlspecialchars(trim($_GET['pokemon_id'] ?? ''));
    $nickname = (string) htmlspecialchars(trim($_GET['nickname'] ?? ''));
    $action = (string) htmlspecialchars(trim($_GET['nickname_action'] ?? ''));

    try {
        if ( !in_array($action, ['set', 'remove']) ) {
            api_error('invalid_nickname_action', 'Invalid nickname action specified.', 400);
            return;
        }

        $Pokemon_Name = PokemonData::FetchPokemonNameById($pokemonId);

        if ( $action === 'remove' ) {
            PokemonCenterService::RemoveNickname($userId, $pokemonId);

            api_success([
                'text' => "{$Pokemon_Name['display_name']}'s nickname has been removed.",
                'new_nickname' => null,
            ]);
        } else {
            PokemonCenterService::ChangeNickname($userId, $pokemonId, $nickname);

            api_success([
                'text' => "{$Pokemon_Name['display_name']} is now known as {$nickname}.",
                'new_nickname' => $nickname,
            ]);
        }
    } catch (Throwable $e) {
        if ( function_exists('HandleError') ) {
            HandleError($e);
        }

        api_error('pokemon_center_nickname_change_failed', 'Failed to change this Pokemon\'s nickname. ' . $e->getMessage(), 500);
    }
