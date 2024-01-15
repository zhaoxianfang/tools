<?php
// +---------------------------------------------------------------------
// | 创建数据库字典
// +---------------------------------------------------------------------
// | Licensed   | http://www.apache.org/licenses/LICENSE-2.0 )
// +---------------------------------------------------------------------
// | Author     | ZhaoXianFang <1748331509@qq.com>
// +---------------------------------------------------------------------
// | 版权       | http://http://www.0l0.net
// +---------------------------------------------------------------------
// | Date       | 2019-07-30
// +---------------------------------------------------------------------

namespace zxf\Tools;


class MysqlTool
{
    /**
     * 生成mysql数据字典
     *
     * 
     * @DateTime 2020-11-27
     *
     * @param string $dbserver   [服务器地址ip或域名]
     * @param string $dbusername [账号]
     * @param string $dbpassword [密码]
     * @param string $database   [数据库名称]
     *
     * @return   [type]                   [description]
     */
    public static function dictionary($dbserver = '127.0.0.1', $dbusername = '', $dbpassword = '', $database = '')
    {
        try {

            //其他配置
            $title = '数据字典';
            $mysql_conn = mysqli_connect("$dbserver", "$dbusername", "$dbpassword", "$database") or die("Mysql connect is error.");
            mysqli_query($mysql_conn, "set names utf8mb4");
//            $table_result = mysqli_query($mysql_conn, 'show tables');
            $table_result = mysqli_query($mysql_conn, 'SELECT table_name, table_comment FROM information_schema.tables WHERE table_schema = ' . "'" . $database . "';");
            //取得所有的表名
            while ($row = mysqli_fetch_array($table_result)) {
                $tables[] = [
                    'TABLE_NAME'    => $row[0],
                    'TABLE_COMMENT' => $row[1],
                ];
            }
            //循环取得所有表的备注及表中列消息
            foreach ($tables as $k => $v) {
                $sql          = 'SELECT * FROM ';
                $sql          .= 'INFORMATION_SCHEMA.TABLES ';
                $sql          .= 'WHERE ';
                $sql          .= "table_name = '{$v['TABLE_NAME']}'  AND table_schema = '{$database}'";
                $table_result = mysqli_query($mysql_conn, $sql);
                while ($t = mysqli_fetch_array($table_result)) {
                    $tables[$k]['TABLE_COMMENT'] = $t['TABLE_COMMENT'];
                }
                $sql          = 'SELECT * FROM ';
                $sql          .= 'INFORMATION_SCHEMA.COLUMNS ';
                $sql          .= 'WHERE ';
                $sql          .= "table_name = '{$v['TABLE_NAME']}' AND table_schema = '{$database}'";
                $fields       = array();
                $field_result = mysqli_query($mysql_conn, $sql);
                while ($t = mysqli_fetch_array($field_result)) {
                    $fields[] = $t;
                }
                $tables[$k]['COLUMN'] = $fields;
            }
            mysqli_close($mysql_conn);
            $html = '';
            //循环所有表
            foreach ($tables as $k => $v) {
                //$html .= '<p><h2>'. $v['TABLE_COMMENT'] . ' </h2>';
                $html .= '<table cellspacing="0" cellpadding="0" align="center">';
                $html .= '<caption>' . $v['TABLE_NAME'] . '  ' . $v['TABLE_COMMENT'] . '</caption>';
                $html .= '<tbody><tr><th>字段名</th><th>数据类型</th><th>默认值</th>
         <th>允许非空</th>
         <th>自动递增</th><th>备注</th></tr>';
                $html .= '';
                foreach ($v['COLUMN'] as $f) {
                    $html .= '<tr><td class="c1">' . $f['COLUMN_NAME'] . '</td>';
                    $html .= '<td class="c2">' . $f['COLUMN_TYPE'] . '</td>';
                    $html .= '<td class="c3"> ' . $f['COLUMN_DEFAULT'] . '</td>';
                    $html .= '<td class="c4"> ' . $f['IS_NULLABLE'] . '</td>';
                    $html .= '<td class="c5">' . ($f['EXTRA'] == 'auto_increment' ? '是' : ' ') . '</td>';
                    $html .= '<td class="c6"> ' . $f['COLUMN_COMMENT'] . '</td>';
                    $html .= '</tr>';
                }
                $html .= '</tbody></table></p>';
            }
            //输出
            $str = '<html>
     <head>
     <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
     <title>' . $title . '</title>
     <style>
     body,td,th {font-family:"宋体"; font-size:12px;}
     table{border-collapse:collapse;border:0;background:#efefef;}
     table caption{text-align:left; background-color:#fff; line-height:2em; font-size:14px; font-weight:bold;caption-side: top; }
     table th{text-align:left; font-weight:bold;height:26px; line-height:26px; font-size:12px; border:1px solid #CCC;}
     table td{height:20px; font-size:12px; border:1px solid #CCC;background-color:#fff;}
     .c1{ width: 120px;}
     .c2{ width: 120px;}
     .c3{ width: 70px;}
     .c4{ width: 80px;}
     .c5{ width: 80px;}
     .c6{ width: 270px;}
     </style>
     </head>
     <body>';
            $str .= '<h1 style="text-align:center;">' . $title . '</h1>';
            $str .= $html;
            $str .= '</body></html>';
            return $str;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

}
