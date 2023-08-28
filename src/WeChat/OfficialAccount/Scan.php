<?php

namespace zxf\WeChat\OfficialAccount;

use Exception;
use zxf\WeChat\WeChatBase;

/**
 * 扫一扫接入管理(源自英文文档)
 */
class Scan extends WeChatBase
{
    public $useToken = true;

    /**
     * 获取商户信息
     *
     * @link https://developers.weixin.qq.com/doc/offiaccount/en/WeChat_Scan/Product_creation.html
     *
     * @return array
     * @throws Exception
     */
    public function getMerchantInfo()
    {
        return $this->get('scan/merchantinfo/get');
    }

    /**
     * 创建商品
     *
     * @link https://developers.weixin.qq.com/doc/offiaccount/en/WeChat_Scan/Product_creation.html
     *
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function addProduct(array $data)
    {
        return $this->post('scan/product/create', $data);
    }

    /**
     * 商品发布
     *
     * @link https://developers.weixin.qq.com/doc/offiaccount/en/WeChat_Scan/Releasing_a_Product.html
     *
     * @param string $keystandard 商品编码标准
     * @param string $keystr      商品编码内容
     * @param string $status      设置发布状态。on为提交审核，off为取消发布
     *
     * @return array
     * @throws Exception
     */
    public function modProduct(string $keystandard, string $keystr, string $status = 'on')
    {
        $data = ['keystandard' => $keystandard, 'keystr' => $keystr, 'status' => $status];
        return $this->post('scan/product/modstatus', $data);
    }

    /**
     * 设置测试人员白名单
     *
     * @link https://developers.weixin.qq.com/doc/offiaccount/en/WeChat_Scan/Releasing_a_Product.html#Setting%20the%20Tester%20Whitelist
     *
     * @param array|null $openids   测试人员的openid列表
     * @param array|null $usernames 测试人员的微信号列表
     *
     * @return array
     * @throws Exception
     */
    public function setTestWhiteList(?array $openids = [], ?array $usernames = [])
    {
        $data = ['openid' => $openids, 'username' => $usernames];

        return $this->post('scan/testwhitelist/set', $data);
    }

    /**
     * 获取商品二维码
     *
     * @link https://developers.weixin.qq.com/doc/offiaccount/en/WeChat_Scan/Releasing_a_Product.html
     *
     * @param string      $keystandard
     * @param string      $keystr
     * @param integer     $qrcode_size
     * @param null|string $extinfo
     *
     * @return array
     * @throws Exception
     */
    public function getQrcode(string $keystandard, string $keystr, int $qrcode_size = 64, ?string $extinfo = null)
    {
        $data = ['keystandard' => $keystandard, 'keystr' => $keystr, 'qrcode_size' => $qrcode_size];
        is_null($extinfo) || $data['extinfo'] = $extinfo;

        return $this->post('scan/product/getqrcode', $data);
    }

    /**
     * 查询商品信息
     *
     * @link https://developers.weixin.qq.com/doc/offiaccount/en/WeChat_Scan/Product_Management.html#1
     *
     * @param string $keystandard 商品编码标准
     * @param string $keystr      商品编码内容
     *
     * @return array
     * @throws Exception
     */
    public function getProduct(string $keystandard, string $keystr)
    {
        $data = ['keystandard' => $keystandard, 'keystr' => $keystr];
        empty($extinfo) || $data['extinfo'] = $extinfo;
        return $this->post('scan/product/get', $data);
    }

    /**
     * 批量查询商品信息
     *
     * @link https://developers.weixin.qq.com/doc/offiaccount/en/WeChat_Scan/Product_Management.html#2
     *
     * @param integer     $offset 批量查询的起始位置，从0开始，包含该起始位置
     * @param integer     $limit  批量查询的数量
     * @param null|string $status 支持按状态拉取。on为发布状态，off为未发布状态，check为审核中状态，reject为审核未通过状态，all为所有状态
     * @param string|null $keystr 支持按部分编码内容拉取。填写该参数后，可将编码内容中包含所传参数的商品信息拉出。类似关键词搜索
     *
     *
     * @return array
     * @throws Exception
     */
    public function getProductList(int $offset, int $limit, ?string $status = null, ?string $keystr = null)
    {
        $data = ['offset' => $offset, 'limit' => $limit];
        empty($status) || $data['status'] = $status;
        empty($keystr) || $data['keystr'] = $keystr;
        return $this->post('scan/product/getlist', $data);
    }

    /**
     * 更新商品信息
     *
     * @link https://developers.weixin.qq.com/doc/offiaccount/en/WeChat_Scan/Product_Management.html#3
     *
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function updateProduct(array $data)
    {
        return $this->post('scan/product/update', $data);
    }

    /**
     * 清除商品信息
     *
     * @link https://developers.weixin.qq.com/doc/offiaccount/en/WeChat_Scan/Product_Management.html#4
     *
     * @param string $keystandard 商品编码标准
     * @param string $keystr      商品编码内容
     *
     * @return array
     * @throws Exception
     */
    public function clearProduct($keystandard, $keystr)
    {
        return $this->post('scan/product/clear', ['keystandard' => $keystandard, 'keystr' => $keystr]);
    }

    /**
     * 检查wxticket参数
     *
     * @link https://developers.weixin.qq.com/doc/offiaccount/en/WeChat_Scan/Product_Management.html#6
     *
     * @param string $ticket
     *
     * @return array
     * @throws Exception
     */
    public function scanTicketCheck(string $ticket)
    {
        return $this->post('scan/scanticket/check', ['ticket' => $ticket]);
    }

    /**
     * 清除扫码记录
     *
     * @link https://developers.weixin.qq.com/doc/offiaccount/en/WeChat_Scan/Product_Management.html#8
     *
     * @param string $keystandard 商品编码标准
     * @param string $keystr      商品编码内容
     * @param string $extinfo     调用“获取商品二维码接口”时传入的extinfo，为标识参数
     *
     * @return array
     * @throws Exception
     */
    public function clearScanticket(string $keystandard, string $keystr, string $extinfo)
    {
        $data = ['keystandard' => $keystandard, 'keystr' => $keystr, 'extinfo' => $extinfo];
        return $this->post('scan/scanticket/check', $data);
    }
}