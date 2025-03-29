<?php

namespace zxf\Xml;

use DOMDocument;
use DOMElement;
use DOMException;
use DOMImplementation;
use DOMNode;
use Exception;

/**
 * 数组转XML字符串
 *      1、支持普通数组转 XML : createXML
 *      2、支持数组转微信官方文档中示例的 XML 数据 : createWechatXML
 *
 * 1、普通数组转XML => createXML 方法
 *       $array = [
 *          'Good_guy' => [
 *              'name' => 'Luke Skywalker',
 *              'weapon' => 'Lightsaber'
 *          ],
 *          'Bad_guy' => [
 *              'name' => 'Sauron',
 *              'weapon' => 'Evil Eye'
 *          ]
 *      ];
 *
 *      $xml = Array2XML::createXML($array,?'root',?[
 *          'version' => '1.0',
 *          'encoding' => 'UTF-8',
 *          'standalone' => true,
 *          'formatOutput' => true
 *      ]);
 *
 *      echo $xml->saveXML();
 *
 * 2、转微信文档中示例的 XML 数据 => createWechatXML 方法
 *      // 输入数组数据
 *      $data = [
 *          'ToUserName' => 'toUser',
 *          'FromUserName' => 'fromUser',
 *          'CreateTime' => 12345678, // int 类型的数据 转换后不会携带CDATA
 *          'MsgType' => 'text',
 *          'Content' => 'Hello World'
 *      ];
 *      $xml = Array2XML::createWechatXML($data);
 *
 *      echo $xml; // <?xml version="1.0"
 *      encoding="UTF-8"?><xml><ToUserName><![CDATA[toUser]]></ToUserName><FromUserName><![CDATA[fromUser]]></FromUserName><CreateTime>12345678</CreateTime><MsgType><![CDATA[text]]></MsgType><Content><![CDATA[Hello
 *      World]]></Content></xml>
 */
class Array2XML
{
    use InitTrait;

    /**
     * Convert an Array to XML.
     *
     * @param array  $arr       - array to be converted
     * @param string $node_name - name of the root node to be converted
     * @param array  $docType   - optional docType
     *
     * @return DomDocument
     * @throws DOMException
     */
    public static function createXML(array $arr, string $node_name = 'root', array $docType = [])
    {
        self::$xml = null;
        $xml       = self::getXMLRoot();

        // BUG 008 - Support <!DOCTYPE>
        if ($docType) {
            $xml->appendChild(
                (new DOMImplementation())
                    ->createDocumentType(
                        $docType['name'] ?? '',
                        $docType['publicId'] ?? '',
                        $docType['systemId'] ?? ''
                    )
            );
        }

        $xml->appendChild(self::convert($node_name, $arr));
        self::$xml = null;    // clear the xml node in the class for 2nd time use.

        return $xml;
    }

    /**
     * Get string representation of boolean value.
     *
     * @param mixed $v
     *
     * @return string
     */
    private static function bool2str($v)
    {
        //convert boolean to text value.
        $v = $v === true ? 'true' : $v;
        $v = $v === false ? 'false' : $v;

        return $v ?? '';
    }

    /**
     * Convert an Array to XML.
     *
     * @param string            $node_name - name of the root node to be converted
     * @param array|string|null $arr       - array to be converted
     *
     * @return DOMNode
     *
     * @throws Exception
     */
    private static function convert(string $node_name, array|string|null $arr = [])
    {
        //print_arr($node_name);
        $xml  = self::getXMLRoot();
        $node = $xml->createElement($node_name);

        if (is_array($arr)) {
            // get the attributes first.;
            if (array_key_exists(self::$labelAttributes, $arr) && is_array($arr[self::$labelAttributes])) {
                foreach ($arr[self::$labelAttributes] as $key => $value) {
                    if (!self::isValidTagName($key)) {
                        throw new Exception('[Array2XML] Illegal character in attribute name. attribute: ' . $key . ' in node: ' . $node_name);
                    }
                    $node->setAttribute($key, self::bool2str($value));
                }
                unset($arr[self::$labelAttributes]); //remove the key from the array once done.
            }

            // check if it has a value stored in @value, if yes store the value and return
            // else check if its directly stored as string
            if (array_key_exists(self::$labelValue, $arr)) {
                if (!self::isValidValue($arr[self::$labelValue])) {
                    throw new Exception('[Array2XML] Illegal character in value : ' . $arr[self::$labelValue] . ' in node: ' . $node_name);
                }
                $node->appendChild($xml->createTextNode(self::bool2str($arr[self::$labelValue])));
                unset($arr[self::$labelValue]);    //remove the key from the array once done.
                //return from recursion, as a note with value cannot have child nodes.
                return $node;
            } elseif (array_key_exists(self::$labelCData, $arr)) {
                if (!self::isValidValue($arr[self::$labelCData])) {
                    throw new Exception('[Array2XML] Illegal character in CData : ' . $arr[self::$labelCData] . ' in node: ' . $node_name);
                }
                $node->appendChild($xml->createCDATASection(self::bool2str($arr[self::$labelCData])));
                unset($arr[self::$labelCData]);    //remove the key from the array once done.
                //return from recursion, as a note with cdata cannot have child nodes.
                return $node;
            }
        }

        //create subnodes using recursion
        if (is_array($arr)) {
            // recurse to get the node for that key
            foreach ($arr as $key => $value) {
                $key = is_numeric($key) ? 'item' : $key;
                if (!self::isValidTagName($key)) {
                    throw new Exception('[Array2XML] Illegal character in tag name. tag: ' . $key . ' in node: ' . $node_name);
                }
                if (is_array($value) && is_numeric(key($value))) {
                    // MORE THAN ONE NODE OF ITS KIND;
                    // if the new array is numeric index, means it is array of nodes of the same kind
                    // it should follow the parent key name
                    foreach ($value as $k => $v) {
                        $node->appendChild(self::convert($key, $v));
                    }
                } else {
                    // ONLY ONE NODE OF ITS KIND
                    $node->appendChild(self::convert($key, $value));
                }
                unset($arr[$key]); //remove the key from the array once done.
            }
        }

        // after we are done with all the keys in the array (if it is one)
        // we check if it has any text value, if yes, append it.
        if (!is_array($arr)) {
            if (!self::isValidValue($arr)) {
                throw new Exception('[Array2XML] Illegal character : ' . $arr . ' in node: ' . $node_name);
            }
            $node->appendChild($xml->createTextNode(self::bool2str($arr)));
        }

        return $node;
    }

