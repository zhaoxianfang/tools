<?php

namespace zxf\Database\Generator;

use Closure;
use Exception;
use InvalidArgumentException;
use JetBrains\PhpStorm\NoReturn;

/**
 * 数据库查询构建器
 * Class DbSqlBuild
 *
 * @package zxf\Database\Generator
 */
class SqlGenerator
{
    // 数据库驱动 eg: mysql、pgsql、sqlite、sqlserver、oracle
    protected string $driver = 'mysql';

    private const OPERATORS = [
        '=', '<>', '!=', '<', '>', '<=', '>=', 'LIKE', 'IN', 'NOT IN', 'BETWEEN', 'IS NULL', 'IS NOT NULL',
    ];

    private const LOGICAL_OPERATORS = ['AND', 'OR'];

    private const JOIN_TYPES = ['INNER', 'LEFT', 'RIGHT', 'FULL'];

    // 主查询表相关属性
    private string $tableName  = '';
    private string $tableAlias = '';

    // SELECT 查询相关属性
    private array  $selectFields     = [];
    private array  $whereConditions  = [];
    private array  $joinClauses      = [];
    private array  $joinOnClauses    = [];
    private array  $groupByFields    = [];
    private array  $havingConditions = [];
    private array  $orderByFields    = [];
    private string $limit            = '';
    private array  $bindings         = [];

    // 构建查询的类型
    private string $buildType = 'select'; // select、insert、update、delete、exists、not_exists

    // INSERT、UPDATE 需要插入或更新的数据
    private array $changeData = [];
    // UPSERT 需要判断是插入还是更新的列
    private array $upsertColumns = [];

    // 是否将绑定参数转换为问号参数
    protected bool $convertBindParamsToQuestionMarks = true;

    // sql 生成器 buildQuery() 操作之后的sql语句，如果存在，则不再重新构建
    private string $buildAfterSql = '';

    /**
     * 设置返回类对象
     *
     * @var object|null
     */
    private object|null $base = null;

    public function __construct($base = null)
    {
        $this->base = !empty($base) && is_object($base) ? $base : $this;
    }

    /**
     * 设置主查询表名和表别名
     *
     * @param string      $name         主表名
     * @param string|null $dynamicAlias 动态生成的表别名（可选）
     *
     * @return $this->base
     */
    public function table(string $name, string $dynamicAlias = null)
    {
        $this->reset();
        $this->buildType  = 'select';
        $this->tableName  = $name;
        $this->tableAlias = is_null($dynamicAlias) ? '' : $dynamicAlias;
        return $this->base;
    }

    public function from(string $name, string $dynamicAlias = null)
    {
        return $this->table($name, $dynamicAlias);
    }

    /**
     * 设置查询字段
     *
     * @param array $fields 查询字段数组
     *
     * @return $this->base
     */
    public function select(array $fields = ['*'])
    {
        $this->buildType    = 'select';
        $this->selectFields = empty($this->selectFields) ? $fields : array_merge($this->selectFields, $fields);
        return $this->base;
    }

    // ==================================================================
    // 连表查询 join 开始
    // ==================================================================


    /**
     * 添加 JOIN 子句
     *
     * @param string $table       关联表名
     * @param string $onCondition 关联条件
     * @param string $joinType    JOIN 类型（INNER、LEFT、RIGHT 等）
     * @param string $alias       表别名（可选）
     *
     * @return $this->base
     */
    public function join(string $table, string $onCondition, string $joinType = 'INNER', string $alias = '')
    {
        $this->validateJoinType($joinType);
        $this->joinClauses[] = "$joinType JOIN $table" . ($alias ? " AS $alias" : '') . " ON $onCondition";
        return $this->base;
    }

    /**
     * 添加 LEFT JOIN 子句
     *
     * @param string $table       关联表名
     * @param string $onCondition 关联条件
     * @param string $alias       表别名（可选）
     *
     * @return $this->base
     */
    public function leftJoin(string $table, string $onCondition, string $alias = '')
    {
        return $this->join($table, $onCondition, 'LEFT', $alias);
    }

    /**
     * 添加 RIGHT JOIN 子句
     *
     * @param string $table       关联表名
     * @param string $onCondition 关联条件
     * @param string $alias       表别名（可选）
     *
     * @return $this->base
     */
    public function rightJoin(string $table, string $onCondition, string $alias = '')
    {
        return $this->join($table, $onCondition, 'RIGHT', $alias);
    }

    /**
     * 添加 JOIN 子查询条件
     *
     * @param self              $query
     * @param                   $as
     * @param callable          $callback 回调函数，用于构建 JOIN 子查询条件
     * @param string            $joinType JOIN 类型（INNER、LEFT、RIGHT 等）
     *
     * @return $this->base
     */
    public function joinSub(self $query, $as, callable $callback, string $joinType = 'INNER')
    {
        $this->validateJoinType($joinType);
        // 创建子查询实例
        $subQueryBuilder = new self();
        // 子查询绑定参数独立
        $subQueryBuilder->bindings = [];
        // 执行回调构建子查询条件
        $callback($subQueryBuilder);

        $this->bindings = array_merge($this->bindings, $query->getBindings());

        $this->joinClauses[] = strtoupper($joinType) . ' JOIN (' . $query->buildQuery() . ') AS ' . $as . $subQueryBuilder->getJoinOnClauses();

        $this->whereConditions = array_merge($this->whereConditions, $subQueryBuilder->getWhereConditions());
        $this->bindings        = array_merge($this->bindings, $subQueryBuilder->getBindings());

        return $this->base;
    }

    /**
     * @param self              $query
     * @param                   $as
     * @param callable          $callback
     *
     * @return $this->base
     */
    public function leftJoinSub(self $query, $as, callable $callback)
    {
        // 执行回调构建 JOIN 子查询条件
        $callback($query);

        $this->joinClauses[] = 'LEFT JOIN (' . $query->buildQuery() . ') AS ' . $as . $query->getJoinOnClauses();
        $this->bindings      = array_merge($this->bindings, $query->getBindings());

        return $this->base;
    }

