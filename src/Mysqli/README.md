# 手册
 
> 该类由 http://github.com/joshcam/PHP-MySQLi-Database-Class 仓库 改造而来
> 主要是把 选择表操作 使用 `Db::table('my_tests)` 或 `Db::name('tests)` 或 `$db->table('users)` 链式 操作，而不需要 进行例如`->get('users')`这样的操作，直接`->get()`即可

## Db 类使用

> Db类是通过mysqli直接连接mysql的核心类，它包含了数据库连接、切换数据库连接、以及对数据库CURD等的操作

### 引入

```
use zxf\Mysqli\Db;
```
### 配置文件格式
```
<?php
/**
 * mysqli配置参数
 */
return [
    //  默认连接
    'default' => [
        'host'     => '127.0.0.1',
        'username' => '',
        'password' => '',
        'db'       => 'test',
        'port'     => 3306,
        'prefix'   => 'my_',
        'charset'  => 'utf8'
    ]
];
```
### 初始化mysql

#### 方式一
```
$db = new MysqliDb ('host', 'username', 'password', 'databaseName');
```
#### 方式二
```
$db = new Db([
    'host' => 'host',
    'username' => 'username', 
    'password' => 'password',
    'db'=> 'databaseName',
    'port' => 3306,
    'prefix' => 'my_',
    'charset' => 'utf8'
]);
```
#### 方式三
定义一个去读配置的函数`config`
```
function config($key='')
{
    if($key = 'mysql.default'){
        return [
            'host' => 'host',
            'username' => 'username', 
            'password' => 'password',
            'db'=> 'databaseName',
            'port' => 3306,
            'prefix' => 'my_',
            'charset' => 'utf8'
        ];
    }
}
```
实例化`Db`类中回去读取`config`函数的配置,默认使用`default`连接
```
$db = new Db();
```

#### 方式四
> 此方式也借助了 方式三中的`config`配置

##### 通过表全名（包含表前缀）实例化
实例化时候直接指定查询表`my_tests`,
```
// 使用表的全名(包含表前缀)进行查询
$tests = Db::table('my_tests);
```
##### 通过表名称（不包含表前缀）实例化
> 实例化时，被查询的表会自动拼接上`config` 中的表前缀字符串

```
// 使用表的名称(不包含表前缀)进行查询，下面查询的数据库表全名为 my_tests
$tests = Db::name('tests);
```
#### 设置表前缀
```
$db->setPrefix ('my_');
```

### 获取实例对象
```
$db = Db::getInstance();
```

### 使用不同的连接
```
$users = $db->connection('slave')->table('users)->get();
```
### CURD

#### 插入(C)(insert)
##### 普通插入
```
$data = Array ("login" => "admin",
               "firstName" => "John",
               "lastName" => 'Doe'
);
$id = $db->table('users)->insert ( $data);
if($id)
    echo 'user was created. Id=' . $id;
```
##### 插入时使用 函数
```
$data = Array (
	'login' => 'admin',
    'active' => true,
	'firstName' => 'John',
	'lastName' => 'Doe',
	'password' => $db->func('SHA1(?)',Array ("secretpassword+salt")),
	// password = SHA1('secretpassword+salt')
	'createdAt' => $db->now(),
	// createdAt = NOW()
	'expires' => $db->now('+1Y')
	// expires = NOW() + interval 1 year
	// Supported intervals [s]econd, [m]inute, [h]hour, [d]day, [M]onth, [Y]ear
);

$id = $db->table('users)->insert ( $data);
if ($id)
    echo 'user was created. Id=' . $id;
else
    echo 'insert failed: ' . $db->getLastError();
```
##### 使用重复的key插入
```
$data = Array ("login" => "admin",
"firstName" => "John",
"lastName" => 'Doe',
"createdAt" => $db->now(),
"updatedAt" => $db->now(),
);
$updateColumns = Array ("updatedAt");
$lastInsertId = "id";
$db->onDuplicate($updateColumns, $lastInsertId);
$id = $db->table('users)->insert ( $data);
```

##### 批量插入
```
$data = Array(
    Array ("login" => "admin",
        "firstName" => "John",
        "lastName" => 'Doe'
    ),
    Array ("login" => "other",
        "firstName" => "Another",
        "lastName" => 'User',
        "password" => "very_cool_hash"
    )
);
$ids = $db->table('users)->insertMulti( $data);
if(!$ids) {
    echo 'insert failed: ' . $db->getLastError();
} else {
    echo 'new users inserted with following id\'s: ' . implode(', ', $ids);
}
```
如果所有数据集使用相同的键名(字段名)插入
```
$data = Array(
    Array ("admin", "John", "Doe"),
    Array ("other", "Another", "User")
);
$keys = Array("login", "firstName", "lastName");

$ids = $db->table('users)->insertMulti( $data, $keys);
if(!$ids) {
    echo 'insert failed: ' . $db->getLastError();
} else {
    echo 'new users inserted with following id\'s: ' . implode(', ', $ids);
}
```

#### 更新（U）（update）
```
$data = Array (
	'firstName' => 'Bobby',
	'lastName' => 'Tables',
	'editCount' => $db->inc(2),
	// editCount = editCount + 2;
	'active' => $db->not()
	// active = !active;
);
$db->table('users)->where ('id', 1);
if ($db->update ( $data))
    echo $db->count . ' records were updated';
else
    echo 'update failed: ' . $db->getLastError();
```

`update()` 还支持限制参数：
```php
$db->update ( $data, 10);
// Gives: UPDATE users SET ... LIMIT 10
```

#### 查询(R)
```
$users = $db->table('users)->get(); //contains an Array of all users 
$users = $db->table('users)->get( 10); //contains an Array 10 users
```
##### 或使用自定义列集进行选择。也可以使用函数

```
$cols = Array ("id", "name", "email");
$users = $db->table('users)->get ( null, $cols);
if ($db->count > 0)
    foreach ($users as $user) { 
        print_r ($user);
    }
```

##### 只选择一行

```
$db->where ("id", 1);
$user = $db->getOne ();
echo $user['id'];

$stats = $db->getOne ("sum(id), count(*) as cnt");
echo "total ".$stats['cnt']. "users found";
```

或者 不传入where条件，直接查询库中的第一条
```
$db->table('tests')->getOne();
```

##### 选择一个列值或函数结果

```
$count = $db->getValue ("count(*)");
echo "{$count} users found";
```

#####  从多行中选择一个列值或函数结果：
```
$logins = $db->getValue ( "login", null);
// select login from users
$logins = $db->getValue ( "login", 5);
// select login from users limit 5
foreach ($logins as $login)
    echo $login;
```
