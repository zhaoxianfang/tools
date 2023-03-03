<?php



namespace zxf\WeChat\Mini;

use zxf\WeChat\WeChatBase;
use Exception;

/**
 * 小程序导购助手
 * Class Guide
 * @package WeMini
 */
class Guide extends WeChatBase
{
    /**
     * 服务号添加导购
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function addGuideAcct($data)
    {
        $url = 'cgi-bin/guide/addguideacct';
        return $this->post($url, $data);
    }

    /**
     * 服务号删除导购
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function delGuideAcct($data)
    {
        $url = 'cgi-bin/guide/delguideacct';
        return $this->post($url, $data);
    }

    /**
     * 服务号获取导购信息
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function getGuideAcct($data)
    {
        $url = 'cgi-bin/guide/getguideacct';
        return $this->post($url, $data);
    }

    /**
     * 获取服务号的敏感词信息与自动回复信息
     * @return array
     * @throws Exception
     */
    public function getGuideAcctConfig()
    {
        $url = 'cgi-bin/guide/getguideacctconfig';
        return $this->callPostApi($url, [], true);
    }

    /**
     * 服务号拉取导购列表
     * @param integer $page
     * @param integer $num
     * @return array
     * @throws Exception
     */
    public function getGuideAcctList($page = 0, $num = 10)
    {
        $url = 'cgi-bin/guide/getguideacctconfig';
        return $this->callPostApi($url, ['page' => $page, 'num' => $num], true);
    }

    /**
     * 获取导购聊天记录
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function getGuideBuyerChatRecord($data)
    {
        $url = 'cgi-bin/guide/getguideacct';
        return $this->post($url, $data);
    }

    /**
     * 获取导购快捷回复信息
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function getGuideConfig($data)
    {
        $url = 'cgi-bin/guide/getguideconfig';
        return $this->post($url, $data);
    }

    /**
     * 生成导购二维码
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function guideCreateQrCode($data)
    {
        $url = 'cgi-bin/guide/guidecreateqrcode';
        return $this->post($url, $data);
    }

    /**
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function pushShowWxaPathMenu($data)
    {
        $url = 'cgi-bin/guide/pushshowwxapathmenu';
        return $this->post($url, $data);
    }

    /**
     * 为服务号设置敏感词与自动回复
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function setGuideAcctConfig($data)
    {
        $url = 'cgi-bin/guide/setguideacctconfig';
        return $this->post($url, $data);
    }

    /**
     * 设置导购快捷回复信息
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function setGuideConfig($data)
    {
        $url = 'cgi-bin/guide/setguideconfig';
        return $this->post($url, $data);
    }

    /**
     * 更新导购昵称或者头像
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function updateGuideAcct($data)
    {
        $url = 'cgi-bin/guide/setguideconfig';
        return $this->post($url, $data);
    }

    /**
     * 添加展示标签信息
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function addGuideBuyerDisplayTag($data)
    {
        $url = 'cgi-bin/guide/addguidebuyerdisplaytag';
        return $this->post($url, $data);
    }

    /**
     * 为粉丝添加可查询标签
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function addGuideBuyerTag($data)
    {
        $url = 'cgi-bin/guide/addguidebuyertag';
        return $this->post($url, $data);
    }

    /**
     * 添加标签可选值
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function addGuideTagOption($data)
    {
        $url = 'cgi-bin/guide/addguidetagoption';
        return $this->post($url, $data);
    }

    /**
     * 删除粉丝标签
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function delGuideBuyerTag($data)
    {
        $url = 'cgi-bin/guide/delguidebuyertag';
        return $this->post($url, $data);
    }

    /**
     * 查询展示标签信息
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function getGuideBuyerDisplayTag($data)
    {
        $url = 'cgi-bin/guide/getguidebuyerdisplaytag';
        return $this->post($url, $data);
    }

    /**
     * 查询粉丝标签
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function getGuideBuyerTag($data)
    {
        $url = 'cgi-bin/guide/getguidebuyertag';
        return $this->post($url, $data);
    }

    /**
     * 查询标签可选值信息
     * @return array
     * @throws Exception
     */
    public function getGuideTagOption()
    {
        $url = 'cgi-bin/guide/getguidetagoption';
        return $this->callPostApi($url, [], true);
    }

