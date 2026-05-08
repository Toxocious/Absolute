<?php
declare(strict_types=1);

require_once $_SERVER['DOCUMENT_ROOT'] . '/backend/session/database.php';

final class LoginService
{
    // Precomputed hash for timing-safe verification when user does not exist.
    private const DUMMY_HASH = '$2y$10$Y6fQ9lqv3s4Q4W2l1FfS8e9I4hU0x6FQnq9Yv8R2mH3x1gE8u1M3u';

    /**
     * Attempt login using username + password.
     *
     * @return array{
     *   ok: bool,
     *   message: string,
     *   errors: array<string, string>,
     *   user_id: int|null,
     *   session_payload: array<string, mixed>|null
     * }
     */
    public static function attempt(string $username, string $password, string $currentPage = '/login.php'): array
    {
        $result = [
            'ok' => false,
            'message' => 'Invalid username or password.',
            'errors' => [],
            'user_id' => null,
            'session_payload' => null,
        ];

        $username = self::normalizeUsername($username);

        if ($username === '' || $password === '') {
            $result['errors']['login'] = 'Please enter both username and password.';
            $result['message'] = 'Please enter both username and password.';
            return $result;
        }

        try {
            $db = Database::get();

            $user = self::fetchAuthRecordByUsername($db, $username);

            // Timing-safe branch: verify against dummy hash if no user.
            $hash = $user['password_hash'] ?? self::DUMMY_HASH;
            $passwordOk = password_verify($password, (string)$hash);

            if (!$user || !$passwordOk) {
                $result['errors']['login'] = 'Invalid username or password.';
                return $result;
            }

            if (self::isRpgBanned($user)) {
                $result['errors']['login'] = 'This account is currently banned from RPG access.';
                $result['message'] = 'This account is currently banned from RPG access.';
                return $result;
            }

            $userId = (int)$user['id'];
            self::updateLoginActivity($db, $userId, $currentPage);

            $result['ok'] = true;
            $result['message'] = 'Login successful.';
            $result['user_id'] = $userId;
            $result['session_payload'] = self::buildSessionPayload($userId, $user);

            return $result;
        } catch (PDOException $e) {
            if (function_exists('HandleError')) {
                HandleError($e);
            }

            $result['errors']['login'] = 'Unable to log in right now. Please try again.';
            $result['message'] = 'Unable to log in right now. Please try again.';
            $result['dump'] = $e->getMessage();

            return $result;
        } catch (Throwable $e) {
            $result['errors']['login'] = 'Unable to log in right now. Please try again.';
            $result['message'] = 'Unable to log in right now. Please try again.';
            $result['dump'] = $e->getMessage();

            return $result;
        }
    }

    /**
     * Auto-login by user id (useful right after registration).
     *
     * @return array{
     *   ok: bool,
     *   message: string,
     *   errors: array<string, string>,
     *   user_id: int|null,
     *   session_payload: array<string, mixed>|null
     * }
     */
    public static function loginByUserId(int $userId, string $currentPage = '/'): array
    {
        $result = [
            'ok' => false,
            'message' => 'Unable to log in.',
            'errors' => [],
            'user_id' => null,
            'session_payload' => null,
        ];

        if ($userId <= 0) {
            $result['errors']['login'] = 'Invalid user id.';
            $result['message'] = 'Invalid user id.';
            return $result;
        }

        try {
            $db = Database::get();

            $user = self::fetchUserById($db, $userId);
            if (!$user) {
                $result['errors']['login'] = 'User not found.';
                $result['message'] = 'User not found.';
                return $result;
            }

            if (self::isRpgBanned($user)) {
                $result['errors']['login'] = 'This account is currently banned from RPG access.';
                $result['message'] = 'This account is currently banned from RPG access.';
                return $result;
            }

            self::updateLoginActivity($db, $userId, $currentPage);

            $result['ok'] = true;
            $result['message'] = 'Login successful.';
            $result['user_id'] = $userId;
            $result['session_payload'] = self::buildSessionPayload($userId, $user);

            return $result;
        } catch (PDOException $e) {
            if (function_exists('HandleError')) {
                HandleError($e);
            }

            $result['errors']['login'] = 'Unable to log in right now. Please try again.';
            $result['message'] = 'Unable to log in right now. Please try again.';
            return $result;
        } catch (Throwable $e) {
            $result['errors']['login'] = 'Unable to log in right now. Please try again.';
            $result['message'] = 'Unable to log in right now. Please try again.';
            return $result;
        }
    }

    /**
     * Apply session payload after a successful login attempt.
     * Expects session_start() to already be called by bootstrap.
     */
    public static function establishSession(array $payload): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            throw new RuntimeException('Session is not active.');
        }

        session_regenerate_id(true);

        $_SESSION['Absolute_Beta'] = [
            'Logged_In_As' => (int)($payload['Logged_In_As'] ?? null),
        ];

        // Optional: keep global CSRF bucket separate from auth bucket.
        if (!isset($_SESSION['CSRF']) || !is_array($_SESSION['CSRF'])) {
            $_SESSION['CSRF'] = [];
        }
    }

    private static function normalizeUsername(string $username): string
    {
        return trim($username);
    }

    private static function fetchAuthRecordByUsername(Database $db, string $username): ?array
    {
        return $db->selectOne(
            'SELECT
                u.id,
                u.username,
                u.avatar,
                u.status,
                ua.password_hash,
                ub.rpg_ban,
                ub.rpg_until,
                urs.money,
                urs.abso_coins,
                urs.playtime
             FROM users u
             INNER JOIN user_auth ua ON ua.user_id = u.id
             LEFT JOIN user_bans ub ON ub.user_id = u.id
             LEFT JOIN user_rpg_state urs ON urs.user_id = u.id
             WHERE LOWER(u.username) = LOWER(:username)
             LIMIT 1',
            ['username' => $username]
        );
    }

    private static function fetchUserById(Database $db, int $userId): ?array
    {
        return $db->selectOne(
            'SELECT
                u.id,
                u.username,
                u.avatar,
                u.status,
                ub.rpg_ban,
                ub.rpg_until
             FROM users u
             LEFT JOIN user_bans ub ON ub.user_id = u.id
             WHERE u.id = :id
             LIMIT 1',
            ['id' => $userId]
        );
    }

    private static function isRpgBanned(array $user): bool
    {
        $rpgBan = (int)($user['rpg_ban'] ?? 0);
        if ($rpgBan !== 1) {
            return false;
        }

        $until = $user['rpg_until'] ?? null;
        if ($until === null) {
            return true;
        }

        return ((int)$until) >= time();
    }

    private static function updateLoginActivity(Database $db, int $userId, string $currentPage): void
    {
        $db->update(
            'users',
            [
                'last_active' => time(),
                'last_page' => $currentPage,
            ],
            'id = :id',
            ['id' => $userId]
        );
    }

    private static function buildSessionPayload(int $userId, array $user): array
    {
        $Payload = [
            'Logged_In_As' => [
                'ID' => $userId,
                'Username' => (string)($user['username'] ?? 'UNKNOWN USER'),
                'Avatar' => (string)($user['avatar'] ?? ''),
                'Playtime' => (int)($user['playtime'] ?? 0),
                'Money' => (int)($user['money'] ?? 0),
                'Abso_Coins' => (int)($user['abso_coins'] ?? 0),
            ]
        ];

        $_SESSION['Absolute_Beta'] = $Payload;

        return $Payload;
    }
}
