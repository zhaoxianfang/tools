<?php

namespace zxf\Tools;

use Random\RandomException;

/**
 * Session 操作类
 * 专注于文件存储，支持过期时间控制与复杂数据处理。
 */
class Session
{
    private string $sessionName = 'TOOLS_SESSION_ID';  // 会话名称

    private int $lifetime = 7200;                       // 默认会话有效时间，单位秒

    private string $savePath = '';                       // 会话存储路径

    private string $sessionId = '';                      // 会话ID

    private array $expirationTimes = [];                 // 会话数据过期时间

    private bool $isEncrypted = false;                   // 是否启用加密

    private string $encryptionKey = '';                  // 加密密钥

    private array $sessionMetadata = [];                 // 存储会话的元数据（例如用户代理）

    private static $instance;

    /**
     * 构造函数，初始化会话
     *
     * @param  array  $options  session配置选项，例如 ['save_path' => '/your/save/path']
     * @param  bool  $isEncrypted  是否启用加密
     * @param  string  $encryptionKey  加密密钥
     *
     * @throws RandomException
     */
    public function __construct(
        array $options = [],
        bool $isEncrypted = false,
        string $encryptionKey = ''
    ) {

        // 检查是否已经输出过头部
        if (headers_sent($file, $line)) {
            throw new \RuntimeException("无法启动 Session，headers 已输出：{$file} 第 {$line} 行");
        }

        if (! empty($options['cookie_lifetime'])) {
            $this->lifetime = $options['cookie_lifetime'];
        }
        if (! empty($options['save_path'])) {
            $this->savePath = $options['save_path'];
            // 检查路径是否有效并可写
            if (! is_dir($this->savePath) || ! is_writable($this->savePath)) {
                throw new \RuntimeException("存储路径不可用或没有写入权限: {$this->savePath}");
            }
            unset($options['save_path']);
            session_save_path($this->savePath); // 设置保存路径
        }

        if (! empty($options['name'])) {
            $this->sessionName = $options['name'];
            unset($options['name']);
            session_name($this->sessionName); // 设置会话名称
        }

        if ($isEncrypted) {
            $this->isEncrypted = $isEncrypted;
        }
        if ($encryptionKey) {
            $this->encryptionKey = $encryptionKey;
        }
        if (! empty($options)) {
            foreach ($options as $key => $value) {
                ini_set('session.'.$key, $value);
            }
        }

        // 如果没有启动 session，则启动它
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start(); // 如果没有启动 session，就启动 session
        }

        // 获取或生成会话ID
        if (! $this->sessionId()) {
            if (isset($_COOKIE[$this->sessionName]) && preg_match('/^[a-zA-Z0-9\-]+$/', $_COOKIE[$this->sessionName])) {
                session_id($_COOKIE[$this->sessionName]);
            } else {
                session_id(bin2hex(random_bytes(32)));  // 如果没有则生成一个新的 session ID
            }
        }

        // 设置 session_id
        $this->sessionId = $this->sessionId();

        // 设置 cookie 以支持跨页面保持 session
        setcookie($this->sessionName, $this->sessionId, time() + $this->lifetime, '/');

