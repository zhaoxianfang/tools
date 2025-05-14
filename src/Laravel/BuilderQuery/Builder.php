<?php

namespace zxf\Laravel\BuilderQuery;

use Illuminate\Database\Eloquent;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;

/**
 * 随机数查询
 *
 * @method $this random(int $limit = 10, string $primaryKey = 'id')
 *
 * 缓存查询
 * @method $this cache(int $cacheTts = 3600, bool $deleteCache = false)
 * @method $this whereHasIn(string $relation, ?\Closure $callable = null)
 * @method $this orWhereHasIn(string $relation, ?\Closure $callable = null)
 * @method $this whereHasNotIn(string $relation, ?\Closure $callable = null)
 * @method $this orWhereHasNotIn(string $relation, ?\Closure $callable = null)
 *
 * 关联查询
 * @method $this whereHasJoin(string $relation, ?\Closure $callable = null)
 * @method $this whereHasCrossJoin(string $relation, ?\Closure $callable = null)
 * @method $this whereHasLeftJoin(string $relation, ?\Closure $callable = null)
 * @method $this whereHasRightJoin(string $relation, ?\Closure $callable = null)
 * @method $this whereHasMorphIn(string $relation, $types, ?\Closure $callable = null)
 * @method $this orWhereHasMorphIn(string $relation, $types, ?\Closure $callable = null)
 *
 * 主表字段查询
 * @method $this mainWhere(string $relation, ?\Closure $callable = null)
 * @method $this mainSum(string $relation, ?\Closure $callable = null)
 * @method $this mainPluck(string $relation, ?\Closure $callable = null)
 * @method $this mainWhereBetween(string $relation, ?\Closure $callable = null)
 * @method $this mainWhereIn(string $relation, ?\Closure $callable = null)
 * @method $this mainOrderBy(string $relation, ?\Closure $callable = null)
 * @method $this mainOrderByDesc(string $relation, ?\Closure $callable = null)
 * @method $this mainSelect(string $relation, ?\Closure $callable = null)
 */

// 公共函数：生成缓存键
function buildQueryCacheKey(Eloquent\Builder $builder, string $method, array $args = []): string
{
    $sql = $builder->toSql();
    $bindings = $builder->getBindings();

    return 'query_cache:'.md5($sql.serialize($bindings).$method.serialize($args));
}

class Builder extends Eloquent\Builder
{
    // 注册宏指令
    // 框架自带的 whereHas 模型查询方法会进行全表扫描，导致查询巨慢，使用下面几个方法进行弥补不足
    public static function register(ServiceProvider $provider)
    {
        // whereHas查询 功能
        self::registerWhereHasInQuery($provider);
        // 随机数查询 功能
        self::registerRandomQuery($provider);
        // 注册查询缓存功能
        self::registerCacheQuery($provider);
    }

