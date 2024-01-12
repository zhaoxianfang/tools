<?php

namespace zxf\Laravel\BuilderQuery;

use Illuminate\Database\Eloquent;

/**
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
 *
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
    public static function register(\Illuminate\Support\ServiceProvider $provider)
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
            Eloquent\Builder::macro('main' . $macroAction, function (...$params) use ($macroAction) {
                $params[0] = $this->getModel()->getTable() . '.' . $params[0];
                return $this->{$macroAction}(...$params);
            });
        }

        Eloquent\Builder::macro('mainSelect', function ($columns = ['*']) {
            $table   = $this->getModel()->getTable();
            $columns = is_array($columns) ? $columns : func_get_args();
            foreach ($columns as &$column) {
                $column = $table . '.' . $column;
            }
            return $this->select($columns);
        });

    }
}