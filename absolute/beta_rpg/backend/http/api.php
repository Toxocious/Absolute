<?php
    declare(strict_types=1);

    function api_json(array $payload, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=UTF-8');

        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    function api_success(array $data = [], array $meta = [], int $status = 200): void
    {
        $payload = [
            'ok' => true,
            'data' => $data,
        ];

        if ( $meta !== [] ) {
            $payload['meta'] = $meta;
        }

        api_json($payload, $status);
    }

    function api_error(
        string $code,
        string $message,
        int $status = 400,
        array $details = []
    ): void
    {
        $error = [
            'code' => $code,
            'message' => $message,
        ];

        if ( $details !== [] ) {
            $error['details'] = $details;
        }

        api_json([
            'ok' => false,
            'error' => $error,
        ], $status);
    }

    function api_require_method(string $method): void
    {
        if ( strtoupper($_SERVER['REQUEST_METHOD'] ?? '') !== strtoupper($method) ) {
            api_error('method_not_allowed', 'Invalid request method.', 405, [
                'expected' => strtoupper($method),
            ]);
        }
    }

    function api_require_auth_user_id(): int
    {
        $userId = (int)($_SESSION['Absolute_Beta']['Logged_In_As']['ID'] ?? 0);

        if ( $userId <= 0 ) {
            api_error('unauthorized', 'You must be logged in to access this endpoint.', 401);
        }

        return $userId;
    }

    function api_query_int(
        string $name,
        int $default,
        int $min,
        int $max
    ): int
    {
        $raw = $_GET[$name] ?? null;

        if ( $raw === null || $raw === '' ) {
            return $default;
        }

        if ( is_int($raw) ) {
            $value = $raw;
        } elseif ( is_string($raw) && ctype_digit($raw) ) {
            $value = (int)$raw;
        } else {
            api_error('invalid_query_param', 'Invalid numeric query parameter.', 422, [
                'field' => $name,
            ]);
        }

        if ( $value < $min || $value > $max ) {
            api_error('query_param_out_of_range', 'Query parameter is out of range.', 422, [
                'field' => $name,
                'min' => $min,
                'max' => $max,
            ]);
        }

        return $value;
    }

    function api_pagination_meta(int $page, int $perPage, int $total): array
    {
        $totalPages = (int)max(1, (int)ceil($total / $perPage));

        return [
            'page' => $page,
            'per_page' => $perPage,
            'total' => $total,
            'total_pages' => $totalPages,
            'has_prev' => $page > 1,
            'has_next' => $page < $totalPages,
        ];
    }
