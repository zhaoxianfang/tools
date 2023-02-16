# 微信模块开发

## MiniProgram 小程序模块

- Live:微信直播
- Material:微信素材管理

## OfficialAccount 公众号模块

- Material:微信素材管理(包含临时素材和永久素材)

## 直播服务类

```
<?php

namespace xxx\WeChat;

class LiveBaseService
{
    // 直播请求的sdk
    public $sdk;
    // 微信小程序配置
    public $config = [
        'app_id' => '',
        'secret' => '',
    ];

    public function __construct()
    {
        $config = 获取你的配置;

        $this->config = [
            'app_id' => $config['mini_wechat_app_id'],
            'secret' => $config['mini_wechat_secret'],
        ];
    }
}

```

### 直播平台商品

#### 实例化

```
use zxf\WeChat\MiniProgram\Live\LiveGoods;
$this->sdk = LiveGoods::instance($this->config);
```

#### demo

```

/**
 * 直播平台商品管理
 */
class LiveGoodsService extends LiveBaseService
{
    public function __construct()
    {
        parent::__construct();
        $this->sdk = LiveGoods::instance($this->config);
    }

    // 导入商品到微信并提交审核
    public function importAndWaitAudit(GoodsProduct $goodsProduct): \Illuminate\Http\JsonResponse
    {
        $goods     = $goodsProduct->goods;
        $path      = 'pages/goods/show?id=' . $goodsProduct->goods_id;
        $goodsInfo = [
            'name'        => $goods->title . '(' . $goodsProduct->title . ')',
            'coverImgUrl' => '', // 商品图片封面 mediaID
            'price'       => $goodsProduct->price,
            'price2'      => '',
            'priceType'   => 1,
        ];
        return res_json($this->sdk->importAndWaitAudit($goodsInfo, $path));

    }

    // 撤回商品审核
    public function cancelAudit(LiveGoods $liveGoods): \Illuminate\Http\JsonResponse
    {
        return res_json($this->sdk->cancelAudit($liveGoods->mini_wechat_goods_id, $liveGoods->mini_wechat_audit_id));
    }

    // 重新提交审核
    public function audit(LiveGoods $liveGoods): \Illuminate\Http\JsonResponse
    {
        return res_json($this->sdk->audit($liveGoods->mini_wechat_goods_id));
    }

    // 删除商品
    public function delete(LiveGoods $liveGoods): \Illuminate\Http\JsonResponse
    {
        return res_json($this->sdk->delete($liveGoods->mini_wechat_goods_id));
    }

    // 更新商品
    public function update(GoodsProduct $goodsProduct, LiveGoods $liveGoods): \Illuminate\Http\JsonResponse
    {
        $goods     = $goodsProduct->goods;
        $path      = 'pages/goods/show?id=' . $goodsProduct->goods_id;
        $goodsInfo = [
            'name'        => $goods->title . '(' . $goodsProduct->title . ')',
            'coverImgUrl' => '', // 商品图片封面 mediaID
            'price'       => $goodsProduct->price,
            'price2'      => '',
            'priceType'   => 1,
        ];
        return res_json($this->sdk->update($liveGoods->mini_wechat_goods_id, $goodsInfo, $path));
    }

    // 获取商品状态
    public function getGoodsWarehouse(array $goodsIds = []): \Illuminate\Http\JsonResponse
    {
        return res_json($this->sdk->getGoodsWarehouse($goodsIds));
    }

    // 获取商品列表
    public function getApproved($status, $page = 1, $limit = 30): \Illuminate\Http\JsonResponse
    {
        return res_json($this->sdk->getApproved($status, $page, $limit));
    }

}
```

### 直播间角色

#### 实例化

```
use zxf\WeChat\MiniProgram\Live\LiveRole;
$this->sdk = LiveRole::instance($this->config);
```

#### demo

```
/**
 * 直播成员角色
 */
class LiveRoleService extends LiveBaseService
{
    public function __construct()
    {
        parent::__construct();
        $this->sdk = LiveRole::instance($this->config);
    }

    // 设置成员角色
    public function addRole($openId, $role = 2): \Illuminate\Http\JsonResponse
    {
        return res_json($this->sdk->add($openId, $role));
    }

    // 解除成员角色
    public function deleteRole($openId, $role = 2): \Illuminate\Http\JsonResponse
    {
        return res_json($this->sdk->delete($openId, $role));
    }

    // 查询成员列表
    public function getRoleList($role = -1, $page = 1, $limit = 10, $keyword = ''): \Illuminate\Http\JsonResponse
    {
        return res_json($this->sdk->list($role, $page, $limit, $keyword));
    }

}
```

