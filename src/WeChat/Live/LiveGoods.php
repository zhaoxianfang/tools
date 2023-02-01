<?php

namespace zxf\WeChat\Live;

/**
 * 直播平台 所有商品管理
 */
class LiveGoods extends Live
{
    /**
     * 导入商品到微信并提交审核 ,每天可以导入500个商品
     *
     * @param        $goods
     * @param string $miniWechatPath 小程序商品详情页路径
     *
     * @return array
     * @throws \Illuminate\Http\Client\RequestException
     */
    public function importAndWaitAudit($goods, string $miniWechatPath = ''): array
    {
        $params = [
            "goodsInfo" => [
                'name'            => $goods['name'],// 商品名称，最长14个汉字，1个汉字相当于2个字符
                'coverImgUrl'     => $goods['coverImgUrl'],// 商品图片封面 mediaID
                'priceType'       => (empty($goods['priceType']) || !in_array($goods['priceType'], [1, 2, 3])) ? 1 : $goods['priceType'],// 价格类型，1：一口价（只需要传入price，price2不传） 2：价格区间（price字段为左边界，price2字段为右边界，price和price2必传） 3：显示折扣价（price字段为原价，price2字段为现价， price和price2必传）
                'price'           => $goods['price'],// 数字，最多保留两位小数，单位元
                'price2'          => !empty($goods['price2']) ? $goods['price2'] : '',// 数字，最多保留两位小数，单位元
                'url'             => urlencode($miniWechatPath),// 商品详情页的小程序路径，路径参数存在 url 的，该参数的值需要进行 encode 处理再填入
                'thirdPartyAppid' => !empty($goods['thirdPartyAppid']) ? $goods['thirdPartyAppid'] : '',// 当商品为第三方小程序的商品则填写为对应第三方小程序的appid，自身小程序商品则为''
            ],
        ];

        $res = $this->post('wxaapi/broadcast/goods/add', $params);
        if ($res['errcode'] != 0) {
            throw new \Exception($this->getCode($res['errcode']), $res['errcode']);
        }
        return [
            'message' => $this->getCode($res['errcode']),
            'code'    => $res['errcode'],
            'data'    => [
                "goodsId" => $res['goodsId'],
                "auditId" => $res['auditId'],
            ],
        ];
    }

    // 撤回商品审核
    public function cancelAudit($goodsId, $auditId): array
    {
        $params = [
            "auditId" => $auditId,
            "goodsId" => $goodsId,
        ];

        $res = $this->post('wxaapi/broadcast/goods/resetaudit', $params);
        if ($res['errcode'] != 0) {
            throw new \Exception($this->getCode($res['errcode']), $res['errcode']);
        }
        return [
            'message' => $this->getCode($res['errcode']),
            'code'    => $res['errcode'],
        ];
    }

    // 重新提交审核
    public function audit($goodsId): array
    {
        $params = [
            "goodsId" => $goodsId,
        ];

        $res = $this->post('wxaapi/broadcast/goods/audit', $params);
        if ($res['errcode'] != 0) {
            throw new \Exception($this->getCode($res['errcode']), $res['errcode']);
        }
        return [
            'message' => $this->getCode($res['errcode']),
            'code'    => $res['errcode'],
            'data'    => [
                'auditId' => $res['auditId'],
            ],
        ];
    }

    // 删除商品
    public function delete($goodsId): array
    {
        $params = [
            "goodsId" => $goodsId,
        ];

        $res = $this->post('wxaapi/broadcast/goods/delete', $params);
        if ($res['errcode'] != 0) {
            throw new \Exception($this->getCode($res['errcode']), $res['errcode']);
        }
        return [
            'message' => $this->getCode($res['errcode']),
            'code'    => $res['errcode'],
        ];
    }

    // 更新商品
    public function update($goodsId, $goods, string $miniWechatPath = ''): array
    {
        $params = [
            "goodsInfo" => [
                'name'            => $goods['name'],// 商品名称，最长14个汉字，1个汉字相当于2个字符
                'coverImgUrl'     => $goods['coverImgUrl'],// 商品图片封面 mediaID
                'priceType'       => (empty($goods['priceType']) || !in_array($goods['priceType'], [1, 2, 3])) ? 1 : $goods['priceType'],// 价格类型，1：一口价（只需要传入price，price2不传） 2：价格区间（price字段为左边界，price2字段为右边界，price和price2必传） 3：显示折扣价（price字段为原价，price2字段为现价， price和price2必传）
                'price'           => $goods['price'],// 数字，最多保留两位小数，单位元
                'price2'          => !empty($goods['price2']) ? $goods['price2'] : '',// 数字，最多保留两位小数，单位元
                'url'             => urlencode($miniWechatPath),// 商品详情页的小程序路径，路径参数存在 url 的，该参数的值需要进行 encode 处理再填入
                'thirdPartyAppid' => !empty($goods['thirdPartyAppid']) ? $goods['thirdPartyAppid'] : '',// 当商品为第三方小程序的商品则填写为对应第三方小程序的appid，自身小程序商品则为''
                'goodsId'         => $goodsId,
            ],
        ];

        $res = $this->post('wxaapi/broadcast/goods/update', $params);
        if ($res['errcode'] != 0) {
            throw new \Exception($this->getCode($res['errcode']), $res['errcode']);
        }
        return [
            'message' => $this->getCode($res['errcode']),
            'code'    => $res['errcode'],
        ];
    }

    // 获取商品状态
    public function getGoodsWarehouse(array $goodsIds = []): array
    {
        $params = [
            "goods_ids" => array_splice($goodsIds, 0, 20),
        ];

        $res = $this->post('wxa/business/getgoodswarehouse', $params);
        if ($res['errcode'] != 0) {
            throw new \Exception($this->getCode($res['errcode']), $res['errcode']);
        }
        return [
            'message' => $this->getCode($res['errcode']),
            'code'    => $res['errcode'],
            'data'    => [
                'total' => $res['total'],
                'goods' => $res['goods'],
            ],
        ];
    }

    // 获取商品列表
    public function getApproved($status, $page = 1, $limit = 30): array
    {
        $offset = max($page - 1, 0) * $limit;
        $params = [
            "offset" => $offset,
            "limit"  => $limit,
            "status" => $status, // 商品状态，0：未审核。1：审核中，2：审核通过，3：审核驳回
        ];

        $res = $this->get('wxaapi/broadcast/goods/getapproved', $params);
        if ($res['errcode'] != 0) {
            throw new \Exception($this->getCode($res['errcode']), $res['errcode']);
        }
        return [
            'message' => $this->getCode($res['errcode']),
            'code'    => $res['errcode'],
            'data'    => [
                'total' => $res['total'],
                'goods' => $res['goods'],
            ],
        ];
    }

}
