<?php

namespace zxf\WeChat\OfficialAccount;

use Exception;
use zxf\WeChat\WeChatBase;

/**
 * 模板消息
 */
class Template extends WeChatBase
{
    public $useToken = true;

    /**
     * 设置所属行业
     *
     * @link https://developers.weixin.qq.com/doc/offiaccount/Message_Management/Template_Message_Interface.html#0
     *
     * @param string $industry_id1 公众号模板消息所属行业编号
     * @param string $industry_id2 公众号模板消息所属行业编号
     *
     * @return array
     * @throws Exception
     */
    public function setIndustry(string $industry_id1, string $industry_id2)
    {
        return $this->post('cgi-bin/template/api_set_industry', ['industry_id1' => $industry_id1, 'industry_id2' => $industry_id2]);
    }

    /**
     * 获取设置的行业信息
     *
     * @link https://developers.weixin.qq.com/doc/offiaccount/Message_Management/Template_Message_Interface.html#1
     *
     * @return array
     * @throws Exception
     */
    public function getIndustry()
    {
        return $this->get('cgi-bin/template/get_industry');
    }

    /**
     * 获得模板ID
     *
     * @link https://developers.weixin.qq.com/doc/offiaccount/Message_Management/Template_Message_Interface.html#2
     *
     * @param string $templateIdShort 板库中模板的编号，有“TM**”和“OPENTMTM**”等形式
     * @param array  $keywordNameList 选用的类目模板的关键词
     *
     * @return array
     * @throws Exception
     */
    public function addTemplate(string $templateIdShort, array $keywordNameList = [])
    {
        return $this->post('cgi-bin/template/api_add_template', ['template_id_short' => $templateIdShort, 'keyword_name_list' => $keywordNameList]);
    }

    /**
     * 获取模板列表
     *
     * @link https://developers.weixin.qq.com/doc/offiaccount/Message_Management/Template_Message_Interface.html#3
     *
     * @return array
     * @throws Exception
     */
    public function getAllPrivateTemplate()
    {
        return $this->get('cgi-bin/template/get_all_private_template');
    }

    /**
     * 删除模板ID
     *
     * @link https://developers.weixin.qq.com/doc/offiaccount/Message_Management/Template_Message_Interface.html#4
     *
     * @param string $tpl_id 公众帐号下模板消息ID
     *
     * @return array
     * @throws Exception
     */
    public function delPrivateTemplate(string $tpl_id)
    {
        return $this->post('cgi-bin/template/del_private_template', ['template_id' => $tpl_id]);
    }

    /**
     * 发送模板消息
     *
     * @link https://developers.weixin.qq.com/doc/offiaccount/Message_Management/Template_Message_Interface.html#5
     *
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function send(array $data)
    {
        return $this->post('cgi-bin/message/template/send', $data);
    }
}