    /**
     * Get the root XML node, if there isn't one, create it.
     *
     * @return DomDocument|null
     */
    private static function getXMLRoot()
    {
        if (empty(self::$xml)) {
            self::init();
        }

        return self::$xml;
    }

    /**
     * Check if the tag name or attribute name contains illegal characters
     * Ref: http://www.w3.org/TR/xml/#sec-common-syn.
     */
    private static function isValidTagName(string $tag): bool
    {
        $pattern = '/^[a-z_][a-z0-9:._-]*[a-z0-9._-]$/i';

        return preg_match($pattern, $tag, $matches) && $matches[0] === $tag;
    }

    /**
     * Check if the value contains illegal characters
     * Ref: https://www.w3.org/TR/xml/#NT-Char
     */
    private static function isValidValue(string $value = null): bool
    {
        $pattern = '/^[\x09\x0A\x0D\x20-\x7E\x85\xA0-\x{D7FF}\x{E000}-\x{FFFD}]*$/u';

        return is_null($value) || (preg_match($pattern, $value, $matches) && $matches[0] === $value);
    }

    /**
     * 将数组转换成 微信官方文档中示例的 XML
     *
     * @param array  $data     数组数据
     * @param string $encoding 编码（默认为 UTF-8）
     * @param string $version  XML 版本（默认为 1.0）
     *
     * @return string 转换后的 XML 字符串
     * @throws DOMException
     */
    public static function createWechatXML(array $data, string $encoding = 'UTF-8', string $version = '1.0'): string
    {
        // 创建 DOMDocument 对象并设置编码
        // $dom = new DOMDocument($version, $encoding);
        self::$xml = null;
        $dom = self::getXMLRoot();

        // 创建根节点 <xml>
        $root = $dom->createElement('xml');
        $dom->appendChild($root);

        // 递归将数组转换为 XML
        self::arrayToWechatXML($data, $root, $dom);

        // 格式化输出 XML
        $dom->formatOutput = true;

        // 返回 XML 字符串
        return $dom->saveXML();
    }

    /**
     * 递归将数组转换为 微信的XML 格式
     *
     * @param array       $data 数组数据
     * @param DOMElement  $xml  根节点
     * @param DOMDocument $dom  DOMDocument 对象
     *
     * @throws DOMException
     */
    private static function arrayToWechatXML(array $data, DOMElement $xml, DOMDocument $dom)
    {
        foreach ($data as $key => $value) {
            $key = is_numeric($key) ? 'item' : $key;
            // 处理 CDATA 标签
            if (is_array($value)) {
                // 递归处理数组
                $subNode = $dom->createElement($key);
                $xml->appendChild($subNode);
                self::arrayToWechatXML($value, $subNode, $dom);
            } else {
                // 检查是否需要使用 CDATA
                if (is_string($value)) {
                    // 使用 DOM 来创建 CDATA 节点
                    $node  = $dom->createElement($key);
                    $cdata = $dom->createCDATASection($value);
                    $node->appendChild($cdata);
                } else {
                    $node = $dom->createElement($key, $value);
                }
                $xml->appendChild($node);
            }
        }
    }
}