    public static function registerWhereHasInQuery(ServiceProvider $provider)
    {
        // in notIn
        Eloquent\Builder::macro('whereHasIn', function ($relationName, $callable = null) {
            return (new WhereHasIn($this, $relationName, function ($nextRelation, $builder) use ($callable) {
                if ($nextRelation) {
                    return $builder->whereHasIn($nextRelation, $callable);
                }
                if ($callable) {
                    return $builder->callScope($callable);
                }

                return $builder;
            }))->execute();
        });

        Eloquent\Builder::macro('whereHasNotIn', function ($relationName, $callable = null) {
            return (new WhereHasNotIn($this, $relationName, function ($nextRelation, $builder) use ($callable) {
                if ($nextRelation) {
                    return $builder->whereHasNotIn($nextRelation, $callable);
                }

                if ($callable) {
                    return $builder->callScope($callable);
                }

                return $builder;
            }))->execute();
        });

        // join(inner join) crossJoin leftJoin rightJoin
        Eloquent\Builder::macro('whereHasJoin', function ($relationName, $callable = null) {
            return (new WhereHasJoin($this, $relationName, function (Eloquent\Builder $builder, Eloquent\Builder $relationBuilder) use ($callable) {
                if ($callable) {
                    $relationBuilder->callScope($callable);

                    return $builder->addNestedWhereQuery($relationBuilder->getQuery());
                }

                return $builder;
            }))->execute();
        });

        Eloquent\Builder::macro('whereHasCrossJoin', function ($relationName, $callable = null) {
            return (new WhereHasCrossJoin($this, $relationName, function (Eloquent\Builder $builder, Eloquent\Builder $relationBuilder) use ($callable) {
                if ($callable) {
                    $relationBuilder->callScope($callable);

                    return $builder->addNestedWhereQuery($relationBuilder->getQuery());
                }

                return $builder;
            }))->execute();
        });

        Eloquent\Builder::macro('whereHasLeftJoin', function ($relationName, $callable = null) {
            return (new WhereHasLeftJoin($this, $relationName, function (Eloquent\Builder $builder, Eloquent\Builder $relationBuilder) use ($callable) {
                if ($callable) {
                    $relationBuilder->callScope($callable);

                    return $builder->addNestedWhereQuery($relationBuilder->getQuery());
                }

                return $builder;
            }))->execute();
        });

        Eloquent\Builder::macro('whereHasRightJoin', function ($relationName, $callable = null) {
            return (new WhereHasRightJoin($this, $relationName, function (Eloquent\Builder $builder, Eloquent\Builder $relationBuilder) use ($callable) {
                if ($callable) {
                    $relationBuilder->callScope($callable);

                    return $builder->addNestedWhereQuery($relationBuilder->getQuery());
                }

                return $builder;
            }))->execute();
        });

        // or in、 or notIn
        Eloquent\Builder::macro('orWhereHasIn', function ($relationName, $callable = null) {
            return $this->orWhere(function ($query) use ($relationName, $callable) {
                return $query->whereHasIn($relationName, $callable);
            });
        });

        Eloquent\Builder::macro('orWhereHasNotIn', function ($relationName, $callable = null) {
            return $this->orWhere(function ($query) use ($relationName, $callable) {
                return $query->whereHasNotIn($relationName, $callable);
            });
        });

        // morph in
        Eloquent\Builder::macro('whereHasMorphIn', WhereHasMorphIn::make());
        Eloquent\Builder::macro('orWhereHasMorphIn', function ($relation, $types, $callback = null) {
            return $this->whereHasMorphIn($relation, $types, $callback, 'or');
        });

        // 主表字段查询
        foreach (['Pluck', 'Sum', 'WhereBetween', 'WhereIn', 'Where', 'OrderBy', 'OrderByDesc'] as $macroAction) {
            Eloquent\Builder::macro('main'.$macroAction, function (...$params) use ($macroAction) {
                $params[0] = $this->getModel()->getTable().'.'.$params[0];

                return $this->{$macroAction}(...$params);
            });
        }

        Eloquent\Builder::macro('mainSelect', function ($columns = ['*']) {
            $table = $this->getModel()->getTable();
            $columns = is_array($columns) ? $columns : func_get_args();
            foreach ($columns as &$column) {
                $column = $table.'.'.$column;
            }

            return $this->select($columns);
        });
    }

