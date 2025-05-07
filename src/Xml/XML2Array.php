<?php

namespace zxf\Xml;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMProcessingInstruction;
use InvalidArgumentException;
use RuntimeException;
use XMLReader;

/**
 * XML解析器
 *
 * 主要特性：
 * 1. 可控的外层节点保留/移除
 * 2. 增强的流式处理能力
 * 3. 智能内存管理
 * 4. 多层级错误处理
 * 5. 支持XML注释处理
 */

/**
 * @example
 *      1、支持解析微信官方文档中示例的 XML 数据
 *      <xml>
 *          <ToUserName><![CDATA[toUser]]></ToUserName>
 *          <FromUserName><![CDATA[fromUser]]></FromUserName>
 *          <CreateTime>12345678</CreateTime>
 *          <MsgType><![CDATA[text]]></MsgType>
 *          <Content><![CDATA[Hello World]]></Content>
 *      </xml>
 *
 *      2、支持解析普通的xml 字符串
 *      <catalog>
 *          <book>
 *              <title>PHP 手册</title>
 *              <author>张三</author>
 *          </book>
 *          <book>
 *              <title>Laravel 高级开发</title>
 *              <author>李四</author>
 *          </book>
 *      </catalog>
 *
 *     1、示例化后调用
 *          $parser = new XML2Array();
 *          // 或者定义配置
 *          // $parser = new XML2Array(['preserveRootNode' => false]);
 *          // 示例：内存受限环境
 *          // $parser = new EnterpriseXMLParser(['memoryLimit' => 1024 * 1024 * 100]); // 100MB限制
 *          $result = $parser->parse('<root><item>value</item></root>'); // 解析XML字符串为数组
 *          // 获取统计信息
 *          $stats = $parser->getStats();
 *          print_r($stats);
 *     2、静态调用
 *         XML2Array::toArray('<root><item>value</item></root>');
 */
final class XML2Array
{
    // 默认配置选项
    private const DEFAULT_OPTIONS = [
        'attributePrefix' => '@',          // 属性前缀
        'textNodeName' => '#text',      // 文本节点名称
        'commentNodeName' => '#comment',   // 注释节点名称
        'autoArray' => false,         // 自动创建数组
        'namespaceHandling' => 'remove',     // 三种命名空间处理模式：none（忽略）、remove（移除前缀）、preserve（保留）
        'parseAttributes' => true,         // 解析属性
        'parseComments' => false,        // 是否解析注释
        'trimValues' => true,         // 修剪值
        'typeDetection' => true,         // 启用类型检测
        'encoding' => 'UTF-8',      // 默认编码
        'preserveRootNode' => false,         // 是否保留根节点
        'removeXmlDeclaration' => true,         // 是否移除XML声明
        'libxmlOptions' => LIBXML_NOCDATA | LIBXML_NOBLANKS | LIBXML_NONET | LIBXML_COMPACT | LIBXML_PARSEHUGE,
        'maxDepth' => 50,           // 最大递归深度
        'validateOnParse' => false,        // 启用XML验证
        'schemaValidation' => false,        // 使用XSD验证
        'schemaPath' => null,         // XSD文件路径
        'memoryLimit' => null,         // 内存限制(字节)
    ];

    private array $options;

    private array $stats = [
        'nodes' => 0,
        'attributes' => 0,
        'comments' => 0,
        'depth' => 0,
        'memory' => 0,
        'parseTime' => 0,
    ];

    public function __construct(array $options = [])
    {
        $this->options = array_merge(self::DEFAULT_OPTIONS, $options);
        $this->initSecurity();
    }

    /**
     * 静态调用 解析 XML 字符串为数组
     *
     * @param  string|DomDocument|DOMElement  $xml  XML 字符串
     * @param  array  $options  配置选项
     */
    public static function toArray(string|DomDocument|DOMElement $xml, array $options = []): array
    {
        $parse = new self($options);

        return $parse->parse($xml);
    }

    /**
     * 解析XML字符串
     */
    public function parse(string|DomDocument|DOMElement $xml): array
    {
        if ($xml instanceof DOMDocument || $xml instanceof DOMElement) {
            $xml = $xml->saveXML();
        }
        $this->resetStats();
        $this->checkMemoryLimit();
        $this->validateXml($xml);

        try {
            $startTime = microtime(true);
            $dom = $this->createDomDocument();
            $this->stats['memory'] = memory_get_usage();

            if (! $dom->loadXML($xml, $this->options['libxmlOptions'])) {
                throw new RuntimeException('XML解析失败');
            }

            if ($this->options['validateOnParse']) {
                $this->validateDocument($dom);
            }

            $result = $this->processDom($dom);

            $this->stats['parseTime'] = microtime(true) - $startTime;
            $this->stats['memory'] = memory_get_peak_usage() - $this->stats['memory'];

            return $this->processResult($result);
        } finally {
            restore_error_handler();
        }
    }

