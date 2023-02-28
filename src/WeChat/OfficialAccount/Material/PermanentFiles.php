<?php

namespace zxf\WeChat\OfficialAccount\Material;

/**
 * 公众号 永久素材
 */
class PermanentFiles extends MaterialBase
{
    protected $uploadType = 'material';

    /**
     * 上传图片
     *
     * @param string $realPath 图片绝对路径
     *
     * @return string
     */
    public function uploadImage(string $realPath)
    {
        $result = $this->uploadFile($realPath, 'image');

        if (isset($result['errcode']) && $result['errcode'] != 0) {
            throw new \Exception($this->getMessage($result['errcode']), $result['errcode']);
        } else {
            return $result; // 包含media_id 和 url
        }
    }

    public function uploadVoice(string $realPath)
    {

        $result = $this->uploadFile($realPath, 'voice');

        if (isset($result['errcode']) && $result['errcode'] != 0) {
            throw new \Exception($this->getMessage($result['errcode']), $result['errcode']);
        } else {
            return $result;
        }
    }

    public function uploadThumb(string $realPath)
    {
        $result = $this->uploadFile($realPath, 'thumb');

        if (isset($result['errcode']) && $result['errcode'] != 0) {
            throw new \Exception($this->getMessage($result['errcode']), $result['errcode']);
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
        $result = $this->uploadFile($realPath, 'video', $videoTitle, $videoDescription);

        if (isset($result['errcode']) && $result['errcode'] != 0) {
            throw new \Exception($this->getMessage($result['errcode']), $result['errcode']);
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
        $offset = max($page - 1, 0) * $limit;

        $result = $this->list($type, $offset, $limit);


        if (isset($result['errcode']) && $result['errcode'] != 0) {
            throw new \Exception($this->getMessage($result['errcode']), $result['errcode']);
        } else {
            return [
                'code'    => 0,
                'message' => '成功',
                'data'    => [
                    'total' => $result['total_count'],
                    'list'  => $result['item'],
                ],
            ];
        }
    }

    public function deleteFile(string $mediaId = '')
    {
        $result = $this->delete($mediaId);

        if (isset($result['errcode']) && $result['errcode'] != 0) {
            throw new \Exception($this->getMessage($result['errcode']), $result['errcode']);
        } else {
            return [
                'code'    => 0,
                'message' => '操作成功',
            ];
        }
    }

    public function getDetail($mediaId)
    {
        $result = $this->detail($mediaId);

        if (isset($result['errcode']) && $result['errcode'] != 0) {
            throw new \Exception($this->getMessage($result['errcode']), $result['errcode']);
        } else {
            return [
                'code'    => 0,
                'message' => '操作成功',
                'data'    => $result,
            ];
        }
    }
}