### 直播间

#### 实例化

```
use zxf\WeChat\MiniProgram\Live\LiveRoom;
$this->sdk = LiveRoom::instance($this->config);
```

#### demo

```

/**
 * 直播间
 */
class LiveRoomService extends LiveBaseService
{
    public function __construct()
    {
        parent::__construct();
        $this->sdk = LiveRoom::instance($this->config);
    }

    // 创建直播间
    public function create($data): \Illuminate\Http\JsonResponse
    {
        $validator = Validator::make($data, [
            'name'            => 'required|min:3|max:17',
            'coverImg'        => 'required',
            'start'           => 'required|date|before:end',
            'end'             => 'required|date|after:start',
            'anchorName'      => 'required|min:2|max:15',
            'anchorWechat'    => 'required',
            'subAnchorWechat' => 'min:2',
            'createrWechat'   => 'min:2',
            'shareImg'        => 'required',
            'feedsImg'        => 'required',
            'isFeedsPublic'   => 'in:0,1',
            'type'            => 'in:0,1',
            'closeLike'       => 'in:0,1',
            'closeGoods'      => 'in:0,1',
            'closeComment'    => 'in:0,1',
            'closeReplay'     => 'in:0,1',
            'closeShare'      => 'in:0,1',
            'closeKf'         => 'in:0,1',
        ], [
            'name.required'         => '请填写房间名字',
            'name.min'              => '房间名字长度应介于3~17个汉字', // 1个汉字相当于2个字符
            'name.max'              => '房间名字长度应介于3~17个汉字',
            'coverImg.required'     => '请填写背景图 mediaID',
            'start.required'        => '请填写开播时间',
            'start.date'            => '开播时间格式错误',
            'start.before'          => '开播时间必须小于结束时间',
            'end.required'          => '请填写直播结束时间',
            'end.date'              => '结束时间格式错误',
            'end.after'             => '结束时间必须大于开播时间',
            'anchorName.required'   => '请填写主播昵称',
            'anchorName.min'        => '主播昵称长度应介于2~15个汉字', // 1个汉字相当于2个字符
            'anchorName.max'        => '主播昵称长度应介于2~15个汉字',
            'anchorWechat.required' => '请填写主播微信号',
            'subAnchorWechat.min'   => '主播副号微信号不能低于2个字',
            'createrWechat.min'     => '创建者微信号不能低于2个字',
            'shareImg.required'     => '请填写分享图 mediaID',
            'feedsImg.required'     => '请填写购物直播频道封面图 mediaID',
            'isFeedsPublic.in'      => '是否开启官方收录值填写错误，【1: 开启，0：关闭】',
            'type.in'               => '直播间类型填写错误，【1: 推流，0：手机直播】',
            'closeLike.in'          => '是否关闭点赞填写错误 【0：开启，1：关闭】',
            'closeGoods.in'         => '是否关闭货架填写错误 【0：开启，1：关闭】',
            'closeComment.in'       => '是否关闭评论填写错误 【0：开启，1：关闭】',
            'closeReplay.in'        => '是否关闭回放填写错误 【0：开启，1：关闭】',
            'closeShare.in'         => '是否关闭分享填写错误 【0：开启，1：关闭】',
            'closeKf.in'            => '是否关闭客服填写错误 【0：开启，1：关闭】',
        ]);
        if ($validator->fails()) {
            throw new \Exception($validator->getMessageBag()->first());
        }

        if (Carbon::parse($data['start'])->lte(Carbon::now()->addMinutes(10)) || Carbon::parse($data['start'])->gt(Carbon::now()->addMonths(6))) {
            throw new \Exception('开播时间需要在当前时间的10分钟后 并且 开始时间不能在 6 个月后');
        }
        $diffMinutes = Carbon::parse($data['start'])->diffInMinutes(Carbon::parse($data['end']), true);
        if ($diffMinutes < 30 || $diffMinutes > 1440) {
            throw new \Exception('直播时长不得短于30分钟，不得超过24小时');
        }

        $res = $this->sdk->create($data);
        return res_json($res, 200);
    }

    // 编辑直播间
    public function edit($roomId, $data): \Illuminate\Http\JsonResponse
    {
        if (empty($roomId)) {
            throw new \Exception('房间id不能为空');
        }
        $validator = Validator::make($data, [
            'name'            => 'required|min:3|max:17',
            'coverImg'        => 'required',
            'start'           => 'required|date|before:end',
            'end'             => 'required|date|after:start',
            'anchorName'      => 'required|min:2|max:15',
            'anchorWechat'    => 'required',
            'subAnchorWechat' => 'min:2',
            'createrWechat'   => 'min:2',
            'shareImg'        => 'required',
            'feedsImg'        => 'required',
            'isFeedsPublic'   => 'in:0,1',
            'type'            => 'in:0,1',
            'closeLike'       => 'in:0,1',
            'closeGoods'      => 'in:0,1',
            'closeComment'    => 'in:0,1',
            'closeReplay'     => 'in:0,1',
            'closeShare'      => 'in:0,1',
            'closeKf'         => 'in:0,1',
        ], [
            'name.required'         => '请填写房间名字',
            'name.min'              => '房间名字长度应介于3~17个汉字', // 1个汉字相当于2个字符
            'name.max'              => '房间名字长度应介于3~17个汉字',
            'coverImg.required'     => '请填写背景图 mediaID',
            'start.required'        => '请填写开播时间',
            'start.date'            => '开播时间格式错误',
            'start.before'          => '开播时间必须小于结束时间',
            'end.required'          => '请填写直播结束时间',
            'end.date'              => '结束时间格式错误',
            'end.after'             => '结束时间必须大于开播时间',
            'anchorName.required'   => '请填写主播昵称',
            'anchorName.min'        => '主播昵称长度应介于2~15个汉字', // 1个汉字相当于2个字符
            'anchorName.max'        => '主播昵称长度应介于2~15个汉字',
            'anchorWechat.required' => '请填写主播微信号',
            'subAnchorWechat.min'   => '主播副号微信号不能低于2个字',
            'createrWechat.min'     => '创建者微信号不能低于2个字',
            'shareImg.required'     => '请填写分享图 mediaID',
            'feedsImg.required'     => '请填写购物直播频道封面图 mediaID',
            'isFeedsPublic.in'      => '是否开启官方收录值填写错误，【1: 开启，0：关闭】',
            'type.in'               => '直播间类型填写错误，【1: 推流，0：手机直播】',
            'closeLike.in'          => '是否关闭点赞填写错误 【0：开启，1：关闭】',
            'closeGoods.in'         => '是否关闭货架填写错误 【0：开启，1：关闭】',
            'closeComment.in'       => '是否关闭评论填写错误 【0：开启，1：关闭】',
            'closeReplay.in'        => '是否关闭回放填写错误 【0：开启，1：关闭】',
            'closeShare.in'         => '是否关闭分享填写错误 【0：开启，1：关闭】',
            'closeKf.in'            => '是否关闭客服填写错误 【0：开启，1：关闭】',
        ]);
        if ($validator->fails()) {
            throw new \Exception($validator->getMessageBag()->first());
        }

        if (Carbon::parse($data['start'])->lte(Carbon::now()->addMinutes(10)) || Carbon::parse($data['start'])->gt(Carbon::now()->addMonths(6))) {
            throw new \Exception('开播时间需要在当前时间的10分钟后 并且 开始时间不能在 6 个月后');
        }
        $diffMinutes = Carbon::parse($data['start'])->diffInMinutes(Carbon::parse($data['end']), true);
        if ($diffMinutes < 30 || $diffMinutes > 1440) {
            throw new \Exception('直播时长不得短于30分钟，不得超过24小时');
        }

        $res = $this->sdk->update($roomId, $data);
        return res_json($res, 200);
    }

    // 删除直播间
    public function delete($roomId = ''): \Illuminate\Http\JsonResponse
    {
        $res = $this->sdk->delete($roomId);
        return res_json($res, 200);
    }

    // 获取直播间列表
    public function list($page = 1, $limit = 10): \Illuminate\Http\JsonResponse
    {
        return res_json($this->sdk->list($page, $limit));
    }

    // 获取直播间回放
    public function getReplay($roomId, $page = 1, $limit = 5): \Illuminate\Http\JsonResponse
    {
        return res_json($this->sdk->getReplay($roomId, $page, $limit));
    }

    // 获取直播间 推流地址
    // 此接口异常，微信服务方，一直返回 无效的accesstoken 错误
    public function getPushUrl($roomId): \Illuminate\Http\JsonResponse
    {
        return res_json($this->sdk->getReplay($roomId));
    }

    // 获取直播间 分享二维码
    public function getSharedCode($roomId, array $custom_params = []): \Illuminate\Http\JsonResponse
    {
        return res_json($this->sdk->getSharedCode($roomId, $custom_params));
    }

    // 开启/关闭直播间官方收录
    public function updateFeedPublic($roomId, $isFeedsPublic): \Illuminate\Http\JsonResponse
    {
        return res_json($this->sdk->getSharedCode($roomId, $isFeedsPublic));
    }

    // 开启/关闭回放功能
    public function updateReplay($roomId, $closeReplay): \Illuminate\Http\JsonResponse
    {
        return res_json($this->sdk->updateReplay($roomId, $closeReplay));
    }

    // 开启/关闭客服功能
    public function updateKf($roomId, $closeKf): \Illuminate\Http\JsonResponse
    {
        return res_json($this->sdk->updateKf($roomId, $closeKf));
    }

    // 开启/关闭直播间全局禁言
    public function updateComment($roomId, $banComment): \Illuminate\Http\JsonResponse
    {
        return res_json($this->sdk->updateComment($roomId, $banComment));
    }


}
```

