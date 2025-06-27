<?php

namespace zxf\Laravel\BuilderQuery\Macros;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\Str;
use InvalidArgumentException;

class WithRecursiveMacro
{
    /**
     * 注册所有递归查询宏方法
     */
    public static function register(): void
    {
        // 1. 查找某个节点的所有子节点
        Builder::macro('withAllChildren', function (int $id, string $pidColumn = 'pid', int $maxDepth = 100): Builder {
            /** @var Builder $this */
            return WithRecursiveMacro::buildRecursiveQuery($this, $id, $pidColumn, 'children', $maxDepth);
        });

        // 2. 查找某个节点的所有父节点
        Builder::macro('withAllParents', function (int $id, string $pidColumn = 'pid', int $maxDepth = 100): Builder {
            /** @var Builder $this */
            return WithRecursiveMacro::buildRecursiveQuery($this, $id, $pidColumn, 'parents', $maxDepth);
        });

        // 3. 查找某个节点的第N级父节点（自己算0级）
        Builder::macro('withNthParent', function (int $id, int $n, string $pidColumn = 'pid'): Builder {
            /** @var Builder $this */
            return WithRecursiveMacro::findNthLevelRelation($this, $id, $pidColumn, 'parents', $n);
        });

        // 4. 查找某个节点的第N级所有子节点（自己算0级）
        Builder::macro('withNthChildren', function (int $id, int $n, string $pidColumn = 'pid'): Builder {
            /** @var Builder $this */
            return WithRecursiveMacro::findNthLevelRelation($this, $id, $pidColumn, 'children', $n);
        });

        // 5. 查找节点的完整路径（包含absolute_path字段）
        Builder::macro('withFullPath', function (array $ids = [], array $conditions = [], string $pidColumn = 'pid', string $nameColumn = 'name', string $pathSeparator = ' > '): Builder {
            /** @var Builder $this */
            return WithRecursiveMacro::findNodePaths($this, $ids, $conditions, $pidColumn, $nameColumn, $pathSeparator);
        });

        // 6. 检查第一个节点是否是第二个节点的父节点
        Builder::macro('isParentOf', function (int $parentId, int $childId, string $pidColumn = 'pid'): bool {
            /** @var Builder $this */
            return WithRecursiveMacro::checkIsParent($this, $parentId, $childId, $pidColumn);
        });

        // 7. 查找某个节点的所有同级节点
        Builder::macro('withSiblings', function (int $id, string $pidColumn = 'pid', bool $includeSelf = false): Builder {
            /** @var Builder $this */
            return WithRecursiveMacro::findSiblings($this, $id, $pidColumn, $includeSelf);
        });

        // 8. 查找从指定节点id(默认为0)开始的树形结构数据
        Builder::macro('withTree', function (?int $pid = null, string $pidColumn = 'pid', string $nameColumn = 'name', int $maxDepth = 100, string $pathSeparator = ' > '): Builder {
            /** @var Builder $this */
            return WithRecursiveMacro::buildTreeQuery($this, $pid, $pidColumn, $nameColumn, $maxDepth, $pathSeparator);
        });

        // 9. 通用递归查询（高级用法）
        Builder::macro('recursiveQuery', function (callable $baseQuery, callable $recursiveQuery, array $columns = ['*'], int $maxDepth = 100, string $depthColumn = 'depth'): Builder {
            /** @var Builder $this */
            return WithRecursiveMacro::buildGenericRecursiveQuery($this, $baseQuery, $recursiveQuery, $columns, $maxDepth, $depthColumn);
        });

        // 10. 重置递归查询（清除所有递归条件）
        Builder::macro('resetRecursive', function (): Builder {
            /** @var Builder $this */
            $query = $this->getQuery();

            // 重置所有可能影响递归查询的属性
            $query->expressions = [];
            $query->bindings['from'] = [];
            $query->from = $this->getModel()->getTable();

            return $this;
        });
    }