    /**
     * 流式解析XML文件
     *
     * @example 流式处理并忽略根节点
     *      foreach ($parser->parseStream('data.xml') as $key => $value) {
     *          // 直接获取子节点，不包含根节点
     *      }
     */
    public function parseStream(string $filePath): \Generator
    {
        $this->resetStats();
        $this->checkFileAccess($filePath);

        $reader = new XMLReader;

        try {
            if (! $reader->open($filePath, $this->options['encoding'], $this->options['libxmlOptions'])) {
                throw new RuntimeException("无法打开XML文件: $filePath");
            }

            $reader->setParserProperty(XMLReader::SUBST_ENTITIES, true);

            while ($reader->read()) {
                $this->checkMemoryLimit();

                switch ($reader->nodeType) {
                    case XMLReader::ELEMENT:
                        $this->stats['nodes']++;
                        $dom = new DOMDocument;
                        $node = $reader->expand($dom);

                        if ($node !== false) {
                            $result = $this->processNode($node);
                            $nodeName = $this->normalizeNodeName($node->nodeName);

                            if (! $this->options['preserveRootNode'] && $reader->depth === 0) {
                                yield from $this->flattenRoot($result);
                            } else {
                                yield $nodeName => $result;
                            }
                        }
                        break;

                    case XMLReader::COMMENT:
                        if ($this->options['parseComments']) {
                            $this->stats['comments']++;
                            yield $this->options['commentNodeName'] => $reader->value;
                        }
                        break;
                }
            }
        } finally {
            $reader->close();
            restore_error_handler();
        }
    }

    /**
     * 处理最终结果（控制根节点）
     */
    private function processResult(array $result): array
    {
        if (! $this->options['preserveRootNode']) {
            return $this->flattenRoot($result);
        }

        if ($this->options['removeXmlDeclaration']) {
            unset($result['?xml']);
        }

        return $result;
    }

    /**
     * 展平根节点结构
     */
    private function flattenRoot(array $result): array
    {
        if (count($result) === 1) {
            return (array) current($result);
        }

        return (array) $result;
    }

    /**
     * 创建DOM文档对象
     */
    private function createDomDocument(): DOMDocument
    {
        $dom = new DOMDocument('1.0', $this->options['encoding']);

        $dom->preserveWhiteSpace = false;
        $dom->strictErrorChecking = false;
        $dom->substituteEntities = true;
        $dom->recover = true;
        $dom->validateOnParse = $this->options['validateOnParse'];

        return $dom;
    }

    /**
     * 处理DOM文档
     */
    private function processDom(DOMDocument $dom): array
    {
        $result = [];

        if ($dom->documentElement !== null) {
            $rootName = $this->normalizeNodeName($dom->documentElement->nodeName);
            $result[$rootName] = $this->processNode($dom->documentElement);
        }

        // 处理XML声明
        if ($dom->firstChild instanceof DOMProcessingInstruction &&
            str_starts_with($dom->firstChild->target, 'xml')
        ) {
            $result['?xml'] = $dom->firstChild->data;
        }

        return $result;
    }

    /**
     * 递归处理节点
     */
    private function processNode(DOMNode $node, int $depth = 0): mixed
    {
        $this->checkDepth($depth);
        $this->stats['depth'] = max($this->stats['depth'], $depth);

        $output = [];
        $hasElements = false;
        $hasText = false;
        $textContent = '';

        // 处理属性
        if ($this->options['parseAttributes'] && $node->hasAttributes()) {
            foreach ($node->attributes as $attr) {
                $this->stats['attributes']++;
                $attrName = $this->normalizeNodeName($attr->nodeName);
                $output[$this->options['attributePrefix'].$attrName] =
                    $this->processValue($attr->nodeValue);
            }
        }

        // 处理子节点
        foreach ($node->childNodes as $child) {
            $this->stats['nodes']++;

            switch ($child->nodeType) {
                case XML_ELEMENT_NODE:
                    $hasElements = true;
                    $childName = $this->normalizeNodeName($child->nodeName);
                    $childValue = $this->processNode($child, $depth + 1);
                    $this->addToOutput($output, $childName, $childValue);
                    break;

                case XML_TEXT_NODE:
                case XML_CDATA_SECTION_NODE:
                    $hasText = true;
                    $textContent .= $child->nodeValue;
                    break;

                case XML_COMMENT_NODE:
                    if ($this->options['parseComments']) {
                        $this->stats['comments']++;
                        $output[$this->options['commentNodeName']][] = $child->nodeValue;
                    }
                    break;
            }
        }

        // 处理文本内容
        if ($hasText) {
            $processedText = $this->processValue($textContent);

            if ($hasElements) {
                if ($processedText !== '') {
                    $output[$this->options['textNodeName']] = $processedText;
                }
            } else {
                $output = $processedText;
            }
        }

        return $output ?: '';
    }

