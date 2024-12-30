<?php

namespace zxf\Laravel\Trace;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;

class Handle
{
    /**
     * @var \Illuminate\Foundation\Application
     */
    protected $app;

    /**
     * @var \Illuminate\Routing\Router
     */
    protected $router;

    protected $startTime;
    protected $startMemory;
    protected $config = [
        'tabs' => [
            'messages' => 'Messages',
            'base'     => 'Base',
            'route'    => 'Route',
            'view'     => 'View',
            'models'   => 'Models',
            'sql'      => 'SQL',
            'session'  => 'Session',
            'request'  => 'Request',
        ],
    ];

    protected array        $sqlList   = [];
    protected static array $modelList = [];
    protected array        $messages  = [];

    protected $request;

    // 实例化并传入参数

    /**
     * @param Application $app
     * @param array       $config
     *
     * @throws BindingResolutionException
     */
    public function __construct(mixed $app = null, array $config = [])
    {
        if (is_enable_trace()) {
            $this->startMemory = memory_get_usage();

            if (!$app) {
                $app = app();   //Fallback when $app is not given
            }
            $this->app       = $app;
            $this->router    = $this->app['router'];
            $this->startTime = $this->app['request']->server('REQUEST_TIME_FLOAT') ?? constant('LARAVEL_START');

            $this->request = $app['request'];
            $this->config  = array_merge($this->config, $config);

            $this->listenModelEvent();
            $this->listenSql();
        }
    }

    public function handle()
    {
        if (is_enable_trace()) {
            $this->startMemory = memory_get_usage();
        }

        return $this;
    }

    /**
     * 监听模型事件
     */
    public function listenModelEvent()
    {
        $events = ['retrieved', 'creating', 'created', 'updating', 'updated', 'saving', 'saved', 'deleting', 'deleted', 'restoring', 'restored', 'replicating'];
        foreach ($events as $event) {
            Event::listen('eloquent.' . $event . ':*', function ($listenString, $model) use ($event) {
                $this->logModelEvent($listenString, $model, $event);
            });
        }
    }

    /**
     * 监听 SQL事件
     *
     * @return void
     */
    protected function listenSql()
    {
        // DB::enableQueryLog();
        $events = isset($this->app['events']) ? $this->app['events'] : null;
        try {
            // 监听SQL执行
            $events->listen(function (\Illuminate\Database\Events\QueryExecuted $query) {
                $this->addQuery($query);
            });
        } catch (\Exception $e) {
        }

        try {
            // 监听事务开始
            $events->listen(\Illuminate\Database\Events\TransactionBeginning::class, function ($transaction) {
                $this->addTransactionQuery('Begin Transaction', $transaction->connection);
            });
            // 监听事务提交
            $events->listen(\Illuminate\Database\Events\TransactionCommitted::class, function ($transaction) {
                $this->addTransactionQuery('Commit Transaction', $transaction->connection);
            });

            // 监听事务回滚
            $events->listen(\Illuminate\Database\Events\TransactionRolledBack::class, function ($transaction) {
                $this->addTransactionQuery('Rollback Transaction', $transaction->connection);
            });

            $connectionEvents = [
                'beganTransaction' => 'Begin Transaction', // 开始事务
                'committed'        => 'Commit Transaction', // 提交事务
                'rollingBack'      => 'Rollback Transaction', // 回滚事务
            ];
            foreach ($connectionEvents as $event => $eventName) {
                $events->listen('connection.*.' . $event, function ($event, $params) use ($eventName) {
                    $this->addTransactionQuery($eventName, $params[0]);
                });
            }
            // 监听连接创建
            $events->listen(function (\Illuminate\Database\Events\ConnectionEstablished $event) {
                $this->addTransactionQuery('Connection Established', $event->connection);
            });
        } catch (\Exception $e) {
        }
    }

    /**
     * 记录sql
     *
     * @param \Illuminate\Database\Events\QueryExecuted $query
     */
    private function addQuery($query)
    {
        $this->sqlList[] = [
            'query'    => (string)$query->sql,
            'type'     => 'query',
            'bindings' => $query->connection->prepareBindings($query->bindings),
            'time'     => $query->time, // 'ms'
            // 'connection' => $query->connection->getName(),
            // 'driver'     => $query->connection->getConfig('driver'),
        ];
    }

