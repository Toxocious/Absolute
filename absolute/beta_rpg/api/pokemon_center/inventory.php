<?php
    declare(strict_types=1);

    require_once $_SERVER['DOCUMENT_ROOT'] . '/backend/http/api_bootstrap.php';

    require_once $_SERVER['DOCUMENT_ROOT'] . '/backend/api/services/pokemon_center_service.php';

    api_require_method('GET');
    $userId = api_require_auth_user_id();
    $category = (string) htmlspecialchars(trim($_GET['category'] ?? 'battle'));

    try {
        $inventory = PokemonCenterService::FetchInventory($userId, $category);

        api_success([
            'inventory' => $inventory,
            'inventory_count' => count($inventory),
            'category' => $category,
            'debug' => [
                'user_id' => $userId,
                'requested_on' => time(),
            ]
        ]);
    } catch (Throwable $e) {
        if ( function_exists('HandleError') ) {
            HandleError($e);
        }

        api_error('pokemon_center_inventory_failed', 'Failed to fetch inventory data.', 500);
    }
