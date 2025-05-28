<?php

namespace zxf\Xml;

use DOMDocument;
use DOMElement;
use DOMException;
use InvalidArgumentException;

/**
 * Array2XML 类 - 数组转XML工具
 *
 * 功能特性：
 * 1. 全面处理各种数据类型和键名类型
 * 2. 严格的XML规范合规性检查
 * 3. 完善的错误处理和异常捕获机制
 * 4. 支持复杂嵌套数组结构
 * 5. 高度可配置的转换规则
 *
 * @example 用法详见 readme.md 文件
 */
class Array2XML
{
    // DOM文档对象实例
    private DOMDocument $document;

    // 默认配置项
    private array $config = [
        'version' => '1.0',               // XML版本号
        'encoding' => 'UTF-8',            // 文档编码
        'standalone' => false,            // 是否独立文档
        'formatOutput' => true,           // 是否格式化输出
        'preserveWhiteSpace' => false,    // 是否保留空白字符
        'rootName' => 'root',             // 默认根节点名称
        'numericElement' => 'item',       // 默认数字元素名称前缀
        'rootAttributes' => [],           // 根节点属性
        'defaultNamespace' => null,       // 默认命名空间URI
        'autoCData' => false,             // 是否自动处理CDATA
        'booleanToString' => true,        // 是否将布尔值转为字符串
        'namespaces' => [],               // 命名空间配置
        'validateElementName' => true,    // 是否验证元素名称有效性
        'strictMode' => false,            // 严格模式(发现错误抛出异常)
        'skipInvalidElements' => true,    // 跳过无效元素而不是抛出异常
        'attributePrefix' => '@',         // 属性前缀标识符
        'specialPrefix' => '#',           // 特殊节点前缀标识符
        'textKey' => '#text',             // 文本内容键名
    ];

    /**
     * 构造函数
     *
     * @param  array  $config  配置数组
     *
     * @throws InvalidArgumentException 当配置无效时抛出
     */
    public function __construct(array $config = [])
    {
        // 深度合并配置(保留多维数组结构)
        $this->config = array_replace_recursive($this->config, $config);

        // 初始化DOM文档
        $this->initDocument();

        // 初始化根元素
        $this->initRootElement();
    }

    /**
     * 初始化DOM文档对象
     *
     * @throws InvalidArgumentException 当配置无效时抛出
     */
    private function initDocument(): void
    {
        try {
            // 验证版本号
            if (! is_string($this->config['version'])) {
                throw new InvalidArgumentException('版本号必须是字符串');
            }

            // 验证编码
            if (! is_string($this->config['encoding']) || ! in_array(strtoupper($this->config['encoding']), ['UTF-8', 'ISO-8859-1', 'US-ASCII'])) {
                throw new InvalidArgumentException('不支持的编码格式');
            }

            // 创建DOM文档实例
            $this->document = new DOMDocument(
                $this->config['version'],
                $this->config['encoding']
            );

            // 设置独立文档声明
            $this->document->standalone = $this->config['standalone'];

            // 设置输出格式
            $this->document->formatOutput = $this->config['formatOutput'];

            // 设置空白字符处理
            $this->document->preserveWhiteSpace = $this->config['preserveWhiteSpace'];
        } catch (DOMException $e) {
            throw new InvalidArgumentException('DOM文档初始化失败: '.$e->getMessage());
        }
    }

    /**
     * 初始化根元素
     *
     * @throws InvalidArgumentException 当根元素无效时抛出
     */
    private function initRootElement(): void
    {
        try {
            // 验证根元素名称
            $rootName = $this->normalizeElementName($this->config['rootName']);
            if ($rootName === '') {
                throw new InvalidArgumentException('无效的根元素名称');
            }

            // 创建根元素
            $root = $this->createElement(
                $rootName,
                null,
                $this->config['rootAttributes'],
                $this->config['defaultNamespace']
            );

            // 添加命名空间声明
            $this->addNamespaces($root);

            // 将根元素添加到文档
            $this->document->appendChild($root);
        } catch (DOMException $e) {
            throw new InvalidArgumentException('根元素创建失败: '.$e->getMessage());
        }
    }

