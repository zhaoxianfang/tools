<?php

namespace zxf\WeChat\Mini\Live;

class LiveRoom extends LiveBase
{

    /**
     * 创建直播间
     *
     * @param $data
     *
     * @return array
     */
    public function create($data): array
    {
        $params = [
            "name"            => $data["name"],  // 房间名字 3~17个汉字
            "coverImg"        => $data["coverImg"],   // 背景图，填入mediaID 通过 uploadfile 上传，填写 mediaID
            "startTime"       => strtotime($data["start"]),   // 开播时间需要在当前时间的10分钟后 并且 开始时间不能在 6 个月后
            "endTime"         => strtotime($data["end"]), // 结束时间 开播时间和结束时间间隔不得短于30分钟，不得超过24小时
            "anchorName"      => $data["anchorName"],  // 主播昵称 最短2个汉字，最长15个汉字，1个汉字相当于2个字符
            "anchorWechat"    => $data["anchorWechat"],  // 主播微信号
            "subAnchorWechat" => !empty($data["subAnchorWechat"]) ? $data["subAnchorWechat"] : "",  // 主播副号微信号
            "createrWechat"   => !empty($data["createrWechat"]) ? $data["createrWechat"] : "",  // 创建者微信号，不传入则此直播间所有成员可见。传入则此房间仅创建者、管理员、超管、直播间主播可见
            "shareImg"        => $data["shareImg"],  // 分享图，填入mediaID
            "feedsImg"        => $data["feedsImg"],  // 购物直播频道封面图，填入mediaID
            "isFeedsPublic"   => isset($data["isFeedsPublic"]) ? ($data["isFeedsPublic"] == 0 ? 0 : 1) : 1,  // 是否开启官方收录 【1: 开启，0：关闭】，默认开启收录
            "type"            => $data["type"],  // 直播间类型 【1: 推流，0：手机直播】
            "closeLike"       => isset($data["closeLike"]) ? ($data["closeLike"] == 1 ? 1 : 0) : 0,  // 是否关闭点赞 【0：开启，1：关闭】（若关闭，观众端将隐藏点赞按钮，直播开始后不允许开启）
            "closeGoods"      => isset($data["closeGoods"]) ? ($data["closeGoods"] == 1 ? 1 : 0) : 0,  // 是否关闭货架 【0：开启，1：关闭】（若关闭，观众端将隐藏商品货架，直播开始后不允许开启）
            "closeComment"    => isset($data["closeComment"]) ? ($data["closeComment"] == 1 ? 1 : 0) : 0,  // 是否关闭评论 【0：开启，1：关闭】（若关闭，观众端将隐藏评论入口，直播开始后不允许开启）
            "closeReplay"     => isset($data["closeReplay"]) ? ($data["closeReplay"] == 1 ? 1 : 0) : 0,  // 是否关闭回放 【0：开启，1：关闭】默认关闭回放（直播开始后允许开启）
            "closeShare"      => isset($data["closeShare"]) ? ($data["closeShare"] == 1 ? 1 : 0) : 0,  // 是否关闭分享 【0：开启，1：关闭】默认开启分享（直播开始后不允许修改）
            "closeKf"         => isset($data["closeKf"]) ? ($data["closeKf"] == 1 ? 1 : 0) : 0,  // 是否关闭客服 【0：开启，1：关闭】 默认关闭客服（直播开始后允许开启）
        ];
        $res    = $this->post("wxaapi/broadcast/room/create", $params);
        if ($res["errcode"] != 0) {
            // 判断主播有没有实名认证
            if (!empty($res["qrcode_url"])) {
                return [
                    "message" => $this->getMessage($res["errcode"]),
                    "code"    => $res["errcode"],
                    "data"    => [
                        "qrcode_url" => $res["qrcode_url"],
                        "room_id"    => !empty($res["roomId"]) ? $res["roomId"] : "",
                    ],
                ];
            } else {
                throw new \Exception($this->getMessage($res["errcode"]), $res["errcode"]);
            }
        }
        if (!empty($res["roomId"])) {
            // 创建直播间成功
            // 判断主播有没有实名认证
            $response = [
                "code"    => $res["errcode"],
                "message" => $this->getMessage($res["errcode"]),
                "data"    => [
                    "room_id" => $res["roomId"],
                ],
            ];
            if (!empty($res["qrcode_url"])) {
                $response["data"]["qrcode_url"] = $res["qrcode_url"];
            }
            return $response;
        }
        throw new \Exception("未知的错误", 500);
    }


