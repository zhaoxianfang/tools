如何使用php8实现一个mysql8的基础封装类，要求使用中文注释，并实现mysql8提供的所有接口类，例如增删查改、关联、事务、锁表、关联、聚合查询、预加载等

```php
<?php
/**
 * MySQL8的基础封装类
 *
 * @author 
 */
class MySQL8 {
    
    /**
     * 数据库连接句柄
     *
     * @var PDO
     */
    private $dbh;
    
    /**
     * MySQL8构造函数
     *
     * @param string $host     数据库主机
     * @param string $username 数据库用户名
     * @param string $password 数据库密码
     * @param string $dbname   数据库名
     * @param string $charset  数据库字符集
     */
    public function __construct($host, $username, $password, $dbname, $charset = 'utf8mb4') {
        $dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        $this->dbh = new PDO($dsn, $username, $password, $options);
    }
    
    /**
     * 查询一条数据
     *
     * @param string $sql      SQL语句
     * @param array  $bindings 绑定参数
     * @return array|false
     */
    public function fetch($sql, $bindings = []) {
        $stmt = $this->dbh->prepare($sql);
        $stmt->execute($bindings);
        return $stmt->fetch();
    }
    
    /**
     * 查询多条数据
     *
     * @param string $sql      SQL语句
     * @param array  $bindings 绑定参数
     * @return array
     */
    public function fetchAll($sql, $bindings = []) {
        $stmt = $this->dbh->prepare($sql);
        $stmt->execute($bindings);
        return $stmt->fetchAll();
    }
    
    /**
     * 执行一条SQL语句
     *
     * @param string $sql      SQL语句
     * @param array  $bindings 绑定参数
     * @return int
     */
    public function execute($sql, $bindings = []) {
        $stmt = $this->dbh->prepare($sql);
        $stmt->execute($bindings);
        return $stmt->rowCount();
    }
    
    /**
     * 开启事务
     *
     * @return void
     */
    public function beginTransaction() {
        $this->dbh->beginTransaction();
    }
    
    /**
     * 提交事务
     *
     * @return void
     */
    public function commit() {
        $this->dbh->commit();
    }
    
    /**
     * 回滚事务
     *
     * @return void
     */
    public function rollback() {
        $this->dbh->rollback();
    }
    
    /**
     * 锁表
     *
     * @param string $table 表名
     * @return void
     */
    public function lockTable($table) {
        $this->execute("LOCK TABLES $table WRITE");
    }
    
    /**
     * 解锁表
     *
     * @return void
     */
    public function unlockTables() {
        $this->execute("UNLOCK TABLES");
    }
    
    /**
     * 查询记录数
     *
     * @param string $table   表名
     * @param string $where   查询条件
     * @param array  $bindings 绑定参数
     * @return int
     */
    public function count($table, $where = '', $bindings = []) {
        $sql = "SELECT COUNT(*) as count FROM `$table`";
        if ($where) {
            $sql .= " WHERE $where";
        }
        $row = $this->fetch($sql, $bindings);
        return $row['count'];
    }
    
    /**
     * 插入一条记录
     *
     * @param string $table   表名
     * @param array  $data    插入的数据
     * @return int
     */
    public function insert($table, $data) {
        $keys = array_keys($data);
        $sql = "INSERT INTO `$table` (`" . implode('`,`', $keys) . "`) VALUES (" . implode(',', array_fill(0, count($keys), '?')) . ")";
        $values = array_values($data);
        $this->execute($sql, $values);
        return $this->dbh->lastInsertId();
    }
    
    /**
     * 更新记录
     *
     * @param string $table   表名
     * @param array  $data    更新的数据
     * @param string $where   更新条件
     * @param array  $bindings 绑定参数
     * @return int
     */
    public function update($table, $data, $where, $bindings = []) {
        $sets = [];
        foreach ($data as $key => $value) {
            $sets[] = "`$key`=?";
        }
        $sql = "UPDATE `$table` SET " . implode(',', $sets) . " WHERE $where";
        $values = array_values($data);
        $values = array_merge($values, $bindings);
        return $this->execute($sql, $values);
    }
    
    /**
     * 删除记录
     *
     * @param string $table   表名
     * @param string $where   删除条件
     * @param array  $bindings 绑定参数
     * @return int
     */
    public function delete($table, $where, $bindings = []) {
        $sql = "DELETE FROM `$table` WHERE $where";
        return $this->execute($sql, $bindings);
    }
    
    /**
     * 关联查询
     *
     * @param string $table   主表名
     * @param string $join    关联表名及关联条件
     * @param string $columns 查询的列名
     * @param string $where   查询条件
     * @param array  $bindings 绑定参数
     * @return array
     */
    public function join($table, $join, $columns = '*', $where = '', $bindings = []) {
        $sql = "SELECT $columns FROM `$table` $join";
        if ($where) {
            $sql .= " WHERE $where";
        }
        return $this->fetchAll($sql, $bindings);
    }
    
    /**
     * 聚合查询
     *
     * @param string $table   表名
     * @param string $column  聚合列名
     * @param string $where   查询条件
     * @param array  $bindings 绑定参数
     * @return int
     */
    public function aggregate($table, $column, $where = '', $bindings = []) {
        $sql = "SELECT $column FROM `$table`";
        if ($where) {
            $sql .= " WHERE $where";
        }
        $row = $this->fetch($sql, $bindings);
        return $row[$column];
    }
    
    /**
     * 预加载
     *
     * @param string $table     表名
     * @param array  $relations 关联关系
     * @param string $where     查询条件
     * @param array  $bindings   绑定参数
     * @return array
     */
    public function with($table, $relations, $where = '', $bindings = []) {
        $columns = "$table.*";
        $joins = [];
        foreach ($relations as $relation) {
            $joinTable = $relation[0];
            $joinColumn = $relation[1];
            $columns .= ", $joinTable.*";
            $joins[] = "LEFT JOIN `$joinTable` ON `$table`.`$joinColumn`=`$joinTable`.`id`";
        }
        $sql = "SELECT $columns FROM `$table` " . implode(' ', $joins);
        if ($where) {
            $sql .= " WHERE $where";
        }
        return $this->fetchAll($sql, $bindings);
    }
}
```