    public function rightJoinSub(self $query, $as, callable $callback)
    {
        // 执行回调构建 JOIN 子查询条件
        $callback($query);

        $this->joinClauses[] = 'RIGHT JOIN (' . $query->buildQuery() . ') AS ' . $as . $query->getJoinOnClauses();
        $this->bindings      = array_merge($this->bindings, $query->getBindings());

        return $this->base;
    }

    /**
     * 添加 JOIN ON 子句
     *
     * @param string $joinCondition JOIN ON 条件
     *
     * @return $this->base
     */
    public function on(string $joinCondition)
    {
        $this->joinOnClauses[] = $joinCondition;
        return $this->base;
    }

    /**
     * 获取join on 的条件
     */
    public function getJoinOnClauses(): string
    {
        if (empty($this->joinOnClauses)) {
            return '';
        }
        return ' ON ' . implode(' AND ', $this->joinOnClauses);
    }

    /**
     * 验证连接类型的有效性
     *
     * @param string $joinType 连接类型
     *
     * @throws InvalidArgumentException
     */
    private function validateJoinType(string $joinType): void
    {
        if (!in_array(strtoupper($joinType), self::JOIN_TYPES)) {
            throw new InvalidArgumentException('Invalid join type: ' . $joinType);
        }
    }

    // ==================================================================
    // 连表查询 join 结束
    // ==================================================================

    // ==================================================================
    // 操作条件 where 开始
    // ==================================================================

    /**
     * 添加 WHERE 条件
     * eg: ->where('id', '=', 1)
     *      ->where('id', '>', 1)
     *      ->where('id',1)
     *      ->where('status','IS NOT NULL')
     *      ->where(function ($query) {
     *          $query->where('u.status',  1)
     *          $query->orWhere('u.cover', 'LIKE', '%weisifang%');
     *      })
     *
     * @param Closure|string|array $column          字段名或闭包
     * @param mixed                $operator        操作符
     * @param mixed                $value           值
     * @param string               $logicalOperator 逻辑运算符（AND 或 OR）
     * @param bool                 $isRow           是否为原生查询  是原生查询的，不需要 调用 addBinding() 方法，因为 $value 是一个字段名
     *
     * @return $this->base
     * @throws InvalidArgumentException
     */
    public function where(Closure|string|array $column, mixed $operator = null, mixed $value = null, string $logicalOperator = 'AND', bool $isRow = false)
    {
        if ($column instanceof Closure && is_null($operator)) {
            return $this->whereClosure($column, 'AND');
        }

        if (in_array($operator, ['IN', 'NOT IN'])) {
            $operator = $operator . ' (';
        }

        // 如果$value为空，$operator不为空 ,说明传入的是两个参数含义是字段和值
        if (is_string($column) && is_null($value) && !empty($operator) && ($operator != 'IS NULL' || $operator != 'IS NOT NULL')) {
            // 判断$operator是否为数字、字符串、布尔值
            // eg: where('id',1)、where('age',18)
            if (is_numeric($operator) || is_string($operator) || is_bool($operator)) {
                $value    = $operator;
                $operator = '=';
            }
        }
        $this->validateOperator($operator);

        $isJoinField = false;
        // 判断 $value 是否包含 ` 或 . 符号，如果是，则表示是一个「关联字段查询」
        // 是关联字段查询的，不需要 调用 addBinding() 方法，因为 $value 是一个字段名
        if ($isRow || is_string($value) && (str_contains($value, '`') || str_contains($value, '.'))) {
            $placeholder = $value;
            $isJoinField = true;
        } else {
            $placeholder = $this->generatePlaceholder();
        }

        $this->whereConditions[] = [
            'field'           => $column,
            'operator'        => $operator,
            'value'           => $placeholder,
            'logicalOperator' => $logicalOperator,
        ];
        if (!$isJoinField) {
            $this->addBinding($placeholder, $value);
        }

        return $this->base;
    }

    /**
     * 原生字段查询，不需要解析绑定参数
     *
     * @param Closure|string|array $column
     * @param mixed|null           $operator
     * @param mixed|null           $value
     *
     * @return $this->base
     */
    public function whereRaw(Closure|string|array $column, mixed $operator = null, mixed $value = null)
    {
        return $this->where($column, $operator, $value, 'AND', true);
    }

    /**
     * Or 原生字段查询，不需要解析绑定参数
     *
     * @param Closure|string|array $column
     * @param mixed|null           $operator
     * @param mixed|null           $value
     *
     * @return $this->base
     */
    public function orWhereRaw(Closure|string|array $column, mixed $operator = null, mixed $value = null)
    {
        return $this->where($column, $operator, $value, 'OR', true);
    }

    /**
     * 添加 OR 查询条件
     *
     * @param Closure|string|array $column   字段名或闭包
     * @param mixed                $operator 操作符
     * @param mixed                $value    值
     *
     * @return $this->base
     * @throws InvalidArgumentException
     */
    public function orWhere(Closure|string|array $column, mixed $operator = null, mixed $value = null)
    {
        return $this->where($column, $operator, $value, 'OR');
    }

    /**
     * 字段列比较
     *  eg: ->whereColumn('id', '=', 'uid')
     *      ->whereColumn('id', '!=', 'uid')
     *      ->whereColumn('id', 'uid') // 等价于 ->whereColumn('id', '=', 'uid')
     *
     * @param string      $columnFirst     第一个字段
     * @param string      $operator        操作符号
     * @param string|null $columnSecond    第二个字段
     * @param string      $logicalOperator 连接逻辑运算符
     *
     * @return $this->base
     */
    public function whereColumn(string $columnFirst, string $operator, string $columnSecond = null, string $logicalOperator = 'AND')
    {
        if (empty($columnSecond)) {
            $columnSecond = $operator;
            $operator     = '=';
        }
        $this->validateOperator($operator);
        $this->whereConditions[] = [
            'field'           => $columnFirst,
            'operator'        => $operator,
            'value'           => $columnSecond,
            'logicalOperator' => $logicalOperator,
        ];
        return $this->base;
    }

    public function orWhereColumn(string $columnFirst, string $operator, string $columnSecond = null)
    {
        return $this->whereColumn($columnFirst, $operator, $columnSecond, 'OR');
    }

