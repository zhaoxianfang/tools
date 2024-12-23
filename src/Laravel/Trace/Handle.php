<?php

namespace zxf\Laravel\Trace;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Closure;
use Illuminate\Support\Facades\Event;

class Handle
{
    protected $startTime;
    protected $startMemory;
    protected $config = [
        'tabs' => [
            'base'    => 'Base',
            'route'   => 'Route',
            'view'    => 'View',
            'models'  => 'Models',
            'sql'     => 'SQL',
            'session' => 'Session',
            'request' => 'Request',
        ],
    ];

    protected array        $sqlList   = [];
    protected static array $modelList = [];

    protected $request;

    // 实例化并传入参数
    public function __construct(Request $request, array $config = [])
    {
        $this->startTime = constant('LARAVEL_START') ?? microtime(true);
        if (is_enable_trace()) {
            $this->request = $request;
            $this->config  = array_merge($this->config, $config);
            DB::enableQueryLog();
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
        if (app()->runningInConsole()) {
            // 运行在命令行下
            return '';
        }

        list($sql, $sqlTimes) = $this->getSqlInfo();
        $base    = $this->getBaseInfo($sqlTimes);
        $route   = $this->getRouteInfo();
        $session = $this->getSessionInfo();
        $request = $this->getRequestInfo();
        $view    = $this->getViewInfo();
        $models  = $this->getModelList();

        // 页面Trace信息
        $trace = [];
        foreach ($this->config['tabs'] as $name => $title) {
            $name   = strtolower($name);
            $result = [];
            foreach ($$name as $subTitle => $item) {
                $result[$subTitle] = $item;
            }
            $trace[$title] = !empty($result) ? $result : ['暂无相关数据'];
        }

        if ($this->request->isMethod('get')) {
            // return $this->randerPage($trace) . $this->randerConsole($trace);
            return $this->randerPage($trace);
        }
        // return $this->randerConsole($trace);
        unset($trace['REQUEST']['header']['cookie']);
        unset($trace['REQUEST']['body']);
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
        $base['PHP']     = phpversion();
        $base['Laravel'] = app()->version();

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
        $route         = Route::current();
        $action        = $route->getAction();
        $parametersObj = $route->parameters();
        $parameters    = [];
        foreach ($parametersObj as $key => $param) {
            if (is_object($param)) {
                if (method_exists($param, 'getRouteKey')) {
                    // $param->getRouteKeyName()
                    $parameters[] = get_class($param) . ':' . $param->getRouteKey();
                } else {
                    $parameters[] = collect($param)->toArray();
                }
            } else {
                $parameters[] = $param;
            }
        }
        $middleware = $route->middleware();
        if (!empty($middleware) && in_array('tools_middleware', $middleware)) {
            $middlewareIndex = array_search('tools_middleware', $middleware);
            unset($middleware[$middlewareIndex]);
            $middleware = array_values($middleware);
        }
        return [
            'uri'        => request()->method() . ' ' . $route->uri(),
            'parameters' => $parameters,
            'middleware' => $middleware,
            'controller' => $route->getControllerClass(),
            'action'     => $route->getActionMethod(),
            'where'      => $action['where'] ?? [],
            'as'         => $route->getName(),
            'prefix'     => $route->getPrefix(),
        ];
    }

    private function getSqlInfo()
    {
        $this->sqlList = DB::getQueryLog();
        $sqlArr        = [];
        $sqlTimes      = 0;
        foreach ($this->sqlList as $sqlItem) {
            $sqlArr[] = [
                'time'     => $sqlItem['time'] . 'ms',
                'query'    => $sqlItem['query'],
                'bindings' => json_encode($sqlItem['bindings']),
            ];
            $sqlTimes = bcadd($sqlTimes, $sqlItem['time'], 3);
        }
        // 毫秒转秒
        $sqlTimes = $sqlTimes > 0 ? bcdiv($sqlTimes, 1000, 3) : 0;
        return [$sqlArr, $sqlTimes];
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

    public function randerPage($trace)
    {
        $str = <<<EOT
    <div id="tools_trace_page_trace" >
    <div id="tools_trace_page_trace_tab">
        <div id="tools_trace_page_trace_tab_tit">
EOT;
        foreach ($trace as $key => $title) {
            $str .= '<span class="tools_trace_tab_title">' . $key . '</span>';
        }
        $str .= <<<EOT
        </div>
        <div id="tools_trace_page_trace_tab_cont">
EOT;
        foreach ($trace as $tabs) {
            $str .= <<<EOT
            <div class="tools_trace_tab_list">
                <ol>
EOT;
            if (is_array($tabs)) {
                foreach ($tabs as $k => $item) {
                    if (is_string($item)) {
                        if (is_numeric($k)) {
                            $str .= '<li class="tools_trace_li" >' . $item . '</li>';
                        } else {
                            $str .= '<li class="tools_trace_li">' . '<div class="tools_trace_li_key">' . $k . '</div><pre class="tools_trace_li_pre">' . $item . '</pre></li>';
                        }
                    } else {
                        if (is_numeric($k)) {
                            $str .= '<li class="tools_trace_li">' . '<pre class="tools_trace_li_only_pre">' . json_encode($item, JSON_PRETTY_PRINT) . '</pre></li>';
                        } else {
                            $str .= '<li class="tools_trace_li">' . '<div class="tools_trace_li_key">' . $k . '</div><pre class="tools_trace_li_pre">' . json_encode($item, JSON_PRETTY_PRINT) . '</pre></li>';
                        }
                    }
                }
            }
            $str .= <<<EOT
                </ol>
            </div>
EOT;
        }
        $str .= <<<EOT
        </div>
<div id="tools_trace_page_trace_close">×</div>
</div>
<div id="tools_trace_page_trace_open">
    <div style="background:#232323;color:#FFF;padding:0 6px;float:right;line-height:30px;font-size:14px">调试跟踪</div>
    <img width="30" style="" title="ShowPageTrace" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACgAAAAoCAYAAACM/rhtAAAAAXNSR0IArs4c6QAABB5JREFUWEft2M1v2mYcB/CvCRiMzTstL6vSCalvU1stPXbSclsO+xO2f6L0lKjn9tRq/8TOk9pDRXbapO00aeo2JVsnpQtKeEkMwUDAGBNPjx1TcGxsY6e7jKPx8/PHv5fHBgo+fQ5/+OqLM4V6TMIFKOX5R+vfbvsRmvIahMCAQFlRlI3ZWBRFVYCzF16hSwPNYCcnI9WYSoWnVq9Q10ArWPPoFKdDWYWxTBC5q6wvUMdADYayolDTUpKMzcKM7WIOVSoAHJfeFrgMzE+oJdAPmB/QC8DLgHmBToEfArYMlNr5/smjBP1uw03ze907nUKFWr5C/f5666kkpzc5ugE23JiuHYoymkcDtNqi3565eJl0BLmrUTCR4PT46VES/eMk6Fj3mQoUx/lN7REl4UNBF8HOxho2km7PA/VbuEyoE5jumAO2O30c1HiUrufAsYx6jp9QJ7D+cIS9Jo9rmSTSMXY+gwT48y+7Kmy1eMUAHYML18HS7nvUDax63Fav//B2aTFQT7EX6DIw/bqOgVbQFWoM1iKjZI3ZVJ4eJzE5b369lHrGjNuEa6Ab6OzFyHbhBrZ0Bo13aCy9MaMaLIXJeEVdapcx3zLoBIpqZ2mYbxk0g97/5GPt8J42ib/9cwirHjOuv7QM6oFDoRVsrD+YA1Z+3cFYnthZTL9fekisrvY/kLwszD5J3NblP89gYSjiniBgO58ztbsF3m0e4CCeRoeJeu/BO90ePud5NdBuPIYfs9kLQd0Ab/ENFHsdQAH+upJHLZa8EM/VkKwOB8gPRKwJghroZaGAVpjGzV4ffyTi6jE74LXOCRrxBLiRiLVGVV2zn8hAYBi0GM4bkKy+K3TxWauF74pFpMYSHrbaoM/O8I6NYjuXWwi81zxEdtCDHAhgN1vAOBjEg9o+/k7lcJBMOS+xusdWG9irNiGK0txCHfgmkcDbGIf7Qhe3ej1SJbwqFMDHWdN9kO31sFavAhTQ4BKoJtLI9wSsdtumwAgdQimXRSmXAShKex/cqTx5NJaZLWkSmzaXGXSd51EcDBGXZfzJcbjd7+OnTAYtml4I5EYj3Dhpos4lUOgLGAZDaEc5vM28H7gpLP++v2l2yIc4+en0Z+fu661NSWHLkmwNDSsKbna7askJlABJHy7qQdJ/BCiGQurkEigpN/lYwWhm9OLO1988I+dc+OHuBErCf1mr41Wx4GhIPq3v403hutoSTmF6j1n+9eEE6vZZ7CRjxqmx/fNIg8bKksxa9qjdNrMMzDaDxjtZBJ1MJqZTvBIIaFM51/wiTzPitMfsHqu2GTSHxsuSHJ3LaGk1r516/j641+ANMImnmYFjmOsMOoHOAvXzaXY5mGegHkAr/UxGzzPoFeYbcBYqTrjHOOwiworP9X3Mrsfsvv8XdoYOW/spfXkAAAAASUVORK5CYII=">
</div>
EOT;
        return $str;
    }

    protected function randerConsole($trace)
    {
        //输出到控制台
        $lines = '';
        foreach ($trace as $type => $msg) {
            $type       = strtolower($type);
            $trace_tabs = array_values($this->config['tabs']);
            $line       = [];
            $line[]     = ($type == $trace_tabs[0] || '调试' == $type || '错误' == $type)
                ? "console.group('{$type}');"
                : "console.groupCollapsed('{$type}');";

            foreach ((array)$msg as $key => $m) {
                switch ($type) {
                    case '调试':
                        $var_type = gettype($m);
                        if (in_array($var_type, ['array', 'string'])) {
                            $line[] = "console.log(" . json_encode($m) . ");";
                        } else {
                            $line[] = "console.log(" . json_encode(var_export($m, true)) . ");";
                        }
                        break;
                    case 'sql':
                        $msg    = str_replace("\n", '\n', addslashes($m));
                        $style  = "color:#009bb4;";
                        $line[] = "console.log(\"%c{$msg}\", \"{$style}\");";
                        break;
                    default:
                        $m      = is_string($key) ? $key . ' ' . $m : $key + 1 . ' ' . $m;
                        $msg    = json_encode($m);
                        $line[] = "console.log({$msg});";
                        break;
                }
            }
            $line[] = "console.groupEnd();";
            $lines  .= implode(PHP_EOL, $line);
        }
        $js = <<<JS
<script type='text/javascript'>
{$lines}
</script>
JS;
        return $js;
    }
}
