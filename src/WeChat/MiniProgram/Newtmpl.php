<?php

namespace zxf\WeChat\MiniProgram;

use Exception;
use zxf\WeChat\Contracts\WeChatBase;

/**
 * 公众号小程序订阅消息支持
 */
class Newtmpl extends WeChatBase
{
    public $useToken = true;

    /**
     * 获取小程序账号的类目
     *
     * @link https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/mp-message-management/subscribe-message/getCategory.html
     *
     * @return array
     * @throws Exception
     */
    public function getCategory()
    {
        return $this->get('wxaapi/newtmpl/getcategory');
    }

    /**
     * 获取帐号所属类目下的公共模板标题
     *
     * @link https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/mp-message-management/subscribe-message/getPubTemplateTitleList.html
     *
     * @param string $ids 类目 id，多个用逗号隔开
     * @param string $start
     * @param int    $limit
     *
     * @return array
     * @throws Exception
     */
    public function getPubTemplateTitleList(string $ids, string $start, int $limit)
    {
        return $this->get('wxaapi/newtmpl/getpubtemplatetitles', [], ['ids' => $ids, 'start' => $start, 'limit' => $limit]);
    }

    /**
     * 获取模板标题下的关键词列表
     *
     * @link https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/mp-message-management/subscribe-message/getPubTemplateKeyWordsById.html
     *
     * @param string $tid 模板标题 id，可通过接口获取
     *
     * @return array
     * @throws Exception
     */
    public function getPubTemplateKeyWordsById(string $tid)
    {
        return $this->get('wxaapi/newtmpl/getpubtemplatekeywords', [], ['tid' => $tid]);
    }

    /**
     * 组合模板并添加至帐号下的个人模板库
     *
     * @link https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/mp-message-management/subscribe-message/addMessageTemplate.html#%E8%B0%83%E7%94%A8%E6%96%B9%E5%BC%8F
     *
     * @param string $tid       模板标题 id，可通过接口获取，也可登录小程序后台查看获取
     * @param array  $kidList   开发者自行组合好的模板关键词列表，关键词顺序可以自由搭配（例如 [3,5,4] 或 [4,5,3]），最多支持5个，最少2个关键词组合
     * @param string $sceneDesc 服务场景描述，15个字以内
     *
     * @return array
     * @throws Exception
     */
    public function addTemplate(string $tid, array $kidList, string $sceneDesc = '')
    {
        return $this->post('wxaapi/newtmpl/addtemplate', ['tid' => $tid, 'kidList' => $kidList, 'sceneDesc' => $sceneDesc], false);
    }

    /**
     * 获取当前帐号下的个人模板列表
     *
     * @link https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/mp-message-management/subscribe-message/getMessageTemplateList.html
     *
     * @return array
     * @throws Exception
     */
    public function getTemplateList()
    {
        return $this->get('wxaapi/newtmpl/gettemplate');
    }

    /**
     * 删除帐号下的个人模板
     *
     * @link https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/mp-message-management/subscribe-message/deleteMessageTemplate.html
     *
     * @param string $priTmplId 要删除的模板id
     *
     * @return array
     * @throws Exception
     */
    public function delTemplate($priTmplId)
    {
        return $this->post('wxaapi/newtmpl/deltemplate', ['priTmplId' => $priTmplId]);
    }

    /**
     * 发送订阅消息
     *
     * @link https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/mp-message-management/subscribe-message/sendMessage.html
     *
     * @param array $data 发送的消息对象数组
     *
     * @return array
     * @throws Exception
     */
    public function send(array $data)
    {
        return $this->post('cgi-bin/message/subscribe/send', $data);
    }
}