    /**
     * 新建可查询标签类型,支持新建4类可查询标签
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function newGuideTagOption($data)
    {
        $url = 'cgi-bin/guide/newguidetagoption';
        return $this->post($url, $data);
    }

    /**
     * 根据标签值筛选粉丝
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function queryGuideBuyerByTag($data)
    {
        $url = 'cgi-bin/guide/queryguidebuyerbytag';
        
        return $this->post($url, $data);
    }

    /**
     * 为服务号导购添加粉丝
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function addGuideBuyerRelation($data)
    {
        $url = 'cgi-bin/guide/addguidebuyerrelation';
        
        return $this->post($url, $data);
    }

    /**
     * 删除导购的粉丝
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function delGuideBuyerRelation($data)
    {
        $url = 'cgi-bin/guide/delguidebuyerrelation';
        
        return $this->post($url, $data);
    }

    /**
     * 查询某一个粉丝与导购的绑定关系
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function getGuideBuyerRelation($data)
    {
        $url = 'cgi-bin/guide/getguidebuyerrelation';
        
        return $this->post($url, $data);
    }

    /**
     * 通过粉丝信息查询该粉丝与导购的绑定关系
     * @param string $openid
     * @return array
     * @throws Exception
     */
    public function getGuideBuyerRelationByBuyer($openid)
    {
        $url = 'cgi-bin/guide/getguidebuyerrelation';
        
        return $this->callPostApi($url, ['openid' => $openid], true);
    }

    /**
     * 拉取导购的粉丝列表
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function getGuideBuyerRelationList($data)
    {
        $url = 'cgi-bin/guide/getguidebuyerrelationlist';
        
        return $this->post($url, $data);
    }

    /**
     * 将粉丝从一个导购迁移到另外一个导购下
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function rebindGuideAcctForBuyer($data)
    {
        $url = 'cgi-bin/guide/rebindguideacctforbuyer';
        
        return $this->post($url, $data);
    }

    /**
     * 更新粉丝昵称
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function updateGuideBuyerRelation($data)
    {
        $url = 'cgi-bin/guide/updateguidebuyerrelation';
        
        return $this->post($url, $data);
    }

    /**
     * 删除小程序卡片素材
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function delGuideCardMaterial($data)
    {
        $url = 'cgi-bin/guide/delguidecardmaterial';
        
        return $this->post($url, $data);
    }

    /**
     * 删除图片素材
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function delGuideImageMaterial($data)
    {
        $url = 'cgi-bin/guide/delguideimagematerial';
        
        return $this->post($url, $data);
    }

    /**
     * 删除文字素材
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function delGuideWordMaterial($data)
    {
        $url = 'cgi-bin/guide/delguidewordmaterial';
        
        return $this->post($url, $data);
    }

    /**
     * 获取小程序卡片素材信息
     * @param integer $type
     * @return array
     * @throws Exception
     */
    public function getGuideCardMaterial($type = 0)
    {
        $url = 'cgi-bin/guide/getguidecardmaterial';
        
        return $this->callPostApi($url, ['type' => $type], true);
    }

    /**
     * 获取图片素材信息
     * @param integer $type 操作类型
     * @param integer $start 分页查询，起始位置
     * @param integer $num 分页查询，查询个数
     * @return array
     * @throws Exception
     */
    public function getGuideImageMaterial($type = 0, $start = 0, $num = 10)
    {
        $url = 'cgi-bin/guide/getguideimagematerial';
        
        return $this->callPostApi($url, ['type' => $type, 'start' => $start, 'num' => $num], true);
    }

    /**
     * 获取文字素材信息
     * @param integer $type 操作类型
     * @param integer $start 分页查询，起始位置
     * @param integer $num 分页查询，查询个数
     * @return array
     * @throws Exception
     */
    public function getGuideWordMaterial($type = 0, $start = 0, $num = 10)
    {
        $url = 'cgi-bin/guide/getguidewordmaterial';
        
        return $this->callPostApi($url, ['type' => $type, 'start' => $start, 'num' => $num], true);
    }

    /**
     * 添加小程序卡片素材
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function setGuideCardMaterial($data)
    {
        $url = 'cgi-bin/guide/setguidecardmaterial';
        
        return $this->post($url, $data);
    }

    /**
     * 添加图片素材
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function setGuideImageMaterial($data)
    {
        $url = 'cgi-bin/guide/setguideimagematerial';
        
        return $this->post($url, $data);
    }

    /**
     * 为服务号添加文字素材
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function setGuideWordMaterial($data)
    {
        $url = 'cgi-bin/guide/setguidewordmaterial';
        
        return $this->post($url, $data);
    }
}