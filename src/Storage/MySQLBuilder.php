<?php

declare(strict_types=1);

namespace Dof\Framework\Storage;

use Closure;
use Dof\Framework\Paginator;

class MySQLBuilder
{
    private $origin;

    private $sql = false;
    private $db;
    private $table;
    private $select = [];
    private $alias = [];
    private $aliasRaw = [];
    private $exists = [];
    private $where = [];
    private $wheres = [];
    private $whereRaw = [];
    private $rawWhere = [];
    private $or = [];
    private $ors = [];
    private $orRaw = [];
    private $rawOr = [];
    private $having = [];
    private $havings = [];
    private $havingRaw = [];
    private $rawHaving = [];
    private $existsHaving = [];
    private $orHaving = [];
    private $orRawHaving = [];
    private $orsHaving = [];
    private $rawOrHaving = [];

    private $order = [];
    private $group = [];
    private $offset;
    private $limit;

    public function reset()
    {
        $this->origin = null;
        $this->sql = false;
        $this->db = null;
        $this->table = null;
        $this->select = [];
        $this->alias = [];
        $this->aliasRaw = [];
        $this->where = [];
        $this->exists = [];
        $this->wheres = [];
        $this->whereRaw = [];
        $this->rawWhere = [];
        $this->or = [];
        $this->ors = [];
        $this->orRaw = [];
        $this->rawOr = [];
        $this->order = [];
        $this->having = [];
        $this->havings = [];
        $this->havingRaw = [];
        $this->rawHaving = [];
        $this->existsHaving = [];
        $this->orHaving = [];
        $this->orRawHaving = [];
        $this->orsHaving = [];
        $this->rawOrHaving = [];
        $this->group = [];
        $this->offset = null;
        $this->limit = null;

        return $this;
    }

    public function setOrigin(Storable $origin)
    {
        $this->origin = $origin;

        return $this;
    }

    public function zero(string $column)
    {
        $this->where[] = [$column, '!=', ''];
        $this->where[] = [$column, '=', 0];

        return $this;
    }

    public function empty(string $column)
    {
        $this->where[] = [$column, '=', ''];

        return $this;
    }

    public function notnull(string $column)
    {
        $this->where[] = [$column, 'IS NOT NULL', null];

        return $this;
    }

    public function null(string $column)
    {
        $this->where[] = [$column, 'IS NULL', null];

        return $this;
    }

    public function not(string $column, $value)
    {
        $this->where[] = [$column, '!=', $value];

        return $this;
    }

    public function inRaw(string $column, $value)
    {
        $this->whereRaw[] = [$column, 'INRAW', $value];

        return $this;
    }

    public function in(string $column, $value)
    {
        $this->where[] = [$column, 'IN', $value];

        return $this;
    }

    public function notin(string $column, $value)
    {
        $this->where[] = [$column, 'NOT IN', $value];

        return $this;
    }

    public function rlike(string $column, $value)
    {
        $value = trim(strval($value));

        $this->where[] = [$column, 'LIKE', "{$value}%"];

        return $this;
    }

    public function orrlike(string $column, $value)
    {
        $value = trim(strval($value));

        $this->or[] = [$column, 'LIKE', "{$value}%"];

        return $this;
    }

    public function llike(string $column, $value)
    {
        $value = trim(strval($value));

        $this->where[] = [$column, 'LIKE', "%{$value}"];

        return $this;
    }

    public function orllike(string $column, $value)
    {
        $value = trim(strval($value));

        $this->or[] = [$column, 'LIKE', "%{$value}"];

        return $this;
    }

    public function orlike(string $column, $value)
    {
        $value = trim(strval($value));

        $this->or[] = [$column, 'LIKE', "%{$value}%"];

        return $this;
    }

    public function like(string $column, $value)
    {
        $value = trim(strval($value));

        $this->where[] = [$column, 'LIKE', "%{$value}%"];

        return $this;
    }

    public function lt(string $column, $value, bool $equal = true)
    {
        $operator = $equal ? '<=' : '<';

        $this->where[] = [$column, $operator, $value];

        return $this;
    }