    /**
     * 添加包裹的查询条件
     *
     * @param callable $callback        回调函数，用于构建包裹的查询条件
     * @param string   $logicalOperator 逻辑运算符（AND 或 OR）
     * @param string   $extend          延伸操作，一般操作置空，若需要 EXISTS 等操作，可传入，形成子查询，eg: (...) 或者 EXISTS (...)
     *
     * @return $this->base
     */
    private function whereClosure(callable $callback, string $logicalOperator = 'AND', string $extend = '')
    {
        $this->whereConditions[] = [
            'field'           => '',
            'operator'        => '',
            'value'           => (empty($extend) ? '' : $extend . ' ') . '(',
            'logicalOperator' => $logicalOperator,
        ];

        // 创建子查询实例
        $subQueryBuilder = new self();
        // 子查询绑定参数独立
        $subQueryBuilder->bindings = [];
        // 执行回调构建子查询条件
        $callback($subQueryBuilder);
        // 将子查询的绑定参数合并到当前实例

        $subWhereConditions = $subQueryBuilder->whereConditions;
        if (!empty($subWhereConditions)) {
            $subWhereConditions[0]['logicalOperator'] = '';
            $this->whereConditions                    = array_merge($this->whereConditions, $subWhereConditions);

            $this->bindings = array_merge($this->bindings, $subQueryBuilder->getBindings());
        }

        $this->whereConditions[] = [
            'field'           => '',
            'operator'        => '',
            'value'           => ')',
            'logicalOperator' => '',
        ];

        return $this->base;
    }

    public function whereExists(Closure $callback, string $logicalOperator = 'AND')
    {
        $this->whereClosure($callback, $logicalOperator, 'EXISTS');
        return $this->base;
    }

    public function whereNotExists(Closure $callback, string $logicalOperator = 'AND')
    {
        $this->whereClosure($callback, $logicalOperator, 'NOT EXISTS');
        return $this->base;
    }

    /**
     * @param Closure|string|array $column
     * @param array                $value
     * @param string               $operator
     * @param string               $logicalOperator
     *
     * @return $this->base
     */
    public function whereIn(Closure|string|array $column, array $value = [], string $operator = 'IN', string $logicalOperator = 'AND')
    {
        $this->where($column, $operator, $value, $logicalOperator);
        $this->whereConditions[] = [
            'field'           => '',
            'operator'        => '',
            'value'           => ')',
            'logicalOperator' => '',
        ];

        return $this->base;
    }

    /**
     * @param Closure|string|array $column
     * @param array                $value
     *
     * @return $this->base
     */
    public function whereNotIn(Closure|string|array $column, array $value = [])
    {
        return $this->whereIn($column, $value, 'NOT IN');
    }

    /**
     * @param Closure|string|array $column
     * @param array                $value
     *
     * @return $this->base
     */
    public function orWhereIn(Closure|string|array $column, array $value = [])
    {
        return $this->orWhere($column, 'IN', $value);
    }

    public function whereBetween(Closure|string|array $column, array $value = [])
    {
        if (count($value) != 2) {
            throw new InvalidArgumentException('The value of the between condition must be an array of two elements');
        }
        return $this->where($column, 'BETWEEN', implode(' AND ', $value), 'AND');
    }

    public function orWhereBetween(Closure|string|array $column, array $value = [])
    {
        if (count($value) != 2) {
            throw new InvalidArgumentException('The value of the between condition must be an array of two elements');
        }
        return $this->orWhere($column, 'BETWEEN', implode(' AND ', $value));
    }

    public function whereNotBetween(Closure|string|array $column, array $value = [])
    {
        if (count($value) != 2) {
            throw new InvalidArgumentException('The value of the between condition must be an array of two elements');
        }
        return $this->where($column, 'BETWEEN', implode(' AND ', $value), 'OR');
    }

    public function orWhereNotBetween(Closure|string|array $column, array $value = [])
    {
        if (count($value) != 2) {
            throw new InvalidArgumentException('The value of the between condition must be an array of two elements');
        }
        return $this->orWhere($column, 'NOT BETWEEN', implode(' AND ', $value));
    }

    public function whereNull(string $column)
    {
        return $this->where($column, 'IS NULL');
    }

    public function whereNotNull(string $column)
    {
        return $this->where($column, 'IS NOT NULL');
    }

    /**
     * 添加全文搜索条件
     *
     * @param array  $columns 全文搜索的字段数组
     * @param string $value   搜索的值 eg: 'weisifang'
     * @param string $mode    搜索模式BOOLEAN、NATURAL、QUERY（可选，默认为 boolean 模式）
     *
     * @return $this->base
     */
    public function whereFullText(array $columns = [], string $value = '', string $mode = 'BOOLEAN', string $logicalOperator = 'AND')
    {
        $modeMap = [
            // 布尔模式 支持 +必须包含, -必须不包含, *通配符 等 ；eg: '+weisifang -zhangsan'
            'BOOLEAN' => 'IN BOOLEAN MODE',
            // 自然语言模式
            'NATURAL' => 'IN NATURAL LANGUAGE MODE',
            // 查询扩展模式
            'QUERY'   => 'WITH QUERY EXPANSION',
        ];
        if (!isset($modeMap[$mode])) {
            $mode = 'BOOLEAN';
        }
        return $this->where('match (' . implode(' , ', $columns) . ')', 'AGAINST', " ($value $modeMap[$mode])", $logicalOperator);
    }

    public function orWhereFullText(array $columns = [], string $value = '', string $mode = 'BOOLEAN')
    {
        return $this->whereFullText($columns, $value, $mode, 'OR');
    }

    /**
     * 获取所有绑定参数
     *
     * @return array 所有绑定参数
     */
    public function getWhereConditions(): array
    {
        return $this->whereConditions;
    }

    // ==================================================================
    // 操作条件 where 结束
    // ==================================================================


    /**
     * 添加 GROUP BY 子句
     *
     * @param mixed ...$fields GROUP BY 字段 eg: ->groupBy('id', 'name')、 ->groupBy('id')
     *
     * @return $this->base
     */
    public function groupBy(mixed ...$fields)
    {
        $this->groupByFields = empty($this->groupByFields) ? $fields : array_merge($this->groupByFields, $fields);
        return $this->base;
    }

