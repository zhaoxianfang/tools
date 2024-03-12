# Db 类使用

> Db 是一个通过pdo驱动查询mysql的基础工具类

## 安装

```
composer require zxf/tools
```

## 初始化

```
use zxf\Database\Db;

$db = new Db('mysql',[
    'host'     => '127.0.0.1',
    'db_name'   => 'db_name
    'username' => 'root',
    'password' => '',
]);
```

或者

```
Db::instance('mysql', [
    'host'     => '127.0.0.1',
    'db_name'   => 'db_name',
    'username' => 'root',
    'password' => '',
]);
```

或者在发布了`tools_database.php`配置文件的能使用`config('tools_database.default')`读取配置文件的框架或自定义项目中使用

```
use zxf\Database\Db;
Db::instance(); <-- 内部回去读取`config('tools_database.default')`配置文件
```

### 在框架中使用

如果在框架中使用，可以在`config/tools_other.php`中配置mysql 连接配置

```
<?php
// ====================================================
// 数据库相关的配置，mysql、redis、elastic 等
// ====================================================
return [
    'default' => [
        'driver'     => 'mysql', // 默认数据库驱动名称，和下面default同级的键名对应，支持: mysql、pgsql、sqlite、sqlserver、oracle
        'connection' => 'default', // 默认连接名称
    ],
    'redis'   => [
        'default' => [
            'host'    => env('REDIS_HOST', ''),
            'port'    => env('REDIS_PORT', '6379'),
            'timeout' => env('TOOLS_REDIS_TIME_OUT', '5'),
            'auth'    => env('REDIS_PASSWORD', ''),
        ],
    ],
    //  mysql
    'mysql'   => [
        'default' => [
            'host'     => env('DB_HOST', '127.0.0.1'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'db_name'   => env('DB_DATABASE', 'test'),
            'port'     => env('DB_PORT', 3306),
            // 'prefix'   => env('DB_PREFIX', ''),
            'charset'  => env('DB_CHARSET', 'utf8mb4'),
            'socket'   => env('DB_SOCKET', null),
        ],
    ],
    //  sqlite
    'sqlite'  => [
        'default' => [
            // SQLite数据库文件路径
            'host'     => '',
            'username' => '',
            'password' => '',
            'db_name'   => '',
            'port'     => '',
            'charset'  => '',
            'socket'   => '',
        ],
    ],
];
```

实例化

```
$db = new Db();
或者
$db = Db::instance();
```

### 使用门面模式 引入

```
use zxf/tools/Facade/Db;
```

### 切换数据库连接

```
$db = Db::instance()->connect([],'default');
```

## 查询

### 选择表

```
$db->table('table_name');
// 设置表别名
$db->table('table_name','t');
```

### 查询指定字段

```
$db->select('id, username')
// OR
$db->select('nickname', 'email')
OR
$db->select(['mobile', 'avatar'])
OR
$db->select(['id', 'name','SUM(number) AS sum_num' ...])
```

## 条件过滤(where)

```
// 单个字段过滤
$db->where('id = 1')
$db->where('id',1)
$db->where('num','=',1)
$db->where('status','IS NULL')

// 使用闭包
$db->where(function($query){
   $query->where('age','>',21);
})
```

### where原生字段

```
$db->whereRow('`users`.`id`' , '`address`.`user_id`')
```

### orWhere

```
$db->orWhere('`users`.`id`' , '`address`.`user_id`')
```

### 条件是否存在

```
$db->whereExists(function($query){
   $query->where('age','>',21);
})
```

### 条件是否不存在

```
$db->whereNotExists(function($query){
   $query->where('age','>',21);
})
```

### IN查询

```
$db->whereIn('id' , ['1','2','3'])
// 使用闭包
$db->whereIn('id' , function($query){
   $query->where('age','>',21);
})

$db->orWhereIn('id' , ['1','2','3'])
```

### NOT IN查询

```
$db->whereNotIn('id' , ['1','2','3'])
// 使用闭包
$db->whereNotIn('id' , function($query){
   $query->where('age','>',21);
})
```

### whereBetween

```
$db->whereBetween('id', [1, 100])
// 使用闭包
$db->whereBetween('id', function($query){
   $query->...;
})

$db->whereNotBetween('id', [1, 100])
```

### orWhereBetween

```
同 whereBetween
```

### 两个字段比较

```
$db->whereRow('time1','=','time2')
$db->whereRow('time1','>','time2')
// OR 连接
$db->orWhereColumn('time1','>','time2','OR')
```

### 空字段查询

```
$db->whereNull('name')
```

