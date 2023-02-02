<?php

namespace zxf\WeChat\Material;

use zxf\WeChat\WeChatBase;

/**
 * 素材
 */
class MaterialBase extends WeChatBase
{
    public $type = 'official_account';

    // 上传类型 material：永久; media: 临时
    protected $uploadType = 'material';

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
     * @throws \Illuminate\Http\Client\RequestException
     */
    protected function upload(string $filePath, string $type = 'image', string $videoTitle = '', string $videoDescription = '')
    {
        if (!file_exists($filePath) || !is_readable($filePath)) {
            throw new \Exception(sprintf('文件不存在或者不可读: "%s"', $filePath));
        }

        if (class_exists('\CURLFile')) {
            $data = ['media' => new \CURLFile(realpath($filePath))];
        } else {
            $data = ['media' => '@' . realpath($filePath)];//<=5.5
        }

        $headers = [
            'Content-Disposition' => 'form-data; name="media"; filename="' . basename($filePath) . '"',
        ];

        if ($type == 'video') {
            $data['description'] = json_encode(
                [
                    'title'        => $videoTitle,
                    'introduction' => $videoDescription,
                ],
                JSON_UNESCAPED_UNICODE
            );
        }

        $url = $this->generateRequestUrl('cgi-bin/' . $this->uploadType . '/add_material') . '&type=' . $type;
        return $this->curlPost($url, $data, $headers);// 成功时候返回数据 包含media_id 、url，失败时返回数据包含 errcode 和 message
    }

    // 获取素材列表
    protected function list(string $type = 'image', int $offset = 0, int $count = 10)
    {
        $data = [
            "type"   => $type,
            "offset" => $offset,
            "count"  => $count,
        ];
        return $this->post('cgi-bin/' . $this->uploadType . '/batchget_material', $data);// 成功时候返回数据 包含media_id 、url，失败时返回数据包含 errcode 和 errmsg
    }

    // 删除素材
    protected function delete(string $mediaId = '')
    {
        $data = [
            "media_id" => $mediaId,
        ];
        return $this->post('cgi-bin/' . $this->uploadType . '/del_material', $data);
    }

    private function curlPost($url, $data, $header = array())
    {
        if (function_exists('curl_init')) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            if (is_array($header) && !empty($header)) {
                $set_head = array();
                foreach ($header as $k => $v) {
                    $set_head[] = "$k:$v";
                }
                curl_setopt($ch, CURLOPT_HTTPHEADER, $set_head);
            }
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $response = curl_exec($ch);
            if (curl_errno($ch)) {
                throw new \Exception(curl_error($ch));
            }
            // print_r(curl_getinfo($ch));
            curl_close($ch);
            $info = array();
            if ($response) {
                $info = $response;
                try {
                    $info = json_decode($response, true);
                } catch (\Exception $e) {
                }
            }
            return $info;
        } else {
            throw new \Exception('不支持CURL功能.');
        }

    }
}
