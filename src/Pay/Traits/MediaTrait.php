<?php

namespace zxf\Pay\Traits;

use Exception;

/**
 * 图片、视频资源文件上传
 *
 * @link https://codeleading.com/article/81466235141/
 *       http://www.manongjc.com/detail/15-hbgpejwpxcnyvkk.html
 */
trait MediaTrait
{

    /**
     * 其他能力-上传图片
     *
     * @param string $filePath 图片文件路径
     *                         返回 media_id
     *
     * @return mixed
     * @throws Exception
     */
    public function uploadImages(string $filePath = '')
    {
        if (empty($filePath) || empty($filePath = realpath($filePath))) {
            throw new \Exception('上传图片文件不存在');
        }

        $this->url('v3/merchant/media/upload');
        $url = $this->parseUrl($this->url);

        return $this->uploadWechatPayMedia($filePath, $url);
    }

    /**
     * 其他能力-上传视频
     *
     * @param string $filePath 视频文件路径
     *                         返回 media_id
     *
     * @return mixed
     * @throws Exception
     */
    public function uploadVideo(string $filePath = '')
    {
        if (empty($filePath) || empty($filePath = realpath($filePath))) {
            throw new \Exception('上传视频文件不存在');
        }

        $this->url('v3/merchant/media/video_upload');
        $url = $this->parseUrl($this->url);

        return $this->uploadWechatPayMedia($filePath, $url);
    }

    /**
     * 营销工具-上传图片(营销专用)
     *
     * @param string $filePath 图片文件路径
     *                         返回 media_id
     *
     * @return mixed
     * @throws Exception
     */
    public function uploadMarketingImages(string $filePath = '')
    {
        if (empty($filePath) || empty($filePath = realpath($filePath))) {
            throw new \Exception('上传图片文件不存在');
        }

        $this->url('v3/marketing/favor/media/image-upload');
        $url = $this->parseUrl($this->url);

        return $this->uploadWechatPayMedia($filePath, $url);
    }

    private function uploadWechatPayMedia(string $filePath, string $url)
    {
        $fileInfo = pathinfo($filePath);;
        $filename = $fileInfo['basename']; // 文件名

        $fi        = new \finfo(FILEINFO_MIME_TYPE);
        $mime_type = $fi->file($filePath);

        $data             = [];
        $data['filename'] = $filename;

        $meta             = [];
        $meta['filename'] = $filename;
        $meta['sha256']   = hash_file('sha256', $filePath);

        $boundary = uniqid(); //分割符号

        $header = $this->v3CreateHeader($url, 'POST', $meta);

        $header['Content-Type'] = 'multipart/form-data;boundary=' . $boundary;

        $boundaryStr = "--{$boundary}\r\n";
        $body        = $boundaryStr;
        $body        .= 'Content-Disposition: form-data; name="meta"' . "\r\n";
        $body        .= 'Content-Type: application/json' . "\r\n";
        $body        .= "\r\n";
        $body        .= json_encode($meta) . "\r\n";
        $body        .= $boundaryStr;
        $body        .= 'Content-Disposition: form-data; name="file"; filename="' . $data['filename'] . '"' . "\r\n";
        $body        .= 'Content-Type: ' . $mime_type . ';' . "\r\n";
        $body        .= "\r\n";
        $body        .= file_get_contents($filePath) . "\r\n";
        $body        .= "--{$boundary}--\r\n";

        return $this->http->setHeader($header, false)->setParams($body, 'string')->post($url);
    }
}