<?php

namespace zxf\Laravel\Trace\Traits;

use zxf\Laravel\Trace\Handle;

/**
 * 调试输出html异常信息 Trait
 */
trait ExceptionShowDebugHtmlTrait
{
    public function outputDebugHtml(array $list = [], string $title = '', int $statusCode = 500)
    {
        $title = ! empty($title) ? $title : '系统错误/调试';

        $newList = [];
        if (! $this->isValidMultiDimensionalArray($list)) {
            foreach ($list as $key => $value) {
                $type = is_array($value) ? 'code' : 'string';
                $newList[] = [
                    'type' => $type,
                    'label' => $key,
                    'value' => $type == 'code' ? json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : $value,
                ];
            }
        } else {
            $newList = $list;
        }

        $content = '';
        /**
         * @var array $row ['type'=> 'code|其他','label' => '错误信息', 'value' => '错误信息']
         */
        foreach ($newList as $row) {
            if ($row['type'] == 'code') {
                $content .= '<li class="info-item">
                    <span class="info-label">'.$row['label'].'：</span>
                    <div class="info-value"><pre><code>'.$row['value'].'</code></pre></div>
                </li>';
            } elseif ($row['type'] == 'debug_file') {
                $editor = config('modules.editor') ?? 'phpstorm';
                $content .= '<li class="info-item">
                    <span class="info-label">'.$row['label'].'：</span>
                    <div class="info-value">'.'<a href="'.$editor.'://open?file='.urlencode($row['file']).'&amp;line='.$row['line'].'" class="phpdebugbar-link">'.($row['value']).'</a>'.'</div>
                </li>';
            } else {
                $content .= '<li class="info-item">
                    <span class="info-label">'.$row['label'].'：</span>
                    <div class="info-value">'.$row['value'].'</div>
                </li>';
            }
        }
        $sysName = config('app.name', '威四方');
        $copyright = '&copy; '.date('Y').' '.$sysName.' ('.config('app.url', 'https://weisifang.com').') 版权所有.';
        $html = <<<HTML
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>出错啦|{$sysName}</title>
    <style>
        /* 基础样式重置 */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background: linear-gradient(135deg, #0f0c29, #302b63, #24243e);
            padding: 20px;
            min-height: 100vh;
        }

        .container {
            width: 88%;
            min-height: calc(100vh - 50px);
            margin: 0 auto;
            /*background: #fff;*/
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 30px;

        }

        h1 {
            text-align: center;
            margin-bottom: 30px;
            /*color: #2c3e50;*/
            color: red;
        }

        .info-list {
            list-style: none;
        }

        .info-item {
            display: flex;
            padding: 15px 0;
            border-bottom: 1px dashed #7f8c8d;
            align-items: flex-start;
        }

        .info-item:last-child {
            border-bottom: none;
        }

        .info-label {
            font-weight: bold;
            color: #3498db;
            min-width: 120px;
            padding-right: 20px;
        }

        .info-value {
            flex: 1;
            word-break: break-word;
            color: #fff;
            overflow: auto;
        }

        .info-value pre {
            color: #000;
            border-radius: 4px;
            padding: 12px;
            overflow-x: auto;
            font-family: 'Courier New', Courier, monospace;
            border-left: 3px solid #3498db;
            margin: 5px 0;
            tab-size: 4;
            background-color: #f8f8f8;
        }

        /* 响应式设计 */
        @media (max-width: 600px) {
            .info-item {
                flex-direction: column;
            }

            .info-label {
                margin-bottom: 5px;
            }
        }
        /* 页脚样式 */
        footer {
            text-align: center;
            padding: 12px;
            color: #7f8c8d;
            font-size: 14px;
            margin-top: 20px;
            position: static;
            width: 100%;
            bottom: 10px;
        }
        footer span{
            font-size: 10px;
            margin-left: 30px;
        }
        footer a{
            color: #3498db;
        }
        .phpdebugbar-link{
            color: #03dac6;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>{$title}</h1>

        <ul class="info-list">
            {$content}
        </ul>
    </div>
    <footer>
        {$copyright}  <span> 本页面由 <a href="https://weisifang.com/" target="_blank">weisifang.com</a> 提供支持</span>
    </footer>
</body>
</html>
HTML;

        $resp = response($html, $statusCode)->header('Content-Type', 'text/html');

        /** @var Handle $trace */
        $trace = app('trace');

        return $trace->renderTraceStyleAndScript(request(), $resp)->send();
    }

    private function isValidMultiDimensionalArray(array $array): bool
    {
        foreach ($array as $item) {
            if (! is_array($item) || ! isset($item['type'], $item['label'], $item['value'])) {
                return false;
            }
        }

        return ! empty($array);
    }
}
