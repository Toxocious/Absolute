<?php
    class DBExpr
    {
        public string $sql;
        public array $params;

        public function __construct(string $sql, array $params = [])
        {
            $this->sql = $sql;
            $this->params = $params;
        }
    }
