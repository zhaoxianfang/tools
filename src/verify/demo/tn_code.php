<?php
/**
 * 图片滑动式验证码
 * @Author   ZhaoXianFang
 * @DateTime 2019-09-30
 */
// error_reporting(0);
require_once dirname(__FILE__) . '/ImgCode.php';
// require './ImgCode.class.php';
$bg      = mt_rand(1, 6);
$file_bg = dirname(__FILE__) . '/bg/' . $bg . '.png';

$tn = ImgCode::instance();
// $tn      = new ImgCode();

$tn->setOptions($file_bg)->make();
// $tn->make();
