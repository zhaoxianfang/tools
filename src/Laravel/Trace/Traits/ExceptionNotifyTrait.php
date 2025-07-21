<?php

namespace zxf\Laravel\Trace\Traits;

use JetBrains\PhpStorm\NoReturn;
use Throwable;

/**
 * 发送异常通知给管理员等 Trait
 */
trait ExceptionNotifyTrait
{
    #[NoReturn]
    public function showExitMessage(Throwable $e): void
    {
        $code = self::$code ?? 500;
        $message = self::$isSysErr ? $e->getMessage() : self::$message;
        $extendedMessage = '';

        if (config('app.debug')) {

            $errFile = str_replace(base_path(), '', $e->getFile()).':'.$e->getLine().' (行)';
            $extendedMessage .= "<p style='font-size: 10px;'>[异常提示]:</p>";
            $extendedMessage .= "<p style='font-size: 10px;'>➤ [异常文件]:{$errFile}</p>";

            // 匹配：Target class [admin] does not exist.
            if (preg_match('/Target class \[([a-z]+)\] does not exist\./', $message, $matches)) {
                $extendedMessage .= "<p style='font-size: 10px;'>[调试提示]:</p>";
                $extendedMessage .= "<p style='font-size: 10px;'>➤ 请检查「{$matches[1]}」相关的类、中间件、路由是否存在；</p>";
                $extendedMessage .= "<p style='font-size: 10px;'>➤ 请检查「{$matches[1]}」相关的命名空间或字符串大小写等是否正确</p>";
                if (in_array($matches[1], (array) config('modules.allow_automatic_load_middleware_groups'))) {
                    $extendedMessage .= "<p style='font-size: 10px;'>➤ 请检查 <code>modules.allow_automatic_load_middleware_groups</code>里的「{$matches[1]}」中间件分组是否定义</p>";
                }
            }
        }

        $sysTitle = config('app.name', '威四方');
        // 定义一个带样式的HTML内容
        $html = <<<HTML
<!DOCTYPE html>
<html lang="zh-cn" >
<head>
    <meta charset="UTF-8">
    <title>出错啦|{$sysTitle}</title>
    <meta name="renderer" content="webkit">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <style type="text/css">
        body{background-color:#2f3242}svg{position:absolute;top:50%;left:50%;margin-top:-250px;margin-left:-400px}.message-box{height:200px;width:380px;position:absolute;top:50%;left:50%;margin-top:-100px;margin-left:50px;color:#FFF;font-family:Roboto;font-weight:300}.message-box h1{font-size:60px;line-height:46px;margin-bottom:40px}.buttons-con .action-link-wrap{margin-top:40px}.buttons-con .action-link-wrap a{background:#007fb2;padding:8px 25px;border-radius:4px;color:#FFF;font-weight:bold;font-size:14px;transition:all .3s linear;cursor:pointer;text-decoration:none;margin-right:10px}.buttons-con .action-link-wrap a:hover{background:#5a5c6c;color:#fff}#Polygon-1,#Polygon-2,#Polygon-3,#Polygon-4,#Polygon-4,#Polygon-5{-webkit-animation:float 1s infinite ease-in-out alternate;animation:float 1s infinite ease-in-out alternate}#Polygon-2{-webkit-animation-delay:.2s;animation-delay:.2s}#Polygon-3{-webkit-animation-delay:.4s;animation-delay:.4s}#Polygon-4{-webkit-animation-delay:.6s;animation-delay:.6s}#Polygon-5{-webkit-animation-delay:.8s;animation-delay:.8s}@-webkit-keyframes float{100%{-webkit-transform:translateY(20px);transform:translateY(20px)}}@keyframes float{100%{-webkit-transform:translateY(20px);transform:translateY(20px)}}@media(max-width:450px){svg{position:absolute;top:50%;left:50%;margin-top:-250px;margin-left:-190px}.message-box{top:50%;left:50%;margin-top:-100px;margin-left:-190px;text-align:center}}
    </style>
</head>
<body>

<svg width="380px" height="500px" viewBox="0 0 837 1045" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:sketch="http://www.bohemiancoding.com/sketch/ns">
    <g id="Page-1" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd" sketch:type="MSPage">
        <path d="M353,9 L626.664028,170 L626.664028,487 L353,642 L79.3359724,487 L79.3359724,170 L353,9 Z" id="Polygon-1" stroke="#007FB2" stroke-width="6" sketch:type="MSShapeGroup"></path>
        <path d="M78.5,529 L147,569.186414 L147,648.311216 L78.5,687 L10,648.311216 L10,569.186414 L78.5,529 Z" id="Polygon-2" stroke="#EF4A5B" stroke-width="6" sketch:type="MSShapeGroup"></path>
        <path d="M773,186 L827,217.538705 L827,279.636651 L773,310 L719,279.636651 L719,217.538705 L773,186 Z" id="Polygon-3" stroke="#795D9C" stroke-width="6" sketch:type="MSShapeGroup"></path>
        <path d="M639,529 L773,607.846761 L773,763.091627 L639,839 L505,763.091627 L505,607.846761 L639,529 Z" id="Polygon-4" stroke="#F2773F" stroke-width="6" sketch:type="MSShapeGroup"></path>
        <path d="M281,801 L383,861.025276 L383,979.21169 L281,1037 L179,979.21169 L179,861.025276 L281,801 Z" id="Polygon-5" stroke="#36B455" stroke-width="6" sketch:type="MSShapeGroup"></path>
    </g>
</svg>
<div class="message-box">
    <h1>{$code}</h1>
    <p>[{$sysTitle}]提示您,出错啦!</p>
    <p style="font-size: 12px;">[错误信息]{$message}</p>
    {$extendedMessage}
    <div class="buttons-con">
        <div class="action-link-wrap">
            <a onClick="history.back(-1)" class="link-button link-back-button">返回上一页</a>
            <a href="/" class="link-button">返回首页</a>
        </div>
    </div>
</div>
</body>
</html>
HTML;

        // 使用 die 输出 HTML
        exit($html);
    }
}