    public function between(string $column, $start, $end)
    {
        $this->where[] = [$column, 'BETWEEN', [$start, $end]];

        return $this;
    }

    public function gt(string $column, $value, bool $equal = true)
    {
        $operator = $equal ? '>=' : '>';

        $this->where[] = [$column, $operator, $value];

        return $this;
    }

    public function existsHaving(Closure $query)
    {
        $this->existsHaving[] = $query;

        return $this;
    }

    public function exists(Closure $query)
    {
        $this->exists[] = $query;

        return $this;
    }

    public function orsHaving(Closure $query)
    {
        $this->orsHaving[] = $query;

        return $this;
    }

    public function ors(Closure $query)
    {
        $this->ors[] = $query;

        return $this;
    }

    public function wheres(Closure $query)
    {
        $this->wheres[] = $query;

        return $this;
    }

    public function where(string $column, $value, string $operator = '=')
    {
        $this->where[] = [$column, $operator, $value];

        return $this;
    }

    public function whereRaw(string $raw, $value, string $operator = '=')
    {
        $this->whereRaw[] = [$raw, $operator, $value];

        return $this;
    }

    public function rawOrHaving(string $or)
    {
        $this->rawOrHaving[] = $or;

        return $this;
    }

    public function rawOr(string $or)
    {
        $this->rawOr[] = $or;

        return $this;
    }

    public function rawWhere(string $where)
    {
        $this->rawWhere[] = $where;

        return $this;
    }

    public function compare(string $column1, string $column2, string $operator = '=')
    {
        $this->whereRaw[] = [$column1, $operator, collect(['key' => 'column', 'value' => $column2])];

        return $this;
    }

    public function orHaving(string $column, $value, string $operator = '=')
    {
        $this->orHaving[] = [$column, $operator, $value];

        return $this;
    }

    public function or(string $column, $value, string $operator = '=')
    {
        $this->or[] = [$column, $operator, $value];

        return $this;
    }

    public function orRawHaving(string $raw, $value, string $operator = '=')
    {
        $this->orRawHaving[] = [$raw, $operator, $value];

        return $this;
    }

    public function orRaw(string $raw, $value, string $operator = '=')
    {
        $this->orRaw[] = [$raw, $operator, $value];

        return $this;
    }

    /**
     * Alias a timestamp column with custom date format
     *
     * @param string $column: Timestamp column
     * @param string $alias: Alias used to name the expression
     * @param string $format: Format used to convert timestamp to date string
     */
    public function date(string $column, string $alias = null, string $format = null)
    {
        $_format = $format ? ','.$this->origin->quote($format) : '';

        $expression = "FROM_UNIXTIME(`$column`{$_format})";

        $_alias = $alias ?: $column;

        $this->aliasRaw[$_alias] = $expression;

        return $this;
    }

    public function aliasRaw(string $expression, string $alias)
    {
        $this->aliasRaw[$alias] = $expression;

        return $this;
    }

    public function alias(string $column, string $alias)
    {
        $this->alias[$alias] = $column;

        return $this;
    }

    public function asc(string $column)
    {
        $this->order[$column] = 'ASC';

        return $this;
    }

    public function desc(string $column)
    {
        $this->order[$column] = 'DESC';

        return $this;
    }

    public function group(...$fields)
    {
        $this->group = array_unique_merge($this->group, $fields);

        return $this;
    }

    public function havings(Closure $query)
    {
        $this->havings[] = $query;

        return $this;
    }

    public function having(string $column, $value, string $operator = '=')
    {
        $this->having[] = [$column, $operator, $value];

        return $this;
    }

    public function havingRaw(string $raw, $value, string $operator = '=')
    {
        $this->havingRaw[] = [$raw, $operator, $value];

        return $this;
    }

    public function rawHaving(string $having)
    {
        $this->rawHaving[] = $having;

        return $this;
    }

    public function order(string $column, string $sort)
    {
        $this->order[$column] = $sort;

        return $this;
    }

    public function db(string $db)
    {
        $this->db = $db;

        return $this;
    }

    public function table(string $table)
    {
        $this->table = $table;

        return $this;
    }

    public function select(...$columns)
    {
        $this->select = $columns;

        return $this;
    }

