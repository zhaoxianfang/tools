<?php


namespace zxf\WeChat\Mini;

use zxf\WeChat\WeChatBase;
use Exception;

/**
 * 小程序导购助手
 * Class Guide
 *
 * @package WeMini
 */
class Guide extends WeChatBase
{
    /**
     * 服务号添加导购
     *
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function addGuideAcct($data)
    {
        return $this->post("cgi-bin/guide/addguideacct", $data);
    }

    /**
     * 服务号删除导购
     *
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function delGuideAcct($data)
    {
        return $this->post("cgi-bin/guide/delguideacct", $data);
    }

    /**
     * 服务号获取导购信息
     *
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function getGuideAcct($data)
    {
        return $this->post("cgi-bin/guide/getguideacct", $data);
    }

    /**
     * 获取服务号的敏感词信息与自动回复信息
     *
     * @return array
     * @throws Exception
     */
    public function getGuideAcctConfig()
    {
        return $this->post("cgi-bin/guide/getguideacctconfig");
    }

    /**
     * 服务号拉取导购列表
     *
     * @param integer $page
     * @param integer $num
     *
     * @return array
     * @throws Exception
     */
    public function getGuideAcctList($page = 0, $num = 10)
    {
        return $this->post("cgi-bin/guide/getguideacctconfig", ["page" => $page, "num" => $num]);
    }

    /**
     * 获取导购聊天记录
     *
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function getGuideBuyerChatRecord($data)
    {
        return $this->post("cgi-bin/guide/getguideacct", $data);
    }

    /**
     * 获取导购快捷回复信息
     *
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function getGuideConfig($data)
    {
        return $this->post("cgi-bin/guide/getguideconfig", $data);
    }

    /**
     * 生成导购二维码
     *
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function guideCreateQrCode($data)
    {
        return $this->post("cgi-bin/guide/guidecreateqrcode", $data);
    }

    /**
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function pushShowWxaPathMenu($data)
    {
        return $this->post("cgi-bin/guide/pushshowwxapathmenu", $data);
    }

    /**
     * 为服务号设置敏感词与自动回复
     *
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function setGuideAcctConfig($data)
    {
        return $this->post("cgi-bin/guide/setguideacctconfig", $data);
    }

    /**
     * 设置导购快捷回复信息
     *
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function setGuideConfig($data)
    {
        return $this->post("cgi-bin/guide/setguideconfig", $data);
    }

    /**
     * 更新导购昵称或者头像
     *
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function updateGuideAcct($data)
    {
        return $this->post("cgi-bin/guide/setguideconfig", $data);
    }

    /**
     * 添加展示标签信息
     *
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function addGuideBuyerDisplayTag($data)
    {
        return $this->post("cgi-bin/guide/addguidebuyerdisplaytag", $data);
    }

    /**
     * 为粉丝添加可查询标签
     *
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function addGuideBuyerTag($data)
    {
        return $this->post("cgi-bin/guide/addguidebuyertag", $data);
    }

    /**
     * 添加标签可选值
     *
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function addGuideTagOption($data)
    {
        return $this->post("cgi-bin/guide/addguidetagoption", $data);
    }

    /**
     * 删除粉丝标签
     *
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function delGuideBuyerTag($data)
    {
        return $this->post("cgi-bin/guide/delguidebuyertag", $data);
    }

    /**
     * 查询展示标签信息
     *
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function getGuideBuyerDisplayTag($data)
    {
        return $this->post("cgi-bin/guide/getguidebuyerdisplaytag", $data);
    }

    /**
     * 查询粉丝标签
     *
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function getGuideBuyerTag($data)
    {
        return $this->post("cgi-bin/guide/getguidebuyertag", $data);
    }

    /**
     * 查询标签可选值信息
     *
     * @return array
     * @throws Exception
     */
    public function getGuideTagOption()
    {
        return $this->post("cgi-bin/guide/getguidetagoption");
    }