    /**
     * 添加聚合条件
     *
     * @param string $field    字段名
     * @param string $operator 聚合操作符
     * @param mixed  $value
     * @param string $logicalOperator
     *
     * @return $this->base
     */
    public function having(string $field, string $operator, mixed $value = '', string $logicalOperator = 'AND')
    {
        $this->validateOperator($operator);
        $this->havingConditions[] = [
            'field'           => $field,
            'operator'        => strtoupper($operator),
            'value'           => $value,
            'logicalOperator' => $logicalOperator,
        ];
        return $this->base;
    }

    /**
     * 添加 OR 聚合条件
     *
     * @param string $field    字段名
     * @param string $operator 聚合操作符
     * @param mixed  $value
     *
     * @return $this->base
     * @throws InvalidArgumentException
     */
    public function orHaving(string $field, string $operator, mixed $value = '')
    {
        return $this->having($field, $operator, $value, 'OR');
    }

    /**
     * 添加 ORDER BY 子句
     * eg: ->orderBy('id', 'ASC')、->orderBy('id', 'DESC')
     *     ->orderBy(['id ASC', 'name DESC'])
     *
     * @param array|string $column
     * @param string       $orderType
     *
     * @return $this->base
     */
    public function orderBy(array|string $column, string $orderType = 'ASC')
    {
        if (is_array($column)) {
            $this->orderByFields = empty($this->orderByFields) ? $column : array_merge($this->orderByFields, $column);
        } else {
            $this->orderByFields[] = $column . ' ' . $orderType;
        }
        return $this->base;
    }

    /**
     * 设置 LIMIT 子句
     *
     * @param int      $offset 偏移量
     * @param int|null $count  结果数量
     *
     * @return $this->base
     */
    public function limit(int $offset = 0, int|null $count = null)
    {
        if (is_null($count)) {
            $this->limit = "LIMIT $offset";
        } else {
            $this->limit = "LIMIT $offset, $count";
        }
        return $this->base;
    }


    /**
     * 构建查询语句
     *
     * @return string 查询语句
     */
    public function buildQuery(): string
    {
        if (empty($this->buildAfterSql)) {
            $query               = match ($this->buildType) {
                'insert'             => $this->buildInsertQuery(),
                'update'             => $this->buildUpdateQuery(),
                'upsert'             => $this->buildUpsertQuery(),
                'delete'             => $this->buildDeleteQuery(),
                'truncate'           => $this->buildTruncateQuery(),
                'min_auto_increment' => $this->buildSetTableMinAutoIncrementQuery(),
                'exists'             => $this->buildExistsQuery(),
                'not_exists'         => $this->buildDoesntExistsQuery(),
                default              => $this->buildSelectQuery(),
            };
            $this->buildAfterSql = $this->convertBindParamsToQuestionMarks ? $this->paramsToQuestionMarks($query) : $query;
        }
        return $this->buildAfterSql;
    }

    /**
     * 把绑定参数转换为问号参数
     *
     * @return string 绑定参数
     */
    private function paramsToQuestionMarks(string $sql): string
    {
        // 正则找出$sql 中的所有 :param_ 开头的一字符串，然后 查找$this->bindings中对应的值按顺序存储在$newBindParams中，并按照顺序替换成问号
        $sqlString = preg_replace_callback('/:param_[a-zA-Z0-9_]+/', function ($matches) {
            $this->bindings[] = $this->bindings[$matches[0]];
            return '?';
        }, $sql);

        // 遍历$this->bindings，如果键名是字符串，就删除此键名
        foreach ($this->bindings as $key => $value) {
            if (is_string($key)) {
                unset($this->bindings[$key]);
            }
        }
        return $sqlString;
    }

    /**
     * 构建 SELECT 查询语句
     *
     * @return string SELECT 查询语句
     */
    private function buildSelectQuery(): string
    {
        // 构建 SELECT 查询语句
        $query = "SELECT " . implode(', ', (!empty($this->selectFields) ? $this->selectFields : ['*'])) . " FROM $this->tableName" . (empty($this->tableAlias) ? '' : " AS $this->tableAlias");

        // 构建 JOIN 子句
        if (!empty($this->joinClauses)) {
            $query .= " " . implode(' ', $this->joinClauses);
        }

        // 构建 WHERE 子句
        if (!empty($this->whereConditions)) {
            $query .= " WHERE " . $this->buildConditions($this->whereConditions);
        }

        // 构建 GROUP BY 子句
        if (!empty($this->groupByFields)) {
            $query .= " GROUP BY " . implode(', ', $this->groupByFields);
        }

        // 构建 HAVING 子句
        if (!empty($this->havingConditions)) {
            $query .= " HAVING " . $this->buildConditions($this->havingConditions);
        }

        // 构建 ORDER BY 子句
        if (!empty($this->orderByFields)) {
            $query .= " ORDER BY " . implode(', ', $this->orderByFields);
        }

        // 构建 LIMIT 子句
        if (!empty($this->limit)) {
            $query .= " $this->limit";
        }
        return $query;
    }


    /**
     * 构建 INSERT 插入语句
     *
     * @return string 插入语句
     */
    private function buildInsertQuery(): string
    {
        if (empty($this->changeData)) {
            throw new InvalidArgumentException('Insert values is empty');
        }

        $valueStrings = [];
        // 判断$this->changeData的第一个元素的键是数字还是字符串
        // 如果是数字，说明是多条插入
        // 如果是字符串，说明是单条插入
        $this->changeData = is_string(array_key_first($this->changeData)) ? [$this->changeData] : $this->changeData;

        // 取出每一条插入的值
        $columnsNames = array_keys($this->changeData[0]);

        $query = "INSERT INTO $this->tableName (" . implode(', ', $columnsNames) . ") VALUES ";

        foreach ($this->changeData as $rows) {
            $placeholders = [];
            foreach ($rows as $field => $value) {
                // 只处理插入的字段名称在第一条数据中存在的字段
                if (in_array($field, $columnsNames)) {
                    $placeholder = $this->generatePlaceholder();
                    $this->addBinding($placeholder, $value);
                    $placeholders[] = $placeholder;
                }
            }

            $valueStrings[] = "(" . implode(', ', $placeholders) . ")";
        }

        $query .= implode(', ', $valueStrings);

        return $query;
    }

