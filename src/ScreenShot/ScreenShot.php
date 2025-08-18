<?php

namespace zxf\ScreenShot;

use Exception;

/**
 * 网页截图
 * $res = ScreenShot::init('/your/ScreenShot/path')->setUrl('http://www.baidu.com')->run(__DIR__.'/img/'.time().'.png');
 */
class ScreenShot
{
    /**
     * 实例
     */
    protected static $_instance;

    private $softPath = '';

    private $scriptPath = '';

    private $waitTime = 600; // 截图 前等待被截图网页渲染多久后再截图，单位(毫秒)

    private $url;   // 截图 url 地址 string|array

    private $customScriptPath = false; // 是否自定义设置了 scriptPath

    public function __construct(string $softPath = '', ?string $scriptPath = '')
    {
        $this->setSoftPath($softPath);
        ! empty($scriptPath) && $this->setScriptPath($scriptPath);
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
        ! empty($softPath) || $softPath = config('tools_other.phantomjs');
        $fileName = (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') ? 'phantomjs.exe' : 'phantomjs';
        $this->softPath = $softPath.DIRECTORY_SEPARATOR.$fileName;

        return $this;
    }

    public function setScriptPath($scriptPath, $customScriptPath = true)
    {
        $this->scriptPath = $scriptPath;
        $this->customScriptPath = $customScriptPath;

        return $this;
    }

    /**
     * 使用拼装好的 完整url地址 进行 截图
     *
     * @param  string|array  $url  带 http[s] 的网页地址，
     *                             例如 'https://www.baidu.com'
     *                             [
     *                             [
     *                             'url'=>"https://www.runoob.com",  // 采集的网址
     *                             'save_path'=>"./file_runoob.png", // 保存的文件路径
     *                             'time_out'=>1000, // 可选参数，单位毫秒，用于设置截图等待熏染的时间，默认600毫秒，部分网页加载缓慢，可能会用到
     *                             ],
     *                             [
     *                             'url'=>"https://360.cn",
     *                             'save_path'=>"./file_360.png",
     *                             ]
     *                             ];
     * @return $this
     *
     * @throws Exception
     */
    public function setUrl(string|array $url)
    {
        if (is_array($url)) {
            foreach ($url as $key => $item) {
                if (! is_array($item) || ! isset($item['url']) || ! isset($item['save_path'])) {
                    throw new Exception('url参数错误');
                }
                $url[$key]['time_out'] = empty($item['time_out']) ? $this->waitTime : max((int) $item['time_out'], 300); // 用于设置截图等待熏染的时间
                $url[$key]['_index'] = $key + 1; // 用于标记是第几个url,从1开始
            }
        }
        $this->url = $url;

        return $this;
    }

    /**
     * 截图前 等待页面渲染的时间（有些页面渲染比较慢就需要设置一个相对大一点的等待时间）
     *
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
     * @param  string|null  $savePath  图片保存地址
     *
     * @throws Exception
     */
    public function run(?string $savePath = '')
    {
        if (! file_exists($this->softPath)) {
            throw new Exception('截图软件路径配置有误:softPath');
        }
        if (! file_exists($this->scriptPath)) {
            throw new Exception('截图脚本文件配置有误:scriptPath');
        }
        if (empty($this->url)) {
            throw new Exception('未设置截图地址:url');
        }
        if (is_array($this->url) && empty($savePath)) {
            throw new Exception('未设置截图保存地址:savePath');
        }

        if (is_array($this->url)) {
            // 批量截图
            $this->customScriptPath || $this->setScriptPath(! empty($scriptPath) ? $scriptPath : __DIR__.'/script/render_multi_url.js', false);
            $aimUrl = json_encode((array) $this->url);
        } else {
            // 单个截图
            $this->customScriptPath || $this->setScriptPath(! empty($scriptPath) ? $scriptPath : __DIR__.'/script/url.js', false);

            $savePath = dirname($savePath).DIRECTORY_SEPARATOR.basename($savePath);
            $aimUrl = (string) $this->url;
        }
        // --ignore-ssl-errors=true 可以忽略目标网址 ssl 证书有问题的情况
        $command = "{$this->softPath} --ignore-ssl-errors=true '{$this->scriptPath}' '{$aimUrl}'".(! is_array($this->url) ? " '{$savePath}' '{$this->waitTime}'" : '');

        $descriptorspec = [
            0 => ['pipe', 'r'], // 标准输入（键盘输入）
            1 => ['pipe', 'w'], // 标准输出（屏幕输出）
            2 => ['pipe', 'w'], // 标准错误输出（屏幕输出）
        ];
        $pipes = []; // 用于接收管道的文件指针的数组

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

                $result = [
                    'status' => 'SUCCESS', // 截图是否成功,SUCCESS:成功;FAIL:失败;部分失败:PART_FAIL;ERROR:传入参数配置错误
                    'data' => [
                        'success' => [], // 截图成功的传入信息
                        'fail' => [], // 截图失败的传入信息
                    ],
                ];
                if (stripos($output, '[shot_error]') !== false) {
                    // 传入参数配置错误
                    $result['status'] = 'ERROR';
                } else {
                    if (is_array($this->url)) {
                        // 多个截图
                        // 遍历字符串$output的每一行内容是否以[shot_succeed]或者[shot_failed]开头的字符串
                        $lines = explode("\n", $output);
                        $succ = [];
                        $fail = [];
                        foreach ($lines as $line) {
                            if (($okLine = str_starts_with($line, '[shot_succeed]')) || ($failLine = str_starts_with($line, '[shot_failed]'))) {
                                // 用:分割字符串$line
                                $info = explode(':', $line);
                                if (! empty($index = $info[1]) && ! empty($item = $this->url[$index - 1])) {
                                    unset($item['_index']);
                                    if ($okLine) {
                                        $succ[] = $item ?? [];
                                    } else {
                                        $fail[] = $item ?? [];
                                    }
                                }
                            }
                        }
                        $result['status'] = (! empty($fail) && ! empty($succ)) ? 'PART_FAIL' : (empty($succ) ? 'FAIL' : 'SUCCESS');
                        $result['data']['success'] = $succ;
                        $result['data']['fail'] = $fail;
                    } else {
                        // 单个截图
                        $result['status'] = (stripos($output, '[shot_succeed]') === false) ? 'FAIL' : 'SUCCESS';
                        $result['data']['success'] = (array) $this->url;
                    }
                }

                return $result;
            }
            // 切记：在调用 proc_close 之前关闭所有的管道以避免死锁。
            $process && is_resource($process) && proc_close($process);

            return false;
        } catch (Exception $e) {
            $process && is_resource($process) && proc_close($process);
            throw new $e;
        }
    }
}
