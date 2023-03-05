<?php

namespace zxf\WeChat\Server;

use zxf\WeChat\WeChatBase;
use Exception;


/**
 * 微信素材管理
 * Class Media
 *
 * @package WeChat
 */
class Media extends WeChatBase
{
    /**
     * 新增临时素材
     *
     * @param string $filename 文件名称
     * @param string $type     媒体文件类型(image|voice|video|thumb)
     *
     * @return array
     * @throws Exception
     */
    public function add($filename, $type = "image")
    {
        if (!in_array($type, ["image", "voice", "video", "thumb"])) {
            throw new Exception("Invalid Media Type.", "0");
        }
        return $this->upload(10, $filename);
    }

    /**
     * 获取临时素材
     *
     * @param string $media_id
     * @param string $outType 返回处理函数
     *
     * @return array|string
     * @throws Exception
     */
    public function getMedia($media_id, $savePath = null)
    {
        return $this->download("cgi-bin/media/get", $savePath, [
            "media_id" => $media_id,
        ]);
    }

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
     * 新增其他类型永久素材
     *
     * @param mixed  $filename    文件名称
     * @param string $type        媒体文件类型(image|voice|video|thumb)
     * @param array  $description 包含素材的描述信息
     *
     * @return array
     * @throws Exception
     */
    public function addMaterial($filename, $type = "image", $videoTitle = "", $videoDescription = "")
    {
        if (!in_array($type, ["image", "voice", "video", "thumb"])) {
            throw new Exception("Invalid Media Type.", "0");
        }
        // 上传类型：10：小程序临时图片，20：公众号临时素材，21：公众号永久素材
        return $this->upload(21, $filename, $type, $videoTitle, $videoDescription);
    }

    /**
     * 获取永久素材
     *
     * @param string      $media_id
     * @param null|string $outType 输出类型
     *
     * @return array|string
     * @throws Exception
     */
    public function getMaterial($media_id, $savePath = "")
    {
        return $this->download("cgi-bin/material/get_material", $savePath, [
            "media_id" => $media_id,
        ]);
    }

    /**
     * 删除永久素材
     *
     * @param string $media_id
     *
     * @return array
     * @throws Exception
     */
    public function delMaterial($media_id)
    {
        return $this->post("cgi-bin/material/del_material", ["media_id" => $media_id]);
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

    /**
     * 获取素材列表
     *
     * @param string $type
     * @param int    $offset
     * @param int    $count
     *
     * @return array
     * @throws Exception
     */
    public function batchGetMaterial($type = "image", $offset = 0, $count = 20)
    {
        if (!in_array($type, ["image", "voice", "video", "news"])) {
            throw new Exception("Invalid Media Type.", "0");
        }
        return $this->post("cgi-bin/material/batchget_material", ["type" => $type, "offset" => $offset, "count" => $count]);
    }
}