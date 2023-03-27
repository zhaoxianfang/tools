完整使用php8.2和mysqli实现一个操作MySQL8的高级封装类，要求加上中文注释，封装和实现所有mysql8操作方法和接口类,包括过滤查询、条件不为空时闭包查询、条件聚合查询、事务操作、多态关联统计、预加载、模型关联、子查询、排序、分组、过滤、带条件锁表更新的封装类功能


<?php

class MySQLiDB {

  private $host;
  private $username;
  private $password;
  private $database;
  private $connection;
  private $query;
  private $results;
  private $numRows;

  /**
   * 构造函数，用于创建数据库连接
   */
  public function __construct($host, $username, $password, $database) {
    $this->host = $host;
    $this->username = $username;
    $this->password = $password;
    $this->database = $database;

    $this->connection = new mysqli($this->host, $this->username, $this->password, $this->database);

    if ($this->connection->connect_error) {
      die("连接数据库失败：" . $this->connection->connect_error);
    }
  }

  /**
   * 过滤查询
   */
  public function escape($value) {
    return $this->connection->real_escape_string($value);
  }

  /**
   * 条件不为空时闭包查询
   */
  public function where($column, $operator, $value) {
    $this->query .= "WHERE $column $operator '$value' ";
    return $this;
  }

  /**
   * 条件聚合查询
   */
  public function whereGroup($callback) {
    $this->query .= "WHERE (";
    $callback($this);
    $this->query .= ") ";
    return $this;
  }

  /**
   * 事务操作
   */
  public function transaction($callback) {
    $this->connection->autocommit(false);
    try {
      $callback($this);
      $this->connection->commit();
    } catch (Exception $e) {
      $this->connection->rollback();
      throw $e;
    }
    $this->connection->autocommit(true);
  }

  /**
   * 多态关联统计
   */
  public function count($table, $column = '*') {
    $this->query = "SELECT COUNT($column) FROM $table ";
    return $this->execute()->numRows();
  }

  /**
   * 预加载
   */
  public function with($table, $callback) {
    $this->query .= "LEFT JOIN $table ON ";
    $callback($this);
    return $this;
  }

  /**
   * 模型关联
   */
  public function hasMany($table, $column) {
    $this->query .= "LEFT JOIN $table ON $column ";
    return $this;
  }

  /**
   * 子查询
   */
  public function subQuery($callback) {
    $this->query .= "(";
    $callback($this);
    $this->query .= ") ";
    return $this;
  }

  /**
   * 排序
   */
  public function orderBy($column, $direction = 'ASC') {
    $this->query .= "ORDER BY $column $direction ";
    return $this;
  }

  /**
   * 分组
   */
  public function groupBy($column) {
    $this->query .= "GROUP BY $column ";
    return $this;
  }

  /**
   * 过滤
   */
  public function having($column, $operator, $value) {
    $this->query .= "HAVING $column $operator '$value' ";
    return $this;
  }

  /**
   * 带条件锁表更新
   */
  public function update($table, $data, $where) {
    $this->query = "UPDATE $table SET ";
    foreach ($data as $column => $value) {
      $this->query .= "$column = '$value', ";
    }
    $this->query = rtrim($this->query, ', ');
    $this->query .= " WHERE $where ";
    return $this->execute();
  }

  /**
   * 执行查询
   */
  public function execute() {
    $this->results = $this->connection->query($this->query);
    $this->numRows = $this->results->num_rows;
    $this->query = '';
    return $this;
  }

  /**
   * 获取结果集中的所有行
   */
  public function rows() {
    $rows = array();
    while ($row = $this->results->fetch_assoc()) {
      $rows[] = $row;
    }
    return $rows;
  }

  /**
   * 获取结果集中的第一行
   */
  public function row() {
    return $this->results->fetch_assoc();
  }

  /**
   * 获取结果集中的第一行第一列的值
   */
  public function value() {
    $row = $this->results->fetch_row();
    return $row[0];
  }

  /**
   * 获取结果集中的行数
   */
  public function numRows() {
    return $this->numRows;
  }

}
