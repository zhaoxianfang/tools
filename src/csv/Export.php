<?php

namespace zxf\csv;
/**
 * 数组输出到浏览器
 */
class Export
{
    protected        $header      = [];
    protected        $specHeaders = [];
    protected        $data        = [];
    protected static $instance;


    public function __construct()
    {
        ob_start();
        set_time_limit(0);
        ini_set('memory_limit', '512M');
    }

    public static function init()
    {
        if (is_null(self::$instance)) {
            self::$instance = new static();
        }
        return self::$instance;
    }

    /**
     * 设置csv标题头
     * @param array $headers
     */
    public function setHeader(array $header = [])
    {
        $this->header = $header;
        return $this;
    }

    /**
     * @param array $specHeaders 需要转成字符串的数组下标
     * @return $this
     */
    public function setSpecHeaders(array $specHeaders = [])
    {
        $this->specHeaders = $specHeaders;
        return $this;
    }

    /**
     * 设置导出数据
     * @param array $data
     * @return $this
     */
    public function setData(array $data = [])
    {
        $this->data = $data;
        return $this;
    }

    /**
     * 添加一行数据
     * @param array $row
     * @return $this
     */
    public function addRow(array $row = [])
    {
        $this->data = array_push($this->data, $row);
        return $this;
    }

    /**
     * 导出csv
     * @param string $fileName 文件名称
     */
    public function output(string $fileName = 'export')
    {
        //设置header头
        header('Content-Description: File Transfer');
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $fileName . '.csv"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        //打开php数据输入缓冲区
        $fp = fopen('php://output', 'a');

        //将数据编码转换成GBK格式
        mb_convert_variables('GBK', 'UTF-8', $header);
        //将数据格式化为CSV格式并写入到output流中
        if (!empty($this->header)) {
            fputcsv($fp, $this->header);
        }

        $data = $this->data;
        //计数器
        $num   = 0;
        $limit = 50000;
        //如果在csv中输出一个空行，向句柄中写入一个空数组即可实现
        foreach ($data as $row) {
            $num++;
            if ($limit % 200 == $num) {
                ob_flush();
                flush();
                $num = 0;
            }
            //将数据编码转换成GBK格式
            mb_convert_variables('GBK', 'UTF-8', $row);
            foreach ($row as $i => $val) {
                // 数字转文本
                if (is_numeric($val)) {
                    $row[$i] = '\'' . $val;
                }
            }

            fputcsv($fp, $row);
            //将已经存储到csv中的变量数据销毁，释放内存
            unset($row);
        }
        unset($data);
        $this->data   = [];
        $this->header = [];
        //关闭句柄
        fclose($fp);
        exit;
    }
}