### 直播间小助手

#### 实例化

```
use zxf\WeChat\MiniProgram\Live\LiveRoomAssistant;
$this->sdk = LiveRoomAssistant::instance($this->config);
```

#### demo

```

/**
 * 直播间小助手
 */
class LiveRoomAssistantService extends LiveBaseService
{
    public function __construct()
    {
        parent::__construct();
        $this->sdk = LiveRoomAssistant::instance($this->config);
    }

    // 添加管理直播间小助手
    public function addAssistant($roomId, $openId, $nickname = ''): \Illuminate\Http\JsonResponse
    {
        return res_json($this->sdk->add($roomId, $openId, $nickname));
    }

    // 修改管理直播间小助手
    public function modifyAssistant($roomId, $openId, $nickname = ''): \Illuminate\Http\JsonResponse
    {
        return res_json($this->sdk->modify($roomId, $openId, $nickname));
    }

    // 删除管理直播间小助手
    public function removeAssistant($roomId, $openId): \Illuminate\Http\JsonResponse
    {
        return res_json($this->sdk->remove($roomId, $openId));
    }

    // 查询管理直播间小助手
    public function getAssistantList($roomId): \Illuminate\Http\JsonResponse
    {
        return res_json($this->sdk->info($roomId));
    }

}

```

### 直播间 里面的 商品

