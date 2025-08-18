<?php

namespace zxf\Tools;

/**
 * 命令行打印输出内容
 *
 * 用法：
 * $output = new CliOutput();
 *
 * $output->info("info");
 * $output->error("error");
 * $output->warning("warning");
 * $output->blink("闪烁文字"); // 闪烁文字
 *
 * 自定义颜色样式
 * $output->printLine("红色文字", CliOutput::ANSI_RED); // 红色文字
 * $output->printLine("绿色加粗文字", CliOutput::ANSI_GREEN, CliOutput::ANSI_BOLD); // 绿色加粗文字
 * $output->printLine("蓝色闪烁文字", CliOutput::ANSI_BLUE, CliOutput::ANSI_BLINK); // 蓝色闪烁文字
 * $output->printLine("黄色反色文字", CliOutput::ANSI_YELLOW, CliOutput::ANSI_REVERSE); // 黄色反色文字
 * $output->printLine("青色下划线文字", CliOutput::ANSI_CYAN, CliOutput::ANSI_UNDERLINE); // 青色下划线文字
 * $output->printLine("带背景色的文字", CliOutput::ANSI_BG_RED, CliOutput::ANSI_BOLD); // 带背景色的文字
 *
 * 打印进度条
 * $total = 100;
 * for ($i = 0; $i <= $total; $i++) {
 *   usleep(100000); // 模拟耗时操作
 *   $output->printProgress($i, $total);
 * }
 *
 * 打印表格
 * $data = [
 *   ['姓名', '年龄', '性别'],
 *   ['张三11', 25, '男'],
 *   ['李四', 30, '女'],
 *   ['王五', 28, '男'],
 * ];
 * $output->printTable($data);
 */
class CliOutput
{
    const ANSI_RESET = "\033[0m";

    const ANSI_BLACK = "\033[30m";

    const ANSI_RED = "\033[31m";

    const ANSI_GREEN = "\033[32m";

    const ANSI_YELLOW = "\033[33m";

    const ANSI_BLUE = "\033[34m";

    const ANSI_MAGENTA = "\033[35m";

    const ANSI_CYAN = "\033[36m";

    const ANSI_WHITE = "\033[37m";

    const ANSI_BG_BLACK = "\033[40m";

    const ANSI_BG_RED = "\033[41m";

    const ANSI_BG_GREEN = "\033[42m";

    const ANSI_BG_YELLOW = "\033[43m";

    const ANSI_BG_BLUE = "\033[44m";

    const ANSI_BG_MAGENTA = "\033[45m";

    const ANSI_BG_CYAN = "\033[46m";

    const ANSI_BG_WHITE = "\033[47m";

    const ANSI_BOLD = "\033[1m";

    const ANSI_DIM = "\033[2m";

    const ANSI_ITALIC = "\033[3m";

    const ANSI_UNDERLINE = "\033[4m";

    const ANSI_BLINK = "\033[5m";

    const ANSI_REVERSE = "\033[7m";

    const ANSI_HIDDEN = "\033[8m";

    public function __construct()
    {
        // 检查环境
        if (PHP_SAPI != 'cli') {
            exit('Please run under command line.');
        }
    }

    /**
     * 打印带有颜色和样式的文本。
     *
     * @param  string  $text  要打印的文本。
     * @param  string  $color  颜色代码。
     * @param  string  $style  样式代码，默认为重置样式。
     * @param  bool  $needWrap  是否需要换行
     */
    public function printColoredText(string $text, string $color, string $style = self::ANSI_RESET, bool $needWrap = false): void
    {
        echo $style.$color.$text.self::ANSI_RESET;
        if ($needWrap) {
            echo "\n";
        }
    }

    /**
     * 打印一行带有颜色和样式的文本，并自动换行。
     *
     * @param  string  $text  要打印的文本。
     * @param  string  $color  颜色代码。
     * @param  string  $style  样式代码，默认为重置样式。
     */
    public function printLine(string $text, string $color, string $style = self::ANSI_RESET): void
    {
        $this->printColoredText($text, $color, $style, true);
    }