    /**
     * 构建 UPDATE 更新语句
     *
     * @return string 更新语句
     */
    private function buildUpdateQuery(): string
    {
        $query = "UPDATE $this->tableName SET ";

        $setExpressions = [];
        foreach ($this->changeData as $field => $value) {
            $placeholder = $this->generatePlaceholder();
            $this->addBinding($placeholder, $value);
            $setExpressions[] = "$field = $placeholder";
        }

        $query .= implode(', ', $setExpressions);

        // 构建 WHERE 子句
        if (!empty($this->whereConditions)) {
            $query .= " WHERE " . $this->buildConditions($this->whereConditions);
        }

        // 构建 LIMIT 子句
        if (!empty($this->limit)) {
            $query .= " $this->limit";
        }

        return $query;
    }

    /**
     * 构建 UPSERT批量更新语句
     *
     * @return string 更新语句
     */
    private function buildUpsertQuery(): string
    {
        if (empty($this->changeData)) {
            throw new InvalidArgumentException('Upsert values is empty');
        }

        $columns = array_keys($this->changeData[0]);

        return match ($this->driver) {
            'mysql'     => $this->buildMysqlUpsertQuery($this->changeData, $columns, $this->upsertColumns['unique_column'], $this->upsertColumns['update_column']),
            'pgsql'     => $this->buildPgsqlUpsertQuery($this->changeData, $columns, $this->upsertColumns['unique_column'], $this->upsertColumns['update_column']),
            'sqlite'    => $this->buildSQLiteUpsertQuery($this->changeData, $columns, $this->upsertColumns['unique_column'], $this->upsertColumns['update_column']),
            'sqlserver' => $this->buildSQLServerUpsertQuery($this->changeData, $columns, $this->upsertColumns['unique_column'], $this->upsertColumns['update_column']),
            'oracle'    => $this->buildOracleUpsertQuery($this->changeData, $columns, $this->upsertColumns['unique_column'], $this->upsertColumns['update_column']),
            default     => throw new InvalidArgumentException('The driver is not supported'),
        };
    }

    private function buildMysqlUpsertQuery(array $data, array $columns, array $uniqueColumns = [], array $updateColumns = []): string
    {
        //INSERT INTO your_table (column1, column2, column3) VALUES
        //  ('value1', 'value2', 'value3')
        //ON DUPLICATE KEY UPDATE
        //  column1 = VALUES(column1), column2 = VALUES(column2), column3 = VALUES(column3);

        $sql    = "INSERT INTO {$this->tableName} (" . implode(', ', $columns) . ") VALUES ";
        $values = [];
        foreach ($data as $row) {
            $placeholders = [];
            foreach ($columns as $field) {
                $value       = isset($row[$field]) ? $row[$field] : null;
                $placeholder = $this->generatePlaceholder();
                $this->addBinding($placeholder, $value);
                $placeholders[] = $placeholder;
            }
            $values[] = '(' . implode(', ', $placeholders) . ')';
        }
        $sql          .= implode(', ', $values);
        $sql          .= ' ON DUPLICATE KEY UPDATE ';
        $updateValues = [];
        foreach ($updateColumns as $column) {
            $updateValues[] = "$column = VALUES($column)";
        }
        $sql .= implode(', ', $updateValues);
        return $sql;
    }

    private function buildPgsqlUpsertQuery(array $data, array $columns, array $uniqueColumns, array $updateColumns): string
    {
        // INSERT INTO your_table (column1, column2, column3) VALUES
        //   ('value1', 'value2', 'value3')
        // ON CONFLICT (unique_column1, unique_column2)
        // DO UPDATE SET
        //   column1 = EXCLUDED.column1, column2 = EXCLUDED.column2, column3 = EXCLUDED.column3;

        $sql    = "INSERT INTO $this->tableName (" . implode(', ', $columns) . ") VALUES ";
        $values = [];
        foreach ($data as $row) {
            $placeholders = [];
            foreach ($columns as $field) {
                $value       = isset($row[$field]) ? $row[$field] : null;
                $placeholder = $this->generatePlaceholder();
                $this->addBinding($placeholder, $value);
                $placeholders[] = $placeholder;
            }
            $values[] = '(' . implode(', ', $placeholders) . ')';
        }
        $sql          .= implode(', ', $values);
        $sql          .= ' ON CONFLICT (';
        $sql          .= implode(', ', $uniqueColumns);
        $sql          .= ') DO UPDATE SET ';
        $updateValues = [];
        foreach ($updateColumns as $column) {
            $updateValues[] = "$column = EXCLUDED.$column";
        }
        $sql .= implode(', ', $updateValues);
        return $sql;
    }

    /**
     * SQLite UPSERT 语句
     *
     * @param array $data
     * @param array $columns
     * @param array $uniqueColumns
     * @param array $updateColumns
     *
     * @return string
     * @deprecated 不推荐SQLite使用UPSERT
     *
     */
    private function buildSQLiteUpsertQuery(array $data, array $columns, array $uniqueColumns, array $updateColumns): string
    {
        // INSERT OR REPLACE INTO your_table (column1, column2, column3) VALUES ('value1', 'value2', 'value3');

        $sql    = "INSERT OR REPLACE INTO {$this->tableName} (" . implode(', ', $columns) . ") VALUES ";
        $values = [];
        foreach ($data as $row) {
            $placeholders = [];
            foreach ($columns as $field) {
                $value       = isset($row[$field]) ? $row[$field] : null;
                $placeholder = $this->generatePlaceholder();
                $this->addBinding($placeholder, $value);
                $placeholders[] = $placeholder;
            }
            $values[] = '(' . implode(', ', $placeholders) . ')';
        }
        $sql .= implode(', ', $values);
        return $sql;
    }

