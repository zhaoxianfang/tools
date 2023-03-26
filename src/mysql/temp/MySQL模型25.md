帮我使用php8和mysqli实现一个操作MySQL8的高级封装类，要求加上中文注释，并封装和实现所有mysql8操作方法和接口类,包括过滤查询、条件聚合查询、事务操作、多态关联统计、预加载、模型关联、子查询、排序、分组、过滤、锁表查询、锁表更新的封装类功能


编程
好的，我可以帮助你回答这个问题。以下是一个使用PHP 8和mysqli实现操作MySQL 8的高级封装类的示例代码，包括中文注释：

<?php

// 定义一个MySQL 8的高级封装类
class MySQLiDB {
  
  // 数据库主机地址
  private $host;
  
  // 数据库用户名
  private $user;
  
  // 数据库密码
  private $password;
  
  // 数据库名称
  private $database;
  
  // mysqli连接对象
  private $mysqli;
  
  // 构造函数，用于初始化数据库连接
  public function __construct($host, $user, $password, $database) {
    $this->host = $host;
    $this->user = $user;
    $this->password = $password;
    $this->database = $database;
    
    // 创建mysqli连接对象
    $this->mysqli = new mysqli($host, $user, $password, $database);
    
    // 检查连接是否成功
    if ($this->mysqli->connect_error) {
      die("连接失败：" . $this->mysqli->connect_error);
    }
  }
  
  // 析构函数，用于关闭数据库连接
  public function __destruct() {
    $this->mysqli->close();
  }
  
  // 过滤查询方法，用于防止SQL注入攻击
  public function filterQuery($query) {
    return $this->mysqli->real_escape_string($query);
  }
  
  // 条件聚合查询方法，用于根据指定条件查询数据
  public function select($table, $columns = '*', $where = '', $orderBy = '', $limit = '') {
    $sql = "SELECT $columns FROM $table";
    
    if (!empty($where)) {
      $sql .= " WHERE $where";
    }
    
    if (!empty($orderBy)) {
      $sql .= " ORDER BY $orderBy";
    }
    
    if (!empty($limit)) {
      $sql .= " LIMIT $limit";
    }
    
    // 执行查询
    $result = $this->mysqli->query($sql);
    
    // 检查查询是否成功
    if (!$result) {
      die("查询失败：" . $this->mysqli->error);
    }
    
    // 返回查询结果
    return $result->fetch_all(MYSQLI_ASSOC);
  }
  
  // 插入方法，用于向指定表中插入数据
  public function insert($table, $data) {
    $keys = array_keys($data);
    $values = array_map(array($this->mysqli, 'real_escape_string'), array_values($data));
    
    $sql = "INSERT INTO $table (" . implode(',', $keys) . ") VALUES ('" . implode("','", $values) . "')";
    
    // 执行插入操作
    $result = $this->mysqli->query($sql);
    
    // 检查插入是否成功
    if (!$result) {
      die("插入失败：" . $this->mysqli->error);
    }
    
    // 返回插入的ID
    return $this->mysqli->insert_id;
  }
  
  // 更新方法，用于更新指定表中的数据
  public function update($table, $data, $where = '') {
    $set = array();
    
    foreach ($data as $key => $value) {
      $set[] = "$key='" . $this->mysqli->real_escape_string($value) . "'";
    }
    
    $sql = "UPDATE $table SET " . implode(',', $set);
    
    if (!empty($where)) {
      $sql .= " WHERE $where";
    }
    
    // 执行更新操作
    $result = $this->mysqli->query($sql);
    
    // 检查更新是否成功
    if (!$result) {
      die("更新失败：" . $this->mysqli->error);
    }
    
    // 返回受影响的行数
    return $this->mysqli->affected_rows;
  }
  
  // 删除方法，用于删除指定表中的数据
  public function delete($table, $where = '') {
    $sql = "DELETE FROM $table";
    
    if (!empty($where)) {
      $sql .= " WHERE $where";
    }
    
    // 执行删除操作
    $result = $this->mysqli->query($sql);
    
    // 检查删除是否成功
    if (!$result) {
      die("删除失败：" . $this->mysqli->error);
    }
    
    // 返回受影响的行数
    return $this->mysqli->affected_rows;
  }
  
  // 开始事务方法，用于开始一个新的事务
  public function beginTransaction() {
    $this->mysqli->begin_transaction();
  }
  
  // 提交事务方法，用于提交一个事务
  public function commit() {
    $this->mysqli->commit();
  }
  
  // 回滚事务方法，用于回滚一个事务
  public function rollback() {
    $this->mysqli->rollback();
  }
  
  // 多态关联统计方法，用于统计指定表中的数据
  public function count($table, $where = '') {
    $sql = "SELECT COUNT(*) AS count FROM $table";
    
    if (!empty($where)) {
      $sql .= " WHERE $where";
    }
    
    // 执行查询
    $result = $this->mysqli->query($sql);
    
    // 检查查询是否成功
    if (!$result) {
      die("查询失败：" . $this->mysqli->error);
    }
    
    // 返回查询结果
    $row = $result->fetch_assoc();
    return $row['count'];
  }
  
