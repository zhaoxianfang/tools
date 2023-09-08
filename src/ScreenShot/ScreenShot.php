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

    private $softPath         = '';
    private $scriptPath       = '';
    private $waitTime         = 1500; // 截图 前等待被截图网页渲染多久后再截图，单位(毫秒)
    private $url;   // 截图 url 地址 string|array
    private $customScriptPath = false; // 是否自定义设置了 scriptPath

    public function __construct(string $softPath = '', ?string $scriptPath = '')
    {
        $this->setSoftPath($softPath);
        !empty($scriptPath) && $this->setScriptPath($scriptPath);
        self::$_instance = $this;
    }

    /**
     * $softPath phantomjs 可执行程序所在的路径！！！ 例如 /www/soft
     */
    public static function init($softPath = '', $scriptPath = '')
    {
        return new static($softPath, $scriptPath);
    }

    public function setSoftPath(?string $softPath = '')
    {
        empty($softPath) || $softPath = config('tools_other.phantomjs');
        $fileName       = (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') ? 'phantomjs.exe' : 'phantomjs';
        $this->softPath = $softPath . DIRECTORY_SEPARATOR . $fileName;
        return $this;
    }

    public function setScriptPath($scriptPath, $customScriptPath = true)
    {
        $this->scriptPath       = $scriptPath;
        $this->customScriptPath = $customScriptPath;
        return $this;
    }

    /**
     * 使用拼装好的 完整url地址 进行 截图
     *
     * @param string|array $url             带 http[s] 的网页地址，
     *                                      例如 'https://www.baidu.com'
     *                                      [
     *                                      [
     *                                      'url'=>"https://www.runoob.com",  // 采集的网址
     *                                      'save_path'=>"./file_runoob.png", // 保存的文件路径
     *                                      ],
     *                                      [ 'url'=>"https://360.cn", 'save_path'=>"./file_360.png", ]
     *                                      ];
     *
     * @return $this
     */
    public function setUrl(string|array $url)
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
     * @param string|null $savePath 图片保存地址
     *
     * @return bool
     * @throws Exception
     */
    public function run(?string $savePath = '')
    {
        if (!file_exists($this->softPath)) {
            throw new Exception('截图软件路径配置有误:softPath');
        }
        if (!file_exists($this->scriptPath)) {
            throw new Exception('截图脚本文件配置有误:scriptPath');
        }
        if (empty($this->url)) {
            throw new Exception('未设置截图地址:url');
        }

        if (is_array($this->url)) {
            // 批量截图
            $this->customScriptPath || $this->setScriptPath(!empty($scriptPath) ? $scriptPath : __DIR__ . '/script/render_multi_url.js', false);

            $command = $this->softPath . " '" . $this->scriptPath . "' ' \'" . json_encode((array)$this->url) . "\'' '" . $this->waitTime . "'";
        } else {
            // 单个截图
            $this->customScriptPath || $this->setScriptPath(!empty($scriptPath) ? $scriptPath : __DIR__ . '/script/url.js', false);

            $savePath = dirname($savePath) . DIRECTORY_SEPARATOR . basename($savePath);
            $command  = $this->softPath . " '" . $this->scriptPath . "' '" . (string)$this->url . "' '" . $savePath . "' '" . $this->waitTime . "'";
        }

        $descriptorspec = [
            0 => ['pipe', 'r'], // 标准输入（键盘输入）
            1 => ['pipe', 'w'], // 标准输出（屏幕输出）
            2 => ['pipe', 'w'] // 标准错误输出（屏幕输出）
        ];
        $pipes          = []; // 用于接收管道的文件指针的数组

        try {
            $process = proc_open($command, $descriptorspec, $pipes);
            if (is_resource($process)) {
                // 读取标准输出
                $output = stream_get_contents($pipes[1]);

                // 关闭进程资源
                fclose($pipes[0]);
                fclose($pipes[1]);
                fclose($pipes[2]);

                // 切记：在调用 proc_close 之前关闭所有的管道以避免死锁。
                proc_close($process);

                if (stripos($output, 'phantomjs_handle_success') === false) {
                    return false;
                } else {
                    return true;
                }
            }
            // 切记：在调用 proc_close 之前关闭所有的管道以避免死锁。
            $process && proc_close($process);
            return false;
        } catch (Exception $e) {
            $process && proc_close($process);
            throw new $e;
        }
    }
}