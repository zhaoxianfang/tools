<?php

namespace zxf\tools;

/**
 * 缓存类
 */
class Cache
{
    private static $instance = null;

    // 当前操作的key
    private $currentKey = '';

    protected $config = [
        'cache_dir' => "./cache", // 缓存地址
        'type'      => 'random', // 缓存方式 key: 直接使用key存储,random:对key加密存储
        'mode'      => '1', //缓存模式 1:serialize ;2:保存为可执行php文件
    ];

    /**
     * 初始化实例
     *
     * @return Cache
     */
    public static function instance(array $config = []): ?Cache
    {
        if (is_null(self::$instance)) {
            self::$instance = new static($config);
        }
        return self::$instance;
    }

    /**
     * 构造函数
     *
     * @param array $config 配置
     *
     * @return void
     */
    public function __construct(array $config = [])
    {
        if (!empty($config)) {
            $this->config = array_merge($this->config, $config);
        }
    }

    /**
     * 得到缓存信息
     *
     * @param string $key
     *
     * @return bool|array|null|string
     */
    public function get(string $key = '')
    {
        if (empty($key)) {
            return null;
        }

        //缓存文件不存在
        if (!$this->has($key)) {
            return null;
        }
        $file = $this->getKeyFilePath($key);
        $res  = $this->checkFileExpiryOrContent($file);
        if ($res === false) {
            return null;
        }
        return $res;
    }

    /**
     * 验证文件是否存在或者是否过期
     *
     * @param string $file
     *
     * @return bool|array
     */
    private function checkFileExpiryOrContent(string $file)
    {
        if (!is_file($file)) {
            return false;
        }
        $data = $this->getCacheFileContents($file);
        if (!empty($data)) {
            try {
                if (gettype($data) != 'array') {
                    $data = unserialize($data);
                }
            } catch (\Exception $e) {
            }
            if (!empty($data['expiry_time']) && (time() < ($data['expiry_time']))) {
                return $data['data'];
            } else {
                // 过期就直接删除
                unlink($file);
            }
        }
        return false;
    }

    /**
     * 设置一个缓存
     *
     * @param string            $key     缓存键
     * @param bool|array|string $value   缓存内容
     * @param int|string        $expiry  缓存生命周期 0表示永久缓存
     *                                   支持格式:
     *                                   int 缓存多少秒，例如 90 表示缓存90秒，如果小于等于0，则用0替换
     *                                   string: 时间字符串格式,例如:+1 day、2023-01-01 09:00:02 等 strtotime 支持的格式均可
     *
     */
    public function set(string $key, bool|array|string $value, int|string $expiry = '+99 year'): bool
    {
        $expiry = is_numeric($expiry) ? time() + (int)$expiry : strtotime($expiry);
        $file   = $this->getKeyFilePath($key);
        return $this->writeCacheFile($file, $value, $expiry);
    }

    /**
     * 删除一条缓存
     *
     * @param string $key
     *
     * @return bool
     */
    public function delete(string $key = ''): bool
    {
        if (empty($key)) {
            return false;
        }
        if (!$this->has($key)) {
            return false;
        }
        $file = $this->getKeyFilePath($key);
        //删除该缓存
        $res = is_file($file) && unlink($file);
        $dir = dirname($file);
        if (dir_is_empty($dir)) {
            deldir($dir, true);
        }
        return $res;
    }

    /**
     * 判断缓存是否存在
     *
     * @param string $key 键
     *
     * @return boolean true 缓存存在 false 缓存不存在
     */
    public function has(string $key = ''): bool
    {
        $file = $this->getKeyFilePath($key);
        $res  = $this->checkFileExpiryOrContent($file);
        return !($res === false);
    }

    /**
     * 通过缓存$key得到缓存信息路径
     *
     * @param string $key 键
     *
     * @return string 缓存文件路径
     */
    protected function getKeyFilePath(string $key = ''): string
    {
        if (empty($key)) {
            return '';
        }
        $path = $this->keyToFileName($key);
        return empty($path) ? '' : $this->config['cache_dir'] . DIRECTORY_SEPARATOR . $path;
    }

