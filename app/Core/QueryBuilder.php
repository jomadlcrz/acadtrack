<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOStatement;

class QueryBuilder
{
    protected PDO $db;
    protected string $table;
    protected array $columns = ['*'];
    protected array $wheres = [];
    protected array $bindings = [];
    protected array $joins = [];
    protected array $orders = [];
    protected array $groups = [];
    protected ?int $limit = null;
    protected ?int $offset = null;

    public function __construct(PDO $db, string $table)
    {
        $this->db = $db;
        $this->table = $table;
    }

    public function select(string ...$columns): static
    {
        $this->columns = !empty($columns) ? $columns : ['*'];
        return $this;
    }

    public function where(string $column, mixed $operatorOrValue, mixed $value = null): static
    {
        if (func_num_args() === 2) {
            $operator = '=';
            $val = $operatorOrValue;
        } else {
            $operator = (string) $operatorOrValue;
            $val = $value;
        }

        $param = ':w_' . count($this->bindings) . '_' . preg_replace('/[^a-zA-Z0-9_]/', '', $column);
        $this->wheres[] = [
            'type' => 'AND',
            'sql' => "{$column} {$operator} {$param}",
        ];
        $this->bindings[$param] = $val;

        return $this;
    }

    public function orWhere(string $column, mixed $operatorOrValue, mixed $value = null): static
    {
        if (func_num_args() === 2) {
            $operator = '=';
            $val = $operatorOrValue;
        } else {
            $operator = (string) $operatorOrValue;
            $val = $value;
        }

        $param = ':w_' . count($this->bindings) . '_' . preg_replace('/[^a-zA-Z0-9_]/', '', $column);
        $this->wheres[] = [
            'type' => 'OR',
            'sql' => "{$column} {$operator} {$param}",
        ];
        $this->bindings[$param] = $val;

        return $this;
    }

    public function whereIn(string $column, array $values): static
    {
        if (empty($values)) {
            $this->wheres[] = [
                'type' => 'AND',
                'sql' => '1 = 0',
            ];
            return $this;
        }

        $params = [];
        foreach ($values as $i => $v) {
            $p = ':win_' . count($this->bindings) . '_' . $i;
            $params[] = $p;
            $this->bindings[$p] = $v;
        }

        $this->wheres[] = [
            'type' => 'AND',
            'sql' => "{$column} IN (" . implode(', ', $params) . ")",
        ];

        return $this;
    }

    public function whereNull(string $column): static
    {
        $this->wheres[] = [
            'type' => 'AND',
            'sql' => "{$column} IS NULL",
        ];
        return $this;
    }

    public function whereNotNull(string $column): static
    {
        $this->wheres[] = [
            'type' => 'AND',
            'sql' => "{$column} IS NOT NULL",
        ];
        return $this;
    }

    public function join(string $table, string $first, string $operator, string $second, string $type = 'INNER'): static
    {
        $this->joins[] = "{$type} JOIN {$table} ON {$first} {$operator} {$second}";
        return $this;
    }

    public function leftJoin(string $table, string $first, string $operator, string $second): static
    {
        return $this->join($table, $first, $operator, $second, 'LEFT');
    }

    public function orderBy(string $column, string $direction = 'ASC'): static
    {
        $dir = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';
        $this->orders[] = "{$column} {$dir}";
        return $this;
    }

    public function groupBy(string ...$columns): static
    {
        foreach ($columns as $c) {
            $this->groups[] = $c;
        }
        return $this;
    }

    public function limit(int $limit, int $offset = 0): static
    {
        $this->limit = $limit;
        $this->offset = $offset > 0 ? $offset : null;
        return $this;
    }

    public function offset(int $offset): static
    {
        $this->offset = $offset;
        return $this;
    }