    private function buildSQLServerUpsertQuery(array $data, array $columns, array $uniqueColumns, array $updateColumns): string
    {
        //MERGE INTO your_table AS target
        //USING (VALUES ('value1', 'value2', 'value3')) AS source (column1, column2, column3)
        //ON target.unique_column1 = source.column1 AND target.unique_column2 = source.column2
        //WHEN MATCHED THEN
        //  UPDATE SET
        //    target.column1 = source.column1,
        //    target.column2 = source.column2,
        //    target.column3 = source.column3
        //WHEN NOT MATCHED THEN
        //  INSERT (column1, column2, column3) VALUES (source.column1, source.column2, source.column3);

        $sql    = "MERGE INTO $this->tableName AS target ";
        $sql    .= "USING (VALUES ";
        $values = [];
        foreach ($data as $row) {
            $placeholders = [];
            foreach ($columns as $field) {
                $value       = isset($row[$field]) ? $row[$field] : null;
                $placeholder = $this->generatePlaceholder();
                $this->addBinding($placeholder, $value);
                $placeholders[] = $placeholder;
            }
            $values[] = '(' . implode(', ', $placeholders) . ')';
        }
        $sql          .= implode(', ', $values);
        $sql          .= ") AS source (";
        $sql          .= implode(', ', $columns);
        $sql          .= ") ";
        $sql          .= "ON target.";
        $sql          .= implode(' = source.', $uniqueColumns);
        $sql          .= " ";
        $sql          .= "WHEN MATCHED THEN ";
        $sql          .= "UPDATE SET ";
        $updateValues = [];
        foreach ($updateColumns as $column) {
            $updateValues[] = "target.$column = source.$column";
        }
        $sql .= implode(', ', $updateValues);
        $sql .= " ";
        $sql .= "WHEN NOT MATCHED THEN ";
        $sql .= "INSERT (";
        $sql .= implode(', ', $columns);
        $sql .= ") VALUES (";
        $sql .= "source.";
        $sql .= implode(', source.', $columns);
        $sql .= ");";
        return $sql;
    }

    private function buildOracleUpsertQuery(array $data = [], array $columns = [], array $uniqueColumns = [], array $updateColumns = []): string
    {
        //MERGE INTO your_table USING dual
        //ON (unique_column1 = 'value1' AND unique_column2 = 'value2')
        //WHEN MATCHED THEN
        //  UPDATE SET
        //    column1 = 'value1',
        //    column2 = 'value2',
        //    column3 = 'value3'
        //WHEN NOT MATCHED THEN
        //  INSERT (column1, column2, column3) VALUES ('value1', 'value2', 'value3');

        $sql          = "MERGE INTO $this->tableName USING dual ";
        $sql          .= "ON (";
        $sql          .= implode(' = ', $uniqueColumns);
        $sql          .= ") ";
        $sql          .= "WHEN MATCHED THEN ";
        $sql          .= "UPDATE SET ";
        $updateValues = [];
        foreach ($updateColumns as $column) {
            $updateValues[] = "$column = 'value'";
        }
        $sql .= implode(', ', $updateValues);
        $sql .= " ";
        $sql .= "WHEN NOT MATCHED THEN ";
        $sql .= "INSERT (";
        $sql .= implode(', ', $columns);
        $sql .= ") VALUES (";
        $sql .= implode(', ', $updateColumns);
        $sql .= ");";
        return $sql;
    }

    /**
     * 构建删除语句
     *
     * @return string 删除语句
     */
    private function buildDeleteQuery(): string
    {
        $query = "DELETE FROM $this->tableName";

        // 构建 WHERE 子句
        if (!empty($this->whereConditions)) {
            $query .= " WHERE " . $this->buildConditions($this->whereConditions);
        } else {
            // 如果没有设置删除条件，抛出异常,禁止全表删除
            throw new InvalidArgumentException('Delete condition is empty');
        }

        // 构建 LIMIT 子句
        if (!empty($this->limit)) {
            $query .= " $this->limit";
        }

        return $query;
    }

    /**
     * 清空表
     *
     * @return string
     */
    private function buildTruncateQuery(): string
    {
        return "TRUNCATE TABLE $this->tableName";
    }

    /**
     * 设置表自增ID为最小值
     *
     * @return string
     */
    private function buildSetTableMinAutoIncrementQuery(): string
    {
        return "ALTER TABLE {$this->tableName} AUTO_INCREMENT = 1";
    }

    /**
     * 判断是否存在记录
     *
     * @return string
     */
    private function buildExistsQuery(): string
    {
        $this->selectFields = [1];
        $query              = $this->buildSelectQuery();
        return 'SELECT EXISTS ( ' . $query . ' ) AS record_exists';
    }

    /**
     * 判断是否不存在记录
     *
     * @return string
     */
    private function buildDoesntExistsQuery(): string
    {
        $this->selectFields = [1];
        $query              = $this->buildSelectQuery();
        return 'SELECT NOT EXISTS ( ' . $query . ' ) AS record_exists';
    }

    /**
     * 构建添加索引语句
     *
     * @param string|array $column    索引列 eg: 'id', ['id', 'name']
     * @param string       $indexName 索引名称 eg: 'index_id'
     * @param string       $comment   索引注释 eg: '索引注释'
     * @param string       $indexType 索引类型 eg: 'FULLTEXT','NORMAL','SPATIAL','UNIQUE' 等 @see
     *                                https://dev.mysql.com/doc/refman/8.0/en/create-index.html
     * @param string       $indexFun  索引函数 eg: 'HASH','BTREE' @see
     *                                https://dev.mysql.com/doc/refman/8.0/en/create-index.html
     *
     * @return string
     */
    public function buildAddIndexQuery(string|array $column, string $indexName = '', string $comment = '', string $indexType = '', string $indexFun = ''): string
    {
        $columnString   = is_string($column) ? $column : implode(',', $column);
        $indexFunString = !empty($indexFun) ? "USING {$indexFun}" : '';
        $commentString  = !empty($comment) ? "COMMENT '{$comment}'" : '';
        $this->bindings = [];
        return " ALTER TABLE `{$this->tableName}` ADD {$indexType} INDEX {$indexName}({$columnString}) {$indexFunString} {$commentString}";
    }

