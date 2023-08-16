<?php

namespace zxf\Database\Contracts;

interface MysqlInterface
{
    // 构造函数类
    public function __construct($connectionName = 'default', ...$args);

    /**
     * 重新示例化
     *
     * @return $this
     */
    public static function newQuery();

    /**
     * 配置 连接数据库
     */
    public function connect($connectionName = 'default', ...$args);

    // 关闭连接
    public function close();

    /**
     * 设置要操作的数据表
     * eg:  table('user')  // 单个字符串参数形式
     *      table('user', 'u')  // 多个字符串参数形式设置别名
     */
    public function table($table, $alias = '');

    /**
     * 选择要查询的列
     *
     * eg:   select('id, name, SUM(number) AS sum_num, ...')     // 单个字符串参数形式
     *       select('id', 'name','SUM(number) AS sum_num' ...)   // 多个字符串参数形式
     *       select(['id', 'name','SUM(number) AS sum_num' ...]) // 数组形式
     */
    public function select(...$columns);

    /**
     * 生成查询的sql语句
     */
    public function toSql();

    /**
     * 添加 WHERE 条件
     * eg: where('id = 1')
     *     where('id',1)
     *     where([ ['id',1], ['name','like','%威四方%'], ['status','<>',1] ])
     *     where(function($query){
     *        $query->where('age','>',21);
     *     })
     */
    public function where(...$conditions);

    public function whereIn($field, $data);

    public function whereNotIn($field, $data);

    /**
     * 添加 OR 条件
     */
    public function orWhere(...$conditions);

    /**
     * 两个字段比较
     */
    public function whereColumn($first, $operator = '=', $second = null, $boolean = 'AND');

    /**
     * OR 两个字段比较
     */
    public function orWhereColumn($first, $operator = '=', $second = null);

    /**
     * 字段为空
     */
    public function whereNull($field);

    /**
     * 字段不为空
     */
    public function whereNotNull($field);

    /**
     * 传入的$column值存在时才执行
     *
     * @param $column
     * @param $callback
     *
     * @return $this
     */
    public function when($column, $callback);

    /**
     * JOIN 的闭包 ON 查询部分
     *
     * @param ...$args
     */
    public function on(...$args);

    /**
     * 多表关联查询
     *
     * eg:  join('table_name AS t','table1.id = t.table1_id')
     *
     */
    public function join(...$joins);

    // 添加左连接查询
    public function leftJoin(...$joins);

    // 添加右连接查询
    public function rightJoin(...$joins);

    // 添加全连接查询
    public function fullJoin($joins);

    // 分组查询
    public function groupBy(...$columns);

    public function having(...$args);

    // 排序功能
    public function orderBy(...$columns);

    public function limit($offset = 0, $limit = 10);

    /**
     * 执行查询
     */
    public function execute();

    /**
     * 获取所有结果
     */
    public function get();

    /**
     * 获取第一条结果
     */
    public function first();

    /**
     * 判断是否存在
     */
    public function exists();

    /**
     * 判断是否 不存在
     */
    public function doesntExist();

    /**
     * 插入数据
     */
    public function insert($data);

    /**
     * 插入数据并获取ID
     */
    public function insertGetId($data);

    /**
     * 获取上一次插入的ID
     *
     * @return false|string
     */
    public function getLastInsertedId();

    /**
     * 获取错误信息
     *
     * @return string
     */
    public function getError();

    /**
     * 更新数据
     */
    public function update($data);

    /**
     * 批量插入并返回插入的ID
     */
    public function batchInsertAndGetIds($data);

    // 添加批量插入功能
    public function batchInsert($data);

    /**
     * 批量更新记录
     *
     * @param array  $data          被跟新的数据二维数组
     * @param string $byColumnField 根据$data中的哪个字段来更新
     *
     * @return false|int 返回影响的行数，false表示更新失败, int表示影响的行数
     *              返回false时，调用用 ->getError() 方法获取错误信息
     *
     * eg:  $updateData = [
     *          ['id' => 2, 'username' => 'username2-multi', 'nickname' => 'nickname2-multi'],
     *          ['id' => 3, 'username' => 'username3-multi', 'nickname' => 'nickname3-multi'],
     *          // 添加更多的更新数据项
     *      ];
     *      $db->table('test')->batchUpdate($updateData,'id');
     */
    public function batchUpdate(array $data, string $byColumnField = 'id');

    // 添加更新自增操作
    public function increment($column, $amount = 1);

    // 添加更新自减操作
    public function decrement($column, $amount = 1);

    /**
     * 删除数据
     */
    public function delete();


    // 添加设置事务隔离级别的方法
    public function setTransactionIsolation($isolationLevel);

    // 执行 SQL 语句，返回PDOStatement对象,可以理解为结果集
    public function query($sql = '');

    // 执行一条 SQL 语句，并返回受影响的行数
    public function exec($sql = '');

    // 添加防止 SQL 注入功能(为SQL语句中的字符串添加引号)
    public function quote($string);

    // 开启事务的方法
    public function beginTransaction();

    // 添加提交事务的方法
    public function commit();

    // 添加回滚事务的方法
    public function rollback();

    /**
     * 执行事务
     */
    public function transaction($callback);

    // 检测是否在一个事务内
    public function inTransaction();

    /**
     * 清除查询条件和参数
     */
    public function reset();

    // 添加闭包查询功能
    public function closure($closure);

    // 添加分页查询的方法
    public function paginate($limit = 10, $currentPage = 1);

    /**
     * 获取数据表字段信息
     */
    public function getColumns();

    /**
     * 获取最后执行的查询语句
     */
    public function getLastQuery();

    // 遍历查询结果的方法
    public function each($callback);

    /**
     * 获取数据表主键列名
     */
    public function getPrimaryKey();

    /**
     * 获取数据表索引列信息
     */
    public function getIndexes();

    /**
     * 获取结果数量
     */
    public function count($column = '*');

    // 添加取最大值的方法
    public function max($column);

    // 添加取最小值的方法
    public function min($column);

    // 添加取平均值的方法
    public function avg($column);

    // 添加取和的方法
    public function sum($column);

}
