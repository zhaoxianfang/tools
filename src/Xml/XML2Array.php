<?php

namespace zxf\Xml;

use DOMDocument;
use SimpleXMLElement;

/**
 * 把xml 解析成 数组
 *    1、支持解析微信官方文档中示例的 XML 数据
 *      <xml>
 *          <ToUserName><![CDATA[toUser]]></ToUserName>
 *          <FromUserName><![CDATA[fromUser]]></FromUserName>
 *          <CreateTime>12345678</CreateTime>
 *          <MsgType><![CDATA[text]]></MsgType>
 *          <Content><![CDATA[Hello World]]></Content>
 *      </xml>
 *    2、支持解析普通的xml 字符串
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
 * Usage:
 *       $array = XML2Array::parse($xml);
 */
class XML2Array
{
    /**
     * 解析 XML 字符串为数组
     *
     * @param string|DOMDocument $xml               XML 字符串
     * @param bool               $includeAttributes 是否解析 XML 属性（默认 true）
     *
     * @return array|null 解析后的数组，失败返回 null
     */
    public static function parse(string|DomDocument $xml, bool $includeAttributes = true): ?array
    {
        if (empty($xml)) {
            return null;
        }
        if ($xml instanceof DOMDocument) {
            $xml = $xml->saveXML();
        }

        // 防止 XXE 攻击
        libxml_use_internal_errors(true);
        $xmlElement = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
        if ($xmlElement === false) {
            return null;
        }

        return self::convertXmlElementToArray($xmlElement, $includeAttributes);
    }

    /**
     * 递归解析 SimpleXMLElement 为数组
     *
     * @param SimpleXMLElement $xmlElement
     * @param bool $includeAttributes 是否解析 XML 属性
     * @return mixed
     */
    private static function convertXmlElementToArray(SimpleXMLElement $xmlElement, bool $includeAttributes)
    {
        $result = [];

        // 解析 XML 属性
        if ($includeAttributes) {
            foreach ($xmlElement->attributes() as $attrKey => $attrValue) {
                $result["@{$attrKey}"] = (string) $attrValue;
            }
        }

        // 解析子节点
        foreach ($xmlElement->children() as $childKey => $childElement) {
            $childData = self::convertXmlElementToArray($childElement, $includeAttributes);

            // 统一转换为数组格式
            if (!isset($result[$childKey])) {
                $result[$childKey] = $childData;
            } elseif (is_array($result[$childKey]) && array_key_exists(0, $result[$childKey])) {
                $result[$childKey][] = $childData;
            } else {
                $result[$childKey] = [$result[$childKey], $childData];
            }
        }

        // 解析当前节点的文本内容，避免丢失 CDATA
        $text = trim((string) $xmlElement);
        if ($text !== '') {
            if (!empty($result)) {
                $result["_value"] = $text; // 既有子元素又有文本内容
            } else {
                return $text; // 纯文本节点直接返回
            }
        }

        return $result;
    }
}
