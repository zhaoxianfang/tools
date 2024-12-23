# TnCode 验证码

## 获取验证码图片

```
$tncode = new \zxf\TnCode\TnCode([
//     'tool_icon_img'         => '', // 前端使用的图标组图片
//     'slide_dark_img'        => '', // 黑色滑块图片
//     'slide_transparent_img' => '', // 透明滑块图片
]);
$tncode->make([
//     'bg1.png',
//     'bg2.png',
//     'bg3.png',
//     '....png',
]);
```

## 验证验证码的值

```
$tncode = new \zxf\TnCode\TnCode();
if ($tncode->check()) {
    $_SESSION['tncode_check'] = 'ok';
    echo "ok";
} else {
    $_SESSION['tncode_check'] = 'error';
    echo "error";
}
die;
```

## 加载 TnCode 的静态资源

```
<script src="/tn_code/assets/tn_code.js"></script>
<script src="/tn_code/assets/style.css"></script>
<link href="/tn_code/assets/style.css" rel="stylesheet">

<img src="/tn_code/assets/img/icon.png"/>
```