<?php

namespace zxf\WeChat\OfficialAccount;

use Exception;
use zxf\WeChat\Contracts\WeChatBase;

/**
 * 小程序顾问
 */
class Guide extends WeChatBase
{
    public $useToken = true;

    /**
     * 获取顾问信息
     *
     * @link https://developers.weixin.qq.com/doc/offiaccount/Shopping_Guide/guide-account/shopping-guide.getGuideAcct.html
     *
     * @param string|null $guide_account 顾问微信号（guide_account和guide_openid二选一）
     * @param string|null $guide_openid  顾问openid或者unionid（guide_account和guide_openid二选一）
     *
     * @return array
     * @throws Exception
     */
    public function getGuideAcct(?string $guide_account, ?string $guide_openid)
    {
        $data = [
            'guide_account' => $guide_account,
            'guide_openid'  => $guide_openid,
        ];
        return $this->post('cgi-bin/guide/getguideacct', $data);
    }
}