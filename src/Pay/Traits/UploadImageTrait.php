<?php

namespace zxf\Pay\Traits;


/**
 * 上传图片
 * https://blog.csdn.net/CS__Love/article/details/123482031
 */
trait UploadImageTrait
{
    /**
     * 图片上传请求
     *
     * @return bool|string
     */
    public function uploadRequestAction()
    {
        if (!in_array('sha256WithRSAEncryption', \openssl_get_md_methods(true))) {
            throw new \Exception("当前PHP环境不支持SHA256withRSA");
        }
        $headerParam    = $this->uploadHeaderParam(); //获取头部信息
        $boundarystr    = "--{$this->boundary}\r\n";// $out是post的内容
        $str            = $boundarystr;
        $str            .= 'Content-Disposition: form-data; name="meta"' . "\r\n";
        $str            .= 'Content-Type: application/json' . "\r\n";
        $str            .= "\r\n";
        $str            .= json_encode($this->data['meta']) . "\r\n";
        $str            .= $boundarystr;
        $str            .= 'Content-Disposition: form-data; name="file"; filename="' . $this->data['meta']['filename'] . '"' . "\r\n";
        $str            .= 'Content-Type: ' . $this->image_type . ";\r\n";
        $str            .= "\r\n";
        $str            .= $this->data['file'] . "\r\n";
        $str            .= $boundarystr . "--\r\n";
        $ThirdClass     = new ThirdRequest();
        $this->response = $ThirdClass->curlPost($this->url, $str, $headerParam);
        return $this->response;
    }


    /**
     * 图片上传头部参数
     *
     * @return array
     */
    public function uploadHeaderParam()
    {
        $this->getUploadSign();        //生成签名
        $this->getToken();        //生成Token
        $header = [
            "Content-Type: multipart/form-data;name='meta'",
            "Content-Type: application/json",
            "User-Agent:" . $_SERVER['HTTP_USER_AGENT'],
            'Authorization: ' . $this->authorization . ' ' . $this->token,
            "Content-Type: multipart/form-data;boundary=" . $this->boundary,
        ];
        return $header;
    }

    /**
     * 图片生成签名
     */
    protected function getUploadSign()
    {
        $url_parts       = parse_url($this->url);  //链接
        $canonical_url   = ($url_parts['path'] . (!empty($url_parts['query']) ? "?${url_parts['query']}" : ""));
        $this->timestamp = time();
        $this->nonce_str = randomStr(32); //随机字符串
        $message         = $this->method . "\n" .
                           $canonical_url . "\n" .
                           $this->timestamp . "\n" .
                           $this->nonce_str . "\n" .
                           json_encode($this->data['meta']) . "\n";
        openssl_sign($message, $raw_sign, openssl_get_privatekey(file_get_contents($this->apiclient_key)), 'sha256WithRSAEncryption');
        $this->sign = base64_encode($raw_sign);
    }
}