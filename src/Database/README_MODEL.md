# 数据操作模型类 Model

> Model 是一个通过pdo驱动查询mysql的 模型操作类
> 只需要继承 Model 类，就可以使用 Model类 和 Db类 的所有方法

## 安装

```
composer require zxf/tools
```

## 配置

> 需要在`config`目录下创建`tools_other.php`文件，内容如下

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

并且可以通过 `config('tools_other.mysql.default')` 函数获取到上面的配置信息，如果你实在没有或者没有使用框架，那么可以使用下面的暴力方法读取mysql连接配置信息

***这是一个无奈之举***

```
if (!function_exists('config')) {
   function config($key='tools_other.mysql.default')
   {
       return [
           'host'     => '127.0.0.1',
           'dbname'   => 'test',
           'username' => 'root',
           'password' => '',
       ];
   }
}
```

## 在 模型类/实体类 中使用

> 假设我们有一个`user`表，表结构如下
> 主键| 名称 | ... | 所属国家id | 状态 |创建时间 | 更新时间
> id | name | ... | country_id | status |created_at | updated_at

### 先定义一个 User 类，继承 Model 类

```
use zxf\Database\Model;

// 用户实体/模型类
class User extends Model
{
    /**
     * 表的主键id,默认为 id，如果不是 id，需要定义 $primaryKey 属性，如果主键值是id，可以不定义
     *
     * @var string
     */
    protected $primaryKey = 'id';
    
    /**
     * 表名称，如果不设置则默认为类名的 [下划线] 形式,如果表名和类名不一致，需要定义 $table 属性
     *     例如：如果类名为 User 对应的数据库表名为 user，如果不是就需要定义 $table 属性
     *          如果类名为 UserRole 对应的数据库表名为 user_role
     *
     * @var string
     */
    protected $dbTable = 'users';
    
    /**
     * 定义一个用户有多个关联地址 (一对多关系)
     */
    public function address(){
        return $this->hasMany(Address::class, 'user_id', 'id');
    }
    
    /**
     * 定义一个用户从属于一个国家 (多对一关系)
     */
    public function country(){
        return $this->belongsTo(Country::class, 'country_id', 'id');
    }
    
    /**
    * 定义一个用户有一个身份证信息 (一对一关系)
    */
    public function idCard(){
        return $this->hasOne(IdCard::class, 'user_id', 'id');
    }
    
    /**
    * 定义一个用户在多个文章中发表了评论 (远程一对多关系)
    */
    public function posts(){
        return $this->belongsToMany(Post::class, Articel::class, 'user_id', 'article_id','id','id');
    }
    
    /**
    * 定义一个用户有多个角色 (多对多关系)
    */
    public function roles(){
        return $this->belongsToMany(Role::class, UserRole::class, 'user_id', 'role_id','id','id');
    }
}

```

### 调用User实体类的方法

```
use xxx/User;// 引入上面定义的 User 类

class TestUser
{
    public function getUserList(){
        $user = new User();
        $res = $user->where('status',1)->get();
        foreach($res as $key => $item){

            // 打印获取到的每个用户数据
            print_r($item->toArray());

            // 获取用户的名称
            print_r($item->name);
            
            // 修改id为1的用户的名称
            if($item->id == 1){
                $item->name = 'name_测试';
                $item->save();
            }
            
            // 删除没有关联国家的用户
            if($item->country_id == 0){
                $item->delete();
            }
            
            // TODO: 其他操作
        }
    }
    
    // ...
}
```

## 支持的操作方法

### 获取表名称

```
class TestUser
{
    public function test(){
        $user = new User();
        $user->getTableName();
    }
}
```

### 插入数据

```
class TestUser
{
    public function test1(){
        $user = new User();
        $user->fill([
            'name' => '...',
            ...
        ]);
        $user->save();
    }
    
    public function test2(){
        $user = new User([
            'name' => '...',
            ...
        ]);
        $user->save();
    }
    
    public function test3(){
        $user = new User();
        $user->create([
            'name' => '...',
            ...
        ]);
    }
}
```

### 查询数据

```
class TestUser
{
    public function test1(){
        $user = User::query()->find(1);
    }
    
    public function test2(){
        $user = User::query()->where('status',1)->get();
    }
    
    public function test3(){
        $user = User::query()->where('status',1)->first();
    }
    
    public function test4(){
        $user = User::query()->where('status',1)->firstOrFail();
    }
    
    public function test5(){
        $user = User::query()->where('status',1)->get();
    }
    
    public function test6(){
        $user = User::query()
          ->where(function($query){
             $query->where('age','>',21);
          })
          ->get();
    }
    
    // ...
    
```

### 更新数据

```
class TestUser
{
    public function test1(){
        $user = User::query()->find(1);
        $user->roles();
    }
    
    public function test2(){
        $user = User::query()->find(1);
        $user->update([
            'name' => '...',
            ...
        ]);
    }
}
```

## 高级用法

```
class TestUser
{
    public function test1(){
        $user = User::query()->find(1);
        $user->roles()->get();
        // OR
        $user->roles()->where(...)->get();
    }
    
    public function test2(){
        $user = User::query()->find(1);
        $idcard = $user->idCard();
    }
}
```