    /**
     * 通过key得到缓存信息存储文件名
     *
     * @param string $key 键
     *
     * @return string 缓存文件名
     */
    protected function keyToFileName(string $key = ''): string
    {
        $type = $this->config['type'];

        $codeFileName = str_replace(['/', '='], ['_', ''], base64_encode($key));

        switch ($type) {
            case 'random':
                $fileName = $codeFileName;
                break;
            case 'key':
                $fileName = $key;
                break;
            default:
                $fileName = $codeFileName;
        }
        $childDir = substr(strtoupper(md5($key)), 0, 2);
        return $childDir . DIRECTORY_SEPARATOR . $fileName;
    }

    /**
     * 把数据写入文件
     *
     * @param string     $file           文件名称
     * @param array      $contents       数据内容
     * @param int|string $expiry         缓存生命周期 0表示永久缓存
     *                                   支持格式:
     *                                   int 缓存多少秒，例如 90 表示缓存90秒，如果小于等于0，则用0替换
     *                                   string: 时间字符串格式,例如:+1 day、2023-01-01 09:00:02 等 strtotime 支持的格式均可
     *
     * @return bool
     */
    protected function writeCacheFile(string $file, $contents, int|string $expiry = '+99 year'): bool
    {
        $expiry = (is_numeric($expiry) || empty($expiry)) ? time() + (int)$expiry : strtotime($expiry);
        if ($this->config['mode'] == 1) {
            $contents = serialize([
                'expiry_time' => $expiry,
                'data'        => $contents,
            ]);
        } else {
            $contents = '<?php' . "\n" . ' return array(' . "\n" . '"expiry_time" => ' . $expiry . ",\n" . '"data"=>' . var_export($contents, true) . ");\n";
        }

        create_folders(dirname($file));
        $result = false;
        $f      = @fopen($file, 'w');
        if ($f) {
            @flock($f, LOCK_EX);
            fseek($f, 0);
            ftruncate($f, 0);
            $tmp = @fwrite($f, $contents);
            if (!($tmp === false)) {
                $result = true;
            }
            @fclose($f);
        }
        @chmod($file, 0755);
        return $result;
    }

    /**
     * 从文件得到数据
     *
     * @param string $file
     *
     * @return boolean|array
     */
    protected function getCacheFileContents(string $file)
    {
        if (!is_file($file)) {
            return false;
        }
        // 快速读取文件的第一行内容
        $fileObject = new \SplFileObject($file, 'r');
        $line       = $fileObject->current();
        if (stripos($line, "<?php") === false) {
            $content = '';
            //  快速读取文件的每一行内容
            foreach ($fileObject as $line) {
                $content .= $line; // 读取文件里的一行数据
            }
            $fileObject = null;
            return unserialize($content);
        } else {
            $fileObject = null;
            return include $file;
        }
    }

    /**
     * 设置缓存路径
     *
     * @param string $path
     *
     * @return $this
     * @throws \Exception
     */
    public function setCacheDir(string $path)
    {
        if (!is_dir($path)) {
            throw new \Exception('file_cache: ' . $path . ' 不是一个有效路径 ');
        }
        if (!is_writable($path)) {
            throw new \Exception('file_cache: 路径 "' . $path . '" 不可写');
        }

        $path                      = rtrim($path, '/') . '/';
        $this->config['cache_dir'] = $path;

        return $this;
    }

    /**
     * 设置缓存存储类型
     *
     * @param int $mode
     *
     * @return $this
     */
    public function setMode(int $mode = 1)
    {
        if ($mode == 1) {
            $this->config['mode'] = 1;
        } else {
            $this->config['mode'] = 2;
        }
        return $this;
    }

    /**
     * 删除所有缓存
     *
     * @return bool
     */
    public function flush(): bool
    {
        try {
            deldir($this->config['cache_dir'], false);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
