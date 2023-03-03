<?php


namespace zxf\WeChat\Mini;

use zxf\WeChat\WeChatBase;
use Exception;

/**
 * 微信小程序数据接口
 * Class Total
 *
 * @package WeMini
 */
class Total extends WeChatBase
{
    /**
     * 数据分析接口
     *
     * @param string $begin_date 开始日期
     * @param string $end_date   结束日期，限定查询1天数据，end_date允许设置的最大值为昨日
     *
     * @return array
     * @throws Exception
     */
    public function getWeanalysisAppidDailySummarytrend($begin_date, $end_date)
    {
        return $this->post('datacube/getweanalysisappiddailysummarytrend', ['begin_date' => $begin_date, 'end_date' => $end_date]);
    }

    /**
     * 访问分析
     *
     * @param string $begin_date 开始日期
     * @param string $end_date   结束日期，限定查询1天数据，end_date允许设置的最大值为昨日
     *
     * @return array
     * @throws Exception
     */
    public function getWeanalysisAppidDailyVisittrend($begin_date, $end_date)
    {
        return $this->post('datacube/getweanalysisappiddailyvisittrend', ['begin_date' => $begin_date, 'end_date' => $end_date]);
    }

    /**
     * 周趋势
     *
     * @param string $begin_date 开始日期，为周一日期
     * @param string $end_date   结束日期，为周日日期，限定查询一周数据
     *
     * @return array
     * @throws Exception
     */
    public function getWeanalysisAppidWeeklyVisittrend($begin_date, $end_date)
    {
        return $this->post('datacube/getweanalysisappidweeklyvisittrend', ['begin_date' => $begin_date, 'end_date' => $end_date]);
    }

    /**
     * 月趋势
     *
     * @param string $begin_date 开始日期，为自然月第一天
     * @param string $end_date   结束日期，为自然月最后一天，限定查询一个月数据
     *
     * @return array
     * @throws Exception
     */
    public function getWeanalysisAppidMonthlyVisittrend($begin_date, $end_date)
    {
        return $this->post('datacube/getweanalysisappidmonthlyvisittrend', ['begin_date' => $begin_date, 'end_date' => $end_date]);
    }

    /**
     * 访问分布
     *
     * @param string $begin_date 开始日期
     * @param string $end_date   结束日期，限定查询1天数据，end_date允许设置的最大值为昨日
     *
     * @return array
     * @throws Exception
     */
    public function getWeanalysisAppidVisitdistribution($begin_date, $end_date)
    {
        return $this->post('datacube/getweanalysisappidvisitdistribution', ['begin_date' => $begin_date, 'end_date' => $end_date]);
    }

    /**
     * 日留存
     *
     * @param string $begin_date 开始日期
     * @param string $end_date   结束日期，限定查询1天数据，end_date允许设置的最大值为昨日
     *
     * @return array
     * @throws Exception
     */
    public function getWeanalysisAppidDailyRetaininfo($begin_date, $end_date)
    {
        return $this->post('datacube/getweanalysisappiddailyretaininfo', ['begin_date' => $begin_date, 'end_date' => $end_date]);
    }

    /**
     * 周留存
     *
     * @param string $begin_date 开始日期，为周一日期
     * @param string $end_date   结束日期，为周日日期，限定查询一周数据
     *
     * @return array
     * @throws Exception
     */
    public function getWeanalysisAppidWeeklyRetaininfo($begin_date, $end_date)
    {
        return $this->post('datacube/getweanalysisappidweeklyretaininfo', ['begin_date' => $begin_date, 'end_date' => $end_date]);
    }

    /**
     * 月留存
     *
     * @param string $begin_date 开始日期，为自然月第一天
     * @param string $end_date   结束日期，为自然月最后一天，限定查询一个月数据
     *
     * @return array
     * @throws Exception
     */
    public function getWeanalysisAppidMonthlyRetaininfo($begin_date, $end_date)
    {
        return $this->post('datacube/getweanalysisappidmonthlyretaininfo', ['begin_date' => $begin_date, 'end_date' => $end_date]);
    }

    /**
     * 访问页面
     *
     * @param string $begin_date 开始日期
     * @param string $end_date   结束日期，限定查询1天数据，end_date允许设置的最大值为昨日
     *
     * @return array
     * @throws Exception
     */
    public function getWeanalysisAppidVisitPage($begin_date, $end_date)
    {
        return $this->post('datacube/getweanalysisappidvisitpage', ['begin_date' => $begin_date, 'end_date' => $end_date]);
    }

    /**
     * 用户画像
     *
     * @param string $begin_date 开始日期
     * @param string $end_date   结束日期，开始日期与结束日期相差的天数限定为0/6/29，分别表示查询最近1/7/30天数据，end_date允许设置的最大值为昨日
     *
     * @return array
     * @throws Exception
     */
    public function getWeanalysisAppidUserportrait($begin_date, $end_date)
    {
        return $this->post('datacube/getweanalysisappiduserportrait', ['begin_date' => $begin_date, 'end_date' => $end_date]);
    }
}