    protected function compileSelect(): string
    {
        $cols = implode(', ', $this->columns);
        $sql = "SELECT {$cols} FROM {$this->table}";

        if (!empty($this->joins)) {
            $sql .= ' ' . implode(' ', $this->joins);
        }

        if (!empty($this->wheres)) {
            $whereParts = [];
            foreach ($this->wheres as $i => $w) {
                if ($i === 0) {
                    $whereParts[] = $w['sql'];
                } else {
                    $whereParts[] = $w['type'] . ' ' . $w['sql'];
                }
            }
            $sql .= ' WHERE ' . implode(' ', $whereParts);
        }

        if (!empty($this->groups)) {
            $sql .= ' GROUP BY ' . implode(', ', $this->groups);
        }

        if (!empty($this->orders)) {
            $sql .= ' ORDER BY ' . implode(', ', $this->orders);
        }

        if ($this->limit !== null) {
            $sql .= " LIMIT {$this->limit}";
            if ($this->offset !== null) {
                $sql .= " OFFSET {$this->offset}";
            }
        }

        return $sql;
    }

    public function get(): array
    {
        $sql = $this->compileSelect();
        $stmt = $this->db->prepare($sql);
        $stmt->execute($this->bindings);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function first(): ?array
    {
        $originalLimit = $this->limit;
        $this->limit = 1;
        $sql = $this->compileSelect();
        $this->limit = $originalLimit;

        $stmt = $this->db->prepare($sql);
        $stmt->execute($this->bindings);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    public function count(): int
    {
        $originalCols = $this->columns;
        $originalOrders = $this->orders;
        $this->columns = ['COUNT(*) as total'];
        $this->orders = [];

        $sql = $this->compileSelect();

        $this->columns = $originalCols;
        $this->orders = $originalOrders;

        $stmt = $this->db->prepare($sql);
        $stmt->execute($this->bindings);
        return (int) $stmt->fetchColumn();
    }

    public function exists(): bool
    {
        return $this->count() > 0;
    }

    public function paginate(int $page = 1, int $perPage = 20): array
    {
        $page = max(1, $page);
        $total = $this->count();
        $offset = ($page - 1) * $perPage;

        $this->limit($perPage, $offset);
        $data = $this->get();

        $lastPage = (int) ceil($total / $perPage);

        return [
            'data' => $data,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'last_page' => max(1, $lastPage),
        ];
    }

    public function insert(array $data): int
    {
        unset($data['_token']);
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));

        $sql = "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($data);

        return (int) $this->db->lastInsertId();
    }

    public function update(array $data): bool
    {
        unset($data['_token']);
        if (empty($data)) {
            return false;
        }

        $setClauses = [];
        $updateBindings = [];
        foreach ($data as $col => $val) {
            $param = ':u_' . $col;
            $setClauses[] = "{$col} = {$param}";
            $updateBindings[$param] = $val;
        }

        $sql = "UPDATE {$this->table} SET " . implode(', ', $setClauses);

        if (!empty($this->wheres)) {
            $whereParts = [];
            foreach ($this->wheres as $i => $w) {
                if ($i === 0) {
                    $whereParts[] = $w['sql'];
                } else {
                    $whereParts[] = $w['type'] . ' ' . $w['sql'];
                }
            }
            $sql .= ' WHERE ' . implode(' ', $whereParts);
        }

        $stmt = $this->db->prepare($sql);
        return $stmt->execute(array_merge($this->bindings, $updateBindings));
    }

    public function delete(): bool
    {
        $sql = "DELETE FROM {$this->table}";

        if (!empty($this->wheres)) {
            $whereParts = [];
            foreach ($this->wheres as $i => $w) {
                if ($i === 0) {
                    $whereParts[] = $w['sql'];
                } else {
                    $whereParts[] = $w['type'] . ' ' . $w['sql'];
                }
            }
            $sql .= ' WHERE ' . implode(' ', $whereParts);
        }

        $stmt = $this->db->prepare($sql);
        return $stmt->execute($this->bindings);
    }
}