    /**
     * Get id list only by given conditions
     */
    public function ids() : array
    {
        $this->select = ['id'];
        $res = $this->get();

        return $res ? array_column($res, 'id') : [];
    }

    public function count() : int
    {
        $this->alias = [];
        $this->aliasRaw = ['total' => 'count(*)'];
        $this->select = ['total'];

        $this->limit = $this->offset = null;

        $res = $this->get();

        return intval($res[0]['total'] ?? 0);
    }

    public function limit(int $limit, int $offset = null)
    {
        if ($offset > 0) {
            $this->offset = $limit;
            $this->limit = $offset;
        } else {
            $this->limit = $limit;
        }

        return $this;
    }

    public function paginate(int $page, int $size)
    {
        $alias = $this->alias;
        $aliasRaw = $this->aliasRaw;
        $select = $this->select;

        $total = $this->count();

        $this->alias = $alias;
        $this->aliasRaw = $aliasRaw;
        $this->select = $select;

        $this->offset = ($page - 1) * $size;
        $this->limit = $size;

        $list = $this->get();

        return $this->sql ? $list : new Paginator($list, [
            'page' => $page,
            'size' => $size,
            'total' => $total,
        ]);
    }

    public function first()
    {
        $this->offset = 0;
        $this->limit = 1;

        $res = $this->get();

        return $res[0] ?? null;
    }

    public function get()
    {
        $params = [];

        $sql = $this->buildSql($params);

        return $this->sql ? $this->generate($sql, $params) : $this->origin->get($sql, $params);
    }

    private function generate(string $sql, array $params) : string
    {
        $sql = $this->origin ? $this->origin->generate($sql) : $sql;

        $placeholders = array_fill(0, count($params), '/\?/');

        array_walk($params, function (&$val) {
            $val = $this->origin
            ? $this->origin->quote((string) $val)
            : "'{$val}'";    // TODO&FIXME
        });

        $idx = 0;
        return $params ? preg_replace_callback('/\?/', function ($matches) use ($params, &$idx) {
            $val = $params[$idx++] ?? null;
            if (! is_null($val)) {
                return $val;
            }
        }, $sql) : $sql;
    }

    public function sql(bool $sql)
    {
        $this->sql = $sql;

        return $this;
    }

    public function buildSql(array &$params) : string
    {
        $selects = '#{COLUMNS}';
        if ($this->select) {
            $selects = '';
            foreach ($this->select as $column) {
                $_column = $this->alias[$column] ?? null;
                if ($_column) {
                    $selects .= "`{$_column}` AS `{$column}`";
                } else {
                    $expression = $this->aliasRaw[$column] ?? null;
                    if ($expression) {
                        $selects .= "{$expression} AS `{$column}`";
                    } else {
                        $selects .= "`{$column}`";
                    }
                }

                if (false !== next($this->select)) {
                    $selects .= ', ';
                }
            }
        } else {
            $selects = '';
            $columns = $this->origin ? $this->origin->getSelectColumns(false) : ['*'];
            foreach ($columns as $column) {
                $selects .= "`{$column}`";
                if (next($columns) !== false) {
                    $selects .= ',';
                }
            }
            if ($this->alias) {
                $selects .= ',';
                foreach ($this->alias as $alias => $column) {
                    $selects .= "`{$column}` AS `{$alias}`";
                    if (next($this->alias) !== false) {
                        $selects .= ',';
                    }
                }
            }
            if ($this->aliasRaw) {
                $selects .= ',';
                foreach ($this->aliasRaw as $alias => $expression) {
                    $selects .= "{$expression} AS `{$alias}`";
                }
                if (next($this->aliasRaw) !== false) {
                    $selects .= ',';
                }
            }
        }

        list($where, $params) = $this->buildWhere();

        $group = '';
        if ($this->group) {
            $group .= ' GROUP BY ';
            foreach ($this->group as $group) {
                $group .= " `{$group}` ";
                if (false !== next($this->group)) {
                    $group .= ',';
                }
            }
        }

        list($having, $_params) = $this->buildHaving();
        foreach ($_params as $_param) {
            array_push($params, $_param);
        }

        $order = '';
        if ($this->order) {
            $order = 'ORDER BY ';
            foreach ($this->order as $by => $sort) {
                $order .= "`{$by}` {$sort}";
                if (next($this->order) !== false) {
                    $order .= ',';
                }
            }
        }

        $limit = '';
        if (is_int($this->limit)) {
            if (is_int($this->offset)) {
                $limit = "LIMIT {$this->offset}, {$this->limit}";
            } else {
                $limit = "LIMIT {$this->limit}";
            }
        }

        $table = $this->table ?: '#{TABLE}';
        if ($this->db) {
            $table = "`{$this->db}`.{$table}";
        }

        $sql = 'SELECT %s FROM %s %s %s %s %s %s';

        return sprintf($sql, $selects, $table, $where, $group, $having, $order, $limit);
    }

