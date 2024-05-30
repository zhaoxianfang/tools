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
     * @param string|DOMDocument $input_xml
     *
     * @return array
     *
     * @throws Exception
     */
    public static function createArray($input_xml): array
    {
        $xml = self::getXMLRoot();
        if (is_string($input_xml)) {
            // Convert the "DOMDocument::loadXML(): Premature end of data in tag root line 1 in Entity, line: 1" to an
            // DOMException
            set_error_handler(
                function ($errno, $errstr, $errfile, $errline) {
                    if ($errno === E_WARNING && (strpos($errstr, 'DOMDocument::loadXML()') === 0)) {
                        throw new DOMException($errstr);
                    }

                    return false;
                }
            );
            try {
                $xml->loadXML($input_xml);
                if (!is_object($xml) || empty($xml->documentElement)) {
                    throw new Exception();
                }
                restore_error_handler();
            } catch (Exception $ex) {
                restore_error_handler();
                throw new Exception('[XML2Array] Error parsing the XML string.'.PHP_EOL.$ex->getMessage());
            }
        } elseif (is_object($input_xml)) {
            if (get_class($input_xml) !== 'DOMDocument') {
                throw new Exception('[XML2Array] The input XML object should be of type: DOMDocument.');
            }
            $xml = self::$xml = $input_xml;
        } else {
            throw new Exception('[XML2Array] Invalid input');
        }

        // Bug 008 - Support <!DOCTYPE>.
        $docType = $xml->doctype;
        if ($docType) {
            $array[self::$labelDocType] = [
                'name' => $docType->name,
                'entities' => self::getNamedNodeMapAsArray($docType->entities),
                'notations' => self::getNamedNodeMapAsArray($docType->notations),
                'publicId' => $docType->publicId,
                'systemId' => $docType->systemId,
                'internalSubset' => $docType->internalSubset,
            ];
        }

        $rootNodeName = $xml->documentElement->tagName;
        $array[$rootNodeName] = self::convert($xml->documentElement);

        // Issue 020 - Support additional namespaces
        $xpath = new DOMXPath($xml);
        $namespaces = $xpath->query('namespace::*', $xml->documentElement);
        if ($namespaces->length > 0) {
            foreach ($namespaces as $namespace) {
                if ($namespace->nodeName !== 'xmlns:xml') {
                    $array[$rootNodeName][Constants::LABEL_ATTRIBUTES] = $array[$rootNodeName][Constants::LABEL_ATTRIBUTES] ?? [];
                    $array[$rootNodeName][Constants::LABEL_ATTRIBUTES][$namespace->nodeName] = $namespace->nodeValue;
                }
            }
        }

        self::$xml = null; // clear the xml node in the class for 2nd time use.

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
                    $v = self::convert($child);
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
                    // if it's a leaf node, store the value in @value instead of directly storing it.
                    if (!is_array($output)) {
                        // @TODO Better handling of empty values (<tag></tag>) and nulls (<tag />).
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
     */
    private static function getXMLRoot(): DOMDocument
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
