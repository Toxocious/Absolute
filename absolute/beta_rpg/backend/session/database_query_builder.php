<?php
    class QueryBuilder
    {
        private PDO $pdo;
        private string $table;

        private array $select = ['*'];
        private array $where = [];
        private array $bindings = [];
        private array $orderBy = [];
        private ?int $limit = null;
        private ?int $offset = null;

        public function __construct(PDO $pdo, string $table)
        {
            $this->pdo = $pdo;
            $this->table = $table;
        }

        /* =========================
        * SELECT
        * ========================= */

        public function select(array|string $columns): self
        {
            $this->select = is_array($columns) ? $columns : [$columns];
            return $this;
        }

        public function where(string $column, string $operator, $value): self
        {
            $param = "w_" . count($this->bindings);

            $this->where[] = "{$column} {$operator} :{$param}";
            $this->bindings[$param] = $value;

            return $this;
        }

        public function whereRaw(string $sql, array $params = []): self
        {
            $this->where[] = $sql;
            $this->bindings = array_merge($this->bindings, $params);
            return $this;
        }

        public function orderBy(string $column, string $direction = 'ASC'): self
        {
            $this->orderBy[] = "{$column} {$direction}";
            return $this;
        }

        public function limit(int $limit): self
        {
            $this->limit = $limit;
            return $this;
        }

        public function offset(int $offset): self
        {
            $this->offset = $offset;
            return $this;
        }

        private function buildSelect(): array
        {
            $sql = "SELECT " . implode(", ", $this->select) . " FROM {$this->table}";

            if ($this->where) {
                $sql .= " WHERE " . implode(" AND ", $this->where);
            }

            if ($this->orderBy) {
                $sql .= " ORDER BY " . implode(", ", $this->orderBy);
            }

            if ($this->limit !== null) {
                $sql .= " LIMIT {$this->limit}";
            }

            if ($this->offset !== null) {
                $sql .= " OFFSET {$this->offset}";
            }

            return [$sql, $this->bindings];
        }

        public function get(): array
        {
            [$sql, $bindings] = $this->buildSelect();

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($bindings);

            return $stmt->fetchAll();
        }

        public function first(): ?array
        {
            $this->limit(1);
            $result = $this->get();

            return $result[0] ?? null;
        }

        public function count(): int
        {
            $this->select(["COUNT(*) AS count"]);
            $row = $this->first();

            return (int)($row['count'] ?? 0);
        }

        /* =========================
        * INSERT
        * ========================= */

        public function insert(array $data): int
        {
            $columns = array_keys($data);

            $fields = implode(", ", $columns);
            $placeholders = implode(", ", array_map(fn($c) => ":$c", $columns));

            $sql = "INSERT INTO {$this->table} ({$fields}) VALUES ({$placeholders})";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($data);

            return (int)$this->pdo->lastInsertId();
        }

        /* =========================
        * UPDATE
        * ========================= */

        public function update(array $data): int
        {
            $set = [];
            $params = $this->bindings;

            foreach ($data as $key => $value) {
                if ($value instanceof DBExpr) {
                    $set[] = "{$key} = {$value->sql}";
                    $params = array_merge($params, $value->params);
                } else {
                    $param = "u_" . count($params);
                    $set[] = "{$key} = :{$param}";
                    $params[$param] = $value;
                }
            }

            $sql = "UPDATE {$this->table} SET " . implode(", ", $set);

            if ($this->where) {
                $sql .= " WHERE " . implode(" AND ", $this->where);
            }

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);

            return $stmt->rowCount();
        }

        public function increment(string $column, int|float $amount = 1): int
        {
            return $this->update([
                $column => new DBExpr("{$column} + :inc", ['inc' => $amount])
            ]);
        }

        public function decrement(string $column, int|float $amount = 1): int
        {
            return $this->update([
                $column => new DBExpr("{$column} - :dec", ['dec' => $amount])
            ]);
        }

        /* =========================
        * DELETE
        * ========================= */

        public function delete(): int
        {
            $sql = "DELETE FROM {$this->table}";

            if ($this->where) {
                $sql .= " WHERE " . implode(" AND ", $this->where);
            }

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($this->bindings);

            return $stmt->rowCount();
        }
    }
