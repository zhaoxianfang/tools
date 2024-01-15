<?php

namespace zxf\Xml;

use DOMDocument;
use DOMNamedNodeMap;
use DOMNode;
use Exception;

/**
 * XML2Array: A class to convert XML to array in PHP
 * It returns the array which can be converted back to XML using the Array2XML script
 * It takes an XML string or a DOMDocument object as an input.
 *
 * Usage:
 *       $array = XML2Array::createArray($xml);
 */
class XML2Array
{
    use InitTrait;

    /**
     * Convert an XML to Array.
     *
     * @param string|DOMDocument $stringOrFilename - XML 字符串 或 DOMDocument 对象 或 xml 文件路径
     *
     * @return array
     *
     * @throws Exception
     */
    public static function createArray($stringOrFilename): array
    {
        $xml = self::getXMLRoot();
        if (is_string($stringOrFilename)) {
            try {
                if (is_file($stringOrFilename)) {
                    // 加载 XML 文件
                    $xmlObj = simplexml_load_file($stringOrFilename);
                    // 将 SimpleXMLElement 对象转换为 XML 字符串
                    $stringOrFilename = $xmlObj->asXML();
                }
                $xml->loadXML($stringOrFilename);
                if (!is_object($xml) || empty($xml->documentElement)) {
                    throw new Exception();
                }
            } catch (Exception $ex) {
                throw new Exception('[XML2Array] Error parsing the XML string.' . PHP_EOL . $ex->getMessage());
            }
        } elseif (is_object($stringOrFilename)) {
            if (get_class($stringOrFilename) != 'DOMDocument') {
                throw new Exception('[XML2Array] The input XML object should be of type: DOMDocument.');
            }
            $xml = self::$xml = $stringOrFilename;
        } else {
            throw new Exception('[XML2Array] Invalid input');
        }

        // Bug 008 - Support <!DOCTYPE>.
        $docType = $xml->doctype;
        if ($docType) {
            $array[self::$labelDocType] = [
                'name'           => $docType->name,
                'entities'       => self::getNamedNodeMapAsArray($docType->entities),
                'notations'      => self::getNamedNodeMapAsArray($docType->notations),
                'publicId'       => $docType->publicId,
                'systemId'       => $docType->systemId,
                'internalSubset' => $docType->internalSubset,
            ];
        }
        $array[self::$labelAttributes] = [
            'encoding'   => $xml->encoding,
            'version'    => $xml->xmlVersion,
            'standalone' => $xml->xmlStandalone,
        ];

        $array[$xml->documentElement->tagName] = self::convert($xml->documentElement);
        self::$xml                             = null;    // clear the xml node in the class for 2nd time use.

        return $array;
    }

    /**
     * Convert an XML to an Array.
     *
     * @param DOMNode $node - XML as a string or as an object of DOMDocument
     *
     * @return array
     */
    private static function convert(DOMNode $node)
    {
        $output = [];

        switch ($node->nodeType) {
            case XML_CDATA_SECTION_NODE: // 4
                $output[self::$labelCData] = trim($node->textContent) ? $node->textContent : trim($node->textContent);
                break;

            case XML_TEXT_NODE: // 3
                $output = trim($node->textContent) ? $node->textContent : trim($node->textContent);
                break;

            case XML_ELEMENT_NODE: // 1

                // for each child node, call the covert function recursively
                for ($i = 0, $m = $node->childNodes->length; $i < $m; ++$i) {
                    $child = $node->childNodes->item($i);
                    $v     = self::convert($child);
                    if (isset($child->tagName)) {
                        $t = $child->tagName;

                        // assume more nodes of same kind are coming
                        if (!array_key_exists($t, $output)) {
                            $output[$t] = [];
                        }
                        $output[$t][] = $v;
                    } else {
                        //check if it is not an empty node
                        if (!empty($v) || $v === '0') {
                            $output = $v;
                        }
                    }
                }

                if (is_array($output)) {
                    // if only one node of its kind, assign it directly instead if array($value);
                    foreach ($output as $t => $v) {
                        if (is_array($v) && count($v) == 1) {
                            $output[$t] = $v[0];
                        }
                    }
                    if (empty($output)) {
                        //for empty nodes
                        $output = '';
                    }
                }

                // loop through the attributes and collect them
                if ($node->attributes->length) {
                    $a = [];
                    foreach ($node->attributes as $attrName => $attrNode) {
                        $a[$attrNode->nodeName] = $attrNode->value;
                    }
                    // if its an leaf node, store the value in @value instead of directly storing it.
                    if (!is_array($output)) {
                        $output = [self::$labelValue => $output];
                    }
                    $output[self::$labelAttributes] = $a;
                }
                break;
        }

        return $output;
    }

    /**
     * Get the root XML node, if there isn't one, create it.
     *
     * @return DOMDocument
     */
    private static function getXMLRoot()
    {
        if (empty(self::$xml)) {
            self::init();
        }

        return self::$xml;
    }

    /**
     * @param DOMNamedNodeMap $namedNodeMap
     *
     * @return array|null
     */
    private static function getNamedNodeMapAsArray(DOMNamedNodeMap $namedNodeMap)
    {
        $result = null;
        if ($namedNodeMap->length) {
            foreach ($namedNodeMap as $key => $entity) {
                $result[$key] = $entity;
            }
        }

        return $result;
    }
}
