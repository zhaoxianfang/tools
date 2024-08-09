# 多模块自动加载解析的目录和文件

## Modules模块

```
Resources/lang/         // 多语言文件
Config/config.php       // 每个模块的配置文件
Console/xxx.php         // 控制台命令 只要继承了 Illuminate\Console\Command 类就会自动注册
Resources/views/        // 视图文件
Database/Migrations/    // 迁移文件
Routes/                 // 路由文件 xxx.php
Providers/              // 里面继承了 \Illuminate\Support\ServiceProvider::class 类的所有服务提供者类
```

## resource 模块

> 优先级高于 Modules 模块

```
lang/modules/模块名小写/
views/modules/模块名小写/
```

## config 配置

```
模块名小写.php
```

## 路由组说明：

> 如果`App\Http\Kernel->$middlewareGroups` 中有同名的小写路由组，则会自动添加到路由中间键上
> 除了 `api` 路由组默认加上路由名称和前缀外，其他路由模块会根据配置`route_need_add_prefix_and_name`确定是否需要加上路由名称
> ->name('api.') 和前缀->prefix('api')