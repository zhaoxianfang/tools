<?php

namespace zxf\WeChat\Material;

/**
 * 临时素材
 */
class TempFiles extends MaterialBase
{
    protected $uploadType = 'media';

    /**
     * 上传图片
     *
     * @param string $realPath 图片绝对路径
     *
     * @return string
     */
    public function uploadImage(string $realPath)
    {

        $result = $this->upload($realPath);

        if (isset($result['errcode']) && $result['errcode'] != 0) {
            throw new \Exception($this->getCode($result['errcode']), $result['errcode']);
        } else {
            return $result; // 包含media_id 和 url
        }
    }

    public function uploadVoice(string $realPath)
    {

        $result = $this->upload($realPath, 'voice');

        if (isset($result['errcode']) && $result['errcode'] != 0) {
            throw new \Exception($this->getCode($result['errcode']), $result['errcode']);
        } else {
            return $result;
        }
    }

    public function uploadThumb(string $realPath)
    {

        $result = $this->upload($realPath, 'thumb');

        if (isset($result['errcode']) && $result['errcode'] != 0) {
            throw new \Exception($this->getCode($result['errcode']), $result['errcode']);
        } else {
            return $result;
        }
    }

    /**
     * 上传视频
     *
     * @param string $realPath 视频绝对路径
     *
     * @return string
     */
    public function uploadVideo(string $realPath, string $videoTitle = '', string $videoDescription = '')
    {

        $result = $this->upload($realPath, 'video', $videoTitle, $videoDescription);

        if (isset($result['errcode']) && $result['errcode'] != 0) {
            throw new \Exception($this->getCode($result['errcode']), $result['errcode']);
        } else {
            return $result; // 包含media_id
        }
    }

    /**
     * 获取素材列表
     *
     * @param string $type  素材的类型，图片（image）、视频（video）、语音 （voice）、图文（news）
     * @param int    $page  页码
     * @param int    $limit 每页显示条数
     *
     * @return array|bool|mixed|string
     * @throws \Illuminate\Http\Client\RequestException
     */
    public function getList(string $type = 'image', int $page = 1, int $limit = 10)
    {
        throw new \Exception('临时素材暂不支持此功能');
    }

    public function deleteFile(string $mediaId = '')
    {
        throw new \Exception('临时素材暂不支持此功能');
    }
}
