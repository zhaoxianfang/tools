<?php

namespace zxf\WeChat\OfficialAccount;

use Exception;
use zxf\WeChat\Contracts\WeChatBase;

/**
 * 微信素材管理
 */
class Media extends WeChatBase
{
    public bool $useToken = true;

    /**
     * 新增临时素材
     *
     * @link https://developers.weixin.qq.com/doc/offiaccount/Asset_Management/New_temporary_materials.html
     *
     * @param  string  $type  媒体文件类型，分别有图片（image）、语音（voice）、视频（video）和缩略图（thumb）
     * @return array
     *
     * @throws Exception
     */
    public function add(string $filePath, string $type = 'image')
    {
        if (! in_array($type, ['image', 'voice', 'video', 'thumb'])) {
            return $this->error('Invalid Media Type.');
        }

        return $this->httpUpload('cgi-bin/media/upload', $filePath, ['type' => $type]);
    }

    /**
     * 获取临时素材
     *
     * @link https://developers.weixin.qq.com/doc/offiaccount/Asset_Management/Get_temporary_materials.html
     *
     * @return array|string
     *
     * @throws Exception
     */
    public function getMedia(string $media_id)
    {
        return $this->get('cgi-bin/media/get', [], ['media_id' => $media_id]);
    }

    /**
     * 上传图文消息内的图片获取URL
     *
     *
     * @return array
     *
     * @throws Exception
     */
    public function uploadImg(string $filename)
    {
        return $this->httpUpload('cgi-bin/media/uploadimg', $filename);
    }

    /**
     * 新增其他类型永久素材
     *
     * @param  string  $filename  文件名称
     * @param  string  $type  媒体文件类型(image|voice|video|thumb)
     * @return array
     *
     * @throws Exception
     */
    public function addMaterial(string $filename, string $type = 'image', ?string $videoTitle = '', ?string $videoDescription = '')
    {
        if (! in_array($type, ['image', 'voice', 'video', 'thumb'])) {
            return $this->error('Invalid Media Type.');
        }

        return $this->upload(21, $filename, $type, $videoTitle, $videoDescription);
    }

    /**
     * 获取永久素材
     *
     * @link https://developers.weixin.qq.com/doc/offiaccount/Asset_Management/Getting_Permanent_Assets.html
     *
     * @return array|string
     *
     * @throws Exception
     */
    public function getMaterial(string $media_id)
    {
        return $this->post('cgi-bin/material/get_material', ['media_id' => $media_id]);
    }

    /**
     * 删除永久素材
     *
     * @link https://developers.weixin.qq.com/doc/offiaccount/Asset_Management/Deleting_Permanent_Assets.html
     *
     * @return array
     *
     * @throws Exception
     */
    public function delMaterial(string $media_id)
    {
        return $this->post('cgi-bin/material/del_material', ['media_id' => $media_id]);
    }

    /**
     * 获取素材总数
     *
     * @link https://developers.weixin.qq.com/doc/offiaccount/Asset_Management/Get_the_total_of_all_materials.html
     *
     * @return array
     *
     * @throws Exception
     */
    public function getMaterialCount()
    {
        return $this->get('cgi-bin/material/get_materialcount');
    }

    /**
     * 获取素材列表
     *
     * @link https://developers.weixin.qq.com/doc/offiaccount/Asset_Management/Get_materials_list.html
     *
     * @return array
     *
     * @throws Exception
     */
    public function batchGetMaterial(string $type = 'image', int $offset = 0, int $count = 20)
    {
        if (! in_array($type, ['image', 'voice', 'video', 'news'])) {
            return $this->error('Invalid Media Type.');
        }

        return $this->post('cgi-bin/material/batchget_material', ['type' => $type, 'offset' => $offset, 'count' => $count]);
    }
}
