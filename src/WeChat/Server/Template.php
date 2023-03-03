<?php

namespace zxf\WeChat\Server;

use zxf\WeChat\WeChatBase;
use Exception;

/**
 * 模板消息
 * Class Template
 *
 * @package WeChat
 */
class Template extends WeChatBase
{
    /**
     * 设置所属行业
     *
     * @param string $industry_id1 公众号模板消息所属行业编号
     * @param string $industry_id2 公众号模板消息所属行业编号
     *
     * @return array
     * @throws Exception
     */
    public function setIndustry($industry_id1, $industry_id2)
    {
        return $this->post('cgi-bin/template/api_set_industry', ['industry_id1' => $industry_id1, 'industry_id2' => $industry_id2]);
    }

    /**
     * 获取设置的行业信息
     *
     * @return array
     * @throws Exception
     */
    public function getIndustry()
    {
        return $this->get("cgi-bin/template/get_industry");
    }

    /**
     * 获得模板ID
     *
     * @param string $tpl_id 板库中模板的编号，有“TM**”和“OPENTMTM**”等形式
     *
     * @return array
     * @throws Exception
     */
    public function addTemplate($tpl_id)
    {
        return $this->post("cgi-bin/template/api_add_template", ['template_id_short' => $tpl_id]);
    }

    /**
     * 获取模板列表
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
     * @param string $tpl_id 公众帐号下模板消息ID
     *
     * @return array
     * @throws Exception
     */
    public function delPrivateTemplate($tpl_id)
    {
        return $this->post('cgi-bin/template/del_private_template', ['template_id' => $tpl_id]);
    }

    /**
     * 发送模板消息
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