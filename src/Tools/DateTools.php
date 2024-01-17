<?php

namespace zxf\Tools;

//设置中国时间为默认时区
date_default_timezone_set('PRC');

/**
 * 时间工具类
 * Class DateTools
 */
class DateTools
{
    /**
     * @desc 得到某天凌晨零点的时间戳
     *
     * @param string $str
     *
     * @return int
     */
    public static function getSomeZeroTimeStamp(string $str = 'today')
    {
        switch ($str) {
            case 'today':   // 今天凌晨零点的时间戳
                return strtotime(date("Y-m-d"), time());
                break;
            case 'yesterday':   // 昨天 即 今天凌晨零点的时间戳 减去 一天的秒数
                return strtotime(date("Y-m-d"), time()) - 3600 * 24;
                break;
            case 'tomorrow':    // 明天 即 今天凌晨零点的时间戳 加上 一天的秒数
                return strtotime(date("Y-m-d"), time()) + 3600 * 24;
                break;
            case 'month_first': // 这个月第一天凌晨零点的时间戳
                return strtotime(date("Y-m"), time());
                break;
            case 'year_first':  // 这一年第一天凌晨零点的时间戳
                return strtotime(date("Y-01"), time());
                break;
            default:   // 指定时间字符串，eg:2017-01-01、2017-01-01 09:10:40、-1days、+1days、+1month、+1year
                return strtotime(date("Y-m-d"), strtotime($str));
                break;
        }
    }

    /**
     * @desc 友好时间显示
     *
     * @param string $time 时间戳 或者 时间字符串
     * @param string $lang $lang 语言, cn 中文, en 英文
     *
     * @return bool|string
     */
    public static function get_friend_date(string $time, string $lang = 'cn')
    {
        if (empty($time)) {
            return '';
        }
        $time       = is_numeric($time) ? $time : strtotime($time);
        $friendDate = '';
        $d          = time() - intval($time);
        $ld         = $time - mktime(0, 0, 0, 0, 0, date('Y')); //得出年
        $md         = $time - mktime(0, 0, 0, date('m'), 0, date('Y')); //得出月
        $byd        = $time - mktime(0, 0, 0, date('m'), date('d') - 2, date('Y')); //前天
        $yd         = $time - mktime(0, 0, 0, date('m'), date('d') - 1, date('Y')); //昨天
        $dd         = $time - mktime(0, 0, 0, date('m'), date('d'), date('Y')); //今天
        $td         = $time - mktime(0, 0, 0, date('m'), date('d') + 1, date('Y')); //明天
        $atd        = $time - mktime(0, 0, 0, date('m'), date('d') + 2, date('Y')); //后天
        if ($lang == 'cn') {
            if ($d <= 10) {
                $friendDate = '刚刚';
            } else {
                switch ($d) {
                    case $d < $td:
                        $friendDate = '后天' . date('H:i', $time);
                        break;
                    case $d < 0:
                        $friendDate = '明天' . date('H:i', $time);
                        break;
                    case $d < 60:
                        $friendDate = $d . '秒前';
                        break;
                    case $d < 3600:
                        $friendDate = floor($d / 60) . '分钟前';
                        break;
                    case $d < $dd:
                        $friendDate = floor($d / 3600) . '小时前';
                        break;
                    case $d < $yd:
                        $friendDate = '昨天' . date('H:i', $time);
                        break;
                    case $d < $byd:
                        $friendDate = '前天' . date('H:i', $time);
                        break;
                    case $d < $md:
                        $friendDate = date('m月d日 H:i', $time);
                        break;
                    case $d < $ld:
                        $friendDate = date('m月d日', $time);
                        break;
                    case $d < $atd:
                    default:
                        $friendDate = date('Y年m月d日', $time);
                        break;
                }
            }
        } else {
            if ($d <= 10) {
                $friendDate = 'just';
            } else {
                switch ($d) {
                    case $d < $td:
                        $friendDate = 'the day after tomorrow' . date('H:i', $time);
                        break;
                    case $d < 0:
                        $friendDate = 'tomorrow' . date('H:i', $time);
                        break;
                    case $d < 60:
                        $friendDate = $d . 'seconds ago';
                        break;
                    case $d < 3600:
                        $friendDate = floor($d / 60) . 'minutes ago';
                        break;
                    case $d < $dd:
                        $friendDate = floor($d / 3600) . 'hour ago';
                        break;
                    case $d < $yd:
                        $friendDate = 'yesterday' . date('H:i', $time);
                        break;
                    case $d < $byd:
                        $friendDate = 'the day before yesterday' . date('H:i', $time);
                        break;
                    case $d < $md:
                        $friendDate = date('m-d H:i', $time);
                        break;
                    case $d < $ld:
                        $friendDate = date('m-d', $time);
                        break;
                    case $d < $atd:
                    default:
                        $friendDate = date('Y-m-d', $time);
                        break;
                }
            }
        }
        return $friendDate;
    }

