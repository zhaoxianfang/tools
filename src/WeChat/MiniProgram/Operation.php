<?php

namespace zxf\WeChat\MiniProgram;

use Exception;
use zxf\WeChat\WeChatBase;

/**
 * 小程序运维中心
 */
class Operation extends WeChatBase
{
    public $useToken = true;

    /**
     * 实时日志查询
     *
     * @link https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/operation/realtimelogSearch.html
     *
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function realtimelogSearch($data)
    {
        return $this->post('wxaapi/userlog/userlog_search', $data);
    }

    /**
     * 查询域名配置
     *
     * @link https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/operation/getDomainInfo.html
     *
     * @param string|null $action 查询配置域名的类型, 可选值如下： 1. getbizdomain 返回业务域名 2. getserverdomain 返回服务器域名 3. 不指明返回全部
     *
     * @return array
     * @throws Exception
     */
    public function getDomainInfo(?string $action)
    {
        $data = [
            'action' => $action,
        ];
        return $this->post('wxa/getwxadevinfo', $data);
    }

    /**
     * 获取性能数据
     *
     * @link https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/operation/getPerformance.html
     *
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function getPerformance(array $data)
    {
        return $this->post('wxaapi/log/get_performance', $data);
    }

    /**
     * 获取访问来源
     *
     * @link https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/operation/getSceneList.html
     *
     * @return array
     * @throws Exception
     */
    public function getSceneList()
    {
        return $this->get('wxaapi/log/get_scene');
    }

    /**
     * 获取客户端版本
     *
     * @link https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/operation/getVersionList.html
     *
     * @return array
     * @throws Exception
     */
    public function getVersionList()
    {
        return $this->get('wxaapi/log/get_client_version');
    }

    /**
     * 获取用户反馈列表
     *
     * @link https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/operation/getFeedback.html
     *
     * @param int      $page
     * @param int      $num
     * @param int|null $type
     *
     * @return array
     * @throws Exception
     */
    public function getFeedback(int $page, int $num, ?int $type)
    {
        $data = [
            'page' => $page,
            'num'  => $num,
            'type' => $type,
        ];
        return $this->get('wxaapi/feedback/list', $data);
    }

    /**
     * 获取 mediaId 图片
     *
     * @link https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/operation/getFeedbackmedia.html
     *
     * @param int    $record_id 用户反馈信息的 record_id, 可通过 getFeedback 获取
     * @param string $media_id  图片的 mediaId
     *
     * @return array
     * @throws Exception
     */
    public function getFeedbackmedia(int $record_id, string $media_id)
    {
        $data = [
            'record_id' => $record_id,
            'media_id'  => $media_id,
        ];
        return $this->get('cgi-bin/media/getfeedbackmedia', $data);
    }

    /**
     * 查询js错误详情
     *
     * @link https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/operation/getJsErrDetail.html
     *
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function getJsErrDetail(array $data)
    {
        return $this->post('wxaapi/log/jserr_detail', $data);
    }

    /**
     * 查询错误列表
     *
     * @link https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/operation/getJsErrList.html
     *
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function getJsErrList(array $data)
    {
        return $this->post('wxaapi/log/jserr_list', $data);
    }

    /**
     * 获取分阶段发布详情
     *
     * @link https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/operation/getGrayReleasePlan.html
     *
     * @return array
     * @throws Exception
     */
    public function getGrayReleasePlan()
    {
        return $this->get('wxa/getgrayreleaseplan');
    }

}