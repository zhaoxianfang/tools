<?php

namespace zxf\Xml;

use DOMDocument;
use DOMElement;
use DOMException;

/**
 * Array2XML 类 - 高效稳定的数组转XML工具
 *
 * 功能特性：
 * 1. 智能CDATA处理：autoCData配置自动识别数字/非数字类型
 * 2. 支持所有XML特性：命名空间、注释、处理指令等
 * 3. 严格遵循PHP8语法规范
 * 4. 完善的错误处理机制
 * 5. 简洁高效的代码实现
 *
 * @example 用法详见 readme.md 文件
 */
class Array2XML
{
    private DOMDocument $document;

    private array $config = [
        'version' => '1.0', // XML版本号
        'encoding' => 'UTF-8', // 文档编码
        'standalone' => false, // 是否独立文档
        'formatOutput' => true, // 是否格式化输出
        'preserveWhiteSpace' => false, // 是否保留空白字符
        'rootName' => 'root', // 根节点名称
        'rootAttributes' => [], // 根节点属性
        'defaultNamespace' => null, // 默认命名空间URI
        'autoCData' => false, // 是否自动处理CDATA
        'booleanToString' => true, // 是否将布尔值转为字符串
        'namespaces' => [], // 命名空间配置
    ];

    public function __construct(array $config = [])
    {
        $this->config = array_merge($this->config, $config);
        $this->initDocument();
        $this->initRootElement();
    }

    private function initDocument(): void
    {
        $this->document = new DOMDocument(
            $this->config['version'],
            $this->config['encoding']
        );
        $this->config['standalone'] && $this->document->standalone = $this->config['standalone'];
        $this->document->formatOutput = $this->config['formatOutput'];
        $this->document->preserveWhiteSpace = $this->config['preserveWhiteSpace'];
    }

    private function initRootElement(): void
    {
        $root = $this->createElement(
            $this->config['rootName'],
            null,
            $this->config['rootAttributes'],
            $this->config['defaultNamespace']
        );

        foreach ($this->config['namespaces'] as $prefix => $uri) {
            $attrName = $prefix === '' ? 'xmlns' : "xmlns:$prefix";
            $root->setAttribute($attrName, $uri);
        }

        $this->document->appendChild($root);
    }

    /**
     * 创建元素 (公开方法，供外部调用)
     */
    public function createElement(
        string $name,
        $value = null,
        array $attributes = [],
        ?string $namespace = null
    ): DOMElement {
        $element = $namespace
            ? $this->document->createElementNS($namespace, $name)
            : $this->document->createElement($name);

        foreach ($attributes as $attrName => $attrValue) {
            if (str_contains($attrName, ':')) {
                [$prefix, $localName] = explode(':', $attrName, 2);
                $nsUri = $this->config['namespaces'][$prefix] ?? 'http://www.w3.org/2000/xmlns/';
                $element->setAttributeNS($nsUri, $attrName, $this->toString($attrValue));
            } else {
                $element->setAttribute($attrName, $this->toString($attrValue));
            }
        }

        if ($value !== null) {
            $this->setElementValue($element, $value);
        }

        return $element;
    }

    private function setElementValue(DOMElement $element, $value): void
    {
        if (is_array($value)) {
            $this->addArrayContent($element, $value);
        } else {
            $this->addScalarContent($element, $value);
        }
    }

    private function addArrayContent(DOMElement $element, array $content): void
    {
        foreach ($content as $key => $value) {
            if ($this->handleSpecialKey($element, $key, $value)) {
                continue;
            }

            if (is_array($value) && ! array_is_list($value)) {
                $child = $this->createElement($key);
                $this->addArrayContent($child, $value);
                $element->appendChild($child);
            } elseif (is_array($value)) {
                foreach ($value as $item) {
                    $child = is_array($item)
                        ? $this->createElement($key)
                        : $this->createElement($key, $item);

                    if (is_array($item)) {
                        $this->addArrayContent($child, $item);
                    }

                    $element->appendChild($child);
                }
            } else {
                $element->appendChild($this->createElement($key, $value));
            }
        }
    }

    private function handleSpecialKey(DOMElement $element, string $key, $value): bool
    {
        if ($key[0] === '@') {
            $element->setAttribute(substr($key, 1), $this->toString($value));

            return true;
        }

        if ($key[0] === '#') {
            $type = substr($key, 1);
            switch ($type) {
                case 'comment':
                    $element->appendChild($this->document->createComment($this->toString($value)));
                    break;
                case 'pi':
                    if (is_array($value)) {
                        $target = $value['target'] ?? '';
                        $data = $value['data'] ?? '';
                        $element->appendChild($this->document->createProcessingInstruction($target, $data));
                    }
                    break;
                case 'cdata':
                    $element->appendChild($this->document->createCDATASection($this->toString($value)));
                    break;
            }

            return true;
        }

        return false;
    }

    private function addScalarContent(DOMElement $element, $value): void
    {
        $strValue = $this->toString($value);
        $useCData = $this->config['autoCData'] && ! is_numeric($value);

        if ($useCData) {
            $element->appendChild($this->document->createCDATASection($strValue));
        } else {
            $element->appendChild($this->document->createTextNode($strValue));
        }
    }

    private function toString($value): string
    {
        if (is_bool($value)) {
            return $this->config['booleanToString'] ? ($value ? 'true' : 'false') : (string) $value;
        }

        return (string) $value;
    }

    public function convert(array $data): string
    {
        try {
            $root = $this->document->documentElement;
            $this->addArrayContent($root, $data);

            return $this->document->saveXML();
        } catch (DOMException $e) {
            throw new DOMException('XML转换失败: '.$e->getMessage());
        }
    }

    public static function toXML(array $data, array $config = []): string
    {
        return (new self($config))->convert($data);
    }

    public function getDocument(): DOMDocument
    {
        return $this->document;
    }
}
