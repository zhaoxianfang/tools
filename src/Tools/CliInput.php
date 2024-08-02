<?php

namespace zxf\Tools;

/**
 * 命令行输入内容
 * eg：$input = new CliInput();
 *     $input = new CliInput();
 *     $name = $input->input('请输入您的名字：');
 *     echo '您的名字为：'.$name ."\n";
 *
 *     $choice = $input->choice('请选择一项：',['a'=>'选项A','b'=>'选项B','c'=>'选项C']);
 *     echo '您选择了：'.$choice ."\n";
 */
class CliInput
{
    /**
     * @param string $tips 提示信息
     *                     eg: $name = $input->input('请输入您的名字：');
     *                     echo '您的名字为：'.$name .'\n';
     *
     * @return string
     */
    public function input(string $tips = '请输入:')
    {
        echo "{$tips}";
        return trim(fgets(STDIN));
    }

    /**
     * @param string $tips    提示信息
     * @param array  $options 候选项
     *                        eg: $choice = $input->choice('请选择一项：',['a'=>'选项A','b'=>'选项B','c'=>'选项C']);
     *
     * @return string
     */
    public function choice(string $tips = '请从下列选项中选择其中一项', array $options = [])
    {
        echo "{$tips}\n";
        // 显示选项列表
        foreach ($options as $key => $value) {
            echo "$key - $value\n";
        }
        // 获取用户的选项
        do {
            echo "请选择: ";
            $choice = trim(fgets(STDIN));
        } while (!array_key_exists($choice, $options));
        return $choice;
    }
}
