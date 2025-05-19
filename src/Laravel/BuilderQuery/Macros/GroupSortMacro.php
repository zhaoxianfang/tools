<?php

namespace zxf\Laravel\BuilderQuery\Macros;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class GroupSortMacro
{
    /**
     * 注册 groupSort 宏函数
     *
     * @return void
     */
    public static function register()
    {
        /**
         * groupSort(分组排序查询) 宏函数
         *
         * @example Article::query()->groupSort('classify_id', [1,3],'read','desc')->get(); // 查询每个文章分组下read最高的第1到3名文章
         *          Article::query()->groupSort('classify_id', 9,'read','desc')->get(); // 获取每个文章分组下read第9名文章
         *          Article::query()->groupSort('classify_id', -1,'read','desc')->get(); // 获取每个文章分组下read最后1名文章
         *
         * @param  string  $groupBy  分组字段名 eg: classify_id
         * @param  int|array  $ranks  排名，名次(数字表示第n名，带2个数字的数组[n,m]表示查询第n到m名)，倒数第n名使用负数，eg: 1, [2, 4], -1
         * @param  string  $orderBy  排序字段名 eg: read
         * @param  string  $direction  排序方向，eg: desc, asc
         */
        Builder::macro('groupSort', function (string $groupBy, int|array $ranks, string $orderBy = 'read', string $direction = 'desc') {
            /** @var Builder $this */
            $model = $this->getModel();
            $table = $model->getTable();
            $primaryKey = $model->getKeyName();

            // 克隆查询构造器并移除分页限制避免影响子查询
            $baseQuery = clone $this;
            $baseQuery->getQuery()->limit = null;
            $baseQuery->getQuery()->offset = null;

            // 添加开窗函数排名
            $orderDirection = strtoupper($direction);
            $partitionExpr = "ROW_NUMBER() OVER (PARTITION BY `$groupBy` ORDER BY `$orderBy` $orderDirection) AS row_rank";
            $baseQuery->select([$table.'.'.$primaryKey, DB::raw($partitionExpr)]);

            // 包装子查询并合并绑定
            $subSql = $baseQuery->toSql();
            $rankedSubQuery = DB::table(DB::raw("({$subSql}) as ranked"))
                ->mergeBindings($baseQuery->getQuery());

            // 添加 row_rank 条件
            if (is_array($ranks) && count($ranks) === 2) {
                $rankedSubQuery->whereBetween('row_rank', [$ranks[0], $ranks[1]]);
            } elseif (is_int($ranks)) {
                $rankedSubQuery->where('row_rank', abs($ranks));
            }

            // 查询主表数据
            return $model->newQuery()->whereIn($primaryKey, $rankedSubQuery->select($primaryKey));
        });
    }
}