#### 实例化

```
use zxf\WeChat\MiniProgram\Live\LiveRoomGoods;
$this->sdk = LiveRoomGoods::instance($this->config);
```

#### demo

```

/**
 * 直播间商品
 */
class LiveRoomGoodsService extends LiveBaseService
{
    public function __construct()
    {
        parent::__construct();
        $this->sdk = LiveRoomGoods::instance($this->config);
    }

    // 直播间导入商品
    public function importGoods($roomId, $goodsIdsArr = []): \Illuminate\Http\JsonResponse
    {
        $goodsIdsArr = is_array($goodsIdsArr) ? $goodsIdsArr : explode(',', $goodsIdsArr);
        return res_json($this->sdk->import($roomId, $goodsIdsArr));
    }

    // 上下架商品
    public function onSale($roomId, $goodsId, $onSale): \Illuminate\Http\JsonResponse
    {
        return res_json($this->sdk->onSale($roomId, $goodsId, $onSale));
    }

    // 删除商品
    public function deleteInRoom($roomId, $goodsId): \Illuminate\Http\JsonResponse
    {
        return res_json($this->sdk->delete($roomId, $goodsId));
    }

    // 推送商品
    public function push($roomId, $goodsId): \Illuminate\Http\JsonResponse
    {
        return res_json($this->sdk->push($roomId, $goodsId));
    }

    // 商品排序
    public function sort($roomId, $goods): \Illuminate\Http\JsonResponse
    {
        $goods = ["123", "234"];
        return res_json($this->sdk->sort($roomId, $goods));

    }

    // 下载商品讲解视频
    public function getVideo($roomId, $goodsId): \Illuminate\Http\JsonResponse
    {
        return res_json($this->sdk->getVideo($roomId, $goodsId));
    }

}
```