    /**
     * 构建由指定顺序的字段组成的复合索引列表 语句
     *
     * @param string|array $column 索引列 eg: 'id', ['id', 'name']
     *
     * @return string
     */
    public function buildIndexComposedOfQueryFieldsSQL(string|array $column): string
    {
        $columnString = is_string($column) ? $column : implode(',', $column);

        $selectIndexSql = 'SELECT INDEX_NAME FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA = DATABASE() ';
        $selectIndexSql .= " AND TABLE_NAME = '{$this->tableName}' AND INDEX_NAME NOT IN ('PRIMARY') GROUP BY INDEX_NAME";
        $selectIndexSql .= " HAVING GROUP_CONCAT(COLUMN_NAME ORDER BY SEQ_IN_INDEX) = '" . $columnString . "'";
        $this->bindings = [];
        return $selectIndexSql;
    }

    /**
     * 添加防止 SQL 注入功能(为SQL语句中的字符串添加引号)
     *
     * @param mixed $value 待转义的值
     *
     * @return string 转义后的字符串
     */
    private function quote(mixed $value): string
    {
        if (is_string($value)) {
            return "'" . addslashes($value) . "'";
        } elseif (is_bool($value)) {
            return $value;
        } elseif (is_null($value)) {
            return 'NULL';
        } elseif (is_array($value)) {
            // 如果是数组，递归调用
            return implode(', ', array_map([$this, 'quote'], $value));
        } else {
            return (string)$value;
        }
    }

    /**
     * 重置所有查询参数和绑定参数
     *
     * @return $this->base
     */
    public function reset()
    {
        // SELECT 查询相关属性
        $this->selectFields     = [];
        $this->whereConditions  = [];
        $this->joinClauses      = [];
        $this->groupByFields    = [];
        $this->havingConditions = [];
        $this->orderByFields    = [];
        $this->limit            = '';
        $this->bindings         = [];

        // INSERT、UPDATE 需要插入或更新的数据
        $this->changeData = [];
        // UPSERT 需要判断是插入还是更新的列
        $this->upsertColumns = [];

        $this->convertBindParamsToQuestionMarks = true;
        // 清空构建后的 SQL 语句
        $this->buildAfterSql = '';

        $this->buildType = 'select';

        return $this->base;
    }

    /**
     * 生成的 SQL 语句 是否转换绑定参数为问号
     *
     * @param bool $status
     *
     * @return $this->base
     */
    public function setConvertBindParamsToQuestionMarks(bool $status = true)
    {
        $this->convertBindParamsToQuestionMarks = $status;
        return $this->base;
    }

    /**
     * 构建条件语句
     *
     * @param array $conditions 条件数组
     *
     * @return string 条件语句
     */
    private function buildConditions(array $conditions): string
    {
        $conditionStrings = [];

        foreach ($conditions as $index => $condition) {
            $field           = $condition['field'];
            $operator        = $condition['operator'];
            $value           = isset($condition['value']) ? $condition['value'] : '';
            $logicalOperator = isset($condition['logicalOperator']) ? $condition['logicalOperator'] : '';

            // 处理左括号
            if ($value === '(') {
                if ($index >= 1) {
                    $conditionStrings[] = "$logicalOperator (";
                } else {
                    $conditionStrings[] = "(";
                }
            } else {
                // 判断 $condition 是不是最后一个元素
                if ($index >= 1) {
                    $conditionStrings[] = "$logicalOperator $field $operator $value ";
                } else {
                    $conditionStrings[] = "$field $operator $value";
                }
            }
        }
        return implode(' ', $conditionStrings);
    }

    // ==================================================================
    // CURD 操作开始
    // ==================================================================

    /**
     * 填充数据
     */
    public function fill(array $data)
    {
        $this->changeData = $data;
        return $this->base;
    }

    /**
     * 设置插入表名和字段
     *
     * @param array $columns           插入的字段数组
     *                                 eg:
     *                                 // 单条插入
     *                                 ['column1'=>'val_1', 'column2'=>'val_2']
     *                                 // 多条插入
     *                                 [
     *                                 ['column1'=>'val_1_0', 'column2'=>'val_2_0'],
     *                                 ['column1'=>'val_1_1', 'column2'=>'val_2_1'],
     *                                 ]
     *
     * @return $this->base
     */
    public function create(array $columns = [])
    {
        $this->reset();
        $this->changeData = !empty($columns) ? $columns : $this->changeData;
        $this->buildType  = 'insert';
        return $this->base;
    }

    /**
     * 设置更新表名
     *
     * @param array $columns
     *
     * @return $this->base
     */
    public function update(array $columns = [])
    {
        $this->changeData = !empty($columns) ? $columns : $this->changeData;
        $this->buildType  = 'update';
        return $this->base;
    }

    /**
     * 设置批量更新
     *  如果库中 $uniqueColumn 的字段(单个或者多个字段联合)值存在，则更新 $updateColumn字段 ，否则创建$data中的数据
     *
     * 重要提示：
     *          1、批量更新的字段值或多个字段组合必须是唯一的，否则会出现更新失败
     *          2、$uniqueColumn 和 $updateColumn 的字段值必须在 $data 中存在
     *          3、【强烈建议】$uniqueColumn 和 $updateColumn 的字段合在一起刚好是 $data 中的「所有」字段
     *
     *
     * @param array $data         需要更新或插入的数据； eg: [
     *                            ['column1'=>'val_1_0', 'column2'=>'val_2_0', 'unique_column'=>'unique_val_0'],
     *                            ['column1'=>'val_1_1','column2'=>'val_2_1', 'unique_column'=>'unique_val_1']
     *                            ]
     * @param array $uniqueColumn 根据$uniqueColumn里的字段组合的值进行判断，如果存在则更新$updateColumn里的字段，否则创建一条新数据 eg:  ['unique_column']
     *                            或 ['column1', 'column2']
     * @param array $updateColumn 需要更新的字段 eg: ['column1', 'column2'] 或 ['column2']
     *
     * @return $this->base
     */
    public function upsert(array $data = [], array $uniqueColumn = [], array $updateColumn = [])
    {
        $this->changeData    = $data;
        $this->upsertColumns = ['unique_column' => $uniqueColumn, 'update_column' => $updateColumn];
        $this->buildType     = 'upsert';
        return $this->base;
    }