    /**
     * @desc 获取当前时间的前n天
     * @return array
     */
    public static function getLastDays(int $day = 7)
    {
        $dayStr             = $day > 0 ? '-' . ($day - 1) . 'days' : '+' . ($day) . 'days';
        $begin              = strtotime(date('Y-m-d', strtotime($dayStr)));  // n天前
        $today_time         = strtotime(date('Y-m-d'));  // 今天
        $now_time           = time();
        $weeks_arr          = array();
        $weeks_arr['date']  = array();
        $weeks_arr['weeks'] = array();
        $weeks_arr['day']   = array();
        $weeks_array        = array("日", "一", "二", "三", "四", "五", "六"); // 先定义一个数组
        $day_second         = 3600 * 24;
        for ($i = $begin; $i < $now_time; $i = $i + $day_second) {
            if ($i != $today_time) {
                $weeks_arr['date'][] = $i;
            } else {
                $weeks_arr['date'][] = $now_time;
            }
            $weeks_arr['weeks'][] = '星期' . $weeks_array[date('w', $i)];
            $weeks_arr['day'][]   = date('Y-m-d', $i);
        }
        return $weeks_arr;

    }

    /**
     * @desc 获取星期几的信息
     *
     * @param string $time 时间
     * @param string $lang 语言, cn 中文, en 英文
     *
     * @return string
     */
    public static function getWeekDay(string $time, $lang = 'cn')
    {
        $time = is_numeric($time) ? $time : strtotime($time);
        if ($lang == 'cn') {
            $week_array = array("星期日", "星期一", "星期二", "星期三", "星期四", "星期五", "星期六");
            return $week_array[date("w", $time)];
        } else {
            return date("l", $time); // date("l") 可以获取英文的星期比如Sunday
        }
    }


    /**
     * @desc 获取月份
     *
     * @param string $time 时间
     * @param string $lang cn 中文, en 英语
     *
     * @return string
     */
    public static function getMonth($time, $lang = 'cn')
    {
        $timestamp = is_numeric($time) ? $time : strtotime($time);
        if ($lang == 'cn') {
            $month_arr = array(
                '1月', '2月', '3月', '4月', '5月', '6月', '7月', '8月', '9月', '10月', '11月', '12月',
            );
        } else {
            $month_arr = array(
                'Jan.', 'Feb.', 'Mar.', 'Apr.', 'May.', 'Jun.', 'Jul.', 'Aug.', 'Sept.', 'Oct.', 'Nov.', 'Dec.',
            );
        }
        $month = date('n', $timestamp);
        return $month_arr[$month - 1];
    }

    /**
     * @desc  判断一个字符串是否为时间戳
     *
     * @param $timestamp  时间戳
     *
     * @return bool|int
     */
    public static function is_timestamp($timestamp)
    {
        $timestamp = intval($timestamp);
        if (strtotime(date('m-d-Y H:i:s', $timestamp)) === $timestamp) {
            return $timestamp;
        } else {
            return false;
        }
    }
}
