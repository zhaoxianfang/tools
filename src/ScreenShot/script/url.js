"use strict";

// 采集单个网页并保存为图片
// $savePath = './11.png'; // 保存的文件路径
// $url = 'https://abc.com/docs'; // 采集的网址
// $command = ".../phantomjs '.../render_multi_url.js' '".$url."' '".$savePath."' '1500'"; // 要执行的命令  1500为延迟时间

var system = require('system');
var webPage = require('webpage');
var page = webPage.create();
//设置phantomjs的浏览器user-agent
page.settings.userAgent = 'Mozilla/5.0 (iPhone; CPU iPhone OS 11_0 like Mac OS X) AppleWebKit/604.1.38 (KHTML, like Gecko) Version/11.0 Mobile/15A372 Safari/604.1';
// page.settings.resourceTimeout = 15000
//获取php exec 函数的命令行参数
if (system.args.length < 3) {
    console.log('请求参数错误');
    // console.log('第2个参数为url地址 第3个参数为截图文件名称');
    phantom.exit(1);
}
//命令行 截图网址参数
var url = system.args[1];
//图片输出路径
var filePath = system.args[2];
var timeOut = (typeof system.args[3] != 'undefined' && system.args[3] != '' && system.args[3] > 500) ? system.args[3] : 1500;
//设置浏览器视口
page.viewportSize = {width: 1200, height: 1280};
// page.viewportSize = {height: 960};
//打开网址
page.open(url, function start(status) {
    if ('success' !== status) {
        console.log("[shot_failed]:1");
        phantom.exit();
    } else {
        // page.onLoadFinished = function() {
        //1000ms之后开始截图
        setTimeout(function () {
            //截图格式为jpg 80%的图片质量
            page.render(filePath, {format: 'png', quality: '100'});
            console.log("[shot_succeed]:1");
            //退出phantomjs 避免phantomjs导致内存泄露
            phantom.exit();
        }, timeOut);
        // };
    }
});