    /**
     * 记录事务sql
     *
     * @param string                          $event
     * @param \Illuminate\Database\Connection $connection
     *
     * @return array
     */
    private function addTransactionQuery($event, $connection)
    {
        $this->sqlList[] = [
            'query'    => '[' . $connection->getName() . ':' . $connection->getConfig('driver') . '] ' . $event,
            'type'     => 'transaction',
            'bindings' => [],
            'time'     => 0,
            // 'connection' => $connection->getName(),
            // 'driver'     => $connection->getConfig('driver'),
        ];
    }

    protected function logModelEvent($listenString, $model, $event)
    {
        $model = isset($model[0]) ? $model[0] : $model;
        // 使用: 分割 $model , 获取模型名称
        $modelName = trim(explode(':', $listenString)[1]);

        $modelId = $model->getKey();

        self::$modelList[] = [
            'model' => $modelName,
            'id'    => $modelId,
            'event' => $event,
        ];
    }

    public function output()
    {
        if (!is_enable_trace()) {
            // 运行在命令行下
            return '';
        }

        list($sql, $sqlTimes) = $this->getSqlInfo();
        $messages = $this->messages;
        $base     = $this->getBaseInfo($sqlTimes);
        $route    = $this->getRouteInfo();
        $session  = $this->getSessionInfo();
        $request  = $this->getRequestInfo();
        $view     = $this->getViewInfo();
        $models   = $this->getModelList();

        // 页面Trace信息
        $trace = [];
        foreach ($this->config['tabs'] as $name => $title) {
            $name   = strtolower($name);
            $result = [];
            foreach ($$name as $subTitle => $item) {
                $result[$subTitle] = $item;
            }
            $trace[$title] = !empty($result) ? $result : ['暂无内容'];
        }
        unset($trace['Request']['header']['cookie']);
        // unset($trace['Request']['body']);

        if ($this->request->isMethod('get')) {
            return $this->randerPage($trace);
        }

        return $trace;
    }

    private function getModelList()
    {
        $data = [];
        foreach (self::$modelList as $model) {
            if (empty($data[$model['model'] . ':' . $model['id']])) {
                $data[$model['model'] . ':' . $model['id']] = 1;
            } else {
                $data[$model['model'] . ':' . $model['id']] += 1;
            }
        }
        $list = [];
        foreach ($data as $model => $num) {
            $list[] = $model . ' 「' . $num . '次」';
        }
        return $list;
    }

