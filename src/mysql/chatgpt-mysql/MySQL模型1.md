好的，以下是一个简单的示例，展示如何使用 PHP 8 和 MySQL 实现一个基本的模型类，包括增加、删除、修改、查询、关联和分页功能。

```php
<?php

class Model {
private $conn;
private $table;

public function __construct($table) {
$this->conn = new mysqli('localhost', 'username', 'password', 'database');
$this->table = $table;
}

public function create($data) {
$keys = array_keys($data);
$values = array_map(function($value) {
return "'" . $value . "'";
}, array_values($data));

$query = sprintf("INSERT INTO %s (%s) VALUES (%s)", $this->table, implode(',', $keys), implode(',', $values));

return $this->conn->query($query);
}

public function update($id, $data) {
$set = array_map(function($key, $value) {
return sprintf("%s='%s'", $key, $value);
}, array_keys($data), array_values($data));

$query = sprintf("UPDATE %s SET %s WHERE id=%d", $this->table, implode(',', $set), $id);

return $this->conn->query($query);
}

public function delete($id) {
$query = sprintf("DELETE FROM %s WHERE id=%d", $this->table, $id);

return $this->conn->query($query);
}

public function find($id) {
$query = sprintf("SELECT * FROM %s WHERE id=%d", $this->table, $id);

$result = $this->conn->query($query);

return $result->fetch_assoc();
}

public function all() {
$query = sprintf("SELECT * FROM %s", $this->table);

$result = $this->conn->query($query);

return $result->fetch_all(MYSQLI_ASSOC);
}

public function paginate($page = 1, $limit = 10) {
$offset = ($page - 1) * $limit;

$query = sprintf("SELECT * FROM %s LIMIT %d, %d", $this->table, $offset, $limit);

$result = $this->conn->query($query);

return $result->fetch_all(MYSQLI_ASSOC);
}

public function belongsTo($model, $foreign_key) {
$query = sprintf("SELECT * FROM %s WHERE id=%d", $model->table, $this->$foreign_key);

$result = $this->conn->query($query);

return $result->fetch_assoc();
}

public function hasMany($model, $foreign_key) {
$query = sprintf("SELECT * FROM %s WHERE %s=%d", $model->table, $foreign_key, $this->id);

$result = $this->conn->query($query);

return $result->fetch_all(MYSQLI_ASSOC);
}
}
```

这个模型类包括一个构造函数，用于连接到数据库并设置表名。它还包括一个 create() 方法，用于插入新记录；一个 update() 方法，用于更新记录；一个 delete() 方法，用于删除记录；一个 find() 方法，用于查找单个记录；一个 all() 方法，用于查找所有记录；一个 paginate() 方法，用于分页查找记录；一个 belongsTo() 方法，用于关联另一个模型类的单个记录；以及一个 hasMany() 方法，用于关联另一个模型类的多个记录。

您可以根据您的具体需求对此模型类进行修改和扩展。