    /**
     * 删除操作
     *
     * @return $this->base
     */
    public function delete()
    {
        $this->buildType = 'delete';
        return $this->base;
    }

    /**
     * 清空表/截断表
     */
    public function truncate()
    {
        $this->buildType = 'truncate';
        $this->bindings  = [];
        return $this->base;
    }

    /**
     * 设置表的自增id为最小
     */
    public function minAutoIncrement()
    {
        $this->buildType = 'min_auto_increment';
        $this->bindings  = [];
        return $this->base;
    }

    // ==================================================================
    // CURD 操作结束
    // ==================================================================

    // ==================================================================
    // 集合查询 操作开始
    // ==================================================================

    /**
     *  COUNT 聚合查询
     *
     * @param string $field 字段名 eg: 'id' 或 '*'
     *
     * @return $this->base
     */
    public function count(string $field = 'id')
    {
        $this->buildType    = 'select';
        $this->selectFields = ["COUNT($field) AS count"];
        return $this->base;
    }

    /**
     *  MAX 聚合查询
     *
     * @param string $field
     *
     * @return $this->base
     */
    public function max(string $field = '*')
    {
        $this->buildType    = 'select';
        $this->selectFields = ["MAX($field) AS max"];
        return $this->base;
    }

    /**
     *  MIN 聚合查询
     *
     * @param string $field
     *
     * @return $this->base
     */
    public function min(string $field = '*')
    {
        $this->buildType    = 'select';
        $this->selectFields = ["MIN($field) AS min"];
        return $this->base;
    }

    /**
     * SUM 聚合查询
     *
     * @param string $field
     *
     * @return $this->base
     */
    public function sum(string $field = '*')
    {
        $this->buildType    = 'select';
        $this->selectFields = ["SUM($field) AS sum"];
        return $this->base;
    }

    /**
     * AVG 聚合查询
     *
     * @param string $field
     *
     * @return $this->base
     */
    public function avg(string $field = '*')
    {
        $this->buildType    = 'select';
        $this->selectFields = ["AVG($field) AS avg"];
        return $this->base;
    }

    /**
     * EXISTS 聚合查询 判断是否存在
     *
     * @return $this->base
     */
    public function exists()
    {
        $this->buildType = 'exists';
        return $this->base;
    }

    /**
     * EXISTS 聚合查询 判断是否不存在
     *
     * @return $this->base
     */
    public function doesntExist()
    {
        $this->buildType = 'not_exists';
        return $this->base;
    }

    // ==================================================================
    // 集合查询 操作结束
    // ==================================================================

    /**
     * 满足条件时执行$callback，否则执行$failCallback
     *
     * @param          $field
     * @param callable $callback
     * @param          $failCallback
     *
     * @return $this->base
     */
    public function when($field, callable $callback, $failCallback)
    {
        if ($field) {
            if ($callback instanceof Closure && is_callable($callback)) {
                $callback($this);
            }
        } else {
            if ($failCallback instanceof Closure && is_callable($failCallback)) {
                $failCallback($this);
            }
        }
        return $this->base;
    }

    // 为参数生成唯一的绑定标识符
    private function generatePlaceholder(): string
    {
        return ':param_' . uniqid() . random_int(100, 999);
    }

    // 添加绑定参数，允许用户自定义参数名称
    private function addBinding(string $placeholder, mixed $value): void
    {
        $this->bindings[$placeholder] = $value;
    }

    /**
     * 验证逻辑运算符的有效性
     *
     * @param string $logicalOperator 逻辑运算符
     *
     * @throws InvalidArgumentException
     */
    private function validateLogicalOperator(string $logicalOperator): void
    {
        if (!in_array(strtoupper($logicalOperator), self::LOGICAL_OPERATORS)) {
            throw new InvalidArgumentException('Invalid logical operator: ' . $logicalOperator);
        }
    }

    /**
     * 验证操作符的有效性
     *
     * @param string $operator 操作符
     *
     * @return string
     * @throws InvalidArgumentException
     */
    private function validateOperator(string $operator): string
    {
        if (!in_array(strtoupper($operator), self::OPERATORS)) {
            throw new InvalidArgumentException('Invalid operator: ' . $operator);
        }

        return strtoupper($operator);
    }

    /**
     * 获取所有绑定参数，包括子查询的参数
     *
     * @return array 所有绑定参数
     */
    public function getBindings(): array
    {
        return $this->bindings;
    }

    public function getTableName(): string
    {
        return $this->tableName;
    }

    /**
     * 把构建后的 SQL 语句 整理成可使用的sql语句
     *
     * @return string SQL 语句
     */
    public function toSql(): string
    {
        $query = $this->buildQuery();
        if ($this->convertBindParamsToQuestionMarks) {
            // 按照顺序把$query里面的问号替换成$this->getBindings()里面的值
            $index = 0;
            $query = preg_replace_callback('/\?/', function ($matches) use (&$index) {
                return $this->quote($this->getBindings()[$index++]);
            }, $query);
        } else {
            // 使用占位符进行参数绑定
            foreach ($this->getBindings() as $placeholder => $value) {
                $query = str_replace($placeholder, $this->quote($value), $query);
            }
        }
        return $query;
    }

    public function clone()
    {
        return clone $this;
    }

    public function __destruct()
    {
        $this->reset();
    }

    /**
     * 调试SQL：获取构建后的 SQL 语句和绑定参数
     */
    #[NoReturn]
    public function dd(): void
    {
        $this->dump();
        exit(1);
    }

    /**
     * 调试SQL：获取构建后的 SQL 语句和绑定参数
     */
    public function dump()
    {
        echo '<pre>';
        var_dump([
            'sql'      => $this->toSql(),
            'building' => $this->buildQuery(),
            'bindings' => $this->getBindings(),
        ]);
    }

    /**
     * 调用一个方法
     */
    public function __call($method, $arg)
    {
        throw new Exception('Method does not exist:' . $method);
    }

    /**
     * 调用静态方法
     */
    public static function __callStatic(string $method, $arg)
    {
        throw new Exception('Method does not exist:' . $method);
    }

}
