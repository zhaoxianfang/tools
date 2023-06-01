# 使用说明

## 参考文档

```
'================================================================================'
| 插    件 | composer require zxf/tools                                           |'
| 格    言 | 人生在勤，不索何获                                                      |'
| 模块发布 | php artisan vendor:publish --provider="zxf\laravel\ServiceProvider"   |'
| 文档地址 | https://weisifang.com/docs/2                                          |'
=================================================================================='
```

## 常用的几个命令
```
php artisan module:make Blog 创建模块
php artisan module:make Blog User Auth  创建多个模块

php artisan module:migrate Blog 迁移给定的模块，或者在没有模块或参数的情况下，迁移所有模块
php artisan module:migrate-rollback Blog 回滚给定的模块，或者在没有模块或参数的情况下，回滚所有模块
php artisan module:migrate-refresh Blog 刷新给定模块的迁移，或者在没有指定模块的情况下刷新所有模块的迁移

php artisan module:make-command CreatePostCommand Blog 为指定的模块生成给定的控制台命令

php artisan module:make-migration create_posts_table Blog 为指定的模块生成迁移

php artisan module:make-controller PostsController Blog 为指定的模块生成控制器
--plain,-p : 创建普通控制器
--api：创建资源控制器
```