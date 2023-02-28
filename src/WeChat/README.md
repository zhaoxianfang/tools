# 微信模块开发

| 模块    | 路径              |
|-------|-----------------|
| 微信小程序 | zxf\WeChat\MiniProgram     |
| 微信公众号 | zxf\WeChat\OfficialAccount |

## 配置文件

```
// 配置
public $config = [
    'token'          => env('TOOLS_WECHAT_OFFICIAL_TOKEN', ''), //填写你设定的key
    'appid'          => env('TOOLS_WECHAT_OFFICIAL_APP_ID', ''), //填写高级调用功能的app id
    'appsecret'      => env('TOOLS_WECHAT_OFFICIAL_APP_SECRET', ''), //填写高级调用功能的密钥
    'encodingaeskey' => env('TOOLS_WECHAT_OFFICIAL_AES_KEY', ''), //填写加密用的EncodingAESKey
    // 配置商户支付参数（可选，在使用支付功能时需要）
    'mch_id'         => "1235704602",
    'mch_key'        => 'IKI4kpHjU94ji3oqre5zYaQMwLHuZPmj',
    // 配置商户支付双向证书目录（可选，在使用退款|打款|红包时需要）
    'ssl_key'        => '',
    'ssl_cer'        => '',
    // 缓存目录配置（可选，需拥有读写权限）
    'cache_path'     => env('TOOLS_WECHAT_OFFICIAL_CACHE_PATH', ''), //插件 缓存目录
    'token_callback' => env('TOOLS_WECHAT_OFFICIAL_TOKEN_CALLBACK_URL', ''), //回调地址
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
$this->sdk-getAccessToken(bool $refreshToken = false)
```

### post 请求

> 例如：调用微信接口(微信直播-商品添加并提审)`https://api.weixin.qq.com/wxaapi/broadcast/goods/add?access_token=`

```
$params: 需要post 发送的部分【非必填】
$urlParams: 如果请求的url中需要拼接url地址，则可以传入此参数，例如['title'=>'aha','test'=>123]【仅需要时候传参即可】
$res = $this->sdk->post('wxaapi/broadcast/goods/add', $params=[],$urlParams=[]);
if ($res['errcode'] != 0) {
    throw new \Exception($this->getMessage($res['errcode']), $res['errcode']);
}
return [
    'message' => $this->sdk->getMessage($res['errcode']),
    'code'    => $res['errcode'],
];
```

### get 请求

> 调用同post一样，把`post`换为`get`即可

```
$this->sdk->get('xxx', $params=[],$urlParams=[]);
```

### 上传素材

```
/**
 *  请求上传文件 ,主要用在上传公众号素材或者小程序临时图片
 *
 *  说明：请在调用此方法前调用 $this->generateRequestUrl(URL_NAME) 方法，处理 url,
 *  URL_NAME示例：永久素材cgi-bin/material/add_material、临时素材cgi-bin/media/upload
 *
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
$this->sdk->upload($filePath, $type,$videoTitle = '',  $videoDescription='');
```

## 服务端token验证

> TODO

## 直播

> 直播中用到的用户信息一般都是`用户微信号` 和 `mediaID`

### 直播间商品

#### 实例化

```
use zxf\WeChat\MiniProgram\Live\LiveGoods;
$this->sdk = LiveGoods::instance($this->config);
```

#### 导入商品到微信并提交审核

```

$path      = 'pages/goods/show?id=' . $goods_id; // 小程序路径
$goodsInfo = [
    'name'        => '',商品名称
    'coverImgUrl' => '', // 商品图片封面 mediaID
    'price'       => '',
    'price2'      => '',
    'priceType'   => 1,
    'goodsKey'   => ['id'],
];
return $this->sdk->importAndWaitAudit($goodsInfo, $path);

```

#### 撤回商品审核

```
$this->sdk->cancelAudit($wechat_goods_id, $wechat_audit_id)
```

#### 删除商品

```
$this->sdk->delete($wechat_goods_id)
```

#### 更新商品

```
$path      = 'pages/goods/show?id=' . $goods_id;
$goodsInfo = [
    'name'        => $title,
    'coverImgUrl' => '', // 商品图片封面 mediaID
    'price'       => $price,
    'price2'      => '',
    'priceType'   => 1,
];
return $this->sdk->update($liveGoods->mini_wechat_goods_id, $goodsInfo, $path);
```

#### 获取商品状态

```
$this->sdk->getGoodsWarehouse($goodsIds)
```

#### 获取商品列表

```
$this->sdk->getApproved($status, $page, $limit)
```

### 直播间用户(角色)

#### 实例化

```
use zxf\WeChat\MiniProgram\Live\LiveRole;
$this->sdk = LiveRole::instance($this->config);
```

#### 设置成员角色

```
$this->sdk->add($wechatName, $role)
```