    /**
     * 构建递归查询基础方法
     *
     * @param  Builder  $query  查询构造器
     * @param  int  $id  起始节点ID
     * @param  string  $pidColumn  父ID字段名
     * @param  string  $direction  查询方向 (children|parents)
     * @param  int  $maxDepth  最大递归深度
     * @return Builder 新的查询构造器实例
     */
    public static function buildRecursiveQuery(Builder $query, int $id, string $pidColumn, string $direction, int $maxDepth): Builder
    {
        $table = $query->getModel()->getTable();
        $primaryKey = $query->getModel()->getKeyName();
        $withTable = 'recursive_'.Str::random(8);

        // 保存原始查询的列选择和绑定
        $originalColumns = $query->getQuery()->columns;
        $originalBindings = $query->getQuery()->bindings;
        $columns = empty($originalColumns) ? ['*'] : $originalColumns;

        // 构建递归CTE查询
        $recursiveQuery = "
            WITH RECURSIVE `{$withTable}` AS (
                -- 基础查询：选择起始节点
                SELECT ".implode(', ', array_map(function ($col) use ($table) {
            return $col === '*' ? '*' : "`{$table}`.`{$col}`";
        }, $columns)).", 0 AS depth 
                FROM `{$table}` 
                WHERE `{$primaryKey}` = ?
                
                UNION ALL
                
                -- 递归查询：根据方向查找子节点或父节点
                SELECT ".implode(', ', array_map(function ($col) {
            return $col === '*' ? 't.*' : "t.`{$col}`";
        }, $columns)).", r.depth + 1 AS depth
                FROM `{$table}` t
                JOIN `{$withTable}` r ON ".
                          ($direction === 'children' ? "t.`{$pidColumn}` = r.`{$primaryKey}`" : "t.`{$primaryKey}` = r.`{$pidColumn}`")."
                WHERE r.depth < ?
            )
            SELECT * FROM `{$withTable}` WHERE `{$primaryKey}` != ?
        ";

        // 创建新的查询构造器实例
        $newQuery = $query->getModel()->newQuery();
        $newQuery->getQuery()->from(new Expression("({$recursiveQuery}) as `{$withTable}`"))
            ->addBinding([$id, $maxDepth, $id], 'from');

        // 恢复原始查询的列选择和绑定
        if ($originalColumns) {
            $newQuery->select($originalColumns);
        }
        $newQuery->addBinding($originalBindings['where'] ?? [], 'where');

        return $newQuery;
    }

    /**
     * 查找第N级关系节点
     *
     * @param  Builder  $query  查询构造器
     * @param  int  $id  起始节点ID
     * @param  string  $pidColumn  父ID字段名
     * @param  string  $direction  查询方向 (children|parents)
     * @param  int  $n  要查询的层级
     * @return Builder 新的查询构造器实例
     */
    public static function findNthLevelRelation(Builder $query, int $id, string $pidColumn, string $direction, int $n): Builder
    {
        $table = $query->getModel()->getTable();
        $primaryKey = $query->getModel()->getKeyName();
        $withTable = 'recursive_'.Str::random(8);

        // 保存原始查询的列选择和绑定
        $originalColumns = $query->getQuery()->columns;
        $originalBindings = $query->getQuery()->bindings;
        $columns = empty($originalColumns) ? ['*'] : $originalColumns;

        // 构建递归CTE查询
        $recursiveQuery = "
            WITH RECURSIVE `{$withTable}` AS (
                -- 基础查询：选择起始节点
                SELECT ".implode(', ', array_map(function ($col) use ($table) {
            return $col === '*' ? '*' : "`{$table}`.`{$col}`";
        }, $columns)).", 0 AS relative_level 
                FROM `{$table}` 
                WHERE `{$primaryKey}` = ?
                
                UNION ALL
                
                -- 递归查询：根据方向查找子节点或父节点
                SELECT ".implode(', ', array_map(function ($col) {
            return $col === '*' ? 't.*' : "t.`{$col}`";
        }, $columns)).", r.relative_level + 1 AS relative_level
                FROM `{$table}` t
                JOIN `{$withTable}` r ON ".
                          ($direction === 'children' ? "t.`{$pidColumn}` = r.`{$primaryKey}`" : "t.`{$primaryKey}` = r.`{$pidColumn}`")."
                WHERE r.relative_level < ?
            )
            -- 筛选特定层级的节点
            SELECT * FROM `{$withTable}` WHERE relative_level = ?
        ";

        // 创建新的查询构造器实例
        $newQuery = $query->getModel()->newQuery();
        $newQuery->getQuery()->from(new Expression("({$recursiveQuery}) as `{$withTable}`"))
            ->addBinding([$id, $n, $n], 'from');

        // 恢复原始查询的列选择和绑定
        if ($originalColumns) {
            $newQuery->select($originalColumns);
        }
        $newQuery->addBinding($originalBindings['where'] ?? [], 'where');

        return $newQuery;
    }

    /**
     * 查找节点的完整路径
     *
     * @param  Builder  $query  查询构造器
     * @param  array  $ids  要查询路径的节点ID数组
     * @param  array  $conditions  额外查询条件
     * @param  string  $pidColumn  父ID字段名
     * @param  string  $nameColumn  名称字段名
     * @param  string  $pathSeparator  路径分隔符
     * @return Builder 新的查询构造器实例
     */
    public static function findNodePaths(Builder $query, array $ids = [], array $conditions = [], string $pidColumn = 'pid', string $nameColumn = 'name', string $pathSeparator = ' > '): Builder
    {
        $table = $query->getModel()->getTable();
        $primaryKey = $query->getModel()->getKeyName();
        $withTable = 'recursive_'.Str::random(8);

        // 保存原始查询的列选择和绑定
        $originalColumns = $query->getQuery()->columns;
        $originalBindings = $query->getQuery()->bindings;
        $columns = empty($originalColumns) ? ['*'] : $originalColumns;
        $columnList = implode(', ', array_map(function ($col) use ($table) {
            return $col === '*' ? '`'.$table.'`.*' : "`{$table}`.`{$col}`";
        }, $columns));

        // 构建条件查询部分
        $whereConditions = '';
        $bindings = [];
        if (! empty($conditions)) {
            $whereParts = [];
            $tempBindings = [];
            foreach ($conditions as $key => $value) {
                $whereParts[] = "`{$table}`.`{$key}` = ?";
                $tempBindings[] = $value;
            }
            for ($i = 0; $i < 2; $i++) {
                $bindings = array_merge($bindings, $tempBindings);
            }
            $whereConditions = implode(' AND ', $whereParts);
        }

        // 构建ID筛选条件
        $idCondition = '';
        if (! empty($ids)) {
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $idCondition = "WHERE `{$withTable}`.`{$primaryKey}` IN ({$placeholders})";
            $bindings = array_merge($bindings, $ids);
        }

        // 构建递归CTE查询
        $recursiveQuery = "
            WITH RECURSIVE `{$withTable}` AS (
                -- 基础查询：选择所有根节点和孤立节点
                SELECT 
                    {$columnList},
                    `{$table}`.`{$nameColumn}` AS absolute_path,
                    CAST(`{$table}`.`{$primaryKey}` AS CHAR(200)) AS path_ids,
                    0 AS depth
                FROM `{$table}`
                WHERE (`{$table}`.`{$pidColumn}` = 0 OR NOT EXISTS (
                    SELECT 1 FROM `{$table}` AS parent WHERE parent.`{$primaryKey}` = `{$table}`.`{$pidColumn}`
                ))
                ".(! empty($whereConditions) ? "AND {$whereConditions}" : '')."
                
                UNION ALL
                
                -- 递归查询：构建子节点的路径
                SELECT 
                    t.*,
                    CONCAT(r.absolute_path, '{$pathSeparator}', t.`{$nameColumn}`) AS absolute_path,
                    CONCAT(r.path_ids, ',', t.`{$primaryKey}`) AS path_ids,
                    r.depth + 1 AS depth
                FROM `{$table}` t
                JOIN `{$withTable}` r ON t.`{$pidColumn}` = r.`{$primaryKey}`
                ".(! empty($whereConditions) ? ' WHERE '.str_replace("`{$table}`.", 't.', $whereConditions) : '')."
            )
            -- 结果查询：返回路径信息
            SELECT `{$withTable}`.* FROM `{$withTable}`
            ".(! empty($idCondition) ? $idCondition : '').'
            ORDER BY path_ids
        ';

        // 创建新的查询构造器实例
        $newQuery = $query->getModel()->newQuery();
        $newQuery->getQuery()->from(new Expression("({$recursiveQuery}) as `{$withTable}`"));

        // 添加绑定参数
        if (! empty($bindings)) {
            $newQuery->addBinding($bindings, 'from');
        }

        // 恢复原始查询的列选择和绑定
        if ($originalColumns) {
            $newQuery->select($originalColumns);
        }
        $newQuery->addBinding($originalBindings['where'] ?? [], 'where');

        return $newQuery;
    }

    /**
     * 检查第一个节点是否是第二个节点的父节点
     *
     * @param  Builder  $query  查询构造器
     * @param  int  $parentId  父节点ID
     * @param  int  $childId  子节点ID
     * @param  string  $pidColumn  父ID字段名
     * @return bool 是否是父子关系
     */
    public static function checkIsParent(Builder $query, int $parentId, int $childId, string $pidColumn): bool
    {
        $table = $query->getModel()->getTable();
        $primaryKey = $query->getModel()->getKeyName();
        $withTable = 'recursive_'.Str::random(8);

        // 构建递归CTE查询
        $recursiveQuery = "
            WITH RECURSIVE `{$withTable}` AS (
                -- 基础查询：从子节点开始
                SELECT * FROM `{$table}` WHERE `{$primaryKey}` = ?
                
                UNION ALL
                
                -- 递归查询：查找所有父节点
                SELECT t.* 
                FROM `{$table}` t
                JOIN `{$withTable}` r ON t.`{$primaryKey}` = r.`{$pidColumn}`
                WHERE t.`{$primaryKey}` != r.`{$primaryKey}`  -- 防止自引用
            )
            -- 检查父节点是否存在
            SELECT COUNT(*) AS count FROM `{$withTable}` WHERE `{$primaryKey}` = ?
        ";

        $result = $query->getConnection()->selectOne($recursiveQuery, [$childId, $parentId]);

        return $result->count > 0;
    }

    /**
     * 查找某个节点的所有同级节点
     *
     * @param  Builder  $query  查询构造器
     * @param  int  $id  节点ID
     * @param  string  $pidColumn  父ID字段名
     * @param  bool  $includeSelf  是否包含自己
     * @return Builder 新的查询构造器实例
     */
    public static function findSiblings(Builder $query, int $id, string $pidColumn, bool $includeSelf = false): Builder
    {
        $table = $query->getModel()->getTable();
        $primaryKey = $query->getModel()->getKeyName();

        // 先获取该节点的父ID
        $parentId = $query->clone()
            ->select($pidColumn)
            ->where($primaryKey, $id)
            ->value($pidColumn);

        // 创建新的查询构造器实例
        $newQuery = $query->getModel()->newQuery();

        // 查询所有具有相同父ID的节点
        $newQuery = $newQuery->where($pidColumn, $parentId ?? 0);

        if (! $includeSelf) {
            $newQuery = $newQuery->where($primaryKey, '!=', $id);
        }

        // 保留原始查询的列选择和绑定
        if (! empty($query->getQuery()->columns)) {
            $newQuery->select($query->getQuery()->columns);
        }
        $newQuery->addBinding($query->getQuery()->bindings['where'] ?? [], 'where');

        return $newQuery;
    }

    /**
     * 构建树形结构查询
     *
     * @param  Builder  $query  查询构造器
     * @param  int|null  $pid  起始父ID
     * @param  string  $pidColumn  父ID字段名
     * @param  string  $nameColumn  名称字段名
     * @param  int  $maxDepth  最大深度
     * @param  string  $pathSeparator  路径分隔符
     * @return Builder 新的查询构造器实例
     */
    public static function buildTreeQuery(Builder $query, ?int $pid, string $pidColumn, string $nameColumn, int $maxDepth, string $pathSeparator = ' > '): Builder
    {
        $table = $query->getModel()->getTable();
        $primaryKey = $query->getModel()->getKeyName();
        $withTable = 'recursive_'.Str::random(8);

        // 保存原始查询的列选择和绑定
        $originalColumns = $query->getQuery()->columns;
        $originalBindings = $query->getQuery()->bindings;
        $columns = empty($originalColumns) ? ['*'] : $originalColumns;
        $columnList = implode(', ', array_map(function ($col) use ($table) {
            return $col === '*' ? '`'.$table.'`.*' : "`{$table}`.`{$col}`";
        }, $columns));

        // 处理孤立节点条件
        $isolatedCondition = $pid === null ?
            "OR NOT EXISTS (SELECT 1 FROM `{$table}` WHERE `{$primaryKey}` = `{$table}`.`{$pidColumn}`)" : '';

        // 构建递归CTE查询
        $recursiveQuery = "
            WITH RECURSIVE `{$withTable}` AS (
                -- 基础查询：从指定节点开始（默认为根节点和孤立节点）
                SELECT 
                    {$columnList},
                    CAST(`{$table}`.`{$primaryKey}` AS CHAR(200)) AS path_ids,
                    CAST(`{$table}`.`{$nameColumn}` AS CHAR(1000)) AS tree_path,
                    0 AS depth
                FROM `{$table}`
                WHERE `{$table}`.`{$pidColumn}` = ? {$isolatedCondition}
                
                UNION ALL
                
                -- 递归查询：查找所有子节点
                SELECT 
                    t.*,
                    CONCAT(r.path_ids, ',', t.`{$primaryKey}`) AS path_ids,
                    CONCAT(r.tree_path, '{$pathSeparator}', t.`{$nameColumn}`) AS tree_path,
                    r.depth + 1 AS depth
                FROM `{$table}` t
                JOIN `{$withTable}` r ON t.`{$pidColumn}` = r.`{$primaryKey}`
                WHERE r.depth < ?
            )
            -- 返回树形结构数据
            SELECT *, tree_path AS absolute_path FROM `{$withTable}` ORDER BY path_ids
        ";

        // 创建新的查询构造器实例
        $newQuery = $query->getModel()->newQuery();
        $newQuery->getQuery()->from(new Expression("({$recursiveQuery}) as `{$withTable}`"))
            ->addBinding([$pid ?? 0, $maxDepth], 'from');

        // 恢复原始查询的列选择和绑定
        if ($originalColumns) {
            $newQuery->select($originalColumns);
        }
        $newQuery->addBinding($originalBindings['where'] ?? [], 'where');

        return $newQuery;
    }

    /**
     * 通用递归查询
     *
     * @param  Builder  $query  查询构造器
     * @param  callable  $baseQuery  基础查询回调
     * @param  callable  $recursiveQuery  递归查询回调
     * @param  array  $columns  查询列
     * @param  int  $maxDepth  最大深度
     * @param  string  $depthColumn  深度字段名
     * @return Builder 新的查询构造器实例
     *
     * @throws InvalidArgumentException
     */
    public static function buildGenericRecursiveQuery(Builder $query, callable $baseQuery, callable $recursiveQuery, array $columns = ['*'], int $maxDepth = 100, string $depthColumn = 'depth'): Builder
    {
        $table = $query->getModel()->getTable();
        $withTable = 'recursive_'.Str::random(8);

        // 验证回调函数
        if (! is_callable($baseQuery) || ! is_callable($recursiveQuery)) {
            throw new InvalidArgumentException('Base query and recursive query must be callable');
        }

        // 保存原始查询的列选择和绑定
        $originalColumns = $query->getQuery()->columns;
        $originalBindings = $query->getQuery()->bindings;

        // 获取列选择
        $columnList = implode(', ', array_map(function ($col) use ($withTable) {
            return $col === '*' ? '`'.$withTable.'`.*' : "`{$withTable}`.`{$col}`";
        }, $columns));

        // 执行回调获取SQL部分
        $baseQuerySql = call_user_func($baseQuery, $query, $withTable);
        $recursiveQuerySql = call_user_func($recursiveQuery, $query, $withTable);

        // 验证返回的SQL
        if (! is_string($baseQuerySql) || ! is_string($recursiveQuerySql)) {
            throw new InvalidArgumentException('Base query and recursive query callbacks must return SQL strings');
        }

        // 确保基础查询包含深度列
        if (! Str::contains($baseQuerySql, "AS `{$depthColumn}`") && ! Str::contains($baseQuerySql, "AS {$depthColumn}")) {
            $baseQuerySql = rtrim($baseQuerySql, ';');
            $baseQuerySql .= ", 0 AS `{$depthColumn}`";
        }

        // 确保递归查询包含深度递增
        if (! Str::contains($recursiveQuerySql, "AS `{$depthColumn}`") && ! Str::contains($recursiveQuerySql, "AS {$depthColumn}")) {
            $recursiveQuerySql = rtrim($recursiveQuerySql, ';');
            $recursiveQuerySql .= ", r.`{$depthColumn}` + 1 AS `{$depthColumn}`";
        }

        // 构建完整的递归CTE查询
        $recursiveQuery = "
            WITH RECURSIVE `{$withTable}` AS (
                -- 基础查询部分
                {$baseQuerySql}
                
                UNION ALL
                
                -- 递归查询部分
                {$recursiveQuerySql}
                WHERE r.`{$depthColumn}` < ?
            )
            SELECT {$columnList} FROM `{$withTable}`
        ";

        // 创建新的查询构造器实例
        $newQuery = $query->getModel()->newQuery();
        $newQuery->getQuery()->from(new Expression("({$recursiveQuery}) as `{$withTable}`"))
            ->addBinding([$maxDepth], 'from');

        // 恢复原始查询的列选择和绑定
        if ($originalColumns) {
            $newQuery->select($originalColumns);
        }
        $newQuery->addBinding($originalBindings['where'] ?? [], 'where');

        return $newQuery;
    }
}
