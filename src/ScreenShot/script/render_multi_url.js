// Render Multiple URLs to file

"use strict";
var RenderUrlsToFile, arrayOfUrls, system;

// 将多个网页渲染为图像
// $urls = [
//     [
//         'url'=>"https://www.runoob.com",  // 采集的网址
//         'save_path'=>"./file_runoob.png", // 保存的文件路径
//         'time_out'=>1000, // 可选参数，单位毫秒，用于设置截图等待熏染的时间，默认600毫秒，部分网页加载缓慢，可能会用到
//     ],
//     [
//         'url'=>"https://360.cn",
//         'save_path'=>"./file_360.png",
//     ],
//     [
//         'url'=>"https://liniandpw.com",
//         'save_path'=>"./file_liniandpw.png",
//     ]
// ];
// $command = ".../phantomjs '.../render_multi_url.js' '".json_encode($urls )."'";

system = require("system");

/*
 Render given urls
 @param array of URLs to render
 @param callbackPerUrl Function called after finishing each URL, including the last URL
 @param callbackFinal Function called after finishing everything
 */
RenderUrlsToFile = function(urls, callbackPerUrl, callbackFinal) {
    var getFilename, next, page, retrieve, urlIndex, webpage;
    urlIndex = 0;
    webpage = require("webpage");
    page = null;
    getFilename = function() {
        return "rendermulti-" + urlIndex + ".png";
    };
    next = function(status, url_item, file) {
        page.close();
        callbackPerUrl(status, url_item, file);
        return retrieve();
    };
    retrieve = function() {
        var item;
        if (urls.length > 0) {
            item = urls.shift();
            urlIndex++;
            page = webpage.create();
            page.viewportSize = {
                width: 1390,
                height: 960
            };
            page.settings.userAgent = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/116.0.0.0 Safari/537.36';
            return page.open(item.url, function(status) {
                var file = item.save_path;
                // file = getFilename();
                if (status === "success") {
                    return window.setTimeout((function() {
                        page.render(file);
                        return next(status, item, file);
                    }), item.time_out || 400);
                } else {
                    return next(status, item, file);
                }
            });
        } else {
            return callbackFinal();
        }
    };
    return retrieve();
};

// arrayOfUrls = null;

if (system.args.length > 1) {
    var urlsParams = Array.prototype.slice.call(system.args, 1);// 数组
    // console.log('urlsParams:==',urlsParams,typeof(urlsParams),typeof(urlsParams[0]))
    arrayOfUrls = typeof(urlsParams) === 'object' && urlsParams !== null ? JSON.parse(urlsParams[0]) :JSON.parse(urlsParams);

    RenderUrlsToFile(arrayOfUrls, (function(status, item, file) {
        if (status !== "success") {
            return console.log("[shot_failed]:" +(item._index || 1));
        } else {
            return console.log("[shot_succeed]:" +(item._index || 1));
        }
    }), function() {
        return phantom.exit();
    });
} else {
    //使用参数示例: phantomjs 'render_multi_url.js' '[{"url":"https:\/\/...","save_path":"...png"},{"url":"...","save_path":"..."}]'
    // console.log("使用参数示例: phantomjs 'render_multi_url.js' [domain.name1, domain.name2, ...]");
    // arrayOfUrls = [{"url":"https://www.baidu.com","save_path":"./file_baidu.png"}, {"url":"https://360.cn","save_path":"./file_360.png"}];
    console.log("[shot_error]:传入的参数有误");
    return phantom.exit();
}

