<?php


namespace zxf\WeChat\Mini;

use zxf\WeChat\WeChatBase;
use Exception;

/**
 * 公众号小程序模板消息支持
 * Class Mini
 *
 * @package WeChat
 */
class Template extends WeChatBase
{

    /**
     * 获取小程序模板库标题列表
     *
     * @return array
     * @throws Exception
     */
    public function getTemplateLibraryList()
    {
        return $this->post("cgi-bin/wxopen/template/library/list", ["offset" => "0", "count" => "20"]);
    }

    /**
     * 获取模板库某个模板标题下关键词库
     *
     * @param string $template_id 模板标题id，可通过接口获取，也可登录小程序后台查看获取
     *
     * @return array
     * @throws Exception
     */
    public function getTemplateLibrary($template_id)
    {
        return $this->post("cgi-bin/wxopen/template/library/get", ["id" => $template_id]);
    }

    /**
     * 组合模板并添加至帐号下的个人模板库
     *
     * @param string $template_id     模板标题id，可通过接口获取，也可登录小程序后台查看获取
     * @param array  $keyword_id_list 开发者自行组合好的模板关键词列表，关键词顺序可以自由搭配（例如[3,5,4]或[4,5,3]），最多支持10个关键词组合
     *
     * @return array
     * @throws Exception
     */
    public function addTemplate($template_id, array $keyword_id_list)
    {
        return $this->post("cgi-bin/wxopen/template/add", ["id" => $template_id, "keyword_id_list" => $keyword_id_list]);
    }

    /**
     * 获取帐号下已存在的模板列表
     *
     * @return array
     * @throws Exception
     */
    public function getTemplateList()
    {
        return $this->post("cgi-bin/wxopen/template/list", ["offset" => "0", "count" => "20"]);
    }

    /**
     * 删除模板消息
     *
     * @param string $template_id 要删除的模板id
     *
     * @return array
     * @throws Exception
     */
    public function delTemplate($template_id)
    {
        return $this->post("cgi-bin/wxopen/template/del", ["template_id" => $template_id]);
    }

    /**
     * 发送模板消息
     *
     * @param array $data 发送的消息对象数组
     *
     * @return array
     * @throws Exception
     */
    public function send(array $data)
    {
        return $this->post("cgi-bin/message/wxopen/template/send", $data);
    }
}