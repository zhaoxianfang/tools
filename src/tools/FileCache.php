<?php

namespace zxf\tools;

use Exception;

/**
 * 文件缓存类
 * @demo
 * $cache = FileCache::instance(__DIR__);
 * 设置缓存
 * $cache->set("userid",123456,3600);
 *
 * 查询缓存是否存在
 * if($cache->has("userid")){
 *     echo "存在名为userid的缓存";
 * }else{
 *     echo "不存在名为userid的缓存";
 * }
 *
 * 获取缓存
 * $cache->get("userid");
 *
 * 删除某个缓存
 * $cache->delete("userid");
 *
 * 清空全部缓存
 * $cache->clear();
 */
class FileCache
{

    private static $instance = null;
    /**
     * 缓存目录
     * @var
     */
    private $cache_dir = '';

    /**
     * 初始化
     * @param $options
     * @return static|null
     * @throws Exception
     */
    public static function instance($options = [])
    {
        if (is_null(self::$instance)) {
            self::$instance = new static($options);
        }
        return self::$instance;
    }

    /**
     * @param string $cache_dir
     * @throws Exception
     */
    public function __construct($cache_dir = '')
    {
        if (!is_dir($cache_dir)) {
            $cache_dir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'my_cache' . DIRECTORY_SEPARATOR;
        }
        if (!is_dir($cache_dir)) {
            $make_dir_result = mkdir($cache_dir, 0755, true);
            if ($make_dir_result === false) throw new Exception('创建缓存目录失败！');
        }
        $this->cache_dir = $cache_dir;
    }


    /**
     * 根据key获取值，会判断是否过期
     * @param $key
     * @return mixed
     */
    public function get($key)
    {
        $cache_data = $this->getItem($key);
        if ($cache_data === false || !is_array($cache_data)) return false;

        return $cache_data['data'];
    }

    /**
     * 添加或覆盖一个key
     * @param $key
     * @param $value
     * @param $expire
     * @return mixed
     */
    public function set($key, $value, $expire = 0)
    {
        return $this->setItem($key, $value, time(), $expire);
    }

    /**
     * 设置包含元数据的信息
     * @param $key
     * @param $value
     * @param $time
     * @param $expire
     * @return bool
     */
    private function setItem($key, $value, $time, $expire)
    {
        $cache_file = $this->createCacheFile($key);
        if ($cache_file === false) return false;

        $cache_data = array('data' => serialize($value), 'time' => $time, 'expire' => $expire);
        $cache_data = json_encode($cache_data);

        $put_result = file_put_contents($cache_file, $cache_data);
        if ($put_result === false) return false;

        return true;
    }

    /**
     * 创建缓存文件
     * @param $key
     * @return bool|string
     */
    private function createCacheFile($key)
    {
        $cache_file = $this->path($key);
        if (!file_exists($cache_file)) {
            $directory = dirname($cache_file);
            if (!is_dir($directory)) {
                $make_dir_result = mkdir($directory, 0755, true);
                if ($make_dir_result === false) return false;
            }
            $create_result = touch($cache_file);
            if ($create_result === false) return false;
        }

        return $cache_file;
    }

    /**
     * 判断Key是否存在
     * @param $key
     * @return mixed
     */
    public function has($key)
    {
        $value = $this->get($key);
        if ($value === false) return false;

        return true;
    }

    /**
     * 加法递增
     * @param $key
     * @param int $value
     * @return mixed
     */
    public function increment($key, $value = 1)
    {
        $item = $this->getItem($key);
        if ($item === false) {
            $set_result = $this->set($key, $value);
            if ($set_result === false) return false;
            return $value;
        }

        $check_expire = $this->checkExpire($item);
        if ($check_expire === false) return false;

        $item['data'] += $value;

        $result = $this->setItem($key, $item['data'], $item['time'], $item['expire']);
        if ($result === false) return false;

        return $item['data'];
    }

    /**
     * 减法递增
     * @param $key
     * @param int $value
     * @return mixed
     */
    public function decrement($key, $value = 1)
    {
        $item = $this->getItem($key);
        if ($item === false) {
            $value      = 0 - $value;
            $set_result = $this->set($key, $value);
            if ($set_result === false) return false;
            return $value;
        }

        $check_expire = $this->checkExpire($item);
        if ($check_expire === false) return false;

        $item['data'] -= $value;

        $result = $this->setItem($key, $item['data'], $item['time'], $item['expire']);
        if ($result === false) return false;

        return $item['data'];
    }

    /**
     * 删除一个key，同事会删除缓存文件
     * @param $key
     * @return mixed
     */
    public function delete($key)
    {
        $cache_file = $this->path($key);
        if (file_exists($cache_file)) {
            $unlink_result = unlink($cache_file);
            if ($unlink_result === false) return false;
        }

        return true;
    }

    /**
     * 清楚所有缓存
     * @return mixed
     */
    public function clear()
    {
        return $this->delTree($this->cache_dir);
    }

    /**
     * 递归删除目录
     * @param $dir
     * @return bool
     */
    function delTree($dir)
    {
        $files = array_diff(scandir($dir), array('.', '..'));
        foreach ($files as $file) {
            (is_dir("$dir/$file")) ? $this->delTree("$dir/$file") : unlink("$dir/$file");
        }
        return rmdir($dir);
    }

    /**
     * 根据key获取缓存文件路径
     *
     * @param string $key
     * @return string
     */
    protected function path($key)
    {
        $parts = array_slice(str_split($hash = md5($key), 2), 0, 2);
        return $this->cache_dir . '/' . implode('/', $parts) . '/' . $hash;
    }

    /**
     * 获取含有元数据的信息
     * @param $key
     * @return bool|mixed|string
     */
    protected function getItem($key)
    {
        $cache_file = $this->path($key);
        if (!file_exists($cache_file) || !is_readable($cache_file)) {
            return false;
        }

        $cache_data = file_get_contents($cache_file);
        if (empty($cache_data)) return false;
        $cache_data = json_decode($cache_data, true);
        if ($cache_data) {
            $check_expire = $this->checkExpire($cache_data);
            if ($check_expire === false) {
                $this->delete($key);
                return false;
            }
            $check_expire['data'] = unserialize($check_expire['data']);
        }
        return $cache_data;
    }

    /**
     * 检查key是否过期
     * @param $cache_data
     * @return bool
     */
    protected function checkExpire($cache_data)
    {
        $time      = time();
        $is_expire = intval($cache_data['expire']) !== 0 && (intval($cache_data['time']) + intval($cache_data['expire']) < $time);
        if ($is_expire) return false;

        return true;
    }
}