    /**
     * 添加命名空间声明
     *
     * @param  DOMElement  $element  要添加命名空间的元素
     */
    private function addNamespaces(DOMElement $element): void
    {
        foreach ($this->config['namespaces'] as $prefix => $uri) {
            // 验证命名空间前缀和URI
            if (! is_string($prefix)) {
                continue;
            }

            if (! is_string($uri) || $uri === '') {
                continue;
            }

            // 构建属性名
            $attrName = $prefix === '' ? 'xmlns' : 'xmlns:'.$prefix;

            try {
                // 设置命名空间属性
                $element->setAttribute($attrName, $uri);
            } catch (DOMException $e) {
                // 在严格模式下抛出异常，否则跳过
                if ($this->config['strictMode']) {
                    throw new InvalidArgumentException("命名空间声明失败: {$attrName}={$uri}");
                }
            }
        }
    }

    /**
     * 创建XML元素
     *
     * @param  string  $name  元素名称
     * @param  mixed  $value  元素值
     * @param  array  $attributes  元素属性
     * @param  string|null  $namespace  命名空间URI
     * @return DOMElement 创建的DOM元素
     *
     * @throws InvalidArgumentException 当元素创建失败时抛出
     */
    public function createElement(
        string $name,
        $value = null,
        array $attributes = [],
        ?string $namespace = null
    ): DOMElement {
        try {
            // 标准化元素名称
            $normalizedName = $this->normalizeElementName($name);
            if ($normalizedName === '') {
                throw new InvalidArgumentException("无效的元素名称: {$name}");
            }

            // 创建元素(带或不带命名空间)
            $element = $namespace
                ? $this->document->createElementNS($namespace, $normalizedName)
                : $this->document->createElement($normalizedName);

            // 添加属性
            $this->addAttributes($element, $attributes);

            // 设置元素值
            if ($value !== null) {
                $this->setElementValue($element, $value);
            }

            return $element;
        } catch (DOMException $e) {
            if ($this->config['strictMode']) {
                throw new InvalidArgumentException('元素创建失败: '.$e->getMessage());
            }

            // 非严格模式下创建备用元素
            return $this->document->createElement('element');
        }
    }

    /**
     * 标准化XML元素名称
     *
     * @param  string  $name  原始名称
     * @return string 标准化后的名称
     */
    private function normalizeElementName(string $name): string
    {
        // 空名称处理
        if ($name === '') {
            return '';
        }

        // 数字键名处理
        if (is_numeric($name)) {
            return $this->config['numericElement'];
        }

        // 验证模式下的严格处理
        if ($this->config['validateElementName']) {
            // 移除非法的XML名称字符
            $cleaned = preg_replace('/[^a-zA-Z0-9_\-\.]/', '', $name);

            // 确保名称以字母或下划线开头
            if (! preg_match('/^[a-zA-Z_]/', $cleaned)) {
                $cleaned = 'element_'.$cleaned;
            }

            // 确保名称不为空
            if ($cleaned === '') {
                return '';
            }

            return $cleaned;
        }

        // 非验证模式直接返回
        return $name;
    }

    /**
     * 添加属性到元素
     *
     * @param  DOMElement  $element  目标元素
     * @param  array  $attributes  属性数组
     */
    private function addAttributes(DOMElement $element, array $attributes): void
    {
        foreach ($attributes as $attrName => $attrValue) {
            // 跳过非字符串键名
            if (! is_string($attrName)) {
                continue;
            }

            // 标准化属性名
            $normalizedAttrName = $this->normalizeAttributeName($attrName);
            if ($normalizedAttrName === '') {
                continue;
            }

            // 转换属性值为字符串
            $strValue = $this->toString($attrValue);

            try {
                // 处理带命名空间的属性
                if (str_contains($normalizedAttrName, ':')) {
                    [$prefix, $localName] = explode(':', $normalizedAttrName, 2);
                    $nsUri = $this->config['namespaces'][$prefix] ?? 'http://www.w3.org/2000/xmlns/';
                    $element->setAttributeNS($nsUri, $normalizedAttrName, $strValue);
                } else {
                    $element->setAttribute($normalizedAttrName, $strValue);
                }
            } catch (DOMException $e) {
                // 严格模式下抛出异常
                if ($this->config['strictMode']) {
                    throw new InvalidArgumentException("属性设置失败: {$normalizedAttrName}={$strValue}");
                }
            }
        }
    }

