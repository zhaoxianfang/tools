<?php

namespace zxf\WeChat\Offiaccount;

use Exception;
use zxf\WeChat\WeChatBase;


/**
 * 微信图文素材管理
 */
class MediaNews extends WeChatBase
{

    /**
     * 新增图文素材
     *
     * @param array $data 文件名称
     *
     * @return array
     * @throws Exception
     */
    public function addNews($data)
    {
        return $this->post("cgi-bin/material/add_news", $data);
    }

    /**
     * 更新图文素材
     *
     * @param string $media_id 要修改的图文消息的id
     * @param int    $index    要更新的文章在图文消息中的位置（多图文消息时，此字段才有意义），第一篇为0
     * @param array  $news     文章内容
     *
     * @return array
     * @throws Exception
     */
    public function updateNews($media_id, $index, $news)
    {
        $data = ["media_id" => $media_id, "index" => $index, "articles" => $news];
        return $this->post("cgi-bin/material/update_news", $data);
    }

    /**
     * 上传图文消息内的图片获取URL
     *
     * @param mixed $filename
     *
     * @return array
     * @throws Exception
     */
    public function uploadImg($filename)
    {
        return $this->customUpload("cgi-bin/media/uploadimg", $filename);
    }


    /**
     * 获取素材总数
     *
     * @return array
     * @throws Exception
     */
    public function getMaterialCount()
    {
        return $this->get("cgi-bin/material/get_materialcount");
    }

}