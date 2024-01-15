<?php

namespace zxf\Tools;

/**
 * 操作xml相关的工具类
 * Class XmlTools
 */
class Xml
{
    /**
     * @desc xml转数组
     *
     * @param $xml
     *
     * @return array|mixed|string
     */
    public static function xml2arr($xml)
    {
        if (PHP_VERSION_ID < 80000) {
            //禁止引用外部xml实体
            libxml_disable_entity_loader(true);
        }
        $data = (array)simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
        return json_decode(json_encode($data), true);
    }

    /**
     * @desc 数组转xml
     *
     * @param     $arr
     * @param int $level
     *
     * @return null|string|string[]
     */
    public function arr2xml($arr, $level = 1)
    {
        $s = $level == 1 ? "<xml>" : '';
        foreach ($arr as $tagname => $value) {
            if (is_numeric($tagname)) {
                $tagname = 'item';
            }
            if (!is_array($value)) {
                $s .= "<{$tagname}>" . (!is_numeric($value) ? '<![CDATA[' : '') . $value . (!is_numeric($value) ? ']]>' : '') . "</{$tagname}>";
            } else {
                $s .= "<{$tagname}>" . $this->arr2xml($value, $level + 1) . "</{$tagname}>";
            }
        }
        $s = preg_replace("/([\x01-\x08\x0b-\x0c\x0e-\x1f])+/", ' ', $s);
        return $level == 1 ? $s . "</xml>" : $s;

    }

    /**
     * 解析XML文本内容
     *
     * @param string $xml
     *
     * @return array|false
     */
    public static function xml3arr($xml)
    {
        $state = xml_parse($parser = xml_parser_create(), $xml, true);
        return xml_parser_free($parser) && $state ? self::xml2arr($xml) : false;
    }
}