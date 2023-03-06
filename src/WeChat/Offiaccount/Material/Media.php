<?php

namespace zxf\WeChat\Offiaccount\Material;

use zxf\WeChat\WeChatBase;

/**
 * 微信素材管理
 */
class Media extends WeChatBase
{
    // 上传类型 material：永久; media: 临时
    protected $uploadType = "material";

    /**
     * 请求上传
     *
     * @param string $filePath 文件路径
     * @param string $type     image|voice|thumb|video
     *                         图片（image）: 10M，支持bmp/png/jpeg/jpg/gif格式
     *                         语音（voice）：2M，播放长度不超过60s，mp3/wma/wav/amr格式
     *                         视频（video）：10MB，支持MP4格式
     *                         缩略图（thumb）：64KB，支持 JPG 格式
     *
     * @return array|bool|mixed|string
     * @throws \Exception
     */
    protected function uploadFile(string $filePath, string $type = "image", string $videoTitle = "", string $videoDescription = "")
    {
        $mediaType = $this->uploadType == "media" ? 20 : 21;
        return $this->upload($mediaType, $filePath, $type, $videoTitle, $videoDescription);
    }

    // 获取素材列表
    protected function list(string $type = "image", int $offset = 0, int $count = 10)
    {
        $data = [
            "type"   => $type,
            "offset" => $offset,
            "count"  => $count,
        ];
        return $this->post("cgi-bin/" . $this->uploadType . "/batchget_material", $data);// 成功时候返回数据 包含media_id 、url，失败时返回数据包含 errcode 和 errmsg
    }

    // 删除素材
    protected function delete(string $mediaId = "")
    {
        $data = [
            "media_id" => $mediaId,
        ];
        return $this->post("cgi-bin/" . $this->uploadType . "/del_material", $data);
    }

    // 获取临时、永久素材内容
    protected function detail(string $mediaId = "", string $savePath = '')
    {
        $data = [
            "media_id" => $mediaId,
        ];
        $url  = $this->uploadType == "material" ? "cgi-bin/material/get_material" : "cgi-bin/media/get";
        return $this->download($url, $savePath, $data);

    }

}