    /**
     * 标准化属性名称
     *
     * @param  string  $name  原始属性名
     * @return string 标准化后的属性名
     */
    private function normalizeAttributeName(string $name): string
    {
        // 空名称处理
        if ($name === '') {
            return '';
        }

        // 移除非法的XML属性名称字符
        $cleaned = preg_replace('/[^a-zA-Z0-9_\-\.:]/', '', $name);

        // 确保名称不为空
        if ($cleaned === '') {
            return '';
        }

        return $cleaned;
    }

    /**
     * 设置元素值
     *
     * @param  DOMElement  $element  目标元素
     * @param  mixed  $value  元素值
     */
    private function setElementValue(DOMElement $element, $value): void
    {
        if (is_array($value)) {
            $this->addArrayContent($element, $value);
        } else {
            $this->addScalarContent($element, $value);
        }
    }

    /**
     * 添加数组内容到元素
     *
     * @param  DOMElement  $element  父元素
     * @param  array  $content  内容数组
     */
    private function addArrayContent(DOMElement $element, array $content): void
    {
        foreach ($content as $key => $value) {
            try {
                // 跳过无效键名
                if (! is_string($key) && ! is_int($key)) {
                    continue;
                }

                $keyStr = (string) $key;

                // 处理特殊键名
                if ($this->handleSpecialKey($element, $keyStr, $value)) {
                    continue;
                }

                // 处理文本内容键
                if ($keyStr === $this->config['textKey']) {
                    $this->addScalarContent($element, $value);

                    continue;
                }

                // 处理关联数组
                if (is_array($value) && ! array_is_list($value)) {
                    $child = $this->createElement($keyStr);
                    $this->addArrayContent($child, $value);
                    $element->appendChild($child);
                }
                // 处理索引数组
                elseif (is_array($value)) {
                    foreach ($value as $item) {
                        $child = is_array($item)
                            ? $this->createElement($keyStr)
                            : $this->createElement($keyStr, $item);

                        if (is_array($item)) {
                            $this->addArrayContent($child, $item);
                        }

                        $element->appendChild($child);
                    }
                }
                // 处理标量值
                else {
                    $element->appendChild($this->createElement($keyStr, $value));
                }
            } catch (DOMException $e) {
                // 严格模式下抛出异常
                if ($this->config['strictMode']) {
                    throw new InvalidArgumentException('添加数组内容失败: '.$e->getMessage());
                }
            }
        }
    }

    /**
     * 处理特殊键名
     *
     * @param  DOMElement  $element  当前元素
     * @param  string  $key  键名
     * @param  mixed  $value  键值
     * @return bool 是否处理了特殊键
     */
    private function handleSpecialKey(DOMElement $element, string $key, $value): bool
    {
        // 处理属性
        if (str_starts_with($key, $this->config['attributePrefix'])) {
            $attrName = substr($key, strlen($this->config['attributePrefix']));
            $normalizedAttrName = $this->normalizeAttributeName($attrName);

            if ($normalizedAttrName !== '') {
                try {
                    $element->setAttribute($normalizedAttrName, $this->toString($value));

                    return true;
                } catch (DOMException $e) {
                    if ($this->config['strictMode']) {
                        throw new InvalidArgumentException("属性设置失败: {$normalizedAttrName}");
                    }
                }
            }

            return false;
        }

        // 处理特殊节点
        if (str_starts_with($key, $this->config['specialPrefix'])) {
            $type = substr($key, strlen($this->config['specialPrefix']));

            switch ($type) {
                case 'comment':
                    $commentText = $this->toString($value);
                    if ($commentText !== '') {
                        try {
                            $element->appendChild($this->document->createComment($commentText));
                        } catch (DOMException $e) {
                            if ($this->config['strictMode']) {
                                throw new InvalidArgumentException('注释添加失败');
                            }
                        }
                    }

                    return true;

                case 'pi':
                    if (is_array($value)) {
                        $target = $this->normalizeProcessingInstructionTarget($value['target'] ?? '');
                        $data = $this->toString($value['data'] ?? '');

                        if ($target !== '') {
                            try {
                                $element->appendChild($this->document->createProcessingInstruction($target, $data));
                            } catch (DOMException $e) {
                                if ($this->config['strictMode']) {
                                    throw new InvalidArgumentException('处理指令添加失败');
                                }
                            }
                        }
                    }

                    return true;

                case 'cdata':
                    $cdataText = $this->toString($value);
                    if ($cdataText !== '') {
                        try {
                            $element->appendChild($this->document->createCDATASection($cdataText));
                        } catch (DOMException $e) {
                            if ($this->config['strictMode']) {
                                throw new InvalidArgumentException('CDATA添加失败');
                            }
                        }
                    }

                    return true;
            }
        }

        return false;
    }

