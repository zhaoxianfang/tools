<?php

namespace zxf\WeChat;

class WechatCode
{
    // 直播错误码参照
    public static $errCode = [
        "-1"      => "系统错误",
        "0"       => "请求成功",
        "1"       => "未创建直播间",
        "1003"    => "POST参数非法",
        "7000"    => "参数错误",
        "20002"   => "POST参数非法",
        "40001"   => "AppSecret错误或者 AppSecret 不属于这个公众号",
        "40002"   => "请确保grant_type字段值为client_credential",
        "40003"   => "计算签名失败",
        "40004"   => "无效的媒体类型",
        "40005"   => "上传素材文件格式不对",
        "40006"   => "上传素材文件大小超出限制",
        "40007"   => "无效的media_id",
        "40008"   => "不合法的消息类型",
        "40009"   => "不合法的图片文件大小",
        "40010"   => "不合法的语音文件大小",
        "40011"   => "不合法的视频文件大小",
        "40012"   => "不合法的缩略图文件大小",
        "40013"   => "不合法的 AppID ，请开发者检查 AppID 的正确性，避免异常字符，注意大小写",
        "40014"   => "不合法的 access_token ，请开发者认真比对 access_token 的有效性（如是否过期），或查看是否正在为恰当的公众号调用接口",
        "40015"   => "不合法的菜单类型",
        "40016"   => "不合法的按钮个数",
        "40017"   => "不合法的按钮个数",
        "40018"   => "不合法的按钮名字长度",
        "40019"   => "不合法的按钮 KEY 长度",
        "40020"   => "不合法的按钮 URL 长度",
        "40021"   => "不合法的菜单版本号",
        "40022"   => "不合法的子菜单级数",
        "40023"   => "不合法的子菜单按钮个数",
        "40024"   => "不合法的子菜单按钮类型",
        "40025"   => "不合法的子菜单按钮名字长度",
        "40026"   => "不合法的子菜单按钮 KEY 长度",
        "40027"   => "不合法的子菜单按钮 URL 长度",
        "40028"   => "不合法的自定义菜单使用用户",
        "40029"   => "不合法的 oauth_code",
        "40030"   => "不合法的 refresh_token",
        "40031"   => "不合法的 openid 列表",
        "40032"   => "不合法的 openid 列表长度",
        "40033"   => "不合法的请求字符，不能包含 \\uxxxx 格式的字符",
        "40035"   => "不合法的参数",
        "40038"   => "不合法的请求格式",
        "40039"   => "不合法的 URL 长度",
        "40048"   => "无效的url",
        "40050"   => "不合法的分组 id",
        "40051"   => "分组名字不合法",
        "40052"   => "action值有误",
        "40060"   => "删除单篇图文时，指定的 article_idx 不合法",
        "40097"   => "参数错误",
        "40117"   => "分组名字不合法",
        "40118"   => "media_id 大小不合法",
        "40119"   => "button 类型错误",
        "40120"   => "button 类型错误",
        "40121"   => "不合法的 media_id 类型",
        "40125"   => "无效的appsecret",
        "40132"   => "微信号不合法",
        "40137"   => "不支持的图片格式",
        "40155"   => "请勿添加其他公众号的主页链接",
        "40159"   => "path 不能为空，且长度不能大于1024",
        "40163"   => "oauth_code已使用",
        "40164"   => "调用接口的 IP 地址不在白名单中",
        "40227"   => "标题为空",
        "41001"   => "缺少 access_token 参数",
        "41002"   => "缺少 appid 参数",
        "41003"   => "缺少 refresh_token 参数",
        "41004"   => "缺少 secret 参数",
        "41005"   => "缺少多媒体文件数据",
        "41006"   => "缺少 media_id 参数",
        "41007"   => "缺少子菜单数据",
        "41008"   => "缺少 oauth code",
        "41009"   => "缺少 openid",
        "42001"   => "access_token 超时，请检查 access_token 的有效期，请参考基础支持 - 获取 access_token 中，对 access_token 的详细机制说明",
        "42002"   => "refresh_token 超时",
        "42003"   => "oauth_code 超时",
        "42007"   => "用户修改微信密码， accesstoken 和 refreshtoken 失效，需要重新授权",
        "42010"   => "相同 media_id 群发过快，请重试",
        "43001"   => "需要 GET 请求",
        "43002"   => "需要 POST 请求",
        "43003"   => "需要 HTTPS 请求",
        "43004"   => "需要接收者关注",
        "43005"   => "需要好友关系",
        "43019"   => "需要将接收者从黑名单中移除",
        "44001"   => "多媒体文件为空",
        "44002"   => "POST 的数据包为空",
        "44003"   => "图文消息内容为空",
        "44004"   => "文本消息内容为空",
        "45001"   => "多媒体文件大小超过限制",
        "45002"   => "消息内容超过限制",
        "45003"   => "标题字段超过限制",
        "45004"   => "描述字段超过限制",
        "45005"   => "链接字段超过限制",
        "45006"   => "图片链接字段超过限制",
        "45007"   => "语音播放时间超过限制",
        "45008"   => "图文消息超过限制",
        "45009"   => "接口调用超过限制",
        "45010"   => "创建菜单个数超过限制",
        "45011"   => "API 调用太频繁，请稍候再试",
        "45015"   => "回复时间超过限制",
        "45016"   => "系统分组，不允许修改",
        "45017"   => "分组名字过长",
        "45018"   => "分组数量超过上限",
        "45029"   => "生成码个数总和到达最大个数限制",
        "45047"   => "客服接口下行条数超过上限",
        "45064"   => "创建菜单包含未关联的小程序",
        "45065"   => "相同 clientmsgid 已存在群发记录，返回数据中带有已存在的群发任务的 msgid",
        "45066"   => "相同 clientmsgid 重试速度过快，请间隔1分钟重试",
        "45067"   => "clientmsgid 长度超过限制",
        "45083"   => "设置的 speed 参数不在0到4的范围内",
        "45084"   => "没有设置 speed 参数",
        "45110"   => "作者字数超出限制",
        "46001"   => "不存在媒体数据",
        "46002"   => "不存在的菜单版本",
        "46003"   => "不存在的菜单数据",
        "46004"   => "不存在的用户",
        "47001"   => "解析 JSON/XML 内容错误",
        "47003"   => "参数值不符合限制要求，详情可参考参数值内容限制说明",
        "48001"   => "api 功能未授权，请确认公众号已获得该接口，可以在公众平台官网 - 开发者中心页中查看接口权限",
        "48002"   => "粉丝拒收消息（粉丝在公众号选项中，关闭了 “ 接收消息 ” ）",
        "48004"   => "api 接口被封禁，请登录 mp.weixin.qq.com 查看详情",
        "48005"   => "api 禁止删除被自动回复和自定义菜单引用的素材",
        "48006"   => "api 禁止清零调用次数，因为清零次数达到上限",
        "48008"   => "没有该类型消息的发送权限",
        "44990"   => "接口请求太快（超过5次/秒）",
        "50001"   => "用户未授权该 api",
        "50002"   => "用户受限，可能是违规后接口被封禁",
        "50005"   => "用户未关注公众号",
        "53500"   => "发布功能被封禁",
        "53501"   => "频繁请求发布",
        "53502"   => "Publish ID 无效",
        "53600"   => "Article ID 无效",
        "61451"   => "参数错误 (invalid parameter)",
        "61452"   => "无效客服账号 (invalid kf_account)",
        "61453"   => "客服帐号已存在 (kf_account exsited)",
        "61454"   => "客服帐号名长度超过限制 ( 仅允许 10 个英文字符，不包括 @ 及 @ 后的公众号的微信号 )(invalid kf_acount length)",
        "61455"   => "客服帐号名包含非法字符 ( 仅允许英文 + 数字 )(illegal character in kf_account)",
        "61456"   => "客服帐号个数超过限制 (10 个客服账号 )(kf_account count exceeded)",
        "61457"   => "无效头像文件类型 (invalid file type)",
        "61450"   => "系统错误 (system error)",
        "61500"   => "日期格式错误",
        "63001"   => "部分参数为空",
        "63002"   => "无效的签名",
        "65301"   => "不存在此 menuid 对应的个性化菜单",
        "65302"   => "没有相应的用户",
        "65303"   => "没有默认菜单，不能创建个性化菜单",
        "65304"   => "MatchRule 信息为空",
        "65305"   => "个性化菜单数量受限",
        "65306"   => "不支持个性化菜单的帐号",
        "65307"   => "个性化菜单信息为空",
        "65308"   => "包含没有响应类型的 button",
        "65309"   => "个性化菜单开关处于关闭状态",
        "65310"   => "填写了省份或城市信息，国家信息不能为空",
        "65311"   => "填写了城市信息，省份信息不能为空",
        "65312"   => "不合法的国家信息",
        "65313"   => "不合法的省份信息",
        "65314"   => "不合法的城市信息",
        "65316"   => "该公众号的菜单设置了过多的域名外跳（最多跳转到 3 个域名的链接）",
        "65317"   => "不合法的 URL",
        "87009"   => "无效的签名",
        "65400"   => "API不可用，即没有开通/升级到新版客服功能",
        "65401"   => "无效客服帐号",
        "65403"   => "客服昵称不合法",
        "65404"   => "客服帐号不合法",
        "65405"   => "帐号数目已达到上限，不能继续添加",
        "65406"   => "已经存在的客服帐号",
        "65407"   => "邀请对象已经是该公众号客服",
        "65408"   => "本公众号已经有一个邀请给该微信",
        "65409"   => "无效的微信号",
        "65410"   => "邀请对象绑定公众号客服数达到上限",//（目前每个微信号可以绑定5个公众号客服帐号）"
        "65411"   => "该帐号已经有一个等待确认的邀请，不能重复邀请",
        "65412"   => "该帐号已经绑定微信号，不能进行邀请",
        "80067"   => "找不到使用的插件",
        "85066"   => "链接错误",
        "85070"   => "URL命中黑名单，无法添加",
        "85071"   => "已添加该链接，请勿重复添加",
        "85072"   => "该链接已被占用",
        "85073"   => "二维码规则已满",
        "85075"   => "个人类型小程序无法设置二维码规则",
        "85096"   => "scancode_time为系统保留参数，不允许配置",
        "89501"   => "此 IP 正在等待管理员确认,请联系管理员",
        "89503"   => "此 IP 调用需要管理员确认,请联系管理员",
        "89504"   => "群发仍在审批流程中，请联系管理员",
        "89505"   => "群发进入管理员确认流程，请稍等",
        "89506"   => "24小时内该 IP 被管理员拒绝调用两次，24小时内不可再使用该 IP 调用",
        "89507"   => "1小时内该 IP 被管理员拒绝调用一次，1小时内不可再使用该 IP 调用",
        "92000"   => "该经营资质已添加，请勿重复添加	该经营资质已添加，请勿重复添加",
        "92002"   => "附近地点添加数量达到上线，无法继续添加	附近地点添加数量达到上线，无法继续添加",
        "92003"   => "	地点已被其它小程序占用	地点已被其它小程序占用",
        "92004"   => "附近功能被封禁",
        "92005"   => "地点正在审核中	地点正在审核中",
        "92006"   => "地点正在展示小程序",
        "92007"   => "地点审核失败",
        "92008"   => "小程序未展示在该地点",
        "93009"   => "小程序未上架或不可见",
        "93010"   => "地点不存在",
        "93011"   => "个人类型小程序不可用",
        "93012"   => "非普通类型小程序（门店小程序、小店小程序等）不可用",
        "93013"   => "从腾讯地图获取地址详细信息失败",
        "93014"   => "同一资质证件号重复添加",
        "200001"  => "入参错误",
        "200002"  => "入参错误",
        "300001"  => "禁止创建/更新商品 或 禁止编辑&更新房间",
        "300002"  => "名称长度不符合规则",
        "300003"  => "价格输入不合规", //（如：现价比原价大、传入价格非数字等）
        "300004"  => "商品名称存在违规违法内容",
        "300005"  => "商品图片存在违规违法内容",
        "300006"  => "图片上传失败",//（如=>mediaID过期）
        "300007"  => "线上小程序版本不存在该链接",
        "300008"  => "添加商品失败",
        "300009"  => "商品审核撤回失败",
        "300010"  => "商品审核状态不对", //（如：商品审核中）
        "300011"  => "操作非法", //（API不允许操作非 API 创建的商品）
        "300012"  => "没有提审额度", //（每天500次提审额度）
        "300013"  => "提审失败",
        "300014"  => "审核中，无法删除", //（非零代表失败）
        "300015"  => "商品id不存在",
        "300017"  => "商品未提审",
        "300018"  => "商品图片尺寸过大",
        "300021"  => "商品添加成功，审核失败",
        "300022"  => "此房间号不存在",
        "300023"  => "房间状态 拦截（当前房间状态不允许此操作）",
        "300024"  => "商品不存在",
        "300025"  => "商品审核未通过",
        "300026"  => "房间商品数量已经满额",
        "300027"  => "导入商品失败",
        "300028"  => "房间名称违规",
        "300029"  => "主播昵称违规",
        "300030"  => "主播微信号不合法",
        "300031"  => "直播间封面图不合规",
        "300032"  => "直播间分享图违规",
        "300033"  => "添加商品超过直播间上限",
        "300034"  => "主播微信昵称长度不符合要求",
        "300035"  => "主播微信号不存在",
        "300036"  => "主播微信号未实名认证",
        "300037"  => "购物直播频道封面图不合规",
        "300038"  => "未在小程序管理后台配置客服",
        "300039"  => "主播副号微信号不合法",
        "300040"  => "名称含有非限定字符（含有特殊字符）",
        "300041"  => "创建者微信号不合法",
        "300042"  => "推流中禁止编辑房间",
        "300043"  => "每天只允许一场直播开启关注",
        "300044"  => "商品没有讲解视频",
        "300045"  => "讲解视频未生成",
        "300046"  => "讲解视频生成失败",
        "300047"  => "已有商品正在推送，请稍后再试",
        "300048"  => "拉取商品列表失败",
        "300049"  => "商品推送过程中不允许上下架",
        "300050"  => "排序商品列表为空",
        "300051"  => "解析 JSON 出错",
        "300052"  => "已下架的商品无法推送",
        "300053"  => "直播间未添加此商品",
        "400001"  => "微信号不合规",
        "400002"  => "微信号需要实名认证",//，仅设置主播角色时可能出现
        "400003"  => " 添加角色达到上限",//（管理员10个，运营者500个，主播500个）
        "400004"  => "重复添加角色",
        "400005"  => "主播角色删除失败，该主播存在未开播的直播间",
        "500001"  => "副号不合规",
        "500002"  => "副号未实名",
        "500003"  => "已经设置过副号了，不能重复设置",
        "500004"  => "不能设置重复的副号",
        "500005"  => "副号不能和主号重复",
        "600001"  => "用户已被添加为小助手",
        "600002"  => "找不到用户",
        "886001"  => "系统繁忙，请重试",
        "9410000" => "直播间列表为空",
        "9410001" => "获取房间失败",
        "9410002" => "获取商品失败",
        "9410003" => "获取回放失败",
        "9001001" => "POST 数据参数不合法",
        "9001002" => "远端服务不可用",
        "9001003" => "Ticket 不合法",
        "9001004" => "获取摇周边用户信息失败",
        "9001005" => "获取商户信息失败",
        "9001006" => "获取 OpenID 失败",
        "9001007" => "上传文件缺失",
        "9001008" => "上传素材的文件类型不合法",
        "9001009" => "上传素材的文件尺寸不合法",
        "9001010" => "上传失败",
        "9001020" => "帐号不合法",
        "9001021" => "已有设备激活率低于 50% ，不能新增设备",
        "9001022" => "设备申请数不合法，必须为大于 0 的数字",
        "9001023" => "已存在审核中的设备 ID 申请",
        "9001024" => "一次查询设备 ID 数量不能超过 50",
        "9001025" => "设备 ID 不合法",
        "9001026" => "页面 ID 不合法",
        "9001027" => "页面参数不合法",
        "9001028" => "一次删除页面 ID 数量不能超过 10",
        "9001029" => "页面已应用在设备中，请先解除应用关系再删除",
        "9001030" => "一次查询页面 ID 数量不能超过 50",
        "9001031" => "时间区间不合法",
        "9001032" => "保存设备与页面的绑定关系参数错误",
        "9001033" => "门店 ID 不合法",
        "9001034" => "设备备注信息过长",
        "9001035" => "设备申请参数不合法",
        "9001036" => "查询起始值 begin 不合法",
    ];

    /**
     * 获取状态码
     *
     * @param $code
     *
     * @return string
     */
    public function getMessage($code = 0): string
    {
        return !empty(self::$errCode[$code]) ? self::$errCode[$code] : "出错啦:" . $code;
    }

}