    /**
     * Update a single column on a table
     *
     * @param string $column: The column to be updated
     * @param mixed $value: The value to be set in that column
     */
    public function set(string $column, $value)
    {
        list($where, $params) = $this->buildWhere();

        array_unshift($params, $value);

        $table = $this->table ?: '#{TABLE}';

        $sql = 'UPDATE %s SET `%s` = ? %s';
        $sql = sprintf($sql, $table, $column, $where);

        return $this->sql ? $this->generate($sql, $params) : $this->origin->exec($sql, $params);
    }

    public function increment(string $column, int $step = 1)
    {
        list($where, $params) = $this->buildWhere();

        $table = $this->table ?: '#{TABLE}';
        $sql = 'UPDATE %s SET `%s` = (`%s` + %d) %s';
        $sql = sprintf($sql, $table, $column, $column, $step, $where);

        return $this->sql ? $this->generate($sql, $params) : $this->origin->exec($sql, $params);
    }

    public function decrement(string $column, int $step = 1)
    {
        list($where, $params) = $this->buildWhere();

        $table = $this->table ?: '#{TABLE}';
        $sql = 'UPDATE %s SET `%s` = (`%s` - %d) %s';
        $sql = sprintf($sql, $table, $column, $column, $step, $where);

        return $this->sql ? $this->generate($sql, $params) : $this->origin->exec($sql, $params);
    }

    /**
     * Update multiple columns at once
     *
     * @param array $data: The assoc array to be updated
     */
    public function update(array $data)
    {
        if (! $data) {
            return 0;
        }

        list($where, $_params) = $this->buildWhere();

        $columns = $params = [];
        foreach ($data as $key => $val) {
            // Primary key is not allowed to update
            if (ci_equal($key, 'id')) {
                continue;
            }
            if (is_closure($val)) {
                $val = $val();
                if (! is_string($val)) {
                    exception('InvalidUpdateClousureReturnValue', compact('val'));
                }
                $columns[] = "`{$key}` = {$val}";
            } else {
                $columns[] = "`{$key}` = ?";
                $params[] = $val;
            }
        }

        foreach ($_params as $param) {
            array_push($params, $param);
        }
        if (! $params) {
            return 0;
        }

        $table = $this->table ?: '#{TABLE}';

        $columns = join(', ', $columns);

        $sql = 'UPDATE %s SET %s %s ';
        $sql = sprintf($sql, $table, $columns, $where);

        return $this->sql ? $this->generate($sql, $params) : $this->origin->exec($sql, $params);
    }

    public function delete()
    {
        list($where, $params) = $this->buildWhere();

        $table = $this->table ?: '#{TABLE}';

        $sql = 'DELETE FROM %s %s';
        $sql = sprintf($sql, $table, $where);

        return $this->sql ? $this->generate($sql, $params) : $this->origin->exec($sql, $params);
    }

    /**
     * Add on record from storage class annotations
     */
    public function add(array $data) : int
    {
        $annotations= $this->origin->annotations();
        $columns = array_keys($data);
        $values = array_values($data);
        $count = count($values);
        $_values = join(',', array_fill(0, $count, '?'));

        $columns = join(',', array_map(function ($column) {
            return "`{$column}`";
        }, $columns));

        $table = $this->table ?: '#{TABLE}';

        $sql = "INSERT INTO %s (%s) VALUES (%s)";
        $sql = sprintf($sql, $table, $columns, $_values);

        return $this->sql ? $this->generate($sql, $values) : $this->origin->insert($sql, $values);
    }

