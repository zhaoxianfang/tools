<?php

namespace zxf\Laravel\BuilderQuery\Macros;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Query\Builder as QueryBuilder;

class RandomMacro
{
    /**
     * 注册 random() 宏函数，用于随机查询记录
     *
     * 使用方式：
     * User::where('status', 1)->random(5)->get();
     */
    public static function register()
    {
        Builder::macro('random', function (int $limit = 10, string $primaryKey = 'id') {
            /** @var Builder $this */
            $model = $this->getModel();
            $table = $model->getTable();

            // 获取当前查询的基础 SQL 构建器（保留作用域）
            $subQuery = $this->toBase()->select(
                "$table.$primaryKey",
                DB::raw('ROW_NUMBER() OVER (ORDER BY RAND()) as rnd_rank')
            );

            // 使用子查询随机排序后选出前 $limit 条记录
            return $model->newQuery()
                ->whereIn("$table.$primaryKey", function ($query) use ($subQuery, $limit, $primaryKey) {
                    $query->select($primaryKey)
                        ->from(DB::raw("({$subQuery->toSql()}) as ranked"))
                        ->mergeBindings($subQuery)
                        ->where('rnd_rank', '<=', $limit);
                });
        });
    }

    // 另一种实现方式
    public function randomTwo()
    {
        /**
         * 随机查询宏
         *
         * @param  int  $limit  返回记录数，默认10
         * @param  string  $primaryKey  主键名，默认'id'
         * @return Builder
         */
        Builder::macro('random', function (int $limit = 10, string $primaryKey = 'id') {
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
        Builder::macro('replicateQuery', function (QueryBuilder $source, QueryBuilder $dest) {
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
        Builder::macro('replicateWheres', function (QueryBuilder $source, QueryBuilder $dest) {
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
}