### 直播间 主播副号

#### 实例化

```
use zxf\WeChat\MiniProgram\Live\LiveRoomSubAnchor;
$this->sdk = LiveRoomSubAnchor::instance($this->config);
```

#### demo

```

/**
 * 直播间主播副号
 */
class LiveRoomSubAnchorService extends LiveBaseService
{

    public function __construct()
    {
        parent::__construct();
        $this->sdk = LiveRoomSubAnchor::instance($this->config);
    }

    // 获取主播副号
    public function getSubAnchor($roomId): \Illuminate\Http\JsonResponse
    {
        return res_json($this->sdk->info($roomId));
    }

    // 添加主播副号
    public function addSubAnchor($roomId, $openId): \Illuminate\Http\JsonResponse
    {
        return res_json($this->sdk->add($roomId, $openId));
    }

    // 修改主播副号
    public function modifySubAnchor($roomId, $openId): \Illuminate\Http\JsonResponse
    {
        return res_json($this->sdk->modify($roomId, $openId));
    }

    // 删除主播副号
    public function deleteSubAnchor($roomId): \Illuminate\Http\JsonResponse
    {
        return res_json($this->sdk->delete($roomId));
    }
}

```

### 直播间 长期订阅

#### 实例化

```
use zxf\WeChat\MiniProgram\Live\LiveSubscribe;
$this->sdk = LiveSubscribe::instance($this->config);
```

#### demo

```

/**
 * 直播订阅
 */
class LiveSubscribeService extends LiveBaseService
{
    public function __construct()
    {
        parent::__construct();
        $this->sdk = LiveSubscribe::instance($this->config);
    }

    // 获取长期订阅用户
    public function getWxaFollowers($page = 1, int $limit = 20): \Illuminate\Http\JsonResponse
    {
        return res_json($this->sdk->list($page, $limit));
    }

    // 长期订阅群发接口
    public function pushMessage($roomId, array $openIds = []): \Illuminate\Http\JsonResponse
    {
        return res_json($this->sdk->pushMessage($roomId, $openIds));
    }

    // 长期订阅群发结果回调
    public function callback()
    {
        // 此接口是微信服务器回调回来的，不是用户请求的
    }

}

```

## 素材服务

```
<?php

namespace xxx\WeChat;

class MaterialBaseService
{
    // 直播请求的sdk
    public $sdk;
    // 微信小程序配置
    public $config = [
        'app_id' => '',
        'secret' => '',
    ];

    public function __construct()
    {
        $config       = setting('official_wechat');
        $this->config = [
            'app_id' => $config['official_wechat_app_id'],
            'secret' => $config['official_wechat_secret'],
        ];
    }
}

```

## 永久素材管理（公账号）

```
<?php

namespace xxx\WeChat;

use zxf\WeChat\OfficialAccount\Material\PermanentFiles;

/**
 * 微信素材管理
 */
class WechatFilesService extends MaterialBaseService
{
    public function __construct()
    {
        parent::__construct();
        $this->sdk = PermanentFiles::instance($this->config);
    }

    // 上传图片
    public function uploadImg(string $imgPath = '')
    {
        return res_json($this->sdk->uploadImage($imgPath));
    }

    // 上传视频
    public function uploadVideo(string $videoPath = '', string $videoTitle = '', string $videoDescription = '')
    {
        return res_json($this->sdk->uploadVideo($videoPath, $videoTitle, $videoDescription));
    }

    // 获取素材列表
    public function getList(string $type = 'image', int $page = 1, int $limit = 10)
    {
        return res_json($this->sdk->getList($type, $page, $limit));
    }

    // 删除素材列表
    public function delete(string $mediaId = '')
    {
        return res_json($this->sdk->deleteFile($mediaId));
    }

}

```

## 临时素材管理

```
<?php

namespace xxx\WeChat;

use zxf\WeChat\OfficialAccount\Material\TempFiles; // 公众号临时素材
use zxf\WeChat\MiniProgram\Material\TempFiles; // 小程序临时素材

/**
 * 微信素材管理
 */
class WechatFilesService extends MaterialBaseService
{
    public function __construct()
    {
        parent::__construct();
        $this->sdk = TempFiles::instance($this->config);
    }

    // 上传图片
    public function uploadImg(string $imgPath = '')
    {
        return res_json($this->sdk->uploadImage($imgPath));
    }
}

```