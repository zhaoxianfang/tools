<?php

namespace zxf\ScreenShot;

use Exception;

/**
 * 网页截图
 * $res = ScreenShot::init('/you-path')->setUrl('http://www.baidu.com')->run(__DIR__.'/img/'.time().'.png');
 */
class ScreenShot
{
    /**
     * 实例
     */
    protected static $_instance;

    private $softPath   = '';
    private $scriptPath = '';
    private $url        = '';   // 截图 url 地址前缀
    private $waitTime   = 1500; // 截图 前等待被截图网页渲染多久后再截图，单位(毫秒)

    public function __construct($softPath = '', $scriptPath = '')
    {
        $this->setSoftPath($softPath);
        $this->setScriptPath(!empty($scriptPath) ? $scriptPath : __DIR__ . '/script/url.js');
        self::$_instance = $this;
    }

    /**
     * $softPath phantomjs 可执行程序所在的路径！！！ 例如 /www/soft
     */
    public static function init($softPath = '', $scriptPath = '')
    {
        return new static($softPath, $scriptPath);
    }

    public function setSoftPath($softPath)
    {
        $fileName       = (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') ? 'phantomjs.exe' : 'phantomjs';
        $this->softPath = $softPath . DIRECTORY_SEPARATOR . $fileName;
        return $this;
    }

    public function setScriptPath($scriptPath)
    {
        $this->scriptPath = $scriptPath;
        return $this;
    }

    /**
     * 使用拼装好的 完整url地址 进行 截图
     *
     * @param string $url 带 http[s] 的网页地址
     *
     * @return $this
     */
    public function setUrl($url = '')
    {
        $this->url = $url;
        return $this;
    }

    /**
     * 截图前 等待页面渲染的时间（有些页面渲染比较慢就需要设置一个相对大一点的等待时间）
     *
     * @param $waitTime
     *
     * @return $this
     */
    public function setWaitTime($waitTime = 1500)
    {
        $this->waitTime = $waitTime;
        return $this;
    }

    /**
     * 执行网页截图
     *
     * @param string $savePath 图片保存地址
     *
     * @return bool
     * @throws Exception
     */
    public function run(string $savePath = '')
    {

        $savePath = dirname($savePath) . DIRECTORY_SEPARATOR . basename($savePath);

        if (!file_exists($this->softPath)) {
            throw new Exception('截图软件路径配置有误:softPath');
        }
        if (!file_exists($this->scriptPath)) {
            throw new Exception('截图脚本文件配置有误:scriptPath');
        }
        if (empty($this->url)) {
            throw new Exception('未设置截图地址:url');
        }

        $command = $this->softPath . " '" . $this->scriptPath . "' '" . $this->url . "' '" . $savePath . "' '" . $this->waitTime . "'";

        // 法 一 使用 exec
        $result = exec($command);

        if ($result != 'success' || !file_exists($savePath)) {
            return false;
        }
        return true;
        // 法 二 使用 passthru
//        $result = passthru($command);
//        if (is_null($result)) {
//            $var = ob_get_contents();
//            if (stripos($var, 'success') === false || !file_exists($savePath)) {
//                return false;
//            } else {
//                return true;
//            }
//        } else {
//            return false;
//        }
    }
}