    /**
     * 修改|编辑 直播间
     *
     * @param $roomId
     * @param $data
     *
     * @return array
     * @throws \Illuminate\Http\Client\RequestException
     */
    public function update($roomId, $data): array
    {
        $params = [
            "id"              => $roomId,  // 房间id
            "name"            => $data["name"],  // 房间名字 3~17个汉字
            "coverImg"        => $data["coverImg"],   // 背景图，填入mediaID 通过 uploadfile 上传，填写 mediaID
            "startTime"       => strtotime($data["start"]),   // 开播时间需要在当前时间的10分钟后 并且 开始时间不能在 6 个月后
            "endTime"         => strtotime($data["end"]), // 结束时间 开播时间和结束时间间隔不得短于30分钟，不得超过24小时
            "anchorName"      => $data["anchorName"],  // 主播昵称 最短2个汉字，最长15个汉字，1个汉字相当于2个字符
            "anchorWechat"    => $data["anchorWechat"],  // 主播微信号
            "subAnchorWechat" => !empty($data["subAnchorWechat"]) ? $data["subAnchorWechat"] : "",  // 主播副号微信号
            "createrWechat"   => !empty($data["createrWechat"]) ? $data["createrWechat"] : "",  // 创建者微信号，不传入则此直播间所有成员可见。传入则此房间仅创建者、管理员、超管、直播间主播可见
            "shareImg"        => $data["shareImg"],  // 分享图，填入mediaID
            "feedsImg"        => $data["feedsImg"],  // 购物直播频道封面图，填入mediaID
            "isFeedsPublic"   => isset($data["isFeedsPublic"]) ? ($data["isFeedsPublic"] == 0 ? 0 : 1) : 1,  // 是否开启官方收录 【1: 开启，0：关闭】，默认开启收录
            "type"            => $data["type"],  // 直播间类型 【1: 推流，0：手机直播】
            "closeLike"       => isset($data["closeLike"]) ? ($data["closeLike"] == 1 ? 1 : 0) : 0,  // 是否关闭点赞 【0：开启，1：关闭】（若关闭，观众端将隐藏点赞按钮，直播开始后不允许开启）
            "closeGoods"      => isset($data["closeGoods"]) ? ($data["closeGoods"] == 1 ? 1 : 0) : 0,  // 是否关闭货架 【0：开启，1：关闭】（若关闭，观众端将隐藏商品货架，直播开始后不允许开启）
            "closeComment"    => isset($data["closeComment"]) ? ($data["closeComment"] == 1 ? 1 : 0) : 0,  // 是否关闭评论 【0：开启，1：关闭】（若关闭，观众端将隐藏评论入口，直播开始后不允许开启）
            "closeReplay"     => isset($data["closeReplay"]) ? ($data["closeReplay"] == 1 ? 1 : 0) : 0,  // 是否关闭回放 【0：开启，1：关闭】默认关闭回放（直播开始后允许开启）
            "closeShare"      => isset($data["closeShare"]) ? ($data["closeShare"] == 1 ? 1 : 0) : 0,  // 是否关闭分享 【0：开启，1：关闭】默认开启分享（直播开始后不允许修改）
            "closeKf"         => isset($data["closeKf"]) ? ($data["closeKf"] == 1 ? 1 : 0) : 0,  // 是否关闭客服 【0：开启，1：关闭】 默认关闭客服（直播开始后允许开启）
        ];
        $res    = $this->post("wxaapi/broadcast/room/editroom", $params);
        if ($res["errcode"] != 0) {
            throw new \Exception($this->getMessage($res["errcode"]), $res["errcode"]);
        }
        return [
            "code"    => $res["errcode"],
            "message" => $this->getMessage($res["errcode"]),
        ];
    }

    /**
     * 删除直播间
     *
     * @param $roomId
     *
     * @return array
     * @throws \Illuminate\Http\Client\RequestException
     */
    public function delete($roomId): array
    {
        $params = [
            "id" => $roomId,
        ];
        $res    = $this->post("wxaapi/broadcast/room/deleteroom", $params);
        if ($res["errcode"] != 0) {
            throw new \Exception($this->getMessage($res["errcode"]), $res["errcode"]);
        }
        return [
            "message" => "操作成功",
            "code"    => $res["errcode"],
        ];
    }

    /**
     * 获取直播间列表
     *
     * @param $page
     * @param $limit
     *
     * @return array
     * @throws \Illuminate\Http\Client\RequestException
     */
    public function list($page = 1, $limit = 10): array
    {
        $params = [
            "start" => $page >= 1 ? $page - 1 : 0, // 起始拉取房间，start = 0 表示从第 1 个房间开始拉取
            "limit" => $limit // 每次拉取的个数上限，不要设置过大，建议 100 以内
        ];

        $res = $this->post("wxa/business/getliveinfo", $params);
        if ($res["errcode"] != 0) {
            throw new \Exception($this->getMessage($res["errcode"]), $res["errcode"]);
        }
        return [
            "message" => "成功",
            "code"    => $res["errcode"],
            "data"    => [
                "total" => $res["total"],
                "list"  => $res["room_info"],
            ],
        ];
    }