        // 检查是否需要过期清理
        $this->cleanExpiredSessions();
    }

    /**
     * 获取 Session 实例（单例模式）
     *
     * @param  array  $options  session配置选项，例如 ['save_path' => '/your/save/path']
     * @param  bool  $isEncrypted  是否启用加密
     * @param  string  $encryptionKey  加密密钥
     * @return static
     *
     * @throws RandomException
     */
    public static function instance(array $options = [], bool $isEncrypted = false, string $encryptionKey = ''): self
    {
        if (! isset(self::$instance)) {
            self::$instance = new static($options,$isEncrypted,$encryptionKey);
        }

        return self::$instance;
    }

    /**
     * 检查和清理过期的 session 数据
     */
    private function cleanExpiredSessions(): void
    {
        foreach ($this->expirationTimes as $key => $expirationTime) {
            // 如果 session 数据已过期
            if (time() > $expirationTime) {
                unset($_SESSION[$key]); // 清理过期数据
                unset($this->expirationTimes[$key]); // 清理过期时间记录
            }
        }
        $this->save();
    }

    /**
     * 设置会话 ID
     *
     * @param  string  $id  会话 ID
     */
    public function setSessionId(string $id): self
    {
        $this->sessionId = $id;
        session_id($id); // 设置 session ID

        return $this;
    }

    /**
     * 设置 session 数据，支持不同的 key 设置不同的过期时间
     *
     * @param  string  $key  键名
     * @param  mixed  $value  值
     * @param  int|null  $expiration  过期时间（秒）
     *
     * @throws RandomException
     */
    public function set(string $key, mixed $value, ?int $expiration = 0): self
    {
        if ($this->isEncrypted) {
            $value = $this->encrypt($value); // 加密数据
        } else {
            $value = $this->compress($value); // 压缩数据
        }

        $_SESSION[$key] = $value; // 存储到 session
        if ($expiration > 0) {
            $this->expirationTimes[$key] = time() + $expiration; // 设置过期时间
        }

        $this->save(); // 保存 session 数据

        return $this;
    }

    /**
     * 获取 session 数据
     *
     * @param  string  $key  键名
     * @param  mixed  $default  默认值（如果不存在）
     * @return mixed 返回存储的值
     */
    public function get(string $key, mixed $default = null): mixed
    {
        // 检查是否需要过期清理
        $this->cleanExpiredSessions();
        if (! $this->has($key)) {
            return $default;
        }
        $value = $_SESSION[$key] ?? $default;

        return $value ? ($this->isEncrypted ? $this->decrypt($value) : $this->decompress($value)) : $default;
    }

    /**
     * 获取所有未过期的 Session 数据
     */
    public function all(): array
    {
        // 检查是否需要过期清理
        $this->cleanExpiredSessions();
        $data = [];
        foreach ($_SESSION as $key => $value) {
            $data[$key] = $this->isEncrypted ? $this->decrypt($value) : $this->decompress($value);
        }

        return $data;
    }

    /**
     * 删除指定的 session 数据
     *
     * @param  string  $key  键名
     */
    public function delete(string $key): self
    {
        unset($_SESSION[$key]);
        unset($this->expirationTimes[$key]);
        $this->save();

        return $this;
    }

    /**
     * 判断 session 是否存在或者已经过期
     *
     * @param  string  $key  键名
     * @return bool 返回是否存在且未过期
     */
    public function has(string $key): bool
    {
        return isset($_SESSION[$key]) && (! isset($this->expirationTimes[$key]) || time() <= $this->expirationTimes[$key]);
    }

    /**
     * 清空所有 Session 数据
     */
    public function clear(): self
    {
        $_SESSION = [];
        $this->expirationTimes = [];
        $this->save();

        return $this;
    }

    /**
     * 保存 session 数据
     */
    private function save(): void
    {
        // 提交 session 数据:
        // 使用下面的两个方式都会导致关闭session文件锁，那么之后的数据就不能再次写入session文件了
        // 所以，这个操作是不必要的，也是不应该的
        // session_write_close();
        // session_commit();
    }

    /**
     * 数据加密
     *
     * @param  mixed  $data  需要加密的数据
     * @return string 加密后的数据
     *
     * @throws RandomException
     */
    private function encrypt(mixed $data): string
    {
        if (! $this->encryptionKey) {
            throw new \RuntimeException('加密密钥为空，请设置加密密钥');
        }

        $iv = random_bytes(16); // 初始化向量
        $encryptedData = openssl_encrypt(serialize($data), 'AES-256-CBC', $this->encryptionKey, 0, $iv);

        return base64_encode($iv.$encryptedData); // 存储 IV 和加密数据
    }

    /**
     * 数据解密
     *
     * @param  string  $data  加密数据
     * @return mixed 解密后的数据
     */
    private function decrypt(string $data): mixed
    {
        if (! $this->encryptionKey) {
            throw new \RuntimeException('加密密钥为空，请设置加密密钥');
        }

        $data = base64_decode($data);
        $iv = substr($data, 0, 16);
        $encryptedData = substr($data, 16);

        $decryptedData = openssl_decrypt($encryptedData, 'AES-256-CBC', $this->encryptionKey, 0, $iv);

        return unserialize($decryptedData);
    }

    /**
     * 数据压缩
     *
     * @param  mixed  $data  需要压缩的数据
     * @return string 压缩后的数据
     */
    private function compress(mixed $data): string
    {
        return gzcompress(serialize($data));
    }

    /**
     * 数据解压
     *
     * @param  string  $data  压缩数据
     * @return mixed 解压后的数据
     */
    private function decompress(string $data): mixed
    {
        return unserialize(gzuncompress($data));
    }

    /**
     * 重新生成 session ID
     *
     * @params bool $deleteOldSession 是否删除旧的会话文件
     *               默认为false，表示保留旧的会话文件
     *               为true表示删除旧的会话文件
     */
    public function regenerateSessionId(bool $deleteOldSession = false): self
    {
        session_regenerate_id($deleteOldSession); // 重新生成 session ID
        $this->sessionId = $this->sessionId(); // 获取新的 session ID

        return $this;
    }

    /**
     * 获取session id
     *
     * @return false|string
     */
    public function sessionId(): bool|string
    {
        return session_id();
    }

    /**
     * 销毁会话
     */
    public function destroy(): void
    {
        // 仅在 session 已初始化时才销毁
        if ($this->sessionId()) {
            $this->clear();
            $_SESSION = [];
            session_unset(); // 清除所有会话数据
            session_destroy();
            setcookie($this->sessionName, '', time() - 3600, '/'); // 删除 cookie
        }
    }

    /**
     * 魔术方法设置值
     */
    public function __set(string $name, mixed $value): void
    {
        $this->set($name, $value);
    }

    /**
     * 魔术方法获取值
     *
     *
     * @return mixed|null
     */
    public function __get(string $name)
    {
        return $this->get($name);
    }

    /**
     * 魔术方法判断键是否存在
     */
    public function __isset(string $name): bool
    {
        return $this->has($name);
    }

    /**
     * 魔术方法删除键
     */
    public function __unset(string $name): void
    {
        $this->delete($name);
    }
}
