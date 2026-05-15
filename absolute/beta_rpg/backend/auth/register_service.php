<?php
    declare(strict_types=1);

    require_once $_SERVER['DOCUMENT_ROOT'] . '/backend/session/database.php';

    require_once $_SERVER['DOCUMENT_ROOT'] . '/backend/data/pokedex_data.php';
    require_once $_SERVER['DOCUMENT_ROOT'] . '/backend/data/pokemon_data.php';
    require_once $_SERVER['DOCUMENT_ROOT'] . '/backend/data/user_data.php';

    /**
     * Registration service for beta_rpg.
     *
     * Notes:
     * - Expects Database::init(...) to already be called by the page bootstrap.
     * - Uses current beta schema:
     *   - users
     *   - user_auth
     *   - user_rpg_state
     */
    final class RegisterService
    {
        /**
         * Allowed starter IDs from current register form.
         */
        private const ALLOWED_STARTERS = [
            1, 4, 7,
            152, 155, 158,
            252, 255, 258,
            387, 390, 393,
            495, 498, 501,
            650, 653, 656,
            722, 725, 728
        ];

        private const BASE_CURRENCY = [
            'money' => 1000,
            'abso_coins' => 0,
        ];

        /**
         * Register a new account.
         *
         * @param array<string, mixed> $input
         * @return array{
         *   ok: bool,
         *   message: string,
         *   errors: array<string, string>,
         *   user_id: int|null,
         *   session_payload: array<string, mixed>|null
         * }
         */
        public static function register(array $input): array
        {
            $result = [
                'ok' => false,
                'message' => '',
                'errors' => [],
                'user_id' => null,
                'session_payload' => null,
            ];

            // Normalize input
            $username = self::normalizeUsername((string)($input['username'] ?? ''));
            $gender = self::normalizeGender((string)($input['gender'] ?? ''));
            $password = (string)($input['password'] ?? '');
            $confirmPassword = (string)($input['confirm_password'] ?? '');
            $email = trim((string)($input['email'] ?? ''));
            $starterId = self::normalizeStarter($input['starter'] ?? null);

            // Validate fields
            self::validateUsername($username, $result['errors']);
            self::validatePassword($password, $confirmPassword, $result['errors']);
            self::validateEmail($email, $result['errors']);
            self::validateGender($gender, $result['errors']);
            self::validateStarter($starterId, $result['errors']);

            if (!empty($result['errors'])) {
                $result['message'] = 'Please fix the highlighted fields.';
                return $result;
            }

            try {
                $db = Database::get();

                $existing = $db->selectOne(
                    'SELECT id FROM users WHERE LOWER(username) = LOWER(:username) LIMIT 1',
                    ['username' => $username]
                );

                if ($existing) {
                    $result['errors']['username'] = 'That username is already taken.';
                    $result['message'] = 'Please choose another username.';
                    return $result;
                }

                $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                if ($passwordHash === false) {
                    $result['message'] = 'Unable to create account right now.';
                    return $result;
                }

                $Starter_Pokemon_Data = PokedexData::fetch($starterId);
                if (!$Starter_Pokemon_Data) {
                    $result['errors']['starter'] = 'Invalid starter selection.';
                    $result['message'] = 'Please select a valid starter.';
                    return $result;
                }
                $Starter_Pokemon_IVs = PokemonData::GenerateRandomIVs();

                $registeredAt = time();
                $authCode = bin2hex(random_bytes(20));

                $newUserId = $db->transaction(function (Database $tx) use (
                    $username,
                    $gender,
                    $authCode,
                    $registeredAt,
                    $passwordHash,
                    $email,
                    $starterId,
                    $Starter_Pokemon_Data,
                    $Starter_Pokemon_IVs,
                ): int {
                    // users
                    $userId = $tx->insert('users', [
                        'username' => $username,
                        'gender' => $gender,
                        'auth_code' => $authCode,
                        'date_registered' => $registeredAt,
                    ]);

                    // user_auth
                    $tx->insert('user_auth', [
                        'user_id' => $userId,
                        'password_hash' => $passwordHash,
                        'email' => $email,
                    ]);

                    // user_rpg_state
                    $tx->insert('user_rpg_state', [
                        'user_id' => $userId,
                        'money' => self::BASE_CURRENCY['money'],
                        'abso_coins' => self::BASE_CURRENCY['abso_coins'],
                    ]);

                    // user_pokemon
                    $tx->insert('user_pokemon', [
                        'owner_current' => $userId,
                        'owner_original' => $userId,
                        'pokedex_id' => $starterId,
                        'name' => $Starter_Pokemon_Data['pokemon'],
                        'slot' => 1,
                        'location' => 'Roster',
                        'experience' => 125,
                        'ability' => PokemonData::GenerateRandomAbility([
                            $Starter_Pokemon_Data['ability_1'],
                            $Starter_Pokemon_Data['ability_2'],
                            $Starter_Pokemon_Data['ability_hidden']
                        ], true),
                        'nature' => PokemonData::GenerateRandomNature(),
                        'gender' => PokemonData::GenerateRandomGender(
                            $Starter_Pokemon_Data['male_odds'],
                            $Starter_Pokemon_Data['female_odds'],
                            $Starter_Pokemon_Data['genderless_odds']
                        ),
                        'iv_hp' => $Starter_Pokemon_IVs['hp_iv'],
                        'iv_attack' => $Starter_Pokemon_IVs['attack_iv'],
                        'iv_defense' => $Starter_Pokemon_IVs['defense_iv'],
                        'iv_special_attack' => $Starter_Pokemon_IVs['special_attack_iv'],
                        'iv_special_defense' => $Starter_Pokemon_IVs['special_defense_iv'],
                        'iv_speed' => $Starter_Pokemon_IVs['speed_iv'],
                        'created_at' => $registeredAt,
                        'created_location' => 'Starter',
                        'move_1' => 1,
                        'move_2' => 2,
                        'move_3' => 3,
                        'move_4' => 4,
                    ]);

                    return $userId;
                });

                UserData::UpdateRosterHash($newUserId);

                $result['ok'] = true;
                $result['user_id'] = $newUserId;
                $result['message'] = 'Account created successfully. You can now log in.';

                return $result;
            } catch (PDOException $e) {
                if ((string)$e->getCode() === '23000') {
                    $result['errors']['username'] = 'That username is already taken.';
                    $result['message'] = 'Please choose another username.';

                    return $result;
                }

                if (function_exists('HandleError')) {
                    HandleError($e);
                }

                $result['message'] = 'Unable to create an account right now. Please try again.';

                $result['e'] = $e->getMessage();
                return $result;
            } catch (Throwable $e) {
                $result['message'] = 'Unable to create an account right now. Please try again.';

                $result['e'] = $e->getMessage();
                return $result;
            }
        }

        private static function normalizeUsername(string $username): string
        {
            return trim($username);
        }

        private static function normalizeGender(string $rawGender): string
        {
            $value = strtolower(trim($rawGender));

            return match ($value) {
                'female' => 'Female',
                'male' => 'Male',
                'genderless' => 'Genderless',
                default => 'Genderless',
            };
        }

        private static function normalizeStarter(mixed $starter): int
        {
            if (is_int($starter)) {
                return $starter;
            }

            if (is_string($starter) && ctype_digit($starter)) {
                return (int)$starter;
            }

            return 0;
        }

        /**
         * @param array<string, string> $errors
         */
        private static function validateUsername(string $username, array &$errors): void
        {
            if ($username === '') {
                $errors['username'] = 'Username is required.';
                return;
            }

            if (strlen($username) < 3 || strlen($username) > 32) {
                $errors['username'] = 'Username must be between 3 and 32 characters.';
                return;
            }

            // Letters, numbers, underscore only
            if (!preg_match('/^[A-Za-z0-9_]+$/', $username)) {
                $errors['username'] = 'Username can only contain letters, numbers, and underscores.';
            }
        }

        /**
         * @param array<string, string> $errors
         */
        private static function validatePassword(string $password, string $confirmPassword, array &$errors): void
        {
            if ($password === '') {
                $errors['password'] = 'Password is required.';
                return;
            }

            if (strlen($password) < 6) {
                $errors['password'] = 'Password must be at least 6 characters.';
                return;
            }

            if ($password !== $confirmPassword) {
                $errors['confirm_password'] = 'Passwords do not match.';
            }
        }

        /**
         * @param array<string, string> $errors
         */
        private static function validateEmail(string $email, array &$errors): void
        {
            if ($email === '') {
                $errors['email'] = 'Email is required.';
                return;
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = 'Please enter a valid email address.';
            }
        }

        /**
         * @param array<string, string> $errors
         */
        private static function validateGender(string $gender, array &$errors): void
        {
            if (!in_array($gender, ['Male', 'Female', 'Genderless'], true)) {
                $errors['gender'] = 'Please select a valid gender.';
            }
        }

        /**
         * @param array<string, string> $errors
         */
        private static function validateStarter(int $starterId, array &$errors): void
        {
            if (!in_array($starterId, self::ALLOWED_STARTERS, true)) {
                $errors['starter'] = 'Please select a valid starter.';
            }
        }
    }