    /**
     * 标准化处理指令目标
     *
     * @param  string  $target  原始目标
     * @return string 标准化后的目标
     */
    private function normalizeProcessingInstructionTarget(string $target): string
    {
        // 移除非法的XML处理指令目标字符
        $cleaned = preg_replace('/[^a-zA-Z0-9_\-\.]/', '', $target);

        // 确保不以XML开头(保留字)
        if (str_starts_with(strtoupper($cleaned), 'XML')) {
            $cleaned = 'pi_'.$cleaned;
        }

        return $cleaned;
    }

    /**
     * 添加标量内容到元素
     *
     * @param  DOMElement  $element  目标元素
     * @param  mixed  $value  标量值
     */
    private function addScalarContent(DOMElement $element, $value): void
    {
        $strValue = $this->toString($value);

        // 空值不添加
        if ($strValue === '') {
            return;
        }

        // 决定是否使用CDATA
        $useCData = $this->config['autoCData'] && ! is_numeric($value);

        try {
            if ($useCData) {
                $element->appendChild($this->document->createCDATASection($strValue));
            } else {
                $element->appendChild($this->document->createTextNode($strValue));
            }
        } catch (DOMException $e) {
            if ($this->config['strictMode']) {
                throw new InvalidArgumentException('内容添加失败: '.$e->getMessage());
            }
        }
    }

    /**
     * 将值转换为字符串
     *
     * @param  mixed  $value  要转换的值
     * @return string 字符串结果
     */
    private function toString($value): string
    {
        // null值转为空字符串
        if ($value === null) {
            return '';
        }

        // 布尔值处理
        if (is_bool($value)) {
            return $this->config['booleanToString'] ? ($value ? 'true' : 'false') : (int) $value;
        }

        // 数组和对象转为空字符串
        if (is_array($value) || is_object($value)) {
            return '';
        }

        // 资源类型转为空字符串
        if (is_resource($value)) {
            return '';
        }

        // 默认转为字符串
        return (string) $value;
    }

    /**
     * 转换数组为XML
     *
     * @param  array  $data  要转换的数组
     * @return string XML字符串
     *
     * @throws DOMException 转换失败时抛出
     */
    public function convert(array $data): string
    {
        try {
            $root = $this->document->documentElement;
            if ($root === null) {
                throw new DOMException('根元素不存在');
            }

            $this->addArrayContent($root, $data);

            $xml = $this->document->saveXML();
            if ($xml === false) {
                throw new DOMException('XML生成失败');
            }

            return $xml;
        } catch (DOMException $e) {
            if ($this->config['strictMode']) {
                throw new DOMException('XML转换失败: '.$e->getMessage());
            }

            // 非严格模式下返回空XML文档
            return '<?xml version="1.0" encoding="UTF-8"?><error/>';
        }
    }

    /**
     * 静态方法快速转换
     *
     * @param  array  $data  要转换的数组
     * @param  array  $config  配置数组
     * @return string XML字符串
     *
     * @throws DOMException 转换失败时抛出
     */
    public static function toXML(array $data, array $config = []): string
    {
        return (new self($config))->convert($data);
    }

    /**
     * 获取DOM文档对象
     *
     * @return DOMDocument DOM文档实例
     */
    public function getDocument(): DOMDocument
    {
        return $this->document;
    }
}
