<?php

namespace zxf\Xml;

use DOMDocument;
use DOMImplementation;
use DOMNode;
use Exception;

/**
 * Array2XML: A class to convert array in PHP to XML
 * It also takes into account attributes names unlike SimpleXML in PHP
 * It returns the XML in form of DOMDocument class for further manipulation.
 * It throws exception if the tag name or attribute name has illegal chars.
 *
 * Usage:
 *       $xml = Array2XML::createXML( $php_array,'root_node_name', $attr, $docType);
 */
class Array2XML
{
    use InitTrait;

    /**
     * Convert an Array to XML.
     *
     * @param array  $arr       - array to be converted
     * @param string $node_name - name of the root node to be converted
     * @param array  $attr      - xml attributes - optional array   - ex: [
     *                          'version' => '1.0',
     *                          'encoding' => 'UTF-8',
     *                          'standalone' => true,
     *                          'formatOutput' => true
     *                          ]
     * @param array  $docType   - optional docType
     *
     * @return false|string
     * @throws \DOMException
     */
    public static function createXML(array $arr = [], string $node_name = 'root', array $attr = [], array $docType = [])
    {
        self::$xml = null; // 先进行重置，防止上次的数据影响
        $xml       = self::getXMLRoot($attr);

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
        $node_name = !empty($node_name) ? $node_name : 'root';
        $xml->appendChild(self::convert($node_name, $arr));
        self::$xml = null;    // clear the xml node in the class for 2nd time use.

        return $xml->saveXML();
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

        return $v;
    }

    /**
     * Convert an Array to XML.
     *
     * @param string $node_name - name of the root node to be converted
     * @param array  $arr       - array to be converted
     *
     * @return DOMNode
     *
     * @throws Exception
     */
    private static function convert($node_name, $arr = [])
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
                $node->appendChild($xml->createTextNode(self::bool2str($arr[self::$labelValue])));
                unset($arr[self::$labelValue]);    //remove the key from the array once done.
                //return from recursion, as a note with value cannot have child nodes.
                return $node;
            } elseif (array_key_exists(self::$labelCData, $arr)) {
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
            $node->appendChild($xml->createTextNode(self::bool2str($arr)));
        }

        return $node;
    }

    /**
     * Get the root XML node, if there isn't one, create it.
     *
     * @return DomDocument|null
     */
    private static function getXMLRoot($attr = [])
    {
        if (empty(self::$xml)) {
            self::init(!empty($attr['version']) ? $attr['version'] : null, !empty($attr['encoding']) ? $attr['encoding'] : null, !empty($attr['standalone']) ? $attr['standalone'] : null, !empty($attr['formatOutput']) ? $attr['formatOutput'] : null);
        }

        return self::$xml;
    }

    /**
     * Check if the tag name or attribute name contains illegal characters
     * Ref: http://www.w3.org/TR/xml/#sec-common-syn.
     *
     * @param string $tag
     *
     * @return bool
     */
    private static function isValidTagName($tag)
    {
        $pattern = '/^[a-z_]+[a-z0-9\:\-\.\_]*[^:]*$/i';

        return preg_match($pattern, $tag, $matches) && $matches[0] == $tag;
    }
}
