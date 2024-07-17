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
            'session' => 'SESSION',
            'request' => 'REQUEST',
        ],
    ];

    protected array        $sqlList   = [];
    protected static array $modelList = [];

    protected $request;

    // 实例化并传入参数
    public function __construct(Request $request, array $config = [])
    {
        $this->request = $request;
        $this->config  = array_merge($this->config, $config);
        DB::enableQueryLog();
    }

    public function handle()
    {
        if (app()->runningInConsole()) {
            // 运行在命令行下
            return $this;
        }
        $this->startTime   = constant('LARAVEL_START') ?? microtime(true);
        $this->startMemory = memory_get_usage();

        return $this;
    }

    /**
     * 监听模型事件
     */
    public function listenModelEvent()
    {
        // 监听调度事件
        Event::listen('eloquent.retrieved:*', function ($listenString, $model) {
            $this->logModelEvent($listenString, $model, 'retrieved');
        });

        // 监听创建事件
        Event::listen('eloquent.creating:*', function ($listenString, $model) {
            $this->logModelEvent($listenString, $model, 'creating');
        });

        // 监听创建完成事件
        Event::listen('eloquent.created:*', function ($listenString, $model) {
            $this->logModelEvent($listenString, $model, 'created');
        });

        // 监听更新事件
        Event::listen('eloquent.updating:*', function ($listenString, $model) {
            $this->logModelEvent($listenString, $model, 'updating');
        });

        // 监听更新完成事件
        Event::listen('eloquent.updated:*', function ($listenString, $model) {
            $this->logModelEvent($listenString, $model, 'updated');
        });

        // 监听更新事件
        Event::listen('eloquent.saving:*', function ($listenString, $model) {
            $this->logModelEvent($listenString, $model, 'saving');
        });

        // 监听更新完成事件
        Event::listen('eloquent.saved:*', function ($listenString, $model) {
            $this->logModelEvent($listenString, $model, 'saved');
        });

        // 监听删除事件
        Event::listen('eloquent.deleting:*', function ($listenString, $model) {
            $this->logModelEvent($listenString, $model, 'deleting');
        });

        // 监听删除完成事件
        Event::listen('eloquent.deleted:*', function ($listenString, $model) {
            $this->logModelEvent($listenString, $model, 'deleted');
        });

        Event::listen('eloquent.restoring:*', function ($listenString, $model) {
            $this->logModelEvent($listenString, $model, 'restoring');
        });

        Event::listen('eloquent.restored:*', function ($listenString, $model) {
            $this->logModelEvent($listenString, $model, 'restored');
        });

        Event::listen('eloquent.replicating:*', function ($listenString, $model) {
            $this->logModelEvent($listenString, $model, 'replicating');
        });
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
        return '';
    }

    private function getModelList()
    {
        $data = [];
        foreach (self::$modelList as $model) {
            $data[] = '「' . $model['event'] . '」' . $model['model'] . ':' . $model['id'];
        }
        return $data;
    }

    private function getBaseInfo($sqlTimes = 0)
    {
        // 获取基本信息
        $runtime = round(microtime(true) - $this->startTime, 3);
        $reqs    = $runtime > 0 ? number_format(1 / $runtime, 2) : '∞';
        $base    = [
            '请求信息' => $this->request->method() . ' ' . $this->request->fullUrl(),
            '运行时间' => round(microtime(true) - $this->startTime, 3) . '秒',
            '吞吐率'   => $reqs . 'req/s',
            '内存消耗' => '内存消耗：' . number_format((memory_get_usage() - $this->startMemory) / 1024, 2) . 'kb',
            '查询信息' => $sqlTimes . '秒',
        ];

        if ($this->request->session()) {
            $base['会话信息'] = 'SESSION_ID=' . $this->request->session()->getId();
        }
        return $base;
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
            $sqlArr[] = 'time:' . $sqlItem['time'] . 'ms || sql: ' . $sqlItem['query'] . ' || args: ' . json_encode($sqlItem['bindings']);
            $sqlTimes = bcadd($sqlTimes, $sqlItem['time'], 3);
        }
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
            'header' => $request->header(),
            'ip'     => $request->ip(),
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
                    // 判断$k是否为奇数
                    if (is_string($item)) {
                        if (is_numeric($k)) {
                            $str .= '<li class="tools_trace_li" >' . $item . '</li>';
                        } else {
                            $str .= '<li class="tools_trace_li">' . '<div class="tools_trace_li_key">' . $k . '</div><pre class="tools_trace_li_pre">' . $item . '</pre></li>';
                        }
                    } else {
                        $str .= '<li class="tools_trace_li">' . '<div class="tools_trace_li_key">' . $k . '</div><pre class="tools_trace_li_pre">' . json_encode($item, JSON_PRETTY_PRINT) . '</pre></li>';
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
<div id="tools_trace_page_trace_close"><img style="vertical-align:top;" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABQAAAAUCAYAAACNiR0NAAAAAXNSR0IArs4c6QAAAQhJREFUOE+t1L0yxUAYBuDnXI9CpaJAQ2WYQUNFQ0VDpVCg8DPjp0CBhoabcBNchKsweybFZk822XC+mTRJ9tlvd99kYMw1GLMnBo/xhY+ek4Rxn9VVA9fwjvUe6BmWqjHfoZF0yX3QSyzGWBMY7pWgt5hPsRzYhT5gpglrA3PoC6ZyWBeYosuYbMNKwBgNkQoJGJ5mrkqC/VZ1NoEjnP4HjPdsBSc4xMVfOnzEdLLMA5xjHzdNaG7Jd5jL7NkerrGLEKFaNYFXWOg4gB3cYxvPsZiCI99mywFs4QmbCAc3rPRvs1oSjWiSDbxW2xP+ODVwFj9dOWvoOIwLNQK2xav4WUmwi7Hw4i+FuToVxfikpwAAAABJRU5ErkJggg==" /></div>
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
