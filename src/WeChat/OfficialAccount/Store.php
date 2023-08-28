<?php

namespace zxf\WeChat\OfficialAccount;

use Exception;
use zxf\WeChat\WeChatBase;

/**
 * 微信门店
 *
 * @link https://developers.weixin.qq.com/doc/offiaccount/WeChat_Stores/WeChat_Store_Interface.html#%E5%BE%AE%E4%BF%A1%E9%97%A8%E5%BA%97%E6%8E%A5%E5%8F%A3%E6%96%87%E6%A1%A3
 */
class Store extends WeChatBase
{
    public $useToken = true;

    /**
     * 上传图片
     *
     * @param string $imagePath
     *
     * @return array
     * @throws Exception
     */
    public function uploadImg(string $imagePath)
    {
        return $this->post('cgi-bin/media/uploadimg', ['buffer' => file_get_contents($imagePath)]);
    }

    /**
     * TODO 此部分微信文档有点头疼，后续补充
     */
}