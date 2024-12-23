/*
 * tncode 1.2
 * https://gitee.com/zhaoxianfang/tncode
 */

// 判断元素是否有某个类名的函数，如果浏览器不支持原生的classList属性则使用正则表达式判断
function hasClass(elem, cls) {
    cls = cls || '';
    if (cls.replace(/\s/g, '').length === 0){
        return false;
    }
    return new RegExp(' ' + cls +'').test(''+ elem.className +'');
}

// 为元素添加类名的函数，如果元素没有该类名则添加
function addClass(elements, cName) {
    if (!hasClass(elements, cName)) {
        elements.className += " " + cName;
    }
}

// 从元素移除类名的函数，如果元素有该类名则移除
function removeClass(elements, cName) {
    if (hasClass(elements, cName)) {
        elements.className = elements.className.replace(new RegExp("(\\s|^)" + cName + "(\\s|$)"), " ");
    }
}

// 创建文档片段并将HTML字符串解析后的节点添加到目标元素的函数
function appendHTML(o, html) {
    let divTemp = document.createElement("div");
    let nodes = null;
    let fragment = document.createDocumentFragment();
    divTemp.innerHTML = html;
    nodes = divTemp.childNodes;
    for (let i = 0, length = nodes.length; i < length; i++) {
        fragment.appendChild(nodes[i].cloneNode(true));
    }
    o.appendChild(fragment);
    nodes = null;
    fragment = null;
}

// 模拟document.getElementsByClassName方法，如果浏览器不支持原生的该方法则通过遍历所有元素判断类名来实现
if (!document.getElementsByClassName) {
    document.getElementsByClassName = function(className, index) {
        let nodes = document.getElementsByTagName("*");
        let arr = [];
        for (let i = 0; i < nodes.length; i++) {
            if (hasClass(nodes[i], className)) {
                arr.push(nodes[i]);
            }
        }
        if (index === undefined) index = 0;
        return index === -1? arr : arr[index];
    };
}


// 定义一个空的函数对象，用于后续扩展AJAX相关功能
var _ajax = function() {};

