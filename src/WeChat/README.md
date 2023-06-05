# 微信模块开发

> 来源库: https://github.com/zoujingli/WeChatDeveloper
> 文档: https://www.kancloud.cn/zoujingli/wechat-developer

## 申明

本模块是由`https://github.com/zoujingli/WeChatDeveloper`仓库改造而来

## 引入和修改记录

1、下载 WeChatDeveloper 库
2、复制文件进来(除AliPay文件夹)
3、修改命名空间 `namespace `批量替换为`namespace zxf\WeChat\`
4、修改引用命名空间 `use WeChat\`批量替换为`use zxf\WeChat\WeChat\` (勾选文件掩码 .php)
5、修改引用命名空间 `use WePay\`批量替换为`use zxf\WeChat\WePay\` (勾选文件掩码 .php)
6、修改引用命名空间 `use WePayV3\`批量替换为`use zxf\WeChat\WePayV3\` (勾选文件掩码 .php)
7、替换文件头注释

```
// +----------------------------------------------------------------------
// | WeChatDeveloper
// +----------------------------------------------------------------------
// | 版权所有 2014~2023 ThinkAdmin [ thinkadmin.top ]
// +----------------------------------------------------------------------
// | 官方网站: https://thinkadmin.top
// +----------------------------------------------------------------------
// | 开源协议 ( https://mit-license.org )
// | 免责声明 ( https://thinkadmin.top/disclaimer )
// +----------------------------------------------------------------------
// | gitee 代码仓库：https://gitee.com/zoujingli/WeChatDeveloper
// | github 代码仓库：https://github.com/zoujingli/WeChatDeveloper
// +----------------------------------------------------------------------
```

替换为

```
// +----------------------------------------------------------------------
// | WeChatDeveloper
// +----------------------------------------------------------------------
```

## 模块

| 模块                                 | 路径                           |
|------------------------------------|------------------------------|
| 视频号                                | zxf\WeChat\Channels          |
| 小游戏                                | zxf\WeChat\Game              |
| 开发平台                               | zxf\WeChat\Oplatform         |
| 微信支付                               | zxf\WeChat\Pay               |
| 智能对话                               | zxf\WeChat\Robot             |
| 小商店                                | zxf\WeChat\Store             |
| 企业微信                               | zxf\WeChat\Work              |
| 腾讯小微                               | zxf\WeChat\XiaoWei           |
| SDK功能测试DEMO代码                      | zxf\WeChat\_test             |
| 默认缓存目录，需要拥有读写权限，可配置                | zxf\WeChat\Cache             |
| 认证服务号支持                            | zxf\WeChat\WeChat            |
| 微信小程序支持                            | zxf\WeChat\WeMini            |
| 微信支付支持                             | zxf\WeChat\WePay             |
| 微信V3支付支持                           | zxf\WeChat\WePayV3           |
| WeChat 基础支持类，通用外界不需要使用             | zxf\WeChat\WeChat/Contracts  |
| WeChat 自定义异常类，在调用接口时可以使用 try 来处理异常 | zxf\WeChat\WeChat/Exceptions |

## 文件

| 文件名               | 类名                  | 描述           | 类型    | 加载 ①                                 |
|-------------------|---------------------|--------------|-------|--------------------------------------|
| App.php           | AliPay\App          | 支付宝App支付     | 支付宝   | \zxf\WeChat\We::AliPayApp()          |
| Bill.php          | AliPay\Bill         | 支付宝账单下载      | 支付宝   | \zxf\WeChat\We::AliPayBill()         |
| Pos.php           | AliPay\Pos          | 支付宝刷卡支付      | 支付宝   | \zxf\WeChat\We::AliPayPos()          |
| Scan.php          | AliPay\Scan         | 支付宝扫码支付      | 支付宝   | \zxf\WeChat\We::AliPayScan()         |
| Transfer.php      | AliPay\Transfer     | 支付宝转账        | 支付宝   | \zxf\WeChat\We::AliPayTransfer()     |
| Wap.php           | AliPay\Wap          | 支付宝Wap支付     | 支付宝   | \zxf\WeChat\We::AliPayWap()          |
| Web.php           | AliPay\Web          | 支付宝Web支付     | 支付宝   | \zxf\WeChat\We::AliPayWeb()          |
| Card.php          | WeChat\Card         | 微信卡券接口支持     | 认证服务号 | \zxf\WeChat\We::WeChatCard()         |
| Custom.php        | WeChat\Custom       | 微信客服消息接口支持   | 认证服务号 | \zxf\WeChat\We::WeChatCustom()       |
| Media.php         | WeChat\Media        | 微信媒体素材接口支持   | 认证服务号 | \zxf\WeChat\We::WeChatMedia()        |
| Oauth.php         | WeChat\Oauth        | 微信网页授权消息类接口  | 认证服务号 | \zxf\WeChat\We::WeChatOauth()        |
| Pay.php           | WeChat\Pay          | 微信支付类接口      | 认证服务号 | \zxf\WeChat\We::WeChatPay()          |
| Product.php       | WeChat\Product      | 微信商店类接口      | 认证服务号 | \zxf\WeChat\We::WeChatProduct()      |
| Qrcode.php        | WeChat\Qrcode       | 微信二维码接口支持    | 认证服务号 | \zxf\WeChat\We::WeChatQrcode()       |
| Receive.php       | WeChat\Receive      | 微信推送事件消息处理支持 | 认证服务号 | \zxf\WeChat\We::WeChatReceive()      |
| Scan.php          | WeChat\Scan         | 微信扫一扫接口支持    | 认证服务号 | \zxf\WeChat\We::WeChatScan()         |
| Script.php        | WeChat\Script       | 微信前端JSSDK支持  | 认证服务号 | \zxf\WeChat\We::WeChatScript()       |
| Shake.php         | WeChat\Shake        | 微信蓝牙设备揺一揺接口  | 认证服务号 | \zxf\WeChat\We::WeChatShake()        |
| Tags.php          | WeChat\Tags         | 微信粉丝标签接口支持   | 认证服务号 | \zxf\WeChat\We::WeChatTags()         |
| Template.php      | WeChat\Template     | 微信模板消息接口支持   | 认证服务号 | \zxf\WeChat\We::WeChatTemplate()     |
| User.php          | WeChat\User         | 微信粉丝管理接口支持   | 认证服务号 | \zxf\WeChat\We::WeChatCard()         |
| Wifi.php          | WeChat\Wifi         | 微信门店WIFI管理支持 | 认证服务号 | \zxf\WeChat\We::WeChatWifi()         |
| Bill.php          | WePay\Bill          | 微信商户账单及评论    | 微信支付  | \zxf\WeChat\We::WePayBill()          |
| Coupon.php        | WePay\Coupon        | 微信商户代金券      | 微信支付  | \zxf\WeChat\We::WePayCoupon()        |
| Order.php         | WePay\Order         | 微信商户订单       | 微信支付  | \zxf\WeChat\We::WePayOrder()         |
| Redpack.php       | WePay\Redpack       | 微信红包支持       | 微信支付  | \zxf\WeChat\We::WePayRedpack()       |
| Refund.php        | WePay\Refund        | 微信商户退款       | 微信支付  | \zxf\WeChat\We::WePayRefund()        |
| Transfers.php     | WePay\Transfers     | 微信商户打款到零钱    | 微信支付  | \zxf\WeChat\We::WePayTransfers()     |
| TransfersBank.php | WePay\TransfersBank | 微信商户打款到银行卡   | 微信支付  | \zxf\WeChat\We::WePayTransfersBank() |
| Crypt.php         | WeMini\Crypt        | 微信小程序数据加密处理  | 微信小程序 | \zxf\WeChat\We::WeMiniCrypt()        |
| Plugs.php         | WeMini\Plugs        | 微信小程序插件管理    | 微信小程序 | \zxf\WeChat\We::WeMiniPlugs()        |
| Poi.php           | WeMini\Poi          | 微信小程序地址管理    | 微信小程序 | \zxf\WeChat\We::WeMiniPoi()          |
| Qrcode.php        | WeMini\Qrcode       | 微信小程序二维码管理   | 微信小程序 | \zxf\WeChat\We::WeMiniCrypt()        |
| Template.php      | WeMini\Template     | 微信小程序模板消息支持  | 微信小程序 | \zxf\WeChat\We::WeMiniTemplate()     |
| Total.php         | WeMini\Total        | 微信小程序数据接口    | 微信小程序 | \zxf\WeChat\We::WeMiniTotal()        |

## 配置文件

```
// 配置
public $config = [
    "token"          => env("EXT_WECHAT_OFFICIAL_TOKEN", ""), //填写你设定的key
    "appid"          => env("EXT_WECHAT_OFFICIAL_APP_ID", ""), //填写高级调用功能的app id
    "appsecret"      => env("EXT_WECHAT_OFFICIAL_APP_SECRET", ""), //填写高级调用功能的密钥
    "encodingaeskey" => env("EXT_WECHAT_OFFICIAL_AES_KEY", ""), //填写加密用的EncodingAESKey
    // 配置商户支付参数（可选，在使用支付功能时需要）
    "mch_id"         => "1235704602",
    "mch_key"        => "IKI4kpHjU94ji3oqre5zYaQMwLHuZPmj",
    // 配置商户支付双向证书目录（可选，在使用退款|打款|红包时需要）
    "ssl_key"        => "",
    "ssl_cer"        => "",
    // 缓存目录配置（可选，需拥有读写权限）
    "cache_path"     => env("EXT_WECHAT_OFFICIAL_CACHE_PATH", ""), //插件 缓存目录
    "token_callback" => env("EXT_WECHAT_OFFICIAL_TOKEN_CALLBACK_URL", ""), //回调地址
];
```

## 直接调用微信接口

### 实例化

```
use zxf\WeChat\WeChatBase;
$this->sdk = WeChatBase::instance($this->config);
```

## 获取 access_token

```
// $refreshToken 是否重新生成token
$this->sdk->getAccessToken(bool $refreshToken = false)
```

### post 请求

> 例如：调用微信接口(微信直播-商品添加并提审)`https://api.weixin.qq.com/wxaapi/broadcast/goods/add?access_token=`

