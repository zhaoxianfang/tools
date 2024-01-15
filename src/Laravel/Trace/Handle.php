<?php

namespace zxf\Laravel\Trace;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class Handle
{
    protected $startTime;
    protected $startMemory;
    protected $config = [
        'tabs' => [
            'base'    => 'Base',
            'route'   => 'Route',
            'view'    => 'View',
            'sql'     => 'SQL',
            'session' => 'SESSION',
            'request' => 'REQUEST',
        ],
    ];

    protected $sqlList = [];
    protected $request;

    // 实例化并传入参数
    public function __construct(Request $request, array $config = [])
    {
        $this->request = $request;
        $this->config  = array_merge($this->config, $config);
    }

    public function handle()
    {
        if (app()->runningInConsole()) {
            // 运行在命令行下
            return $this;
        }
        $this->startTime   = constant('LARAVEL_START') ?? microtime(true);
        $this->startMemory = memory_get_usage();

        $this->listenSql();

        listen_sql($this->sqlList, false);
        return $this;
    }

    // 监听sql
    private function listenSql()
    {
        listen_sql($this->sqlList, false);
        return $this;
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

    private function getBaseInfo($sqlTimes = 0)
    {
        // 获取基本信息
        $runtime = round(microtime(true) - $this->startTime, 3);
        $reqs    = $runtime > 0 ? number_format(1 / $runtime, 2) : '∞';
        $base    = [
            '请求信息' => '【' . $this->request->method() . '】' . $this->request->fullUrl(),
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
        $route  = Route::current();
        $action = $route->getAction();
        return [
            'uri'        => request()->method() . ' ' . $route->uri(),
            'middleware' => $route->middleware(),
            'controller' => $route->getControllerClass(),
            'action'     => $route->getActionMethod(),
            'where'      => $action['where'] ?? [],
            'as'         => $route->getName(),
            'prefix'     => $route->getPrefix(),
            'parameters' => $route->parameters(),
        ];
    }

    private function getSqlInfo()
    {
        $sqlArr   = [];
        $sqlTimes = 0;
        foreach ($this->sqlList as $sqlItem) {
            $sqlArr[] = '执行时间(' . $sqlItem['time'] . 's) : ' . $sqlItem['sql'];
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
        foreach (app('view')->getFinder()->getViews() as $view) {
            $viewFiles[] = '/' . trim(str_replace(base_path(), '', $view), '/');
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
<style>
.tools_trace_li::after{content:"";display:block;clear:both;}#tools_trace_page_trace{position:fixed;bottom:0;right:0;font-size:14px;width:100%;z-index:999999;color:#000;text-align:left;font-family:'微软雅黑';}#tools_trace_page_trace_tab{display:none;background:white;margin:0;height:250px;}#tools_trace_page_trace_tab_tit{height:30px;padding:6px 12px 0;border-bottom:1px solid #ececec;border-top:1px solid #ececec;font-size:16px;}.tools_trace_tab_title{color:#000;padding-right:12px;height:30px;line-height:30px;display:inline-block;margin-right:3px;cursor:pointer;font-weight:700;}#tools_trace_page_trace_tab_cont{overflow:auto;height:212px;padding:0;line-height:24px;color: #999;}.tools_trace_tab_list{display:none;}.tools_trace_tab_list ol{padding:0;margin:0;}.tools_trace_li{border-bottom:1px solid #EEE;font-size:14px;padding:0 12px;}.tools_trace_li_key{width:25%;float:left;clear:both;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}#tools_trace_page_trace_close{display:none;text-align:right;height:15px;position:absolute;top:10px;right:12px;cursor:pointer;}#tools_trace_page_trace_open{height:30px;float:right;text-align:right;overflow:hidden;position:fixed;bottom:0;right:0;color:#000;line-height:30px;cursor:pointer;}pre.tools_trace_li_pre{outline:0;padding:0;margin:0;text-align:left;margin-left:25%;}
</style>
<script type="text/javascript">
(function(){var tab_tit=document.getElementById('tools_trace_page_trace_tab_tit').getElementsByClassName('tools_trace_tab_title');var tab_cont=document.getElementById('tools_trace_page_trace_tab_cont').getElementsByClassName('tools_trace_tab_list');var open=document.getElementById('tools_trace_page_trace_open');var close=document.getElementById('tools_trace_page_trace_close').children[0];var trace=document.getElementById('tools_trace_page_trace_tab');var cookie=document.cookie.match(/tools_trace_show_page_trace=(\d\|\d)/);var history=(cookie&&typeof cookie[1]!='undefined'&&cookie[1].split('|'))||[0,0];open.onclick=function(){trace.style.display='block';this.style.display='none';close.parentNode.style.display='block';history[0]=1;document.cookie='tools_trace_show_page_trace='+history.join('|')};close.onclick=function(){trace.style.display='none';this.parentNode.style.display='none';open.style.display='block';history[0]=0;document.cookie='tools_trace_show_page_trace='+history.join('|')};for(var i=0;i<tab_tit.length;i++){tab_tit[i].onclick=(function(i){return function(){for(var j=0;j<tab_cont.length;j++){tab_cont[j].style.display='none';tab_tit[j].style.color='#999'}tab_cont[i].style.display='block';tab_tit[i].style.color='#000';history[1]=i;document.cookie='tools_trace_show_page_trace='+history.join('|')}})(i)};parseInt(history[0])&&open.click();tab_tit[history[1]]&&tab_tit[history[1]].click()})();
</script>
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
