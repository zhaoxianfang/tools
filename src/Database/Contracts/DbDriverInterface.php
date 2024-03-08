<?php

namespace zxf\Database\Contracts;

use Exception;

interface DbDriverInterface
{

    // ================================================
    // 以下是数据库驱动的方法
    // ================================================

    /**
     * 重新示例化
     *
     * @return $this
     */
    public static function newQuery();

    /**
     * 配置 驱动连接数据库的实现
     *
     * @param string $connectionName 连接名称
     * @param array  $options        连接参数, 包含 host、dbname、username、password 等
     *
     * @throws Exception
     */
    public function connect(string $connectionName = 'default', array $options = []);

    /**
     * 关闭连接
     */
    public function close();

    /**
     * 执行$sql直接 「查询」
     *
     * @param string $sql sql语句
     *
     * @return array
     */
    public function query(string $sql);

    /**
     * 直接执行$sql语句的实现
     *
     * @param string     $sql        sql语句
     * @param array|null $bindParams 绑定参数
     *
     * @return mixed
     * @throws Exception
     */
    public function runSql(string $sql = '', array|null $bindParams = null): mixed;

    /**
     * 各个驱动实现自己的数据处理
     *
     * @param mixed $resource 资源
     *
     * @return array
     */
    public function dataProcessing(mixed $resource): array;

    // ================================================
    // 以下是查询构造器的方法
    // ================================================


    /**
     * 获取所有结果
     */
    public function get();

    /**
     * 获取一条结果
     */
    public function find();

    /**
     * 插入数据
     */
    public function insert(array $data);

    /**
     * 插入数据, 返回插入的id
     */
    public function insertGetId(array $data);

    /**
     * 获取错误信息
     *
     * @return string
     */
    public function getError();

    /**
     * 更新数据
     */
    public function update(array $data);

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
     */
    public function upsert(array $data = [], array $uniqueColumn = [], array $updateColumn = []);

    /**
     * 添加更新自增操作
     */
    public function increment(string $column, int $amount = 1);

    /**
     * 添加更新自减操作
     */
    public function decrement(string $column, int $amount = 1);

    /**
     * 删除数据
     */
    public function delete();

    /**
     * 清除查询条件和参数
     */
    public function reset();

    /**
     * 遍历查询结果
     */
    public function each($callback);

    /**
     * 聚合查询
     */
    public function aggregate(string $aggregate = 'count', string $column = 'id');

    /**
     * 聚合查询 - 获取结果数量
     */
    public function count(string $column = 'id');

    /**
     * 聚合查询 - 最大值
     */
    public function max(string $column);

    /**
     * 聚合查询 - 最小值
     */
    public function min(string $column);

    /**
     * 聚合查询 - 平均值
     */
    public function avg(string $column);

    /**
     * 聚合查询 - 求和
     */
    public function sum(string $column);

    /**
     * 判断是否存在
     */
    public function exists();

    /**
     * 判断是否 不存在
     */
    public function doesntExist();

    // ================================================
    // 以下是事务操作
    // ================================================

    /**
     * 开启事务
     */
    public function beginTransaction();

    /**
     * 提交事务
     */
    public function commit();

    /**
     * 回滚事务
     */
    public function rollback();

    /**
     * 执行事务
     */
    public function transaction($callback);
}