    public function insert(array $list)
    {
        if (! $list) {
            return 0;
        }

        if (! is_index_array($list)) {
            exception('InvalidInsertValues', ['Non-Index Array']);
        }

        $first = $list[0] ?? [];
        ksort($first);
        $first = array_keys($first);
        $columns = join(',', array_map(function ($column) {
            return "`{$column}`";
        }, $first));
        $values = join(',', array_fill(0, count($first), '?'));
        $_values = '';
        $params = [];

        foreach ($list as $idx => $item) {
            ksort($item);
            $_params = array_values($item);
            foreach ($_params as $param) {
                array_push($params, $param);
            }

            if (array_keys($item) !== $first) {
                exception('InvalidInsertRows', [
                    'err' => 'Insert columns not match against the first one',
                    'num' => $idx,
                    'first' => $first,
                    'invalid' => $item,
                ]);
            }

            $_values .= "({$values})";
            if (false !== next($list)) {
                $_values .= ',';
            }
        }

        $table = $this->table ?: '#{TABLE}';

        $sql = "INSERT INTO %s (%s) VALUES %s";
        $sql = sprintf($sql, $table, $columns, $_values);

        return $this->sql ? $this->generate($sql, $params) : $this->origin->exec($sql, $params);
    }

    private function checkHavingKeyword(string $having, string $exists)
    {
        $having = trim($having);

        return ($having ? ci_equal(mb_strcut($having, 0, 7), 'having ') : false)
            ? $exists : ' HAVING ';
    }

    private function checkWhereKeyword(string $where, string $exists)
    {
        $where = trim($where);

        return ($where ? ci_equal(mb_strcut($where, 0, 6), 'where ') : false)
            ? $exists : ' WHERE ';
    }

    private function ltrimAndOr(string &$expr, string $type) : string
    {
        $expr = trim($expr);
        if (! $expr) {
            return '';
        }

        if (ci_equal(mb_strcut($expr, 0, 4), 'and ')) {
            $expr = mb_substr($expr, 3);
        } elseif (ci_equal(mb_strcut($expr, 0, 3), 'or ')) {
            $expr = mb_substr($expr, 2);
        }

        return $type;
    }

