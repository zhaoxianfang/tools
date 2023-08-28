<?php

namespace zxf\WeChat\OfficialAccount;

use Exception;
use zxf\WeChat\WeChatBase;


/**
 * 扫服务号二维码打开小程序
 */
class QrcodeJump extends WeChatBase
{
    public $useToken = true;

    /**
     * 增加或修改二维码规则
     *
     * @link https://developers.weixin.qq.com/doc/offiaccount/qrcode/qrcodejumpadd.html
     *
     * @param string $prefix 二维码规则，填服务号的带参二维码url
     *                       ，必须是http://weixin.qq.com/q/开头的url，例如http://weixin.qq.com/q/02P5KzM_xxxxx
     * @param string $appid  这里填要扫了服务号二维码之后要跳转的小程序的appid
     * @param string $path   小程序功能页面 编辑标志位，0 表示新增二维码规则，1 表示修改已有二维码规则（注意，已经发布的规则，不支持修改）
     * @param int    $is_edit
     *
     * @return mixed
     * @throws Exception
     */
    public function add(string $prefix, string $appid, string $path, int $is_edit = 0)
    {
        $data = [
            'prefix'  => 'http://weixin.qq.com/q/' . $prefix,
            'appid'   => $appid,
            'path'    => $path,
            'is_edit' => $is_edit,
        ];
        return $this->post('cgi-bin/wxopen/qrcodejumpadd', $data);
    }

    /**
     * 删除已设置的二维码规则
     *
     * @link https://developers.weixin.qq.com/doc/offiaccount/qrcode/qrcodejumpdelete.html
     *
     * @param string $prefix 服务号的带参的二维码url
     * @param string $appid  服务号二维码跳转的小程序的appid
     *
     * @return mixed
     * @throws Exception
     */
    public function delete(string $prefix, string $appid)
    {
        $data = [
            'prefix' => 'http://weixin.qq.com/q/' . $prefix,
            'appid'  => $appid,
        ];
        return $this->post('cgi-bin/wxopen/qrcodejumpdelete', $data);
    }

    /**
     * 获取已设置的二维码规则
     *
     * @link https://developers.weixin.qq.com/doc/offiaccount/qrcode/qrcodejumpget.html
     *
     * @param string     $appid String    是    小程序的appid
     * @param int        $getType
     * @param array|null $prefixList
     * @param int|null   $pageNum
     * @param int|null   $pageSize
     *
     * @return mixed
     * @throws Exception
     */
    public function getList(string $appid, int $getType, ?array $prefixList = [], ?int $pageNum = 1, ?int $pageSize = 200)
    {
        $data = [
            'appid'    => $appid,
            'get_type' => $getType,
        ];
        if ($getType == 1) {
            $data['prefix_list'] = $prefixList;
        } else {
            $data['page_num']  = $pageNum;
            $data['page_size'] = $pageSize;
        }
        return $this->post('cgi-bin/wxopen/qrcodejumpget', $data);
    }

    /**
     * 发布已设置的二维码规则
     *
     * @link https://developers.weixin.qq.com/doc/offiaccount/qrcode/qrcodejumppublish.html
     *
     * @param string $prefix
     *
     * @return mixed
     * @throws Exception
     */
    public function publish(string $prefix)
    {
        $data = [
            'prefix' => 'http://weixin.qq.com/q/' . $prefix,
        ];
        return $this->post('cgi-bin/wxopen/qrcodejumppublish', $data);
    }

}