    /**
     * 新建可查询标签类型,支持新建4类可查询标签
     *
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function newGuideTagOption($data)
    {
        return $this->post("cgi-bin/guide/newguidetagoption", $data);
    }

    /**
     * 根据标签值筛选粉丝
     *
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function queryGuideBuyerByTag($data)
    {
        return $this->post("cgi-bin/guide/queryguidebuyerbytag", $data);
    }

    /**
     * 为服务号导购添加粉丝
     *
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function addGuideBuyerRelation($data)
    {
        return $this->post("cgi-bin/guide/addguidebuyerrelation", $data);
    }

    /**
     * 删除导购的粉丝
     *
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function delGuideBuyerRelation($data)
    {
        return $this->post("cgi-bin/guide/delguidebuyerrelation", $data);
    }

    /**
     * 查询某一个粉丝与导购的绑定关系
     *
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function getGuideBuyerRelation($data)
    {
        return $this->post("cgi-bin/guide/getguidebuyerrelation", $data);
    }

    /**
     * 通过粉丝信息查询该粉丝与导购的绑定关系
     *
     * @param string $openid
     *
     * @return array
     * @throws Exception
     */
    public function getGuideBuyerRelationByBuyer($openid)
    {
        return $this->post("cgi-bin/guide/getguidebuyerrelation", ["openid" => $openid]);
    }

    /**
     * 拉取导购的粉丝列表
     *
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function getGuideBuyerRelationList($data)
    {
        return $this->post("cgi-bin/guide/getguidebuyerrelationlist", $data);
    }

    /**
     * 将粉丝从一个导购迁移到另外一个导购下
     *
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function rebindGuideAcctForBuyer($data)
    {
        return $this->post("cgi-bin/guide/rebindguideacctforbuyer", $data);
    }

    /**
     * 更新粉丝昵称
     *
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function updateGuideBuyerRelation($data)
    {
        return $this->post("cgi-bin/guide/updateguidebuyerrelation", $data);
    }

    /**
     * 删除小程序卡片素材
     *
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function delGuideCardMaterial($data)
    {
        return $this->post("cgi-bin/guide/delguidecardmaterial", $data);
    }

    /**
     * 删除图片素材
     *
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function delGuideImageMaterial($data)
    {
        return $this->post("cgi-bin/guide/delguideimagematerial", $data);
    }

    /**
     * 删除文字素材
     *
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function delGuideWordMaterial($data)
    {
        return $this->post("cgi-bin/guide/delguidewordmaterial", $data);
    }

    /**
     * 获取小程序卡片素材信息
     *
     * @param integer $type
     *
     * @return array
     * @throws Exception
     */
    public function getGuideCardMaterial($type = 0)
    {
        return $this->post("cgi-bin/guide/getguidecardmaterial", ["type" => $type]);
    }

    /**
     * 获取图片素材信息
     *
     * @param integer $type  操作类型
     * @param integer $start 分页查询，起始位置
     * @param integer $num   分页查询，查询个数
     *
     * @return array
     * @throws Exception
     */
    public function getGuideImageMaterial($type = 0, $start = 0, $num = 10)
    {
        return $this->post("cgi-bin/guide/getguideimagematerial", ["type" => $type, "start" => $start, "num" => $num]);
    }

    /**
     * 获取文字素材信息
     *
     * @param integer $type  操作类型
     * @param integer $start 分页查询，起始位置
     * @param integer $num   分页查询，查询个数
     *
     * @return array
     * @throws Exception
     */
    public function getGuideWordMaterial($type = 0, $start = 0, $num = 10)
    {
        return $this->post("cgi-bin/guide/getguidewordmaterial", ["type" => $type, "start" => $start, "num" => $num]);
    }

    /**
     * 添加小程序卡片素材
     *
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function setGuideCardMaterial($data)
    {
        return $this->post("cgi-bin/guide/setguidecardmaterial", $data);
    }

    /**
     * 添加图片素材
     *
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function setGuideImageMaterial($data)
    {
        return $this->post("cgi-bin/guide/setguideimagematerial", $data);
    }

    /**
     * 为服务号添加文字素材
     *
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function setGuideWordMaterial($data)
    {
        return $this->post("cgi-bin/guide/setguidewordmaterial", $data);
    }
}