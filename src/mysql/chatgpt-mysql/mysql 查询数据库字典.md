以下是使用mysqli实现查询数据库表名、字段名、数据类型、默认值、是否允许为空、是否自动递增、字段注释的示例代码：

```php
<?php
//连接数据库
$conn = new mysqli("localhost", "username", "password", "database");

//查询表名
$query = "SELECT table_name FROM information_schema.tables WHERE table_schema = 'database'";
$result = $conn->query($query);
while ($row = $result->fetch_assoc()) {
    $table_name = $row['table_name'];

    //查询字段信息
    $query_columns = "SELECT column_name, column_type, column_default, is_nullable, extra, column_comment FROM information_schema.columns WHERE table_name = '$table_name'";
    $result_columns = $conn->query($query_columns);
    while ($row_columns = $result_columns->fetch_assoc()) {
        $column_name = $row_columns['column_name'];
        $column_type = $row_columns['column_type'];
        $column_default = $row_columns['column_default'];
        $is_nullable = $row_columns['is_nullable'];
        $extra = $row_columns['extra'];
        $column_comment = $row_columns['column_comment'];

        //输出字段信息
        echo "表名：$table_name，";
        echo "字段名：$column_name，";
        echo "数据类型：$column_type，";
        echo "默认值：$column_default，";
        echo "是否允许为空：$is_nullable，";
        echo "是否自动递增：$extra，";
        echo "字段注释：$column_comment&lt;br&gt;";
    }
}

//关闭数据库连接
$conn->close();

```

注意：以上代码仅为示例，实际应用中需要考虑安全性和性能等因素。