### 非空字段查询

```
$db->whereNotNull('name')
```

### 全文索引

```
// 匹配单个词
$db->whereFullText(['title','content'],'中国')
// 匹配多个词 包含中国 但不包含美国
$db->whereFullText(['title','content'],'+中国 -美国')
// 通配符匹配
$db->whereFullText(['title','content'],'*中国')


$db->orWhereFullText(['title','content'],'中国')
```

## 传入的值存在值时才执行

```
$db->when($id,funcion($query,$id){
    $query->where('id',$id); // $id存在时才执行
},funcion($query,$id){
    $query->where('id',$id); // $id不存在时执行
})

$db->when(!empty($name),funcion($query) use($name){
    $query->where('name','like','%'.$name.'%');
})
```

## 表连接

```
$db->join('table_name AS t','table1.id = t.table1_id')
```

### 左连接

```
$db->leftJoin('table_name AS t','table1.id = t.table1_id')
```

### 右连接

```
$db->rightJoin('table_name AS t','table1.id = t.table1_id')
```

### 子连接

```
$subQuery = $db->table('table_name')->select('id','name')->where('status',1);

$db->joinSub($subQuery,function($query){
   $query->on('table1.id','=','t.table1_id');
})->get();
```

### 左子连接

```
leftJoinSub：同joinSub
```

### 右子连接

```
rightJoinSub：同joinSub
```

## 查询结果

```
// 查询满足条件的全部数据
$db->get()
// 第一条数据
$db->find()
// 是否存在
$db->exists()
// 是否不存在
$db->doesntExist()
```

### 遍历查询结果中的每一项

```
$db->table('test')->each(function ($item) {
    print_r($item);
});
```

## 插入数据

```
$db->table('test')->insert([
    'username' => 'admin',
    'nickname' => 'admin',
    'mobile'   => 'mobile',
])
```

### 返回ID

```
$db->table('test')->insertGetId([
    'username' => 'admin',
    'nickname' => 'admin',
    'mobile'   => 'mobile',
])
```

### 批量插入或更新

> 例如：插入多条数据
> 根据mobile判断库中是否有相同的值;有则更新username和nickname，没有则插入整行数据
> 也可以根据多个字段判断是否有相同的值
> 【建议】：第三个参数的字段 必须是具有唯一性的字段或联合字段

```
$db->table('test')->upsert(
// 第一个参数，要插入或更新的数据
[
    [
        'username' => 'admin',
        'nickname' => 'admin',
        'mobile'   => 'mobile',
    ],[
        'username' => 'admin',
        'nickname' => 'admin',
        'mobile'   => 'mobile',
    ]
],
// 第二个参数，要更新的字段
[
    'username',
    'nickname',
],
// 第三个参数，判断是插入还是更新的条件字段
[
    'mobile', // 也可能是多个字段 ['mobile','username']
],
)
```

## 修改

```
$db->table('test')->where('id',1)->update([
        'username' => 'admin',
        'nickname' => 'admin',
        'mobile'   => 'mobile',
])
```

### 自增和自减

```
// 自增
$db->table('test')->where('id',1)->increment('num')
$db->table('test')->where('id',1)->increment('num',5)
// 自减
$db->table('test')->where('id',1)->decrement('num');
$db->table('test')->where('id',1)->decrement('num',5);
```

## 删除

```
$db->table('test')->where('id',1)->delete()
```

## 事务操作

### 开启事务

```
$db->beginTransaction()
```

### 提交事务

```
$db->commit()
```

### 回滚事务

```
$db->rollback()
```

### 闭包执行

```
$db->transaction(function ($query) use ($data, &$ids) {
    foreach ($data as $row) {
        $ids[] = $query->insertGetId($row);
    }
});
```

## 聚合查询

### count

```
$db->count()
$db->count('id')
```

### max

```
$db->max('id')
```

### min

```
$db->min('id')
```

### avg

```
$db->avg('id')
```

### sum

```
$db->sum('id')
```

## 分组

```
$db->groupBy('id')
$db->groupBy('id', 'name')
```

## 筛选

```
$db->having('id', '>', 1)
```

## 排序

```
// 默认升序
$db->orderBy('id')
// 降序
$db->orderBy('id', 'DESC')

$db->orderBy('name' , 'DESC')
```

## 分页

```
$db->limit(10)
$db->limit(0,10)
```

## 杂项

### 打印sql

```
// 打印sql
$db->toSql()
```

### 中断调试

```
$db->dd()
```

### 填充数据

```
$db->fill([
    'name' => 'name',
    'age' => 18,
])
```