// 为_ajax函数的原型添加方法，实现AJAX请求等功能
_ajax.prototype = {
    // 发起AJAX请求的方法
    request: function(method, url, callback, postVars) {
        // 创建XMLHttpRequest对象，兼容不同浏览器
        let xhr = this.createXhrObject()();
        // 当AJAX请求状态改变时的回调函数
        xhr.onreadystatechange = function() {
            if (xhr.readyState!== 4) {return;}
            // 根据请求状态码判断请求是否成功，调用相应的回调函数
            (xhr.status === 200)?
                callback.success(xhr.responseText, xhr.responseXML) :
                callback.failure(xhr, xhr.status);
        };
        // 如果不是POST请求且有POST参数，则将参数拼接到URL上
        if (method!== "POST" && postVars) {
            url += "?" + this.JSONStringify(postVars);
            postVars = null;
        }
        // 初始化AJAX请求，设置请求方法、URL等信息
        xhr.open(method, url, true);
        // 发送请求，传递POST参数（如果是POST请求）
        xhr.send(postVars);
    },
    // 创建XMLHttpRequest对象的方法，尝试不同的创建方式以兼容不同浏览器
    createXhrObject: function() {
        let methods = [
            function() { return new XMLHttpRequest(); },
            function() { return new ActiveXObject("Msxml2.XMLHTTP"); },
            function() { return new ActiveXObject("Microsoft.XMLHTTP"); }
        ];
        for (let i = 0, len = methods.length; i < len; i++) {
            try {
                // 尝试使用当前创建方式，如果不报错则使用该方式并返回对应的创建函数
                return methods[i];
            } catch (e) {
                continue;
            }
        }
        // 如果所有创建方式都失败，抛出错误
        throw new Error("ajax created failure");
    },
    // 将对象转换为URL查询字符串格式的方法，进行一些字符替换操作
    JSONStringify: function(obj) {
        return JSON.stringify(obj).replace(/"|{|}/g, "")
            .replace(/b:b/g, "=")
            .replace(/b,b/g, "&");
    }
};

// 定义验证码相关的对象，包含各种属性和方法来处理验证码的展示、交互及验证等功能
var tncode = {
    _obj: null,
    _tncode: null,
    _img: null,
    _img_loaded: false,
    _is_draw_bg: false,
    _is_moving: false,
    _block_start_x: 0,
    _block_start_y: 0,
    _doing: false,
    _mark_w: 50,
    _mark_h: 50,
    _mark_offset: 0,
    _img_w: 240,
    _img_h: 150,
    _result: false,
    _err_c: 0,
    _onSuccess: null,
    _onFail: null,
    _options:{},
    // 绑定事件的方法，兼容不同浏览器（DOM2.0和IE低版本）
    _bind: function(elm, evType, fn) {
        if (elm.addEventListener) {
            // 使用DOM2.0标准的addEventListener方法绑定事件
            elm.addEventListener(evType, fn);
            return true;
        } else if (elm.attachEvent) {
            // 使用IE低版本的attachEvent方法绑定事件
            var r = elm.attachEvent("on" + evType, fn);
            return r;
        }
    },
    // 滑块开始移动的事件处理函数
    _block_start_move: function(e) {
        if (tncode._doing ||!tncode._img_loaded) {
            return;
        }
        e.preventDefault();
        // 获取事件对象，兼容移动端的触摸事件和桌面端的鼠标事件
        let theEvent = e || window.event;
        if (theEvent.touches) {
            theEvent = theEvent.touches[0];
        }

        console.log("_block_start_move");

        // 获取指定类名的元素并隐藏其文本内容
        let obj = document.getElementsByClassName('slide_block_text')[0];
        obj.style.display = "none";
        tncode._draw_bg();
        tncode._block_start_x = theEvent.clientX;
        tncode._block_start_y = theEvent.clientY;
        tncode._doing = true;
        tncode._is_moving = true;
    },
    // 滑块移动过程中的事件处理函数
    _block_on_move: function(e) {
        if (!tncode._doing) return true;
        if (!tncode._is_moving) return true;
        e.preventDefault();
        let theEvent = e || window.event;
        if (theEvent.touches) {
            theEvent = theEvent.touches[0];
        }
        tncode._is_moving = true;
        console.log("_block_on_move");

        let offset = theEvent.clientX - tncode._block_start_x;
        if (offset < 0) {
            offset = 0;
        }
        // 计算滑块最大可移动偏移量
        let max_off = tncode._img_w - tncode._mark_w;
        if (offset > max_off) {
            offset = max_off;
        }
        // 获取滑块元素并设置其平移样式
        let obj = document.getElementsByClassName('slide_block')[0];
        obj.style.cssText = "transform: translate(" + offset + "px, 0px)";
        tncode._mark_offset = offset / max_off * (tncode._img_w - tncode._mark_w);
        tncode._draw_bg();
        tncode._draw_mark();
    },
    // 滑块移动结束的事件处理函数
    _block_on_end: function(e) {
        if (!tncode._doing) return true;
        e.preventDefault();
        let theEvent = e || window.event;
        if (theEvent.touches) {
            theEvent = theEvent.touches[0];
        }
        console.log("_block_on_end");
        tncode._is_moving = false;
        tncode._send_result();
    },
    // 发送验证结果到服务器的方法
    _send_result: function() {
        let haddle = {
            success: tncode._send_result_success,
            failure: tncode._send_result_failure
        };
        tncode._result = false;
        // 创建AJAX请求实例并发起验证请求
        let re = new _ajax();
        // re.request('get',tncode._currentUrl()+'check.php?tn_r='+tncode._mark_offset,haddle);
        re.request('get', tncode._options.checkUrl + '?tn_r=' + tncode._mark_offset, haddle);
    },
    // 验证成功的回调函数
    _send_result_success: function(responseText, responseXML) {
        tncode._doing = false;
        if (responseText === 'ok') {
            tncode._tncode.innerHTML = '✓ 验证成功';
            tncode._showmsg('✓ 验证成功', 1);
            tncode._result = true;
            // 显示指定类名的元素
            document.getElementsByClassName('hgroup')[0].style.display = "block";
            setTimeout(tncode.hide, 3000);
            if (tncode._onSuccess) {
                tncode._onSuccess();
            }
        } else {
            // 获取指定元素并添加类名
            let obj = document.getElementById('tncode_div');
            addClass(obj, 'dd');
            setTimeout(function() {
                removeClass(obj, 'dd');
            }, 200);
            tncode._result = false;
            tncode._showmsg('验证失败');
            tncode._err_c++;
            // if (tncode._err_c > 5) {
            //    tncode.refresh();
            // }
            setTimeout(tncode.refresh(), 600);
            if (tncode._onFail) {
                tncode._onFail();
            }
        }
    },
    // 验证失败的回调函数（目前为空，可根据实际需求扩展）
    _send_result_failure: function(xhr, status) {

    },
    // 绘制完整背景图片的方法（用于验证码背景）
    _draw_fullbg: function() {
        let canvas_bg = document.getElementsByClassName('tncode_canvas_bg')[0];
        let ctx_bg = canvas_bg.getContext('2d');
        ctx_bg.drawImage(tncode._img, 0, tncode._img_h * 2, tncode._img_w, tncode._img_h, 0, 0, tncode._img_w, tncode._img_h);
    },
    // 绘制背景图片的方法（用于验证码背景，有重复绘制判断）
    _draw_bg: function() {
        if (tncode._is_draw_bg) {
            return;
        }
        tncode._is_draw_bg = true;
        let canvas_bg = document.getElementsByClassName('tncode_canvas_bg')[0];
        let ctx_bg = canvas_bg.getContext('2d');
        ctx_bg.drawImage(tncode._img, 0, 0, tncode._img_w, tncode._img_h, 0, 0, tncode._img_w, tncode._img_h);
    },
    // 绘制滑块覆盖区域标记的方法
    _draw_mark: function() {
        var canvas_mark = document.getElementsByClassName('tncode_canvas_mark')[0];
        var ctx_mark = canvas_mark.getContext('2d');
        // 清理画布
        ctx_mark.clearRect(0, 0, canvas_mark.width, canvas_mark.height);
        ctx_mark.drawImage(tncode._img, 0, tncode._img_h, tncode._mark_w, tncode._img_h, tncode._mark_offset, 0, tncode._mark_w, tncode._img_h);
        var imageData = ctx_mark.getImageData(0, 0, tncode._img_w, tncode._img_h);
        var data = imageData.data;

        var x = tncode._img_h, y = tncode._img_w;
        for (let j = 0; j < x; j++) {
            let ii = 1, k1 = -1;
            for (let k = 0; k < y && k >= 0 && k > k1;) {
                let i = (j * y + k) * 4;
                k += ii;
                let r = data[i], g = data[i + 1], b = data[i + 2];
                if (r + g + b < 200) {
                    data[i + 3] = 0;
                } else {
                    let arr_pix = [1, -5];
                    let arr_op = [250, 0];
                    for (let i = 1; i < arr_pix[0] - arr_pix[1]; i++) {
                        let iiii = arr_pix[0] - 1 * i;
                        let op = parseInt(arr_op[0] - (arr_op[0] - arr_op[1]) / (arr_pix[0] - arr_pix[1]) * i);
                        let iii = (j * y + k + iiii * ii) * 4;
                        data[iii + 3] = op;
                    }
                    if (ii === -1) {
                        break;
                    }
                    k1 = k;
                    k = y - 1;
                    ii = -1;
                }
            }
        }
        ctx_mark.putImageData(imageData, 0, 0);
    },
    // 重置滑块位置和相关绘制的方法
    _reset: function() {
        tncode._mark_offset = 0;
        tncode._draw_bg();
        tncode._draw_mark();
        let obj = document.getElementsByClassName('slide_block')[0];
        obj.style.cssText = "transform: translate(0px, 0px)";
    },
    // 显示验证码的方法
    show: function() {
        let obj = document.getElementsByClassName('hgroup')[0];
        if (obj) {
            obj.style.display = "none";
        }
        tncode.refresh();
        tncode._tncode = this;
        document.getElementById('tncode_div_bg').style.display = "block";
        document.getElementById('tncode_div').style.display = "block";
    },
    // 隐藏验证码的方法
    hide: function() {
        document.getElementById('tncode_div_bg').style.display = "none";
        document.getElementById('tncode_div').style.display = "none";
    },
    // 显示提示消息的方法，根据状态控制消息的显示样式和淡入淡出效果
    _showmsg: function(msg, status) {
        let obj;
        if (!status) {
            status = 0;
            obj = document.getElementsByClassName('tncode_msg_error')[0];
        } else {
            obj = document.getElementsByClassName('tncode_msg_ok')[0];
        }
        obj.innerHTML = msg;

        // 设置元素透明度的函数，兼容不同浏览器
        function setOpacity(ele, opacity) {
            if (ele.style.opacity!== undefined) {
                ele.style.opacity = opacity / 100;
            } else {
                ele.style.filter = "alpha(opacity=" + opacity + ")";
            }
        }

        // 元素淡入淡出效果的函数（淡出）
        function fadeout(ele, opacity, speed) {
            if (ele) {
                let v = ele.style.filter.replace("alpha(opacity=", "").replace(")", "") || ele.style.opacity || 100;
                v < 1 && (v = v * 100);
                let count = speed / 1000;
                let avg = (100 - opacity) / count;
                let timer = null;
                timer = setInterval(function() {
                    if (v - avg > opacity) {
                        v -= avg;
                        setOpacity(ele, v);
                    } else {
                        setOpacity(ele, 0);
                        if (status === 0) {
                            tncode._reset();
                        }
                        clearInterval(timer);
                    }
                }, 100);
            }
        }

        // 元素淡入淡出效果的函数（淡入）
        function fadein(ele, opacity, speed) {
            if (ele) {
                let v = ele.style.filter.replace("alpha(opacity=", "").replace(")", "") || ele.style.opacity;
                v < 1 && (v = v * 100);
                let count = speed / 1000;
                let avg = count < 2? (opacity / count) : (opacity / count - 1);
                let timer = null;
                timer = setInterval(function() {
                    if (v < opacity) {
                        v += avg;
                        setOpacity(ele, v);
                    } else {
                        clearInterval(timer);
                        setTimeout(function() { fadeout(obj, 0, 6000); }, 1000);
                    }
                }, 100);
            }
        }

        fadein(obj, 80, 4000);
    },
    // 生成验证码相关HTML结构的方法
    _html: function() {
        let d = document.getElementById('tncode_div_bg');
        if (d) return;
        let html = '<div class="tncode_div_bg" id="tncode_div_bg"></div><div class="tncode_div" id="tncode_div"><div class="loading">加载中</div><canvas class="tncode_canvas_bg"></canvas><canvas class="tncode_canvas_mark"></canvas><div class="hgroup"></div><div class="tncode_msg_error"></div><div class="tncode_msg_ok"></div><div class="slide"><div class="slide_block"></div><div class="slide_block_text">拖动左边滑块完成上方拼图</div></div><div class="tools"><div class="tncode_close"></div><div class="tncode_refresh"></div><div class="tncode_tips"><a href="//weisifang.com" target=_blank>by 威四方</a></div></div></div>';
        let bo = document.getElementsByTagName('body')[0];
        appendHTML(bo, html);
    },
    // 获取当前脚本所在URL的方法，用于后续拼接相关资源路径等
    _currentUrl: function() {
        let list = document.getElementsByTagName('script');
        for (let i in list) {
            let d = list[i];
            if (d.src.indexOf('tn_code')!== -1) {
                let arr = d.src.split('tn_code');
                return arr[0];
            }
        }
    },
    // 刷新验证码的方法，重新加载图片等资源并重置相关状态和样式
    refresh: function() {
        let isSupportWebp =!![].map && document.createElement('canvas').toDataURL('image/webp').indexOf('data:image/webp') === 0;
        let _this = this;
        tncode._err_c = 0;
        tncode._is_draw_bg = false;
        tncode._result = false;
        tncode._img_loaded = false;
        let obj = document.getElementsByClassName('tncode_canvas_bg')[0];
        obj.style.display = "none";
        obj = document.getElementsByClassName('tncode_canvas_mark')[0];
        obj.style.display = "none";
        tncode._img = new Image();
        // let img_url = tncode._currentUrl()+"tncode.php?t="+Math.random();
        let img_url = tncode._options.getImgUrl+"?t=" + Math.random();
        if (!isSupportWebp) {
            img_url += "&nowebp=1";
        }
        tncode._img.src = img_url;
        tncode._img.onload = function() {
            tncode._draw_fullbg();
            let canvas_mark = document.getElementsByClassName('tncode_canvas_mark')[0];
            let ctx_mark = canvas_mark.getContext('2d');
            ctx_mark.clearRect(0, 0, canvas_mark.width, canvas_mark.height);
            tncode._img_loaded = true;
            obj = document.getElementsByClassName('tncode_canvas_bg')[0];
            obj.style.display = "";
            obj = document.getElementsByClassName('tncode_canvas_mark')[0];
            obj.style.display = "";
        };
        let obj1 = document.getElementsByClassName('slide_block')[0];
        obj1.style.cssText = "transform: translate(0px, 0px)";
        obj1 = document.getElementsByClassName('slide_block_text')[0];
        obj1.style.display = "block";
    },
    // 初始化验证码相关功能的方法，绑定各种事件处理函数等
    init: function(options={}) {
        tncode._options = Object.assign({},{
            handleDom:'.tncode',
            getImgUrl:'./get_img.php',
            checkUrl:'./check.php'
        },options);
        let _this = this;
        if (!tncode._img) {
            tncode._html();
            let obj = document.getElementsByClassName('slide_block')[0];
            tncode._bind(obj,'mousedown', _this._block_start_move);
            tncode._bind(document,'mousemove', _this._block_on_move);
            tncode._bind(document,'mouseup', _this._block_on_end);
            tncode._bind(obj, 'touchstart', _this._block_start_move);
            tncode._bind(document, 'touchmove', _this._block_on_move);
            tncode._bind(document, 'touchend', _this._block_on_end);

            let obj1 = document.getElementsByClassName('tncode_close')[0];
            tncode._bind(obj1, 'touchstart', _this.hide);
            tncode._bind(obj1, 'click', _this.hide);

            let obj2 = document.getElementsByClassName('tncode_refresh')[0];
            tncode._bind(obj2, 'touchstart', _this.refresh);
            tncode._bind(obj2, 'click', _this.refresh);
            // let objs = document.getElementsByClassName('tncode', -1);
            let objs = document.querySelectorAll(tncode._options.handleDom,-1);

            for (let i in objs) {
                let o = objs[i];
                o.innerHTML = '点击按钮进行验证';
                tncode._bind(o, 'touchstart', _this.show);
                tncode._bind(o, 'click', _this.show);
            }
        }
        return tncode;
    },
    // 获取验证结果的方法
    result: function() {
        return tncode._result;
    },
    // 设置验证成功回调函数的方法
    onSuccess: function(fn) {
        tncode._onSuccess = fn;
        return tncode;
    },
    // 设置验证失败回调函数的方法
    onFail: function(fn) {
        tncode._onFail = fn;
        return tncode;
    }
};

// 创建一个全局变量指向tncode对象，方便外部访问
var $TN = tncode;

// 保存原始的window.onload函数
let _old_onload = window.onload;
// 重写window.onload函数，先执行原始的onload函数（如果存在），再执行验证码的初始化函数
window.onload = function() {
    if (typeof _old_onload === 'function') {
        _old_onload();
    }
    // tncode.init();
};