    /**
     * 检查内存限制
     */
    private function checkMemoryLimit(): void
    {
        if ($this->options['memoryLimit'] &&
            memory_get_usage(true) > $this->options['memoryLimit']) {
            throw new RuntimeException("超出内存限制: {$this->options['memoryLimit']}字节");
        }
    }

    /**
     * 检查递归深度
     */
    private function checkDepth(int $depth): void
    {
        if ($depth > $this->options['maxDepth']) {
            throw new RuntimeException("超过最大递归深度 ({$this->options['maxDepth']})");
        }
    }

    /**
     * 检查文件访问权限
     */
    private function checkFileAccess(string $filePath): void
    {
        if (! file_exists($filePath)) {
            throw new RuntimeException("文件不存在: $filePath");
        }

        if (! is_readable($filePath)) {
            throw new RuntimeException("文件不可读: $filePath");
        }
    }

    /**
     * 初始化安全设置
     */
    private function initSecurity(): void
    {
        // PHP 8.0+ 不再需要禁用实体加载器
        // 替代方案：使用LIBXML_NONET选项防止网络访问

        // 设置默认编码
        if (function_exists('mb_internal_encoding')) {
            mb_internal_encoding($this->options['encoding']);
        }
    }

    /**
     * 验证XML输入
     */
    private function validateXml(string $xml): void
    {
        if (empty(trim($xml))) {
            throw new InvalidArgumentException('XML内容不能为空');
        }

        if (! str_starts_with(trim($xml), '<')) {
            throw new InvalidArgumentException('无效的XML格式');
        }
    }

    /**
     * 验证XML文档
     */
    private function validateDocument(DOMDocument $dom): void
    {
        if ($this->options['schemaValidation'] && $this->options['schemaPath']) {
            if (! file_exists($this->options['schemaPath'])) {
                throw new RuntimeException("XSD架构文件不存在: {$this->options['schemaPath']}");
            }

            if (! $dom->schemaValidate($this->options['schemaPath'])) {
                throw new RuntimeException('XML文档验证失败');
            }
        } elseif ($this->options['validateOnParse']) {
            if (! $dom->validate()) {
                throw new RuntimeException('XML文档验证失败');
            }
        }
    }

    /**
     * 标准化节点名称（处理命名空间）
     */
    private function normalizeNodeName(string $name): string
    {
        switch ($this->options['namespaceHandling']) {
            case 'none':
                return preg_replace('/^.*:/', '', $name);

            case 'remove':
                return preg_replace('/^[^:]+:/', '', $name);

            case 'preserve':
            default:
                return $name;
        }
    }

    /**
     * 添加子节点到输出
     */
    private function addToOutput(array &$output, string $name, $value): void
    {
        if (isset($output[$name])) {
            if (! is_array($output[$name]) || ! isset($output[$name][0])) {
                $output[$name] = [$output[$name]];
            }
            $output[$name][] = $value;
        } else {
            $output[$name] = $this->options['autoArray'] ? [$value] : $value;
        }
    }

    /**
     * 智能值处理
     */
    private function processValue(string $value): mixed
    {
        if ($this->options['trimValues']) {
            $value = trim($value);
        }

        if (! $this->options['typeDetection'] || $value === '') {
            return $value;
        }

        // 检测布尔值
        $lower = strtolower($value);
        if ($lower === 'true') {
            return true;
        }
        if ($lower === 'false') {
            return false;
        }
        if ($lower === 'null') {
            return null;
        }

        // 检测数字
        if (is_numeric($value)) {
            return str_contains($value, '.') ? (float) $value : (int) $value;
        }

        // 检测JSON
        if ($this->looksLikeJson($value)) {
            $decoded = json_decode($value, true);

            return json_last_error() === JSON_ERROR_NONE ? $decoded : $value;
        }

        return $value;
    }

    /**
     * 判断是否为JSON字符串
     */
    private function looksLikeJson(string $value): bool
    {
        $value = trim($value);
        if (empty($value)) {
            return false;
        }

        $firstChar = $value[0];
        $lastChar = $value[strlen($value) - 1];

        return ($firstChar === '{' && $lastChar === '}') ||
               ($firstChar === '[' && $lastChar === ']');
    }

    /**
     * 获取解析统计信息
     */
    public function getStats(): array
    {
        return $this->stats;
    }

    /**
     * 重置统计信息
     */
    private function resetStats(): void
    {
        $this->stats = [
            'nodes' => 0,
            'attributes' => 0,
            'depth' => 0,
            'memory' => 0,
            'parseTime' => 0,
        ];
    }
}
