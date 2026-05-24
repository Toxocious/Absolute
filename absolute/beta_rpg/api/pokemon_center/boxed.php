<?php
    declare(strict_types=1);

    require_once $_SERVER['DOCUMENT_ROOT'] . '/backend/http/api_bootstrap.php';

    require_once $_SERVER['DOCUMENT_ROOT'] . '/backend/api/services/pokemon_center_service.php';

    api_require_method('GET');

    $userId = api_require_auth_user_id();

    $page = api_query_int('page', 1, 1, 500);
    $perPage = api_query_int('per_page', 21, 1, 21);
    $filter = api_query('filters', null);
    $filter = $filter ? json_decode($filter, true) : null;

    try {
        $boxData = PokemonCenterService::fetchBox($userId, $page, $perPage, $filter);
        $meta = api_pagination_meta($page, $perPage, (int)$boxData['total']);

        api_success([
            'pokemon' => $boxData['pokemon'],
            'debug' => [
                'filter' => $filter,
                'page' => $page,
                'per_page' => $perPage,
                'user_id' => $userId,
            ],
        ], $meta);
    } catch (Throwable $e) {
        if ( function_exists('HandleError') ) {
            HandleError($e);
        }

        api_error('pokemon_center_box_failed', 'Failed to fetch boxed Pokemon.', 500);
    }
