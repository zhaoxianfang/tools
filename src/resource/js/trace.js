(function (window) {
    const tabsLogo = document.querySelector("#tools_trace .trace-logo");
    const tabsContainer = document.querySelector("#tools_trace .tabs-container");
    const closeButton = document.querySelector("#tools_trace .tabs-close");
    const tabItems = document.querySelectorAll("#tools_trace .tabs-item");
    const tabContents = document.querySelectorAll("#tools_trace .tabs-content");

    // 点击 Logo 展开 Tabs
    tabsLogo.addEventListener("click", () => {
        tabsLogo.style.display = "none"; // 隐藏 Logo
        tabsContainer.style.display = "flex"; // 显示 Tabs
    });

    // 点击关闭按钮隐藏 Tabs
    closeButton.addEventListener("click", () => {
        tabsContainer.style.display = "none"; // 隐藏 Tabs
        tabsLogo.style.display = "block"; // 显示 Logo
    });

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

            // 格式化 JSON 数据
            // jsonElement.textContent = `array:${arrayLength} [\n` +
            jsonElement.textContent = `[\n` +
                Object.entries(jsonData).map(([key, value]) => {
                    const valueStr = typeof value === "object" && value !== null
                        ? JSON.stringify(value, null, 2)
                        : JSON.stringify(value);
                    return `  "${key}" => ${valueStr}`;
                }).join(",\n") +
                "\n]";

            // 设置箭头的初始文本
            // arrowElement.textContent = `▶`;
            // labelElement.textContent += ` array:${arrayLength}`;

            arrowElement.textContent = `▶ array:${arrayLength}`;
            // labelElement.textContent += ` array:${arrayLength}`;
        } catch (e) {
            console.error("初始化 JSON 数据时出错:", e);
        }
    }

    // 初始化 JSON 列表
    document.addEventListener("DOMContentLoaded", function () {
        const jsonElements = document.querySelectorAll("#tools_trace .json");
        const arrowElements = document.querySelectorAll("#tools_trace .json-arrow");
        const labelElements = document.querySelectorAll("#tools_trace .json-label");

        jsonElements.forEach((jsonElement, index) => {
            const jsonText = jsonElement.textContent.trim();
            jsonElement.setAttribute("data-original", jsonText);
            initializeJsonDisplay(jsonElement, arrowElements[index], labelElements[index]);
        });
    });
})(window);

// 展开/收起 JSON
function toggleJson(arrowElement) {
    const preElement = arrowElement.nextElementSibling;
    const labelElement = arrowElement.parentNode.previousElementSibling;
    const jsonData = JSON.parse(preElement.getAttribute("data-original"));
    const arrayLength = Array.isArray(jsonData) ? jsonData.length : Object.keys(jsonData).length;

    if (preElement.classList.contains("show")) {
        // 收起
        // arrowElement.textContent = `▶`;
        // labelElement.textContent = labelElement.textContent.replace(/▼.+/, ` array:${arrayLength}`);
        arrowElement.textContent = `▶ array:${arrayLength}`;
        // labelElement.textContent = labelElement.text/Content.replace(/▼.+/, ` array:${arrayLength}`);
        preElement.classList.remove("show");
    } else {
        // 展开
        // arrowElement.textContent = `▼`;
        arrowElement.textContent = `▼ array:${arrayLength}`;
        // labelElement.textContent = labelElement.textContent.replace(/array:\d+/, '');
        preElement.classList.add("show");
    }
}
