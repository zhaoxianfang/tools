弥补laravel wehereHas 的不足

> 来源：https://gitee.com/yjshop/laravel-builder

## 使用

### whereHasIn

> whereHasIn(string $relation, ?\Closure $callable = null)
> whereHasNotIn(string $relation, ?\Closure $callable = null)

```
$model->whereHasIn('section', function ($query) {
    $query->where('id', 1);
});
```

### 其他方法

```
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
 *         eg: User::query()->mainWhere('id', 1); => selsect xxx where user.id = 1
 * @method $this mainWhere(string $relation, ?\Closure $callable = null)
 * @method $this mainSum(string $relation, ?\Closure $callable = null)
 * @method $this mainPluck(string $relation, ?\Closure $callable = null)
 * @method $this mainWhereBetween(string $relation, ?\Closure $callable = null)
 * @method $this mainWhereIn(string $relation, ?\Closure $callable = null)
 * @method $this mainOrderBy(string $relation, ?\Closure $callable = null)
 * @method $this mainOrderByDesc(string $relation, ?\Closure $callable = null)
 * @method $this mainSelect(string $relation, ?\Closure $callable = null)
 */
```
