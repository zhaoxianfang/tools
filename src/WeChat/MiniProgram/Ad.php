<?php

namespace zxf\WeChat\MiniProgram;

use Exception;
use zxf\WeChat\WeChatBase;

/**
 * 广告
 */
class Ad extends WeChatBase
{
    public $useToken = false;

    /**
     * 回传广告数据
     *
     * @link https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/ad/ad.addUserAction.html
     *
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function addUserAction(array $data)
    {
        return $this->post('marketing/user_actions/add', $data);
    }

    /**
     * 广告创建数据源
     *
     * @link https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/ad/ad.addUserActionSet.html
     *
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function addUserActionSet(array $data)
    {
        return $this->post('marketing/user_action_sets/add', $data);
    }

    /**
     * 广告数据源报表查询
     *
     * @link https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/ad/ad.getUserActionSetReports.html
     *
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function getUserActionSetReports(array $data)
    {
        return $this->post('marketing/user_action_set_reports/get', $data);
    }

    /**
     * 广告数据源查询
     *
     * @link https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/ad/ad.getUserActionSets.html
     *
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function getUserActionSets(array $data)
    {
        return $this->post('marketing/user_action_sets/get', $data);
    }
}