    /**
     * 打印一个进度条，显示完成的百分比。
     *
     * @param  int  $current  当前进度。
     * @param  int  $total  总数。
     * @param  int  $barLength  进度条长度，默认为 20 个占位符号。
     * @param  string  $color  颜色代码，默认为绿色。
     * @param  string  $style  样式代码，默认为重置样式。
     */
    public function printProgress(int $current, int $total, int $barLength = 20, string $color = self::ANSI_GREEN, string $style = self::ANSI_RESET): void
    {
        $progress = round(($current / $total) * 100);
        $filledLength = floor($barLength * $current / $total);
        $bar = str_repeat('▓', $filledLength).str_repeat('░', $barLength - $filledLength);

        $this->printColoredText("[$bar] $progress%", $color, $style);
        echo " ($current/$total)\r";
        if ($current == $total) {
            echo "\n"; // 当进度达到 100% 时换行
        }
    }

    /**
     * 提示
     */
    public function info(string $test, $color = self::ANSI_GREEN)
    {
        $this->printLine($test, $color, self::ANSI_BOLD); // $color色加粗文字,默认绿色
    }

    /**
     * 错误
     */
    public function error(string $test)
    {
        $this->printLine($test, self::ANSI_RED, self::ANSI_BOLD); // 红色加粗文字
    }

    /**
     * 警告
     */
    public function warning(string $test)
    {
        $this->printLine($test, self::ANSI_YELLOW, self::ANSI_BOLD); // 黄色加粗文字
    }

    /**
     *  闪烁文字
     *
     * @param  string  $test  闪烁文字
     * @param  string  $color  色闪烁文字的颜色,默认黄色
     */
    public function blink(string $test, string $color = self::ANSI_YELLOW): void
    {
        $this->printLine($test, $color, self::ANSI_BLINK); // $color色闪烁文字
    }

    /**
     * 打印一个简单的表格。
     *
     * @param  array  $data  二维数组，表示表格的数据。
     */
    public function printTable(array $data): void
    {
        $topBorder = '+';
        $separator = '+';
        $horizontal = '-';
        $vertical = '|';

        // 计算列宽
        $columnWidths = [];
        foreach ($data as $row) {
            foreach ($row as $colIndex => $cell) {
                if (! isset($columnWidths[$colIndex])) {
                    $columnWidths[$colIndex] = 0;
                }
                $cellWidth = mb_strlen($cell, 'UTF-8');
                $cellWidth += strlen(preg_replace('/[\x{4e00}-\x{9fff}]/u', '', $cell)) * 1;
                $columnWidths[$colIndex] = max($columnWidths[$colIndex], $cellWidth);
            }
        }

        $totalWidth = array_sum($columnWidths) + (count($columnWidths) + 1) * 3; // Add extra space for vertical bars

        echo $topBorder.str_repeat($horizontal, $totalWidth - 2).$topBorder.PHP_EOL;

        foreach ($data as $row) {
            $output = $vertical;
            foreach ($row as $colIndex => $cell) {
                $cellWidth = mb_strlen($cell, 'UTF-8');
                $cellWidth += strlen(preg_replace('/[\x{4e00}-\x{9fff}]/u', '', $cell)) * 1;
                $padding = $columnWidths[$colIndex] - $cellWidth;
                $leftPadding = floor($padding / 2);
                $rightPadding = ceil($padding / 2);

                $paddedCell = str_repeat(' ', $leftPadding).$cell.str_repeat(' ', $rightPadding);
                $output .= ' '.$paddedCell.' '.$vertical;
            }
            echo $output.PHP_EOL;
            echo $separator.str_repeat($horizontal, $totalWidth - 2).$separator.PHP_EOL;
        }
    }
}
