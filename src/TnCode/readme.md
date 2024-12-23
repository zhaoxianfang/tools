# TnCode 滑动验证码

## 使用示例

在页面中引用静态资源

```
<script src="/tn_code/assets/tn_code.min.js"></script>
<link href="/tn_code/assets/style.min.css" rel="stylesheet">
```

页面中定义一个容器 `.tncode`(名称可随意起名) 传递给 下面的 `handleDom`

在js 中添加事件

```
<script type="text/javascript">
    $TN.init({
        handleDom:'.tncode', // 触发验证码容器
        getImgUrl:'./get_img.php', // 获取验证码图片地址
        checkUrl:'./check.php' // 验证地址
    }).onSuccess(function(){
        //验证通过
        console.log('验证通过')
    }).onFail(function(){
        //验证失败
        console.log('验证失败')
    });
</script>
```

## 其他说明

### 获取验证码图片

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

### 验证验证码的值

#### 初次验证

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

#### 二次验证【此步骤可选】【非强制】

> 在`laravel`中使用，已经内置了一个名为`TnCode`的专用验证规则，直接使用即可

```
public function form(Request $request)
{
    // 前提是初次验证已经通过此步骤才生效
    $request->validate([
        'tn_r' => 'required|TnCode:min,max',
    ], [
        'tn_r.required'   => '必填',
        'tn_r.TnCode' => 'TnCode 验证',
    ]);

    // TODO
}
```

### 加载 TnCode 的静态资源

> 仅可加载 TnCode 下 Resources 目录下的png、css、js 三种静态资源

```
<script src="/tn_code/assets/tn_code.min.js"></script>
<link href="/tn_code/assets/style.min.css" rel="stylesheet">

<img src="/tn_code/assets/img/icon.png"/>
```