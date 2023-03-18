
支持的命令：
```
php artisan module:make 支持的命令：
php artisan module:make Blog 创建模块
php artisan module:make Blog User Auth  创建多个模块
php artisan module:migrate Blog 迁移给定的模块，或者在没有模块或参数的情况下，迁移所有模块
php artisan module:migrate-rollback Blog 回滚给定的模块，或者在没有模块或参数的情况下，回滚所有模块
php artisan module:migrate-refresh Blog 刷新给定模块的迁移，或者在没有指定模块的情况下刷新所有模块的迁移
php artisan module:migrate-reset Blog 重置给定模块的迁移，或者在没有指定模块的情况下重置所有模块的迁移
php artisan module:seed Blog 为给定模块设定种子，或者在没有参数的情况下为所有模块设定种子
php artisan module:publish-config Blog 发布给定的模块配置文件，或者不带参数发布所有模块配置文件
php artisan module:publish-translation Blog 发布给定模块的翻译文件，或者在没有指定模块的情况下发布所有模块迁移
php artisan module:update Blog 更新给定的模块

php artisan module:make-command CreatePostCommand Blog 为指定的模块生成给定的控制台命令
php artisan module:make-migration create_posts_table Blog 为指定的模块生成迁移
php artisan module:make-seed seed_fake_blog_posts Blog 为指定的模块生成给定的种子名称
php artisan module:make-controller PostsController Blog 为指定的模块生成控制器
--plain,-p : 创建普通控制器
--api：创建资源控制器
php artisan module:make-model Post Blog 为指定的模块生成给定的模型
--fillable=field1,field2: 在生成的模型上设置可填充字段
--migration, -m: 为给定的模型创建迁移文件
php artisan module:make-provider BlogServiceProvider Blog 为指定的模块生成给定的服务提供程序名称
php artisan module:make-middleware CanReadPostsMiddleware Blog 为指定的模块生成给定的中间件名称
php artisan module:make-mail SendWeeklyPostsEmail Blog 为指定的模块生成给定的邮件类
php artisan module:make-notification NotifyAdminOfNewComment Blog 为指定的模块生成给定的通知类名

为指定的模块生成给定的侦听器。您可以选择指定它应该侦听的事件类。它还接受--queued标志允许排队的事件侦听器
php artisan module:make-listener NotifyUsersOfANewPost Blog
php artisan module:make-listener NotifyUsersOfANewPost Blog --event=PostWasCreated
php artisan module:make-listener NotifyUsersOfANewPost Blog --event=PostWasCreated --queued

php artisan module:make-request CreatePostRequest Blog 生成指定模块的给定请求
php artisan module:make-event BlogPostWasUpdated Blog 为指定的模块生成给定的事件

为指定模块生成给定作业
php artisan module:make-job JobName Blog
php artisan module:make-job JobName Blog --sync # A synchronous job class

php artisan module:route-provider Blog 为指定的模块生成给定的路由服务提供程序
php artisan module:make-factory ModelName Blog 为指定的模块生成给定的数据库工厂

为指定的模块生成给定的策略类
创建新模块时，默认情况下不会生成策略。将modules.php中paths.generator.policies的值更改为所需位置
php artisan module:make-policy PolicyName Blog

为指定的模块生成给定的验证规则类。
创建新模块时，默认情况下不会生成“规则”文件夹。将modules.php中paths.generator.rules的值更改为所需位置
php artisan module:make-rule ValidationRule Blog

为指定的模块生成给定的资源类。它可以有一个可选的--collection参数来生成资源集合
创建新模块时，默认情况下不会生成Resource文件夹。将modules.php中paths.generator.resource的值更改为所需位置
php artisan module:make-resource PostResource Blog
php artisan module:make-resource PostResource Blog --collection

php artisan module:make-test EloquentPostRepositoryTest Blog 为指定的模块生成给定的测试类
```