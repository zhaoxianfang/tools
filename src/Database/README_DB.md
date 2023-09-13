# Db 类使用

> Db 是一个通过pdo驱动查询mysql的基础工具类

## 安装

```
composer require zxf/tools
```

## 初始化

```
use zxf\Database\Db;

$db = new Db([
    'host'     => '127.0.0.1',
    'dbname'   => 'db_name
    'username' => 'root',
    'password' => '',
]);
```

或者

```
Db::newQuery()->connect('default', [
    'host'     => '127.0.0.1',
    'dbname'   => 'db_name',
    'username' => 'root',
    'password' => '',
]);
```

### 在框架中使用

如果在框架中使用，可以在`config/tools_other.php`中配置mysql 连接配置

```
<?php
// 数据库相关的配置，mysql、redis、elastic 等
return [
    //  mysql
    'mysql' => [
        'default' => [
            'host'     => env('TOOLS_MYSQL_HOST', '127.0.0.1'),
            'username' => env('TOOLS_MYSQL_HOST', 'root'),
            'password' => env('TOOLS_MYSQL_HOST', ''),
            'db'       => env('TOOLS_MYSQL_HOST', 'test'),
            'port'     => env('TOOLS_MYSQL_HOST', 3306),
            // 'prefix'   => env('TOOLS_MYSQL_HOST', ''),
            'charset'  => env('TOOLS_MYSQL_HOST', 'utf8mb4'),
            'socket'   => env('TOOLS_MYSQL_SOCKET', null),
        ],
    ],
    //  redis 等其他配置
];
```

实例化

```
$db = new Db();
或者
$db = Db::newQuery();
```

### 使用门面模式 引入

```
use zxf/tools/Facade/Db;
```

### 切换数据库连接

```
$db = Db::newQuery()->connect('default');
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

//多字段过滤
$db->where([ ['id',1], ['name','like','%威四方%'], ['status','<>',1] ])

// 使用闭包
$db->where(function($query){
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
```

### NOT IN查询

```
$db->whereNotIn('id' , ['1','2','3'])
// 使用闭包
$db->whereNotIn('id' , function($query){
   $query->where('age','>',21);
})
```

### OR 查询

```
// 同where
$db->orWhere(...)
```

### 两个字段比较

```
$db->whereColumn('time1','=','time2')
$db->whereColumn('time1','>','time2')
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

## 传入的值存在值时才执行

```
$db->when($id,funcion($query,$id){
    $query->where('id',$id);
})

$db->when(!empty($name),funcion($query) use($name){
    $query->where('name','like','%'.$name.'%');
})
```

## 表连接

```
$db->join('table_name AS t','table1.id = t.table1_id')
// 使用闭包
$db->join('table1 AS t',function($query){
   $query->on('table1.id','=','t.table1_id');
})
```

### 左连接

```
$db->leftJoin('table_name AS t','table1.id = t.table1_id')
// 使用闭包
$db->leftJoin('table1 AS t',function($query){
   $query->on('table1.id','=','t.table1_id');
})
```

### 右连接

```
$db->rightJoin('table_name AS t','table1.id = t.table1_id')
// 使用闭包
$db->rightJoin('table1 AS t',function($query){
   $query->on('table1.id','=','t.table1_id');
})
```

### 全连接

```
$db->fullJoin('table_name AS t','table1.id = t.table1_id')
// 使用闭包
$db->fullJoin('table1 AS t',function($query){
   $query->on('table1.id','=','t.table1_id');
})
```

## 查询结果

```
// 查询满足条件的全部数据
$db->get()
// 第一条数据
$db->first()
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

### 批量插入

```
$db->table('test')->batchInsert([
    [
        'username' => 'admin',
        'nickname' => 'admin',
        'mobile'   => 'mobile',
    ],[
        'username' => 'admin',
        'nickname' => 'admin',
        'mobile'   => 'mobile',
    ]
])
```

### 批量插入并返回id

```
$db->table('test')->batchInsertAndGetIds([
    [
        'username' => 'admin',
        'nickname' => 'admin',
        'mobile'   => 'mobile',
    ],[
        'username' => 'admin',
        'nickname' => 'admin',
        'mobile'   => 'mobile',
    ]
])
```

## 修改

```
$db->table('test')->where('id',1)->update([
        'username' => 'admin',
        'nickname' => 'admin',
        'mobile'   => 'mobile',
])
```

### 批量修改

```
// 根据 mobile 修改
$db->table('test')->batchUpdate([
    [
        'username' => 'admin',
        'nickname' => 'admin',
        'mobile'   => 'mobile1',
    ],[
        'username' => 'admin',
        'nickname' => 'admin',
        'mobile'   => 'mobile2',
    ]
],'mobile')
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
$db->rollBack()
```

### 闭包执行

```
$db->transaction(function ($query) use ($data, &$ids) {
    foreach ($data as $row) {
        $ids[] = $query->insertGetId($row);
    }
});
```

### 检查一个操作是否在事务中

```
$db->inTransaction()
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
$db->orderBy('id', 'desc')
// 多字段排序
$db->orderBy(['name' => 'desc', 'age' => 'asc'])
```

## 分页

```
$db->limit(0,10)
$db->paginate($limit = 10, $currentPage = 1)
```

## 杂项

### 打印sql

```
// 打印sql
$db->toSql()
```

### 获取数据表字段信息

```
$db->table('test')->getColumns()
```

### 最后一次执行的sql

```
$db->table('test')->getLastQuery()
```

### 获取数据表主键列名

```
$db->table('test')->getPrimaryKey()
```

### 获取数据表索引列信息

```
$db->table('test')->getIndexes()
```

### 获取异常信息

```
$db->getError()
```