#### 解除成员角色

```
$this->sdk->delete($wechatName, $role)
```

#### 查询成员列表

```
$this->sdk->list($role, $page, $limit, $keyword)
```

### 直播间

#### 实例化

```
use zxf\WeChat\MiniProgram\Live\LiveRoom;
$this->sdk = LiveRoom::instance($this->config);
```

#### 创建直播间

```
$this->sdk->create($data)
```

#### 编辑直播间

```
$this->sdk->update($roomId, $data);
```

#### 删除直播间

```
$this->sdk->delete($roomId);
```

#### 获取直播间列表

```
$this->sdk->list($page, $limit)
```

#### 获取直播间回放

```
$this->sdk->getReplay($roomId, $page, $limit)
```

#### 获取直播间 推流地址

```
$this->sdk->getReplay($roomId)
```

#### 获取直播间 分享二维码

```
$this->sdk->getSharedCode($roomId, $custom_params)
```

#### 开启/关闭直播间官方收录

```
$this->sdk->getSharedCode($roomId, $isFeedsPublic)
```

#### 开启/关闭回放功能

```
$this->sdk->updateReplay($roomId, $closeReplay)
```

#### 开启/关闭客服功能

```
$this->sdk->updateKf($roomId, $closeKf)
```

#### 开启/关闭直播间全局禁言

```
$this->sdk->updateComment($roomId, $banComment)
```

### 直播间小助手

#### 实例化

```
use zxf\WeChat\MiniProgram\Live\LiveRoomAssistant;
$this->sdk = LiveRoomAssistant::instance($this->config);
```

#### 添加管理直播间小助手

```
$this->sdk->add($roomId, $openId, $nickname)
```

#### 修改管理直播间小助手

```
$this->sdk->modify($roomId, $openId, $nickname)
```

#### 删除管理直播间小助手

```
$this->sdk->remove($roomId, $openId)
```

#### 查询管理直播间小助手

```
$this->sdk->info($roomId)
```

### 直播间里面的商品

#### 实例化

```
use zxf\WeChat\MiniProgram\Live\LiveRoomGoods;
$this->sdk = LiveRoomGoods::instance($this->config);
```

#### 直播间导入商品

```
$this->sdk->import($roomId, $goodsIdsArr)
```

#### 上下架商品

```
$this->sdk->onSale($roomId, $goodsId, $onSale)
```

#### 删除商品

```
$this->sdk->delete($roomId, $goodsId)
```

#### 推送商品

```
$this->sdk->push($roomId, $goodsId)
```

#### 商品排序

```
$goods = ["123", "234"];
return $this->sdk->sort($roomId, $goods);
```

#### 下载商品讲解视频

```
$this->sdk->getVideo($roomId, $goodsId)
```

### 直播间 主播副号

#### 实例化

```
use zxf\WeChat\MiniProgram\Live\LiveRoomSubAnchor;
$this->sdk = LiveRoomSubAnchor::instance($this->config);
```

#### 获取主播副号

```
$this->sdk->info($roomId)
```

#### 添加主播副号

```
$this->sdk->add($roomId, $openId)
```

#### 修改主播副号

```
$this->sdk->modify($roomId, $openId)
```

#### 删除主播副号

```
$this->sdk->delete($roomId)
```

### 直播间 长期订阅

#### 实例化

```
use zxf\WeChat\MiniProgram\Live\LiveSubscribe;
$this->sdk = LiveSubscribe::instance($this->config);
```

#### 获取长期订阅用户

```
$this->sdk->list($page, $limit)
```

#### 长期订阅群发接口

```
$this->sdk->pushMessage($roomId, $openIds)
```

## 素材管理

### 公众号素材

> 分为永久素材和临时素材

#### 实例化 永久素材

```
use zxf\WeChat\OfficialAccount\Material\PermanentFiles;
$this->sdk = PermanentFiles::instance($this->config);
```

#### 实例化 临时素材

```
use zxf\WeChat\OfficialAccount\Material\TempFiles;
$this->sdk = TempFiles::instance($this->config);
```

#### 上传图片

```
$this->sdk->uploadImage($realPath)
```

#### 上传视频

```
$this->sdk->uploadVideo($videoPath, $videoTitle, $videoDescription)
```

#### 上传语音

```
$this->sdk->uploadVoice($realPath)
```

#### 上传缩略图

```
$this->sdk->uploadThumb($realPath)
```

#### 获取素材列表

```
$this->sdk->getList($type, $page, $limit)
```

#### 删除素材

```
$this->sdk->deleteFile($mediaId)
```

### 小程序素材

> 目前微信只有临时图片素材

#### 实例化

```
use zxf\WeChat\MiniProgram\Material\TempFiles; 
 $this->sdk = TempFiles::instance($this->config);
```

#### 上传图片

```
$this->sdk->uploadImage($realPath)
```