    /**
     * 获取直播间回放
     *
     * @param $roomId
     * @param $page
     * @param $limit
     *
     * @return array
     * @throws \Illuminate\Http\Client\RequestException
     */
    public function getReplay($roomId, $page = 1, $limit = 5): array
    {
        $params = [
            "action"  => "get_replay",
            "room_id" => $roomId,
            "start"   => $page >= 1 ? $page - 1 : 0, // 起始拉取视频，0表示从第一个视频片段开始拉取
            "limit"   => $limit // 每次拉取的数量
        ];

        $res = $this->post("wxa/business/getliveinfo", $params);
        if ($res["errcode"] != 0) {
            throw new \Exception($this->getMessage($res["errcode"]), $res["errcode"]);
        }
        return [
            "message" => "获取成功",
            "code"    => $res["errcode"],
            "data"    => [
                "total" => $res["total"],
                "list"  => $res["live_replay"],
            ],
        ];
    }

    /**
     * 获取直播间 推流地址
     *
     * @param $roomId
     *
     * @return array
     * @throws \Illuminate\Http\Client\RequestException
     */
    public function getPushUrl($roomId): array
    {
        $params = [
            "roomId" => $roomId,
        ];

        $res = $this->get("wxaapi/broadcast/room/getpushurl", $params);
        if ($res["errcode"] != 0) {
            throw new \Exception($this->getMessage($res["errcode"]), $res["errcode"]);
        }
        return [
            "message" => "获取成功",
            "code"    => $res["errcode"],
            "data"    => [
                "url" => $res["pushAddr"],
            ],
        ];
    }

    // 获取直播间 分享二维码
    public function getSharedCode($roomId, array $custom_params = []): array
    {
        $params = [
            "roomId" => $roomId,
            "params" => urlencode(json_encode($custom_params)),
        ];

        $res = $this->get("wxaapi/broadcast/room/getsharedcode", $params);
        if ($res["errcode"] != 0) {
            throw new \Exception($this->getMessage($res["errcode"]), $res["errcode"]);
        }
        return [
            "message" => "获取成功",
            "code"    => $res["errcode"],
            "data"    => [
                "cdn_path"   => $res["cdnUrl"],
                "page_path"  => $res["pagePath"],
                "poster_url" => $res["posterUrl"],
            ],
        ];
    }

    // 开启/关闭直播间官方收录
    public function updateFeedPublic($roomId, $isFeedsPublic): array
    {
        $params = [
            "roomId"        => $roomId,
            "isFeedsPublic" => $isFeedsPublic,
        ];

        $res = $this->post("wxaapi/broadcast/room/updatefeedpublic", $params);
        if ($res["errcode"] != 0) {
            throw new \Exception($this->getMessage($res["errcode"]), $res["errcode"]);
        }
        return [
            "message" => "操作成功",
            "code"    => $res["errcode"],
        ];
    }

    // 开启/关闭回放功能
    public function updateReplay($roomId, $closeReplay): array
    {
        $params = [
            "roomId"      => $roomId,
            "closeReplay" => $closeReplay,
        ];

        $res = $this->post("wxaapi/broadcast/room/updatereplay", $params);
        if ($res["errcode"] != 0) {
            throw new \Exception($this->getMessage($res["errcode"]), $res["errcode"]);
        }
        return [
            "message" => "操作成功",
            "code"    => $res["errcode"],
        ];
    }

    // 开启/关闭客服功能
    public function updateKf($roomId, $closeKf): array
    {
        $params = [
            "roomId"  => $roomId,
            "closeKf" => $closeKf,
        ];

        $res = $this->post("wxaapi/broadcast/room/updatekf", $params);
        if ($res["errcode"] != 0) {
            throw new \Exception($this->getMessage($res["errcode"]), $res["errcode"]);
        }
        return [
            "message" => "操作成功",
            "code"    => $res["errcode"],
        ];
    }

    // 开启/关闭直播间全局禁言
    public function updateComment($roomId, $banComment): array
    {
        $params = [
            "roomId"     => $roomId,
            "banComment" => $banComment,
        ];

        $res = $this->post("wxaapi/broadcast/room/updatecomment", $params);
        if ($res["errcode"] != 0) {
            throw new \Exception($this->getMessage($res["errcode"]), $res["errcode"]);
        }
        return [
            "message" => "操作成功",
            "code"    => $res["errcode"],
        ];
    }

}
