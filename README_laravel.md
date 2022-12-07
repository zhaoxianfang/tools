# LARAVEL modules 多模块使用说明


## 关于本模块

本模块是基于 `nWidart/laravel-modules` 仓库改造而来
> 本模块仅在 laravel 框架下有效，如果使用的是非laravel框架,不会加载此模块，所以无须考虑本模块内容，直接跳


## 多模块使用

### 发布模块和配置
```
php artisan vendor:publish --provider="zxf\laravel\ServiceProvider"
```

### 在项目 composer.json 中新增自动加载
```
    "autoload": {
        "psr-4": {
            "App\\": "app/",
            "Modules\\": "Modules/", <-- 增加本行即可
        }
    },
```

### 重新加载composer
```
composer dump-autoload
```

## 多模块可使用的命令

### 创建模块

#### 创建普通模块
```
php artisan module:make <module-name>
```

#### 同时创建多个模块
```
php artisan module:make Blog User Auth
```

#### 创建不包含控制器、种子类、服务提供者等的模块
```
php artisan module:make Blog --plain
# or
php artisan module:make Blog -p
```
#### 生成 `modules.php` 配置中的所有模块
> 默认不带 --all 参数生成的模块都来自于 `modules.php` 配置中 `generator.generate` 为 `true` 的模块

```
php artisan module:make Blog --all
```

### 自定义命名空间
```
语言调用：
Lang::get('blog::group.name');

@trans('blog::group.name');
调用视图：
view('blog::index')

view('blog::partials.sidebar')
调用配置：
Config::get('blog.name')
```

### 获取模块路径助手函数
```
$path = module_path('Blog');
```

### 模块迁移
#### 创建 Blog模块下的数据迁移文件
```
php artisan module:make-migration create_posts_table Blog
```
#### 执行某个模块下的迁移文件
```
php artisan module:migrate Blog
```
#### 执行全部模块下的迁移文件
```
php artisan module:migrate
```
#### Blog模块下的数据迁移回滚
```
php artisan module:migrate-rollback Blog
```
#### Blog模块下的数据迁移刷新
> 删除数据重新执行迁移

```
php artisan module:migrate-refresh Blog
```
#### Blog模块下的数据迁移重置
```
php artisan module:migrate-reset Blog
```

### 数据填充

```
php artisan module:seed Blog
```

### 命令操作

#### 为指定模块生成给定的控制台命令。
```
php artisan module:make-command CreatePostCommand Blog
```
#### 为指定模块生成迁移。
```
php artisan module:make-migration create_posts_table Blog
```
#### 为指定模块生成给定的种子名称。
```
php artisan module:make-seed seed_fake_blog_posts Blog
```
#### 为指定模块生成控制器。
```
php artisan module:make-controller PostsController Blog
```
#### 为指定的模块生成给定的模型。
```
php artisan module:make-model Post Blog
```

#### 为指定模块生成给定的服务提供者名称。
```
php artisan module:make-provider BlogServiceProvider Blog
```
#### 为指定模块生成给定的中间件名称。
```
php artisan module:make-middleware CanReadPostsMiddleware Blog
```
#### 为指定模块生成给定的邮件类。
```
php artisan module:make-mail SendWeeklyPostsEmail Blog
```
#### 为指定模块生成给定的通知类名称。
```
php artisan module:make-notification NotifyAdminOfNewComment Blog
```

#### 为指定的模块生成给定的监听器。您可以选择指定它应该监听的事件类。它还接受一个--queued允许排队事件侦听器的标志。
```
php artisan module:make-listener NotifyUsersOfANewPost Blog
php artisan module:make-listener NotifyUsersOfANewPost Blog --event=PostWasCreated
php artisan module:make-listener NotifyUsersOfANewPost Blog --event=PostWasCreated --queued
```
#### 为指定模块生成给定的请求。
```
php artisan module:make-request CreatePostRequest Blog
```
#### 为指定模块生成给定事件。
```
php artisan module:make-event BlogPostWasUpdated Blog
```
#### 为指定的模块生成给定的作业。
```
php artisan module:make-job JobName Blog

php artisan module:make-job JobName Blog --sync # A synchronous job class
```
#### 为指定模块生成给定的路由服务提供者。
```
php artisan module:route-provider Blog
```
#### 为指定模块生成给定的数据库工厂。
```
php artisan module:make-factory FactoryName Blog
```
#### 为指定模块生成给定的策略类。
Policies创建新模块时默认不生成。paths.generator.policies将in的值更改modules.php为您想要的位置。
```
php artisan module:make-policy PolicyName Blog
```
#### 为指定模块生成给定的验证规则类。

Rules创建新模块时默认不生成该文件夹。paths.generator.rules将in的值更改modules.php为您想要的位置。
```
php artisan module:make-rule ValidationRule Blog
```
#### 为指定的模块生成给定的资源类。它可以有一个可选--collection参数来生成资源集合。

Transformers创建新模块时默认不生成该文件夹。paths.generator.resource将in的值更改modules.php为您想要的位置。
```
php artisan module:make-resource PostResource Blog
php artisan module:make-resource PostResource Blog --collection
```
#### 为指定模块生成给定的测试类。
```
php artisan module:make-test EloquentPostRepositoryTest Blog
```

### 创建command
```
php artisan module:make-command TestCommand Test 
```

## 更多

请移步查看
```
https://nwidart.com/laravel-modules/v6/advanced-tools/artisan-commands
```