    private function buildHaving(bool $asGroup = false) : array
    {
        $having = '';
        $params = [];

        if ($this->having) {
            $having .= $asGroup ? $this->ltrimAndOr($having, ' AND ') : ' HAVING ';
            foreach ($this->having as list($column, $operator, $val)) {
                $having .= $this->__buildWhere($column, $operator, $val, $params, false);
                if (false !== next($this->having)) {
                    $having .= ' AND ';
                }
            }
        }
        if ($this->havingRaw) {
            $having .= $asGroup ? $this->ltrimAndOr($having, ' AND ') : $this->checkHavingKeyword($having, ' AND ');
            foreach ($this->havingRaw as list($expression, $operator, $val)) {
                $having .= $this->__buildWhere($expression, $operator, $val, $params, true);
                if (false !== next($this->havingRaw)) {
                    $having .= ' AND ';
                }
            }
        }
        if ($this->havings) {
            $having .= $asGroup ? $this->ltrimAndOr($having, ' AND ') : $this->checkHavingKeyword($having, ' AND ');
            foreach ($this->havings as $query) {
                $builder = new self;
                $query($builder);
                list($_having, $_params) = $builder->buildHaving(true);

                $having .= "({$_having})";
                foreach ($_params as $param) {
                    array_push($params, $param);
                }
                if (false !== next($this->havings)) {
                    $having .= ' AND ';
                }
            }
        }
        if ($this->existsHaving) {
            $having .= $asGroup ? $this->ltrimAndOr($having, ' AND ') : $this->checkHavingKeyword($having, ' AND ');
            foreach ($this->exists as $query) {
                $builder = new self;
                $_params = [];
                $query($builder);
                $sql = $builder->buildSql($_params);
                $having .= " EXISTS ({$sql})";
                foreach ($_params as $param) {
                    array_push($params, $param);
                }
                if (false !== next($this->exists)) {
                    $having .= ' AND ';
                }
            }
        }
        if ($this->rawHaving) {
            $having .= $asGroup ? $this->ltrimAndOr($having, ' AND ') : $this->checkHavingKeyword($having, ' AND ');

            foreach ($this->rawHaving as $rawHaving) {
                $having .= "({$rawHaving})";
                if (false !== next($this->rawHaving)) {
                    $having .= ' AND ';
                }
            }
        }
        if ($this->orHaving) {
            $having .= $asGroup ? $this->ltrimAndOr($having, ' OR ') : $this->checkHavingKeyword($having, ' OR ');
            foreach ($this->orHaving as list($column, $operator, $val)) {
                $having .= $this->__buildWhere($column, $operator, $val, $params, false);
                if (false !== next($this->orHaving)) {
                    $having .= ' OR ';
                }
            }
        }
        if ($this->orRawHaving) {
            $having .= $asGroup ? $this->ltrimAndOr($having, ' OR ') : $this->checkHavingKeyword($having, ' OR ');
            foreach ($this->orRawHaving as list($expression, $operator, $val)) {
                $having .= $this->__buildWhere($expression, $operator, $val, $params, true);
                if (false !== next($this->orRawHaving)) {
                    $having .= ' OR ';
                }
            }
        }
        if ($this->orsHaving) {
            $having .= $asGroup ? $this->ltrimAndOr($having, ' OR ') : $this->checkHavingKeyword($having, ' OR ');
            foreach ($this->orsHaving as $query) {
                $builder = new self;
                $query($builder);
                list($_having, $_params) = $builder->buildHaving(true);

                $having .= "({$_having})";
                foreach ($_params as $param) {
                    array_push($params, $param);
                }
                if (false !== next($htis->orsHaving)) {
                    $having .= ' OR ';
                }
            }
        }
        if ($this->rawOrHaving) {
            $having .= $asGroup ? $this->ltrimAndOr($having, ' OR ') : $this->checkHavingKeyword($having, ' OR ');

            foreach ($this->rawOrHaving as $rawOr) {
                $having .= "({$rawOr})";
                if (false !== next($this->rawOrHaving)) {
                    $having .= ' OR ';
                }
            }
        }

        return [$having, $params];
    }