    private function getBaseInfo($sqlTimes = 0)
    {
        // 获取基本信息
        $runtime = bcsub(microtime(true), $this->startTime, 3);
        $reqs    = $runtime > 0 ? number_format(1 / $runtime, 2) : '∞';
        $base    = [
            '请求信息' => $this->request->method() . ' ' . $this->request->fullUrl(),
            '运行时间' => $runtime . '秒',
            '吞吐率'   => $reqs . 'req/s',
            '内存消耗' => byteFormat(memory_get_usage() - $this->startMemory),
            '查询时间' => $sqlTimes . '秒',
        ];

        if ($this->request->session()) {
            $base['会话信息'] = 'SESSION_ID=' . $this->request->session()->getId();
        }
        $base['PHP version']     = phpversion();
        $base['Laravel version'] = $this->app->version();
        $base['environment']     = $this->app->environment();
        $base['locale']          = $this->app->getLocale();

        // DB 数据库连接信息
        $dbConfig = DB::connection()->getConfig();
        $username = $dbConfig['username'] ?? '-';

        $base['DB Driver']  = ($dbConfig['driver'] ?? '-') . '(' . $this->maskIP($dbConfig['host'] ?? '-') . ') ' . ($dbConfig['charset'] ?? '-');
        $base['DB Connect'] = ($dbConfig['database'] ?? '-') . '(' . substr($username, 0, 2) . '***' . substr($username, -2) . ')';

        // 操作系统名称
        $osName = php_uname('s');
        // 根据需要，你可以将系统名称转换为更友好的格式
        $friendlyOsName = match (strtoupper($osName)) {
            'DARWIN'     => 'macOS',
            'LINUX'      => 'Linux',
            'WINDOWS NT' => 'Windows',
            default      => $osName,
        };
        // 系统信息
        $base['OS'] = $friendlyOsName . ' v' . php_uname('r') . ' ' . php_uname('m');
        if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
            $directoryPath      = '/'; // 根目录
            $totalSpace         = disk_total_space($directoryPath);// 磁盘总空间
            $freeSpace          = disk_free_space($directoryPath);// 磁盘可用空间
            $useSpace           = bcsub($totalSpace, $freeSpace, 0);// 磁盘已用空间
            $usageRate          = bcmul(bcdiv($useSpace, $totalSpace, 5), 100, 2) . '%';// 磁盘使用率
            $base['Disk Space'] = 'total:' . byteFormat($totalSpace) . '; used:' . byteFormat($useSpace) . '; free:' . byteFormat($freeSpace) . '; usage-rate:' . $usageRate;
        }
        return $base;
    }

    private function maskIP($ip)
    {
        // 检查是否是空或特殊的地址
        if (empty($ip) || strlen($ip) < 5 || $ip === 'localhost' || $ip === '127.0.0.1') {
            return $ip;
        }

        // 验证是否为有效的IPv4地址
        if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            return $ip; // 如果不是有效的IPv4，直接返回原值
        }

        // 将 IP 地址分割成数组
        $parts = explode('.', $ip);

        // 检查是否是标准的IPv4地址（4个部分）
        if (count($parts) !== 4) {
            return $ip; // 如果不是4个部分，直接返回原值
        }

        // 只保留第一个和最后一个部分，中间用 ***.*** 替换
        return $parts[0] . '.***.***.' . $parts[3];
    }

    private function getRouteInfo()
    {
        $route = $this->router->current();
        if (!is_a($route, 'Illuminate\Routing\Route')) {
            return [];
        }
        $uri        = head($route->methods()) . ' ' . $route->uri();
        $action     = $route->getAction();
        $result     = [
            'uri' => $uri ?: '-',
        ];
        $result     = array_merge($result, $action);
        $controller = is_string($action['controller'] ?? null) ? $action['controller'] : '';
        $uses       = $action['uses'] ?? null;

        if (str_contains($controller, '@')) {
            list($controller, $method) = explode('@', $controller);
            if (class_exists($controller) && method_exists($controller, $method)) {
                $reflector = new \ReflectionMethod($controller, $method);
            }
            unset($result['uses']);
        } elseif ($uses instanceof \Closure) {
            $reflector      = new \ReflectionFunction($uses);
            $result['uses'] = $uses;
        } elseif (is_string($uses) && str_contains($uses, '@__invoke')) {
            if (class_exists($controller) && method_exists($controller, 'render')) {
                $reflector            = new \ReflectionMethod($controller, 'render');
                $result['controller'] = $controller . '@render';
            }
        }

        // 运行某个控制器方法的那几行
        if (isset($reflector)) {
            $fileName       = $this->getFilePath($reflector->getFileName()); //
            $result['file'] = $fileName . ':' . $reflector->getStartLine() . '-' . $reflector->getEndLine();
        }

        $parametersObj = $route->parameters();
        $parameters    = [];
        foreach ($parametersObj as $param) {
            if (is_object($param)) {
                if (method_exists($param, 'getRouteKey')) {
                    $parameters[] = get_class($param) . ':[' . $param->getRouteKeyName() . ':' . $param->getRouteKey() . ']';
                } else {
                    $parameters[] = collect($param)->toArray();
                }
            } else {
                $parameters[] = $param;
            }
        }
        if ($parameters) {
            $result['params'] = $parameters;
        }

        $result['middleware'] = implode(', ', array_diff($route->middleware(), ['tools_middleware']));
        $result['action']     = $route->getActionMethod();

        return $result;
    }

    private function getSqlInfo()
    {
        // $this->sqlList = DB::getQueryLog(); // 获取查询sql

        $sqlTimes = 0;
        foreach ($this->sqlList as &$item) {
            $sqlTimes = bcadd($sqlTimes, $item['time'], 3);

            if ($item['type'] == 'transaction') {
                // 事务
                $item = $item['query'];
            } else {
                // curd
                $item['right'] = $item['time'] . 'ms'; // 显示时间
                // 提取 $item['query'] 里面 第一个空格前的字符串
                $query         = trim($item['query']);
                $item['label'] = strtoupper(substr($query, 0, strpos($query, ' ')));
                unset($item['time']);
                unset($item['connection']);
                unset($item['driver']);
            }

        }
        // 毫秒转秒
        $sqlTimes = $sqlTimes > 0 ? bcdiv($sqlTimes, 1000, 3) : 0;
        return [$this->sqlList, $sqlTimes];
    }

    private function getSessionInfo()
    {
        $session = app('session');
        if (empty($session)) {
            return $_SESSION ?? [];
        }
        return $session->all();
    }

    private function getRequestInfo()
    {
        $request = request();
        return [
            'path'   => $request->path(),
            'host'   => $request->host(),
            'ip'     => $request->ip(),
            'header' => $request->header(),
            'body'   => $request->all(),
        ];
    }

    public function getViewInfo()
    {
        $viewFiles = [];
        // 获取当前路由的其他视图文件
        foreach (app('view')->getFinder()->getViews() as $alias => $view) {
            $viewFiles[] = $alias . ' (' . trim(str_replace(base_path(), '', $view), '/') . ')';
        }
        return $viewFiles;
    }

    /**
     * 添加调试信息
     *
     * @param mixed  $var1
     * @param string $type
     *
     * @return void
     */
    public function addMessage(mixed $var1, string $type = 'trace'): void
    {
        if (!is_enable_trace()) {
            return;
        }

        $stacktrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5);
        $stackItem  = $stacktrace[0];
        foreach ($stacktrace as $trace) {
            if (!isset($trace['file']) || str_contains($trace['file'], '/vendor/')) {
                continue;
            }

            $stackItem = $trace;
            break;
        }
        if (empty($stackItem)) {
            return;
        }
        $baseFilePath     = $this->getFilePath($stackItem['file']);
        $this->messages[] = [
            'var'       => $var1,// 传入的变量调试值
            'local'     => basename($baseFilePath) . '#' . $stackItem['line'],// 文件名+行号',
            'type'      => 'trace',
            'right'     => strtoupper($type),
            'file_path' => $stackItem['file'],
            'base_path' => $baseFilePath, // 相对于 项目 的路径
            'line'      => $stackItem['line'] ?? 1,
        ];
        return;
    }

    private function getFilePath($file = '')
    {
        return substr($file, strlen(base_path()) + 1);
    }

    public function randerPage($trace)
    {
        $html = <<<EOT
    <div id="tools_trace">
    <div class="trace-logo">
      <img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABQAAAAUCAYAAACNiR0NAAAAAXNSR0IArs4c6QAAAT9JREFUOE+tVFuOwjAMtMtqF06FtOVc/eBcFCmn2oegWU1Su7ZJ6X4QCakJycx4/OCc845euHgT8Ho4Fr7Pr+R4cR7PiGgdcNwNlPORCD8sTnSa+vJZ/psGdzazPQKCefoeFQQf3cdZVSpYIGkCWjDuztTfK5BdF775A07EnOSuVyiXu32v/oAk/9aw+T0t6gPRKb+VK5oUCcUqu3SjeojzNUDzZgEUdTOTGi9AyKjzT0zz1lRA8U6Y4t5mVggk4lA6bUBRIl5GO2AFMi9gZu8BI4DdQxGyHtUH8uDhXLytkCXENfUPWS4ZJdJukAxbz1A+6BBXCajLpYuel00rq60WbJYN1ImqZ4UtiVixxXfKf1rPDQdYVDtEVnva2FARjrbdz1AfYgKZ6bMJqCrs+FINydVgaOntARsebG1fDvgH5+lCJDZyUmAAAAAASUVORK5CYII=" alt="Logo" class="logo">
      <span class="title">Trace</span>
    </div>
    <div class="tabs-container">
      <div class="tabs-header">
        <img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABQAAAAUCAYAAACNiR0NAAAAAXNSR0IArs4c6QAAAT9JREFUOE+tVFuOwjAMtMtqF06FtOVc/eBcFCmn2oegWU1Su7ZJ6X4QCakJycx4/OCc845euHgT8Ho4Fr7Pr+R4cR7PiGgdcNwNlPORCD8sTnSa+vJZ/psGdzazPQKCefoeFQQf3cdZVSpYIGkCWjDuztTfK5BdF775A07EnOSuVyiXu32v/oAk/9aw+T0t6gPRKb+VK5oUCcUqu3SjeojzNUDzZgEUdTOTGi9AyKjzT0zz1lRA8U6Y4t5mVggk4lA6bUBRIl5GO2AFMi9gZu8BI4DdQxGyHtUH8uDhXLytkCXENfUPWS4ZJdJukAxbz1A+6BBXCajLpYuel00rq60WbJYN1ImqZ4UtiVixxXfKf1rPDQdYVDtEVnva2FARjrbdz1AfYgKZ6bMJqCrs+FINydVgaOntARsebG1fDvgH5+lCJDZyUmAAAAAASUVORK5CYII=" alt="Logo" class="tabs-logo-small">
        <div class="tabs-menu">
EOT;

        $tabNames = array_keys($trace);
        // tab name
        foreach ($tabNames as $key => $name) {
            $tabKey = ($key + 1);
            $html   .= "<div class='tabs-item " . ($key < 1 ? 'active' : '') . "' data-tab='tab" . $tabKey . "'>" . $name . "</div>";
        }

        $html .= <<<EOT
        </div>
        <div class="tabs-close">关闭</div>
      </div>
EOT;

        $tabIndex = 0;
        // tab content
        foreach ($trace as $key => $tabs) {
            $tabKey = ($tabIndex + 1);
            $tabIndex++;
            $active = ($tabIndex < 2 ? 'active' : '');
            $html   .= <<<EOT
        <div id="tab{$tabKey}" class="tabs-content {$active}">
<ul>
EOT;
            foreach ($tabs as $k => $item) {
                $html .= '<li>';
                if (is_numeric($k)) {
                    if (!empty($item['type']) && $item['type'] == 'trace') {
                        // trace 数据跟踪信息打印
                        $html .= $this->handleTraceData($item);
                    } else {
                        if (isset($item['label'])) {
                            $html .= "<span class='json-label'>{$item['label']}</span>";
                        }
                        if (is_array($item) && !empty($item)) {
                            $arrayString = json_encode($item, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
                            $html        .= <<<EOT
                    <div class="json-arrow-pre-wrapper">
                      <span class="json-arrow" onclick="toggleJson(this)">▶</span>
                      <pre class="json">{$arrayString}</pre>
                    </div>
EOT;
                        } else {
                            $html .= "<span class='json-label'>" . (is_array($item) ? 'array[]' : $item) . "</span>";
                        }
                    }
                } else {
                    $html .= "<span class='json-label'>{$k}</span>";
                    if (is_array($item) && !empty($item)) {
                        $arrayString = empty($item) ? '[]' : json_encode($item, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
                        $html        .= <<<EOT
                    <div class="json-arrow-pre-wrapper">
                      <span class="json-arrow" onclick="toggleJson(this)">▶</span>
                      <pre class="json">{$arrayString}</pre>
                    </div>
EOT;
                    } else {
                        $html .= "<div class='json-string-content'>" . (is_array($item) ? '[]' : $item) . "</div>";
                    }
                }
                if (!empty($item['right'])) {
                    $html .= "<span class='json-right'>" . $item['right'] . "</span>";
                } else {
                    $html .= "<span class='json-right'></span>";
                }
                $html .= '</li>';
            }

            $html .= <<<EOT
        </ul>
       </div>
EOT;
        }

        $html .= <<<EOT
      </div></div>
EOT;

        return $html;
    }

    protected function handleTraceData($data = []): string
    {

        $str = '<span class="json-label"><a href="phpstorm://open?file=' . urlencode($data['file_path']) . '&amp;line=' . $data['line'] . '" class="phpdebugbar-link">' . $data['local'] . '</a></span>';

        if (is_array($data['var']) && !empty($data['var'])) {
            $arrayString = json_encode($data['var'], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            $str         .= <<<EOT
                    <div class="json-arrow-pre-wrapper">
                      <span class="json-arrow" onclick="toggleJson(this)">▶</span>
                      <pre class="json">{$arrayString}</pre>
                    </div>
EOT;
        } else {
            $str .= "<div class='json-string-content'>" . (is_array($data['var']) ? '[]' : $data['var']) . "</div>";
        }
        return $str;
    }
}
