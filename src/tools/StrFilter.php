<?php

namespace zxf\tools;

/**
 * 替换敏感字符串工具类
 * Class CharacterTools
 */

/**
 * Func:
 * public  replace            替换非法字符
 * public  check              检查是否含有非法字符
 * private protect_white_list 保护白名单
 * private resume_white_list  还原白名单
 * private getval             白名单 key转为value
 */

// 使用示例
//$white = array('敏感政治', '色彩');
//$black = array('反执政党', '暴力');
//$content = "敏感词一般是指带有敏感政治倾向（或反执政党倾向）、暴力倾向、不健康色彩的词或不文明语，也有一些网站根据自身实际情况，设定一些只适用于本网站的特殊敏感词";
//
//$obj = new StrFilterTools($white, $black);
//echo $obj->replace($content); // 输出：敏感词一般是指带有敏感政治倾向（或*倾向）、*倾向、不健康色彩的词或不文明语，也有一些网站根据自身实际情况，设定一些只适用于本网站的特殊敏感词

class StrFilter
{
    private $_white_list  = array();
    private $_black_list  = array();
    private $_replacement = '*';
    private $_LTAG        = '[[##';
    private $_RTAG        = '##]]';

    /**
     * @param array  $white_list
     * @param array  $black_list
     * @param string $replacement
     */
    public function __construct($white_list = array(), $black_list = array(), $replacement = '*')
    {
        $this->_white_list  = $white_list;
        $this->_black_list  = $black_list;
        $this->_replacement = $replacement;
    }

    /**
     * @desc 替换非法字符
     *
     * @param String $content 要替換的字符串
     *
     * @return String          替換后的字符串
     */
    public function replace($content)
    {

        if (!isset($content) || $content == '') {
            return '';
        }

        // protect white list
        $content = $this->protect_white_list($content);

        // replace black list
        if ($this->_black_list) {
            foreach ($this->_black_list as $val) {
                $content = str_replace($val, $this->_replacement, $content);
            }
        }

        // resume white list
        $content = $this->resume_white_list($content);

        return $content;
    }

    /**
     * @desc 检查是否含有非法自符
     *
     * @param String $content 字符串
     *
     * @return boolean
     */
    public function check($content)
    {

        if (!isset($content) || $content == '') {
            return true;
        }

        // protect white list
        $content = $this->protect_white_list($content);

        // check
        if ($this->_black_list) {
            foreach ($this->_black_list as $val) {
                if (strstr($content, $val) != '') {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * @desc 保护白名单
     *
     * @param String $content 字符串
     *
     * @return String
     */
    private function protect_white_list($content)
    {
        if ($this->_white_list) {
            foreach ($this->_white_list as $key => $val) {
                $content = str_replace($val, $this->_LTAG . $key . $this->_RTAG, $content);
            }
        }
        return $content;
    }

    /**
     * @desc 还原白名单
     *
     * @param String $content
     *
     * @return String
     */
    private function resume_white_list($content)
    {
        if ($this->_white_list) {
            $content = preg_replace_callback("/\[\[##(.*?)##\]\].*?/si", array($this, 'getval'), $content);
        }
        return $content;
    }

    /**
     * @desc 白名单 key还原为value
     *
     * @param Array $matches 匹配white_list的key
     *
     * @return String white_list val
     */
    private function getval($matches)
    {
        return isset($this->_white_list[$matches[1]]) ? $this->_white_list[$matches[1]] : ''; // key->val
    }

}
