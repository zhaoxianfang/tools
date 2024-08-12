# 多模块控制台命令

## 创建模块

```
创建Blog模块
php artisan module:make Blog

一次创建多个模块
php artisan module:make Blog User Auth

列出所有可用的模块
php artisan module:list
```

## 迁移

```
指定模块下创建迁移文件
php artisan module:make-migration create_posts_table Blog

执行迁移：迁移给定模块，或者在没有模块参数的情况下迁移所有模块
php artisan module:migrate Blog

迁移回滚：回滚给定模块，或者不带参数回滚所有模块
php artisan module:migrate-rollback Blog

迁移刷新：刷新给定模块的迁移，或者在没有指定模块的情况下刷新所有模块迁移
php artisan module:migrate-refresh Blog

重置迁移：重置给定模块的迁移，或者在没有指定模块的情况下重置所有模块迁移「作用：删除本模块的迁移表，不删文件」
php artisan module:migrate-reset Blog

发布迁移：发布给定模块的迁移文件，或者不带参数发布所有模块迁移「作用：把各个模块下的迁移文件复制到项目根目录下的/database/migrations下」
php artisan module:publish-migration Blog
```

## 种子

```
制作种子,为指定模块生成给定的种子名称
php artisan module:make-seed seed_fake_blog_posts Blog

运行种子填充，为给定模块播种，或者不带参数为所有模块播种

php artisan module:seed Blog
```

## 发布配置

```
发布给定的模块配置文件，或者不带参数发布所有模块配置文件「作用：把各模块下的配置复制到项目根目录下的/config下」
php artisan module:publish-config Blog
```

## 发布翻译

```
发布给定模块的翻译文件，或者在没有指定模块的情况下发布所有模块翻译文件
php artisan module:publish-translation Blog

检查指定模块中缺少的语言键(对比翻译文件里面的差异)
php artisan module:lang Blog
```

## 命令控制台

```
为指定模块生成给定的控制台命令
php artisan module:make-command CreatePostCommand Blog
```

## 控制器

```
为指定模块生成控制器
php artisan module:make-controller PostsController Blog
可选选项：

--plain, -p: 创建一个普通控制器
--api：创建资源控制器
```

## 模型

```
为指定模块生成给定模型
php artisan module:make-model Post Blog
可选选项：

--fillable=field1,field2：在生成的模型上设置可填写字段
--migration, -m: 为给定模型创建迁移文件
--request, -r: 为给定模型创建请求文件
--seed, -s: 为给定模型创建种子文件
--controller, -c: 为给定模型创建控制器文件
-mcrs：为给定模型创建迁移、控制器、请求和播种器文件
```

## 定服务提供者

```
生成指定模块的给定服务提供者名称
php artisan module:make-provider BlogServiceProvider Blog
```

## 中间件

```
制作中间件,为指定模块生成给定的中间件名称
php artisan module:make-middleware CanReadPostsMiddleware Blog
```

## 邮件

```
制作邮件,为指定模块生成给定的邮件类
php artisan module:make-mail SendWeeklyPostsEmail Blog
```

## 通知类

```
为指定模块生成给定的通知类名称
php artisan module:make-notification NotifyAdminOfNewComment Blog
```

## 侦听器

```
为指定模块生成给定侦听器您可以选择指定它应该侦听哪个事件类它还接受--queued允许排队事件侦听器的标志
php artisan module:make-listener NotifyUsersOfANewPost Blog
php artisan module:make-listener NotifyUsersOfANewPost Blog --event=PostWasCreated
php artisan module:make-listener NotifyUsersOfANewPost Blog --event=PostWasCreated --queued
```

## 请求

```
生成指定模块的给定请求
php artisan module:make-request CreatePostRequest Blog
```

## 事件

```
为指定模块生成给定事件
php artisan module:make-event BlogPostWasUpdated Blog
```

## 作业

```
为指定模块生成给定作业
php artisan module:make-job JobName Blog

php artisan module:make-job JobName Blog --sync # A synchronous job class
```

## 路由服务提供者

```
为指定模块生成给定的路由服务提供者
php artisan module:route-provider Blog
```

## 为指定模块生成给定的数据库工厂

```
php artisan module:make-factory ModelName Blog
```

## 为指定模块生成给定策略类

Policies创建新模块时默认不会生成将paths.generator.policiesin的值更改modules.php为您想要的位置

```
php artisan module:make-policy PolicyName Blog
```

## 为指定模块生成给定的验证规则类

Rules创建新模块时默认不会生成该文件夹将paths.generator.rulesin的值更改modules.php为您想要的位置

```
php artisan module:make-rule ValidationRule Blog
```

## 为指定模块生成给定资源类它可以有一个可选--collection参数来生成资源集合

Transformers创建新模块时默认不会生成该文件夹将paths.generator.resourcein的值更改modules.php为您想要的位置

```
php artisan module:make-resource PostResource Blog
php artisan module:make-resource PostResource Blog --collection
```

## 为指定模块生成给定的测试类

```
php artisan module:make-test EloquentPostRepositoryTest Blog
```

## 生成指定模块的给定视图

```
php artisan module:make-view index Blog
```

## 观察者

```
php artisan module:make-observer UserObserver User 为指定的模块创建一个观察者
```