<?php
    require_once $_SERVER['DOCUMENT_ROOT'] . '/backend/session/database_expression.php';
    require_once $_SERVER['DOCUMENT_ROOT'] . '/backend/session/database_query_builder.php';

    class Database
    {
        private static ?Database $instance = null;
        private PDO $pdo;

        private function __construct(array $config)
        {
            $dsn = sprintf(
                "mysql:host=%s;dbname=%s;charset=%s",
                $config['host'],
                $config['database'],
                $config['charset'],
            );

            try {
                $this->pdo = new PDO(
                    $dsn,
                    $config['user'],
                    $config['password'],
                    [
                        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES   => false,
                    ]
                );
            } catch (PDOException $e) {
                throw new Exception("Database connection failed: " . $e->getMessage());
            }
        }

        public static function init(array $config): self
        {
            if (!self::$instance) {
                self::$instance = new self($config);
            }

            return self::$instance;
        }

        public static function get(): self
        {
            if (!self::$instance) {
                throw new Exception("Database not initialized.");
            }

            return self::$instance;
        }

        public function pdo(): PDO
        {
            return $this->pdo;
        }

        public function table(string $table): QueryBuilder
        {
            return new QueryBuilder($this->pdo, $table);
        }
    }
