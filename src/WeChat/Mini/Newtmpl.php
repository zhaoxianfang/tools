<?php


namespace zxf\WeChat\Mini;

use zxf\WeChat\WeChatBase;
use Exception;

/**
 * 公众号小程序订阅消息支持
 * Class Mini
 *
 * @package WeChat
 */
class Newtmpl extends WeChatBase
{
    /**
     * 获取小程序账号的类目
     *
     * @param array $data 类目信息列表
     *
     * @return array
     * @throws Exception
     */
    public function addCategory($data)
    {
        return $this->post("cgi-bin/wxopen/addcategory", $data);
    }

    /**
     * 获取小程序账号的类目
     *
     * @return array
     * @throws Exception
     */
    public function getCategory()
    {
        return $this->get("wxaapi/newtmpl/getcategory");
    }

    /**
     * 获取小程序账号的类目
     *
     * @return array
     * @throws Exception
     */
    public function deleteCategory()
    {
        return $this->post("cgi-bin/wxopen/deletecategory");
    }

    /**
     * 获取帐号所属类目下的公共模板标题
     *
     * @param string $ids 类目 id，多个用逗号隔开
     *
     * @return array
     * @throws Exception
     */
    public function getPubTemplateTitleList($ids)
    {
        $url = "wxaapi/newtmpl/getpubtemplatetitles";
        $url .= "&" . http_build_query(["ids" => $ids, "start" => "0", "limit" => "30"]);
        return $this->get($url);
    }

    /**
     * 获取模板标题下的关键词列表
     *
     * @param string $tid 模板标题 id，可通过接口获取
     *
     * @return array
     * @throws Exception
     */
    public function getPubTemplateKeyWordsById($tid)
    {
        $url = "wxaapi/newtmpl/getpubtemplatekeywords";
        $url .= "&" . http_build_query(["tid" => $tid]);
        return $this->get($url);
    }

    /**
     * 组合模板并添加至帐号下的个人模板库
     *
     * @param string $tid       模板标题 id，可通过接口获取，也可登录小程序后台查看获取
     * @param array  $kidList   开发者自行组合好的模板关键词列表，关键词顺序可以自由搭配（例如 [3,5,4] 或 [4,5,3]），最多支持5个，最少2个关键词组合
     * @param string $sceneDesc 服务场景描述，15个字以内
     *
     * @return array
     * @throws Exception
     */
    public function addTemplate($tid, array $kidList, $sceneDesc = "")
    {
        return $this->post("wxaapi/newtmpl/addtemplate", ["tid" => $tid, "kidList" => $kidList, "sceneDesc" => $sceneDesc]);
    }

    /**
     * 获取当前帐号下的个人模板列表
     *
     * @return array
     * @throws Exception
     */
    public function getTemplateList()
    {
        return $this->get("wxaapi/newtmpl/gettemplate");
    }

    /**
     * 删除帐号下的个人模板
     *
     * @param string $priTmplId 要删除的模板id
     *
     * @return array
     * @throws Exception
     */
    public function delTemplate($priTmplId)
    {
        return $this->post("wxaapi/newtmpl/deltemplate", ["priTmplId" => $priTmplId]);
    }

    /**
     * 发送订阅消息
     *
     * @param array $data 发送的消息对象数组
     *
     * @return array
     * @throws Exception
     */
    public function send(array $data)
    {
        return $this->post("cgi-bin/message/subscribe/send", $data);
    }
}