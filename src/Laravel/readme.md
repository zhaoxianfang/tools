# Laravel 模块

## 调试

> 在非生产环境(production)下和非ajax 请求下，可以调用 trace() 方法进行调试
> 需要 配置 APP_DEBUG 为 true

```php
trace('this is a test string');
trace(['this is a test array1']);
trace('string',['array1'],['array2'],'string2');
```