  // 预加载方法，用于预先加载与指定表相关联的数据
  public function preload($table, $column, $where = '') {
    $sql = "SELECT $column FROM $table";
    
    if (!empty($where)) {
      $sql .= " WHERE $where";
    }
    
    // 执行查询
    $result = $this->mysqli->query($sql);
    
    // 检查查询是否成功
    if (!$result) {
      die("查询失败：" . $this->mysqli->error);
    }
    
    // 返回查询结果
    return $result->fetch_all(MYSQLI_ASSOC);
  }
  
  // 模型关联方法，用于将指定表与其他表进行关联
  public function join($table, $joinTable, $joinColumn, $where = '') {
    $sql = "SELECT * FROM $table JOIN $joinTable ON $table.$joinColumn = $joinTable.$joinColumn";
    
    if (!empty($where)) {
      $sql .= " WHERE $where";
    }
    
    // 执行查询
    $result = $this->mysqli->query($sql);
    
    // 检查查询是否成功
    if (!$result) {
      die("查询失败：" . $this->mysqli->error);
    }
    
    // 返回查询结果
    return $result->fetch_all(MYSQLI_ASSOC);
  }
  
  // 子查询方法，用于在查询中使用子查询
  public function subquery($table, $columns = '*', $where = '', $orderBy = '', $limit = '') {
    $sql = "SELECT $columns FROM (SELECT * FROM $table";
    
    if (!empty($where)) {
      $sql .= " WHERE $where";
    }
    
    if (!empty($orderBy)) {
      $sql .= " ORDER BY $orderBy";
    }
    
    if (!empty($limit)) {
      $sql .= " LIMIT $limit";
    }
    
    $sql .= ") AS subquery";
    
    // 执行查询
    $result = $this->mysqli->query($sql);
    
    // 检查查询是否成功
    if (!$result) {
      die("查询失败：" . $this->mysqli->error);
    }
    
    // 返回查询结果
    return $result->fetch_all(MYSQLI_ASSOC);
  }
  
  // 排序方法，用于按照指定的列对查询结果进行排序
  public function orderBy($table, $columns = '*', $orderBy = '', $where = '', $limit = '') {
    $sql = "SELECT $columns FROM $table";
    
    if (!empty($where)) {
      $sql .= " WHERE $where";
    }
    
    if (!empty($orderBy)) {
      $sql .= " ORDER BY $orderBy";
    }
    
    if (!empty($limit)) {
      $sql .= " LIMIT $limit";
    }
    
    // 执行查询
    $result = $this->mysqli->query($sql);
    
    // 检查查询是否成功
    if (!$result) {
      die("查询失败：" . $this->mysqli->error);
    }
    
    // 返回查询结果
    return $result->fetch_all(MYSQLI_ASSOC);
  }
  
  // 分组方法，用于按照指定的列对查询结果进行分组
  public function groupBy($table, $columns = '*', $groupBy = '', $where = '', $limit = '') {
    $sql = "SELECT $columns FROM $table";
    
    if (!empty($where)) {
      $sql .= " WHERE $where";
    }
    
    if (!empty($groupBy)) {
      $sql .= " GROUP BY $groupBy";
    }
    
    if (!empty($limit)) {
      $sql .= " LIMIT $limit";
    }
    
    // 执行查询
    $result = $this->mysqli->query($sql);
    
    // 检查查询是否成功
    if (!$result) {
      die("查询失败：" . $this->mysqli->error);
    }
    
    // 返回查询结果
    return $result->fetch_all(MYSQLI_ASSOC);
  }
  
  // 过滤方法，用于对查询结果进行过滤
  public function filter($table, $columns = '*', $filter = '', $where = '', $limit = '') {
    $sql = "SELECT $columns FROM $table";
    
    if (!empty($where)) {
      $sql .= " WHERE $where";
    }
    
    if (!empty($filter)) {
      $sql .= " $filter";
    }
    
    if (!empty($limit)) {
      $sql .= " LIMIT $limit";
    }
    
    // 执行查询
    $result = $this->mysqli->query($sql);
    
    // 检查查询是否成功
    if (!$result) {
      die("查询失败：" . $this->mysqli->error);
    }
    
    // 返回查询结果
    return $result->fetch_all(MYSQLI_ASSOC);
  }
  
  // 锁表查询方法，用于锁定指定表并查询数据
  public function lock($table, $columns = '*', $where = '', $orderBy = '', $limit = '') {
    $sql = "SELECT $columns FROM $table";
    
    if (!empty($where)) {
      $sql .= " WHERE $where";
    }
    
    if (!empty($orderBy)) {
      $sql .= " ORDER BY $orderBy";
    }
    
    if (!empty($limit)) {
      $sql .= " LIMIT $limit";
    }
    
    // 执行查询并锁定表
    $result = $this->mysqli->query("$sql FOR UPDATE");
    
    // 检查查询是否成功
    if (!$result) {
      die("查询失败：" . $this->mysqli->error);
    }
    
    // 返回查询结果
    return $result->fetch_all(MYSQLI_ASSOC);
  }
  
  // 锁表更新方法，用于锁定指定表并更新数据
  public function lockUpdate($table, $data, $where = '') {
    $set = array();
    
    foreach ($data as $key => $value) {
      $set[] = "$key='" . $this->mysqli->real_escape_string($value) . "'";
    }
    
    $sql = "UPDATE $table SET " . implode(',', $set);
    
    if (!empty($where)) {
      $sql .= " WHERE $where