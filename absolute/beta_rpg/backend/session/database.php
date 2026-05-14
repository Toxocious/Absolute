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

        public function query(string $sql, array $params = []): PDOStatement
        {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);

            return $stmt;
        }

        public function select(string $sql, array $params = []): array
        {
            return $this->query($sql, $params)->fetchAll();
        }

        public function selectOne(string $sql, array $params = []): ?array
        {
            $result = $this->query($sql, $params)->fetch();

            return $result ?: null;
        }

        public function insert(string $table, array $data): int
        {
            $columns = array_keys($data);
            $fields = implode(', ', $columns);
            $placeholders = implode(', ', array_map(fn($column) => ':' . $column, $columns));

            $sql = "INSERT INTO {$table} ({$fields}) VALUES ({$placeholders})";

            $this->query($sql, $data);

            return (int)$this->pdo->lastInsertId();
        }

        public function update(string $table, array $data, string $where, array $whereParams = []): int
        {
            $setParts = [];
            $params = [];

            foreach ($data as $key => $value) {
                if ($value instanceof DBExpr) {
                    $setParts[] = "{$key} = {$value->sql}";
                    $params = array_merge($params, $value->params);
                } else {
                    $setParts[] = "{$key} = :{$key}";
                    $params[$key] = $value;
                }
            }

            $sql = "UPDATE {$table} SET " . implode(', ', $setParts) . " WHERE {$where}";

            $stmt = $this->query($sql, array_merge($params, $whereParams));

            return $stmt->rowCount();
        }

        public function delete(string $table, string $where, array $params = []): int
        {
            $sql = "DELETE FROM {$table} WHERE {$where}";
            $stmt = $this->query($sql, $params);

            return $stmt->rowCount();
        }

        public function beginTransaction(): void
        {
            $this->pdo->beginTransaction();
        }

        public function commit(): void
        {
            $this->pdo->commit();
        }

        public function rollBack(): void
        {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
        }

        public function transaction(callable $callback)
        {
            try {
                $this->beginTransaction();
                $result = $callback($this);
                $this->commit();

                return $result;
            } catch (Throwable $e) {
                $this->rollBack();
                throw $e;
            }
        }

        public function table(string $table): QueryBuilder
        {
            return new QueryBuilder($this->pdo, $table);
        }
    }

    function HandleError(Throwable $Exception): void
    {
        $Fetch_Date = date('m/d/y h:i A');

        $Error_Code = (string)$Exception->getCode();
        $Triggered_By_File = $Exception->getFile();
        $Triggered_On_Line = $Exception->getLine();
        $Error_Message = $Exception->getMessage();

        $Formatted_Error = "[Error: {$Error_Code}] {$Error_Message} in {$Triggered_By_File} on line {$Triggered_On_Line}";

        if ( !is_dir($_SERVER['DOCUMENT_ROOT'] . '/logs/absolute_beta') )
        {
            mkdir($_SERVER['DOCUMENT_ROOT'] . '/logs/absolute_beta', 0755, true);
        }

        $Absolute_Error_Log = fopen($_SERVER['DOCUMENT_ROOT'] . '/logs/absolute_beta/pdo_errors.log', 'a');
        if ( $Absolute_Error_Log )
        {
            fwrite($Absolute_Error_Log, "[ {$Fetch_Date} ] {$Formatted_Error}\n");
            fclose($Absolute_Error_Log);
        }
    }