    /**
     * 注册随机查询宏
     *
     * 示例：User::where('status', 1)->random(5)->get();
     */
    public static function registerRandomQuery(ServiceProvider $provider)
    {
        /**
         * 随机查询宏
         *
         * @param  int  $limit  返回记录数，默认10
         * @param  string  $primaryKey  主键名，默认'id'
         * @return Eloquent\Builder
         */
        Eloquent\Builder::macro('random', function (int $limit = 10, string $primaryKey = 'id') {
            $model = $this->getModel();
            $table = $model->getTable();

            // 创建全新的查询构建器
            $baseQuery = $model->newQuery()->getQuery();

            // 复制完整的查询条件（包括所有where类型）
            $this->replicateQuery($this->getQuery(), $baseQuery);

            // 构建窗口函数子查询
            $subQuery = DB::table($table)
                ->select(
                    $primaryKey,
                    DB::raw('ROW_NUMBER() OVER (ORDER BY RAND()) AS rnd_rank')
                );

            // 复制所有where条件到子查询
            $this->replicateWheres($this->getQuery(), $subQuery);

            // 构建主查询
            return $model->newQuery()
                ->whereIn("$table.$primaryKey", function ($query) use ($subQuery, $limit, $primaryKey) {
                    $query->select($primaryKey)
                        ->from(DB::raw("({$subQuery->toSql()}) AS ranked"))
                        ->mergeBindings($subQuery)
                        ->where('rnd_rank', '<=', $limit);
                });
        });

        /**
         * 完整复制查询条件
         *
         * @param  QueryBuilder  $source  源查询
         * @param  QueryBuilder  $dest  目标查询
         */
        Eloquent\Builder::macro('replicateQuery', function (QueryBuilder $source, QueryBuilder $dest) {
            // 复制基础属性
            $dest->bindings = $source->bindings;
            $dest->columns = $source->columns;
            $dest->distinct = $source->distinct;
            $dest->from = $source->from;
            $dest->joins = $source->joins;
            $dest->wheres = $source->wheres;
            $dest->groups = $source->groups;
            $dest->havings = $source->havings;
            $dest->orders = $source->orders;
            $dest->limit = $source->limit;
            $dest->offset = $source->offset;
            $dest->unions = $source->unions;
            $dest->unionLimit = $source->unionLimit;
            $dest->unionOrders = $source->unionOrders;
        });

        /**
         * 完整复制所有where条件
         *
         * @param  QueryBuilder  $source  源查询
         * @param  QueryBuilder  $dest  目标查询
         */
        Eloquent\Builder::macro('replicateWheres', function (QueryBuilder $source, QueryBuilder $dest) {
            foreach ($source->wheres as $where) {
                $method = $where['type'] ?? 'Basic';
                $boolean = $where['boolean'] ?? 'and';

                switch ($method) {
                    case 'Basic':
                        $dest->where($where['column'], $where['operator'], $where['value'], $boolean);
                        break;
                    case 'In':
                        $dest->whereIn($where['column'], $where['values'], $boolean, $where['not'] ?? false);
                        break;
                    case 'NotIn':
                        $dest->whereNotIn($where['column'], $where['values'], $boolean);
                        break;
                    case 'Null':
                        $dest->whereNull($where['column'], $boolean, $where['not'] ?? false);
                        break;
                    case 'NotNull':
                        $dest->whereNotNull($where['column'], $boolean);
                        break;
                    case 'Between':
                        $dest->whereBetween($where['column'], $where['values'], $boolean, $where['not'] ?? false);
                        break;
                    case 'NotBetween':
                        $dest->whereNotBetween($where['column'], $where['values'], $boolean);
                        break;
                    case 'Exists':
                        $dest->whereExists($where['query'], $boolean, $where['not'] ?? false);
                        break;
                    case 'NotExists':
                        $dest->whereNotExists($where['query'], $boolean);
                        break;
                    case 'Raw':
                        $dest->whereRaw($where['sql'], $where['bindings'], $boolean);
                        break;
                    case 'Nested':
                        $dest->whereNested(function ($query) use ($where) {
                            $this->replicateWheres($where['query'], $query);
                        }, $boolean);
                        break;
                    case 'Column':
                        $dest->whereColumn($where['first'], $where['operator'], $where['second'], $boolean);
                        break;
                    case 'Date':
                        $dest->whereDate($where['column'], $where['operator'], $where['value'], $boolean);
                        break;
                    case 'Time':
                        $dest->whereTime($where['column'], $where['operator'], $where['value'], $boolean);
                        break;
                    case 'Day':
                        $dest->whereDay($where['column'], $where['operator'], $where['value'], $boolean);
                        break;
                    case 'Month':
                        $dest->whereMonth($where['column'], $where['operator'], $where['value'], $boolean);
                        break;
                    case 'Year':
                        $dest->whereYear($where['column'], $where['operator'], $where['value'], $boolean);
                        break;
                }
            }
        });
    }

    // 注册查询缓存功能
    public static function registerCacheQuery(ServiceProvider $provider)
    {
        /**
         * 查询缓存宏
         *
         * @param  int  $ttl  缓存时间(秒)，默认3600
         * @param  bool  $clear  是否删除缓存，默认false
         * @return Eloquent\Builder
         */
        Eloquent\Builder::macro('cache', function (int $ttl = 3600, bool $clear = false) {
            // TODO: 待实现
            return $this;
        });
    }
}
