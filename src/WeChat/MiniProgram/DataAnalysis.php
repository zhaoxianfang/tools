<?php

namespace zxf\WeChat\MiniProgram;

use Exception;
use zxf\WeChat\WeChatBase;

/**
 * 微信小程序数据接口
 */
class DataAnalysis extends WeChatBase
{
    public $useToken = false;


    /**
     * 访问留存-周留存
     *
     * @link https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/data-analysis/visit-retain/getWeeklyRetain.html
     *
     * @param string $begin_date 开始日期，为周一日期 格式为 yyyymmdd
     * @param string $end_date   结束日期，为周日日期，限定查询一周数据 格式为 yyyymmdd
     *
     * @return array
     * @throws Exception
     */
    public function getWeeklyRetain(string $begin_date, string $end_date)
    {
        return $this->post('datacube/getweanalysisappidweeklyretaininfo', ['begin_date' => $begin_date, 'end_date' => $end_date], true);
    }

    /**
     * 访问留存-月留存
     *
     * @link https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/data-analysis/visit-retain/getMonthlyRetain.html
     *
     * @param string $begin_date 开始日期，为自然月第一天 格式为 yyyymmdd
     * @param string $end_date   结束日期，为自然月最后一天，限定查询一个月数据 格式为 yyyymmdd
     *
     * @return array
     * @throws Exception
     */
    public function getMonthlyRetain(string $begin_date, string $end_date)
    {
        return $this->post('datacube/getweanalysisappidmonthlyretaininfo', ['begin_date' => $begin_date, 'end_date' => $end_date], true);
    }

    /**
     * 访问留存-日留存
     *
     * @link https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/data-analysis/visit-retain/getDailyRetain.html
     *
     * @param string $begin_date 开始日期 格式为 yyyymmdd
     * @param string $end_date   结束日期，限定查询1天数据，end_date允许设置的最大值为昨日 格式为 yyyymmdd
     *
     * @return array
     * @throws Exception
     */
    public function getDailyRetain(string $begin_date, string $end_date)
    {
        return $this->post('datacube/getweanalysisappiddailyretaininfo', ['begin_date' => $begin_date, 'end_date' => $end_date], true);
    }

    /**
     * 访问趋势-日趋势
     *
     * @link https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/data-analysis/visit-trend/getDailyVisitTrend.html
     *
     * @param string $begin_date 开始日期
     * @param string $end_date   结束日期，限定查询1天数据，end_date允许设置的最大值为昨日
     *
     * @return array
     * @throws Exception
     */
    public function getDailyVisitTrend(string $begin_date, string $end_date)
    {
        return $this->post('datacube/getweanalysisappiddailyvisittrend', ['begin_date' => $begin_date, 'end_date' => $end_date], true);
    }

    /**
     * 访问趋势-周趋势
     *
     * @link https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/data-analysis/visit-trend/getWeeklyVisitTrend.html
     *
     * @param string $begin_date 开始日期，为周一日期
     * @param string $end_date   结束日期，为周日日期，限定查询一周数据
     *
     * @return array
     * @throws Exception
     */
    public function getWeanalysisAppidWeeklyVisittrend(string $begin_date, string $end_date)
    {
        return $this->post('datacube/getweanalysisappidweeklyvisittrend', ['begin_date' => $begin_date, 'end_date' => $end_date], true);
    }

    /**
     * 访问趋势-月趋势
     *
     * @link https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/data-analysis/visit-trend/getMonthlyVisitTrend.html
     *
     * @param string $begin_date 开始日期，为自然月第一天
     * @param string $end_date   结束日期，为自然月最后一天，限定查询一个月数据
     *
     * @return array
     * @throws Exception
     */
    public function getMonthlyVisitTrend(string $begin_date, string $end_date)
    {
        return $this->post('datacube/getweanalysisappidmonthlyvisittrend', ['begin_date' => $begin_date, 'end_date' => $end_date], true);
    }

    /**
     * 获取用户访问小程序数据概况
     *
     * @link https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/data-analysis/others/getDailySummary.html#%E8%B0%83%E7%94%A8%E6%96%B9%E5%BC%8F
     *
     * @param string $begin_date 开始日期。格式为 yyyymmdd
     * @param string $end_date   结束日期，限定查询1天数据，end_date允许设置的最大值为昨日 格式为 yyyymmdd
     *
     * @return array
     * @throws Exception
     */
    public function getDailySummary(string $begin_date, string $end_date)
    {
        return $this->post('datacube/getweanalysisappiddailysummarytrend', ['begin_date' => $begin_date, 'end_date' => $end_date], true);
    }

    /**
     * 获取用户小程序访问分布数据
     *
     * @link https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/data-analysis/others/getVisitDistribution.html
     *
     * @param string $begin_date 开始日期
     * @param string $end_date   结束日期，限定查询1天数据，end_date允许设置的最大值为昨日
     *
     * @return array
     * @throws Exception
     */
    public function getWeanalysisAppidVisitdistribution(string $begin_date, string $end_date)
    {
        return $this->post('datacube/getweanalysisappidvisitdistribution', ['begin_date' => $begin_date, 'end_date' => $end_date], true);
    }


    /**
     * 获取访问页面数据
     *
     * @link https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/data-analysis/others/getVisitPage.html
     *
     * @param string $begin_date 开始日期
     * @param string $end_date   结束日期，限定查询1天数据，end_date允许设置的最大值为昨日
     *
     * @return array
     * @throws Exception
     */
    public function getVisitPage(string $begin_date, string $end_date)
    {
        return $this->post('datacube/getweanalysisappidvisitpage', ['begin_date' => $begin_date, 'end_date' => $end_date], true);
    }

    /**
     * 获取小程序用户画像分布
     *
     * @link https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/data-analysis/others/getUserPortrait.html
     *
     * @param string $begin_date 开始日期
     * @param string $end_date   结束日期，开始日期与结束日期相差的天数限定为0/6/29，分别表示查询最近1/7/30天数据，end_date允许设置的最大值为昨日
     *
     * @return array
     * @throws Exception
     */
    public function getUserPortrait(string $begin_date, string $end_date)
    {
        return $this->post('datacube/getweanalysisappiduserportrait', ['begin_date' => $begin_date, 'end_date' => $end_date], true);
    }

    /**
     * 获取小程序性能数据
     *
     * @link https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/data-analysis/others/getPerformanceData.html
     *
     * @param int   $module          查询数据的类型
     * @param int   $begin_timestamp 开始日期时间戳
     * @param int   $end_timestamp   结束日期时间戳
     * @param array $params          查询条件，比如机型，网络类型等等
     *
     * @return array
     * @throws Exception
     */
    public function getPerformanceData(int $module, int $begin_timestamp, int $end_timestamp, array $params)
    {
        return $this->post('wxa/business/performance/boot', [
            'module' => $module,
            'time'   => [
                'begin_timestamp' => $begin_timestamp,
                'end_timestamp'   => $end_timestamp,
            ],
            'params' => $params,
        ]);
    }

}