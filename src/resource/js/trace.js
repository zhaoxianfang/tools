var isClickAllowed = true; // 初始允许点击
function trace_reset_allowed_value() {
    isClickAllowed = false; // 禁止后续点击
    setTimeout(() => { isClickAllowed = true;}, 500); // 500ms 后恢复
}
document.addEventListener('click', function(e) {
    const tabsLogoEvent = e.target.closest("#tools_trace .trace-logo");
    const closeButton = e.target.closest("#tools_trace .tabs-close");
    const tabsContainerDom = document.querySelector("#tools_trace .tabs-container");
    const tabsLogoDom = document.querySelector("#tools_trace .trace-logo");
    if (tabsLogoEvent && isClickAllowed) { // 点击 Logo 展开 Tabs
        tabsLogoDom.style.display = "none"; // 隐藏 Logo
        tabsContainerDom.style.display = "flex"; // 显示 Tabs
        trace_reset_allowed_value();
    }
    if (closeButton && isClickAllowed) { // 点击关闭按钮隐藏 Tabs
        tabsContainerDom.style.display = "none"; // 隐藏 Tabs
        tabsLogoDom.style.display = "block"; // 显示 Logo
        trace_reset_allowed_value();
    }
});

document.addEventListener('DOMContentLoaded', function() {
    const tabItems = document.querySelectorAll("#tools_trace .tabs-item");
    const tabContents = document.querySelectorAll("#tools_trace .tabs-content");

    // 激活指定 Tab
    function activateTab(index) {
        tabItems.forEach((item, i) => {
            item.classList.toggle("active", i === index);
            tabContents[i].classList.toggle("active", i === index);
        });
    }

    // Tab 切换逻辑
    tabItems.forEach((tab, index) => {
        tab.addEventListener("click", () => activateTab(index));
    });

    // 初始化默认激活第一个 Tab
    activateTab(0);

    // ========================================

    // 初始化 JSON 显示
    function initializeJsonDisplay(jsonElement, arrowElement, labelElement) {
        try {
            const jsonText = jsonElement.textContent.trim();
            const jsonData = JSON.parse(jsonText);

            // 计算 JSON 对象的长度
            const arrayLength = Array.isArray(jsonData) ? jsonData.length : Object.keys(jsonData).length;

            // 格式化 JSON 数据 为 带缩进的 JSON 字符串
            jsonElement.textContent = JSON.stringify(jsonData, null, 4);

            // 设置箭头的初始文本

            arrowElement.textContent = `▶ array:${arrayLength}`;
            // labelElement.textContent += ` array:${arrayLength}`;
        } catch (e) {
            console.error("初始化 JSON 数据时出错:", e);
        }
    }

    // 初始化 JSON 列表
    const jsonElements = document.querySelectorAll("#tools_trace .json");
    const arrowElements = document.querySelectorAll("#tools_trace .json-arrow");
    const labelElements = document.querySelectorAll("#tools_trace .json-label");

    jsonElements.forEach((jsonElement, index) => {
        const jsonText = jsonElement.textContent.trim();
        jsonElement.setAttribute("data-original", jsonText);
        initializeJsonDisplay(jsonElement, arrowElements[index], labelElements[index]);
    });
});

// 展开/收起 JSON , 在 html 中指定触发
function toggleJson(arrowElement) {
    const preElement = arrowElement.nextElementSibling;
    const labelElement = arrowElement.parentNode.previousElementSibling;
    const jsonData = JSON.parse(preElement.getAttribute("data-original"));
    const arrayLength = Array.isArray(jsonData) ? jsonData.length : Object.keys(jsonData).length;

    if (preElement.classList.contains("show")) {
        // 收起
        arrowElement.textContent = `▶ array:${arrayLength}`;
        preElement.classList.remove("show");
    } else {
        // 展开
        arrowElement.textContent = `▼ array:${arrayLength}`;
        preElement.classList.add("show");
    }
}
