# 第三方登录

## OAuth2.0 登录

```
来源：https://github.com/majiameng/OAuth2
tags:v2.2.6
date:2025-03-24
```

### 使用

引入命名空间

```php
use zxf\Login\OAuth;
```

1、 初始化实例类

```php
// 实例化方式一：
$oauth = OAuth::Sina(?array $config=[]);
$oauth = OAuth::Qq(?array $config=[]);

// 实例化方式二：
$name = 'qq';
/** @var $oauth \zxf\Login\Contracts\Gateway */
$oauth = OAuth::$name(?array $config=[]);

// 实例化方式三：
$oauth = new \zxf\Login\Gateways\Qq(?array $config=[]);
```

2、 可选：需要强制验证回跳地址中的state参数
> 提示:为了不暴露参数信息，内部会自动生成和处理state参数
> 可以传入一个参数，例如字符串或者数组，在回调中进行自定义业务逻辑处理

```php
// $data 为空时内部会默认生成一个值
// 传入$data数据后可以在回调中获取到
$oauth->mustCheckState(string|array $data=''); // 如需手动验证state,请关闭此行

// 微博、微信：特别指定用于手机端登录【正常情况下不设置】，则需要设定->setDisplay('mobile')
```

3、 得到授权跳转地址

```php
$url = $oauth->getRedirectUrl();
```

4、重定向到外部第三方授权地址

```php
// 各个框架差异，请自行参考框架文档

// Laravel
return redirect()->away($url);
// ThinkPHP
$this->redirect($url);
```

5、可选：回调时验证 `state` 并返回之前传入的参数`$data`

```php
$data = $oauth->mustCheckState()->checkState(); // 如需手动验证state,请关闭此行
```

6、获取第三方用户信息

```php
$userInfo = $oauth->userInfo(); // 【推荐】处理后的用户信息
// OR
$userInfo = $oauth->getUserInfo(); // 第三方返回的原始用户信息
```

### OAuth 公共方法

```php
// 得到跳转地址
public function getRedirectUrl();

// 获取当前授权用户的openid标识
public function openid();

// 【推荐】获取格式化后的用户信息
public function userInfo();

// 获取原始接口返回的用户信息
public function getUserInfo();
```
