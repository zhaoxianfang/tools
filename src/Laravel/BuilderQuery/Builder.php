<?php

namespace zxf\Laravel\BuilderQuery;

use Illuminate\Database\Eloquent;
use Illuminate\Support\ServiceProvider;
use zxf\Laravel\BuilderQuery\Macros\GroupSortMacro;
use zxf\Laravel\BuilderQuery\Macros\RandomMacro;
use zxf\Laravel\BuilderQuery\Macros\WithRecursiveMacro;

/**
 * Macros 宏定义
 *
 * 随机查询出$limit条数据
 * @method $this random(int $limit = 10, string $primaryKey = 'id')
 *
 * 根据$groupColumn进行分组，然后每组中随机取出$limit条数据
 * @method $this groupRandom(string $groupColumn, int $limit = 10, string $primaryKey = 'id')
 *
 * 缓存查询
 * @method $this cache(int $cacheTts = 3600, bool $deleteCache = false)
 *
 * groupSort(分组排序查询) 宏函数
 *
 * @example Article::query()->groupSort('classify_id', [1,3],'read','desc')->get(); // 查询每个文章分组下read最高的第1到3名文章
 *          Article::query()->groupSort('classify_id', 9,'read','desc')->get(); // 获取每个文章分组下read第9名文章
 *
 * @method $this groupSort(string $groupBy, int|array $ranks, string $orderBy = 'read', string $direction = 'desc')
 *
 * 子数据查询
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
class Builder extends Eloquent\Builder
{
    // 注册宏指令
    // 框架自带的 whereHas 模型查询方法会进行全表扫描，导致查询巨慢，使用下面几个方法进行弥补不足
    public static function register(ServiceProvider $provider)
    {
        // whereHas查询 功能
        self::registerWhereHasInQuery($provider);
        // 随机数查询 功能
        RandomMacro::register();
        // 分组排序功能
        GroupSortMacro::register();
        // 注册查询缓存功能
        self::registerCacheQuery($provider);
        // 注册递归查询宏
        WithRecursiveMacro::register();

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