    /**
     * Build where condition part of sql
     *
     * @param bool $asGroup: Whether build where conditions only rather than the whole sql
     */
    private function buildWhere(bool $asGroup = false) : array
    {
        $where = '';
        $params = [];

        if ($this->where) {
            $where .= $asGroup ? $this->ltrimAndOr($where, ' AND ') : ' WHERE ';
            foreach ($this->where as list($column, $operator, $val)) {
                $where .= $this->__buildWhere($column, $operator, $val, $params, false);
                if (false !== next($this->where)) {
                    $where .= ' AND ';
                }
            }
        }
        if ($this->whereRaw) {
            $where .= $asGroup ? $this->ltrimAndOr($where, ' AND ') : $this->checkWhereKeyword($where, ' AND ');
            foreach ($this->whereRaw as list($expression, $operator, $val)) {
                $where .= $this->__buildWhere($expression, $operator, $val, $params, true);
                if (false !== next($this->whereRaw)) {
                    $where .= ' AND ';
                }
            }
        }
        if ($this->wheres) {
            $where .= $asGroup ? $this->ltrimAndOr($where, ' AND ') : $this->checkWhereKeyword($where, ' AND ');
            foreach ($this->wheres as $query) {
                $builder = new self;
                $query($builder);
                list($_where, $_params) = $builder->buildWhere(true);

                $where .= "({$_where})";
                foreach ($_params as $param) {
                    array_push($params, $param);
                }
                if (false !== next($this->wheres)) {
                    $where .= ' AND ';
                }
            }
        }
        if ($this->exists) {
            $where .= $asGroup ? $this->ltrimAndOr($where, ' AND ') : $this->checkWhereKeyword($where, ' AND ');
            foreach ($this->exists as $query) {
                $builder = new self;
                $_params = [];
                $query($builder);
                $sql = $builder->buildSql($_params);
                $where .= " EXISTS ({$sql})";
                foreach ($_params as $param) {
                    array_push($params, $param);
                }
                if (false !== next($this->exists)) {
                    $where .= ' AND ';
                }
            }
        }
        if ($this->rawWhere) {
            $where .= $asGroup ? $this->ltrimAndOr($where, ' AND ') : $this->checkWhereKeyword($where, ' AND ');

            foreach ($this->rawWhere as $rawWhere) {
                $where .= "({$rawWhere})";
                if (false !== next($this->rawWhere)) {
                    $where .= ' AND ';
                }
            }
        }
        if ($this->or) {
            $where .= $asGroup ? $this->ltrimAndOr($where, ' OR ') : $this->checkWhereKeyword($where, ' OR ');
            foreach ($this->or as list($column, $operator, $val)) {
                $where .= $this->__buildWhere($column, $operator, $val, $params, false);
                if (false !== next($this->or)) {
                    $where .= ' OR ';
                }
            }
        }
        if ($this->orRaw) {
            $where .= $asGroup ? $this->ltrimAndOr($where, ' OR ') : $this->checkWhereKeyword($where, ' OR ');
            foreach ($this->orRaw as list($expression, $operator, $val)) {
                $where .= $this->__buildWhere($expression, $operator, $val, $params, true);
                if (false !== next($this->orRaw)) {
                    $where .= ' OR ';
                }
            }
        }
        if ($this->ors) {
            $where .= $asGroup ? $this->ltrimAndOr($where, ' OR ') : $this->checkWhereKeyword($where, ' OR ');
            foreach ($this->ors as $query) {
                $builder = new self;
                $query($builder);
                list($_where, $_params) = $builder->buildWhere(true);

                $where .= "({$_where})";
                foreach ($_params as $param) {
                    array_push($params, $param);
                }
                if (false !== next($this->ors)) {
                    $where .= ' OR ';
                }
            }
        }
        if ($this->rawOr) {
            $where .= $asGroup ? $this->ltrimAndOr($where, ' OR ') : $this->checkWhereKeyword($where, ' OR ');

            foreach ($this->rawOr as $rawOr) {
                $where .= "({$rawOr})";
                if (false !== next($this->rawOr)) {
                    $where .= ' OR ';
                }
            }
        }

        return [$where, $params];
    }

    private function __buildWhere(
        string $column,
        string $operator,
        $val,
        &$params,
        bool $expression = false
    ) : string {
        $operator = trim($operator);
        $placeholder = '?';
        if (! $expression) {
            $column = "`{$column}`";
        }

        if (ciin($operator, ['in', 'not in'])) {
            $placeholder = '(?)';
            if (is_array($val) || is_string($val)) {
                $val = is_string($val) ? array_trim_from_string($val, ',') : $val;
                $placeholder = '('.join(',', array_fill(0, count($val), '?')).')';
                foreach ($val as $v) {
                    array_push($params, $v);
                }
            } elseif (is_closure($val)) {
                $builder = new self;
                $_params = [];
                $val($builder);
                $sql = $builder->buildSql($_params);
                $placeholder = "({$sql})";
                foreach ($_params as $param) {
                    array_push($params, $param);
                }
            } else {
                $params[] = (array) $val;
            }
        } elseif (ciin($operator, ['is not null', 'is null'])) {
            $placeholder = '';
        // No params need when null conditions
        } elseif (ci_equal($operator, 'inraw')) {
            $column = "`{$column}`";
            $operator = 'IN';
            $placeholder = "({$val})";
        } elseif (ciin($operator, ['between', 'not between'])) {
            list($start, $end) = $val;

            $operator = strtoupper($operator);

            return "({$column} {$operator} {$start} AND {$end})";
        } elseif (is_collection($val)) {
            $column = "`{$column}`";
            $type = $val->key ?? null;
            if ($type) {
                switch (strtolower($type)) {
                    case 'column':
                    default:
                        $_column = $val->value;
                        $placeholder = "`{$_column}`";
                        break;
                }
            }
        } else {
            $params[] = $val;
        }

        return "{$column} {$operator} {$placeholder}";
    }
}