```
$params: 需要post 发送的部分【非必填】
$urlParams: 如果请求的url中需要拼接url地址，则可以传入此参数，例如["title"=>"aha","test"=>123]【仅需要时候传参即可】
$res = $this->sdk->post("wxaapi/broadcast/goods/add", $params=[],$urlParams=[]);
if ($res["errcode"] != 0) {
    throw new \Exception($this->getMessage($res["errcode"]), $res["errcode"]);
}
return [
    "message" => $this->sdk->getMessage($res["errcode"]),
    "code"    => $res["errcode"],
];
```

### get 请求

> 调用同post一样，把`post`换为`get`即可

```
$this->sdk->get("xxx", $params=[],$urlParams=[]);
```

### 上传素材

```
/**
 *  请求上传文件 ,主要用在上传公众号素材或者小程序临时图片
 *
 * @param string $mediaType 上传类型：10：小程序临时图片，20：公众号临时素材，21：公众号永久素材
 * @param string $filePath 文件绝对路径
 * @param string $type     image|voice|thumb|video 小程序只有 image 类型
 *                         图片（image）: 10M，支持bmp/png/jpeg/jpg/gif格式       【公众号、小程序】
 *                         语音（voice）：2M，播放长度不超过60s，mp3/wma/wav/amr格式 【公众号】
 *                         视频（video）：10MB，支持MP4格式                        【公众号】
 *                         缩略图（thumb）：64KB，支持 JPG 格式                    【公众号】
 * @param string $videoTitle        视频标题【仅上传公众号视频文件时候使用】
 * @param string $videoDescription  视频描述【仅上传公众号视频文件时候使用】
 *
 * @return array|bool|mixed|string
 * @throws Exception
 */
$this->sdk->upload(10|20|21,$url,$filePath, $type,$videoTitle = "",  $videoDescription="");
```

## 服务端token验证

> TODO

## 直播

> 直播中用到的用户信息一般都是指`用户微信号` 和 `mediaID`

