<?php

namespace zxf\tools;

/**
 * 常见的几种排序方法
 * bubbleSort: 冒泡排序（Bubble Sort）
 * selectionSort: 选择排序（Selection Sort）
 * insertionSort: 插入排序（Insertion Sort）
 * shellSort: 希尔排序（Shell Sort）
 * mergeSort: 归并排序（Merge Sort）
 * quickSort: 快速排序（Quick Sort）
 * heapSort: 堆排序（Heap Sort）
 * countingSort: 计数排序（Counting Sort）
 * bucketSort: 桶排序（Bucket Sort）
 * radixSort: 基数排序（Radix Sort）
 * cocktailSort: 鸡尾酒排序（Cocktail Sort）
 */
class Sort
{
    /**
     * 冒泡排序
     * 支持对一维数组、二维数组并按照指定字段进行排序
     *
     * @param array  $arr    要排序的数组
     * @param string $field  要按照哪个字段进行排序（仅对关联数组有效）
     * @param bool   $is_asc 是否升序排列，默认为true
     *
     * demo:
     *      一维数组：bubbleSort([5, 3, 8, 4, 6])
     *      二维数组：bubbleSort(array(
     *                   array('name' => '张三', 'age' => 18),
     *                   array('name' => '李四', 'age' => 20),
     *                   array('name' => '王五', 'age' => 15),
     *               ), 'age');
     *
     * @return array 排序后的数组
     */
    public function bubbleSort(array $arr, $field = null, $is_asc = true): array
    {
        $len = count($arr);
        for ($i = 0; $i < $len - 1; $i++) {
            for ($j = 0; $j < $len - 1 - $i; $j++) {
                if ($field) {
                    // 比较两个元素的大小
                    if (
                        ($is_asc && $arr[$j][$field] > $arr[$j + 1][$field])
                        || (!$is_asc && $arr[$j][$field] < $arr[$j + 1][$field])
                    ) {
                        // 交换两个元素的位置
                        $temp        = $arr[$j];
                        $arr[$j]     = $arr[$j + 1];
                        $arr[$j + 1] = $temp;
                    }
                } else {
                    // 比较两个元素的大小
                    if (
                        ($is_asc && $arr[$j] > $arr[$j + 1])
                        || (!$is_asc && $arr[$j] < $arr[$j + 1])
                    ) {
                        // 交换两个元素的位置
                        $temp        = $arr[$j];
                        $arr[$j]     = $arr[$j + 1];
                        $arr[$j + 1] = $temp;
                    }
                }
            }
        }
        return $arr;
    }

    /**
     * 选择排序，支持对一维数组、二维数组按照指定字段进行排序
     *
     * @param array  $arr    要排序的数组
     * @param string $field  要按照哪个字段进行排序（仅对关联数组有效）
     * @param bool   $is_asc 是否升序排列，默认为true
     *
     * @return array 排序后的数组
     */
    public function selectionSort(array $arr, $field = null, $is_asc = true): array
    {
        $len = count($arr);
        for ($i = 0; $i < $len - 1; $i++) {
            $min_index = $i;
            for ($j = $i + 1; $j < $len; $j++) {
                if ($field) {
                    // 比较两个元素的大小
                    if (
                        ($is_asc && $arr[$j][$field] < $arr[$min_index][$field])
                        || (!$is_asc && $arr[$j][$field] > $arr[$min_index][$field])
                    ) {
                        $min_index = $j;
                    }
                } else {
                    // 比较两个元素的大小
                    if (
                        ($is_asc && $arr[$j] < $arr[$min_index])
                        || (!$is_asc && $arr[$j] > $arr[$min_index])
                    ) {
                        $min_index = $j;
                    }
                }
            }
            // 将最小值交换到当前位置
            if ($min_index != $i) {
                $temp            = $arr[$i];
                $arr[$i]         = $arr[$min_index];
                $arr[$min_index] = $temp;
            }
        }
        return $arr;
    }

    /**
     * 插入排序，支持对一维数组、二维数组按照指定字段进行排序
     *
     * @param array  $arr    要排序的数组
     * @param string $field  要按照哪个字段进行排序（仅对关联数组有效）
     * @param bool   $is_asc 是否升序排列，默认为true
     *
     * @return array 排序后的数组
     */
    public function insertionSort(array $arr, $field = null, $is_asc = true): array
    {
        $len = count($arr);
        for ($i = 1; $i < $len; $i++) {
            $current = $arr[$i];
            if ($field) {
                $j = $i - 1;
                while ($j >= 0 && (($is_asc && $arr[$j][$field] > $current[$field])
                                   || (!$is_asc && $arr[$j][$field] < $current[$field]))) {
                    $arr[$j + 1] = $arr[$j];
                    $j--;
                }
            } else {
                $j = $i - 1;
                while ($j >= 0 && (($is_asc && $arr[$j] > $current)
                                   || (!$is_asc && $arr[$j] < $current))) {
                    $arr[$j + 1] = $arr[$j];
                    $j--;
                }
            }
            $arr[$j + 1] = $current;
        }
        return $arr;
    }

    /**
     * 希尔排序，支持对一维数组、二维数组按照指定字段进行排序
     *
     * @param array  $arr    要排序的数组
     * @param string $field  要按照哪个字段进行排序（仅对关联数组有效）
     * @param bool   $is_asc 是否升序排列，默认为true
     *
     * @return array 排序后的数组
     */
    public function shellSort(array $arr, $field = null, $is_asc = true): array
    {
        $len = count($arr);
        $gap = floor($len / 2);
        while ($gap > 0) {
            for ($i = $gap; $i < $len; $i++) {
                $current = $arr[$i];
                if ($field) {
                    $j = $i - $gap;
                    while ($j >= 0 && (($is_asc && $arr[$j][$field] > $current[$field])
                                       || (!$is_asc && $arr[$j][$field] < $current[$field]))) {
                        $arr[$j + $gap] = $arr[$j];
                        $j              -= $gap;
                    }
                } else {
                    $j = $i - $gap;
                    while ($j >= 0 && (($is_asc && $arr[$j] > $current)
                                       || (!$is_asc && $arr[$j] < $current))) {
                        $arr[$j + $gap] = $arr[$j];
                        $j              -= $gap;
                    }
                }
                $arr[$j + $gap] = $current;
            }
            $gap = floor($gap / 2);
        }
        return $arr;
    }

    /**
     * 归并排序，支持对一维数组、二维数组按照指定字段进行排序
     *
     * @param array  $arr    要排序的数组
     * @param string $field  要按照哪个字段进行排序（仅对关联数组有效）
     * @param bool   $is_asc 是否升序排列，默认为true
     *
     * @return array 排序后的数组
     */
    public function mergeSort(array $arr, $field = null, $is_asc = true): array
    {
        $len = count($arr);
        if ($len <= 1) {
            return $arr;
        }
        $mid   = floor($len / 2);
        $left  = array_slice($arr, 0, $mid);
        $right = array_slice($arr, $mid);
        $left  = $this->mergeSort($left, $field, $is_asc);
        $right = $this->mergeSort($right, $field, $is_asc);
        return $this->merge($left, $right, $field, $is_asc);
    }

    /**
     * 归并操作，将两个有序数组合并成一个有序数组
     *
     * @param array  $left   左侧有序数组
     * @param array  $right  右侧有序数组
     * @param string $field  要按照哪个字段进行排序（仅对关联数组有效）
     * @param bool   $is_asc 是否升序排列，默认为true
     *
     * @return array 合并后的有序数组
     */
    private function merge(array $left, array $right, $field, $is_asc): array
    {
        $result = array();
        while (count($left) > 0 && count($right) > 0) {
            if ($field) {
                // 比较两个数组第一个元素的大小
                if (
                    ($is_asc && $left[0][$field] <= $right[0][$field])
                    || (!$is_asc && $left[0][$field] >= $right[0][$field])
                ) {
                    $result[] = array_shift($left);
                } else {
                    $result[] = array_shift($right);
                }
            } else {
                // 比较两个数组第一个元素的大小
                if (
                    ($is_asc && $left[0] <= $right[0])
                    || (!$is_asc && $left[0] >= $right[0])
                ) {
                    $result[] = array_shift($left);
                } else {
                    $result[] = array_shift($right);
                }
            }
        }
        while (count($left) > 0) {
            $result[] = array_shift($left);
        }
        while (count($right) > 0) {
            $result[] = array_shift($right);
        }
        return $result;
    }

    /**
     * 快速排序，支持对一维数组、二维数组按照指定字段进行排序
     *
     * @param array  $arr    要排序的数组
     * @param string $field  要按照哪个字段进行排序（仅对关联数组有效）
     * @param bool   $is_asc 是否升序排列，默认为true
     *
     * @return array 排序后的数组
     */
    public function quickSort(array $arr, $field = null, $is_asc = true): array
    {
        $len = count($arr);
        if ($len <= 1) {
            return $arr;
        }
        $pivot = $arr[0];
        $left  = array();
        $right = array();
        for ($i = 1; $i < $len; $i++) {
            if ($field) {
                if (
                    ($is_asc && $arr[$i][$field] < $pivot[$field])
                    || (!$is_asc && $arr[$i][$field] > $pivot[$field])
                ) {
                    $left[] = $arr[$i];
                } else {
                    $right[] = $arr[$i];
                }
            } else {
                if (($is_asc && $arr[$i] < $pivot) || (!$is_asc && $arr[$i] > $pivot)) {
                    $left[] = $arr[$i];
                } else {
                    $right[] = $arr[$i];
                }
            }
        }
        $left  = $this->quickSort($left, $field, $is_asc);
        $right = $this->quickSort($right, $field, $is_asc);
        return array_merge($left, array($pivot), $right);
    }

    /**
     * 堆排序，支持对一维数组、二维数组按照指定字段进行排序
     *
     * @param array  $arr    要排序的数组
     * @param string $field  要按照哪个字段进行排序（仅对关联数组有效）
     * @param bool   $is_asc 是否升序排列，默认为true
     *
     * @return array 排序后的数组
     */
    public function heapSort(array $arr, $field = null, $is_asc = true): array
    {
        $len = count($arr);
        $this->buildHeap($arr, $len, $field, $is_asc);
        for ($i = $len - 1; $i > 0; $i--) {
            $this->swap($arr, 0, $i);
            $len--;
            $this->heapify($arr, 0, $len, $field, $is_asc);
        }
        return $arr;
    }

    /**
     * 构建大根堆或小根堆
     *
     * @param array  $arr    要构建的数组
     * @param int    $len    数组长度
     * @param string $field  要按照哪个字段进行排序（仅对关联数组有效）
     * @param bool   $is_asc 是否升序排列，默认为true
     */
    private function buildHeap(array &$arr, $len, $field, $is_asc)
    {
        for ($i = floor(($len - 1) / 2); $i >= 0; $i--) {
            $this->heapify($arr, $i, $len, $field, $is_asc);
        }
    }

    /**
     * 堆化（将一个节点以及它的子树变成大根堆或小根堆）
     *
     * @param array  $arr    要排序的数组
     * @param int    $i      当前节点在数组中的下标
     * @param int    $len    数组长度
     * @param string $field  要按照哪个字段进行排序（仅对关联数组有效）
     * @param bool   $is_asc 是否升序排列，默认为true
     */
    private function heapify(array &$arr, $i, $len, $field, $is_asc)
    {
        $left    = 2 * $i + 1;
        $right   = 2 * $i + 2;
        $largest = $i;
        if ($field) {
            if (
                $left < $len && (($is_asc && $arr[$left][$field] > $arr[$largest][$field])
                                 || (!$is_asc && $arr[$left][$field] < $arr[$largest][$field]))
            ) {
                $largest = $left;
            }
            if (
                $right < $len && (($is_asc && $arr[$right][$field] > $arr[$largest][$field])
                                  || (!$is_asc && $arr[$right][$field] < $arr[$largest][$field]))
            ) {
                $largest = $right;
            }
        } else {
            if (
                $left < $len && (($is_asc && $arr[$left] > $arr[$largest])
                                 || (!$is_asc && $arr[$left] < $arr[$largest]))
            ) {
                $largest = $left;
            }
            if (
                $right < $len && (($is_asc && $arr[$right] > $arr[$largest])
                                  || (!$is_asc && $arr[$right] < $arr[$largest]))
            ) {
                $largest = $right;
            }
        }
        if ($largest != $i) {
            $this->swap($arr, $i, $largest);
            $this->heapify($arr, $largest, $len, $field, $is_asc);
        }
    }

    /**
     * 交换数组中两个元素的值
     *
     * @param array $arr 数组
     * @param int   $i   下标1
     * @param int   $j   下标2
     */
    private function swap(array &$arr, $i, $j)
    {
        $temp    = $arr[$i];
        $arr[$i] = $arr[$j];
        $arr[$j] = $temp;
    }


    /**
     * 计数排序，支持对一维数组、二维数组按照指定字段进行排序
     *
     * @param array  $arr    要排序的数组
     * @param string $field  要按照哪个字段进行排序（仅对关联数组有效）
     * @param bool   $is_asc 是否升序排列，默认为true
     *
     * @return array 排序后的数组
     */
    public function countingSort(array $arr, $field = null, $is_asc = true): array
    {
        $max_val   = max(array_column($arr, $field));
        $min_val   = min(array_column($arr, $field));
        $count_arr = array_fill($min_val, $max_val - $min_val + 1, 0);
        foreach ($arr as $item) {
            $count_arr[$item[$field]]++;
        }
        for ($i = $min_val + 1; $i <= $max_val; $i++) {
            $count_arr[$i] += $count_arr[$i - 1];
        }
        $result = array();
        if ($is_asc) {
            for ($i = count($arr) - 1; $i >= 0; $i--) {
                $result[--$count_arr[$arr[$i][$field]]] = $arr[$i];
            }
            ksort($result);
        } else {
            foreach ($arr as $item) {
                $result[$count_arr[$item[$field]] - 1] = $item;
                $count_arr[$item[$field]]--;
            }
        }
        return $result;
    }

    /**
     * 桶排序，支持对一维数组、二维数组按照指定字段进行排序
     *
     * @param array  $arr    要排序的数组
     * @param string $field  要按照哪个字段进行排序（仅对关联数组有效）
     * @param bool   $is_asc 是否升序排列，默认为true
     *
     * @return array 排序后的数组
     */
    public function bucketSort(array $arr, $field = null, $is_asc = true): array
    {
        // 计算桶数量
        $bucket_num = count($arr);
        if ($field) {
            $max_val = max(array_column($arr, $field));
        } else {
            $max_val = max($arr);
        }
        $bucket_size = ceil(($max_val + 1) / $bucket_num);

        // 将元素放入桶中
        $buckets = array();
        foreach ($arr as $item) {
            $key = $field ? floor($item[$field] / $bucket_size) : floor($item / $bucket_size);
            if (!isset($buckets[$key])) {
                $buckets[$key] = array();
            }
            array_push($buckets[$key], $item);
        }

        // 对每个桶内部进行快速排序
        foreach ($buckets as &$bucket) {
            $this->quickSort($bucket, $field, $is_asc);
        }
        unset($bucket);

        // 合并所有桶的元素
        $result = array();
        foreach ($buckets as $bucket) {
            foreach ($bucket as $item) {
                array_push($result, $item);
            }
        }
        return $result;
    }

    /**
     * 基数排序，支持对一维数组、二维数组按照指定字段进行排序
     *
     * @param array  $arr    要排序的数组
     * @param string $field  要按照哪个字段进行排序（仅对关联数组有效）
     * @param bool   $is_asc 是否升序排列，默认为true
     *
     * @return array 排序后的数组
     */
    public function radixSort(array $arr, $field = null, $is_asc = true): array
    {
        // 计算最大值的位数
        if ($field) {
            $max_val = max(array_column($arr, $field));
        } else {
            $max_val = max($arr);
        }
        $max_digit = strlen((string)$max_val);

        // 从低位到高位依次排序
        for ($i = 1; $i <= $max_digit; $i++) {
            $buckets = array_fill(0, 10, array());
            foreach ($arr as $item) {
                $num = $field ? intval($item[$field] / pow(10, $i - 1)) % 10 : intval($item / pow(10, $i - 1)) % 10;
                array_push($buckets[$num], $item);
            }
            $arr = array();
            for ($j = 0; $j <= 9; $j++) {
                foreach ($buckets[$j] as $item) {
                    array_push($arr, $item);
                }
            }
        }
        return $is_asc ? $arr : array_reverse($arr);
    }

    /**
     * 鸡尾酒排序，支持对一维数组、二维数组按照指定字段进行排序
     *
     * @param array  $arr    要排序的数组
     * @param string $field  要按照哪个字段进行排序（仅对关联数组有效）
     * @param bool   $is_asc 是否升序排列，默认为true
     *
     * @return array 排序后的数组
     */
    public function cocktailSort(array $arr, $field = null, $is_asc = true): array
    {
        $left  = 0;
        $right = count($arr) - 1;
        while ($left < $right) {
            // 从左到右扫描，将最大值放到右边
            for ($i = $left; $i < $right; $i++) {
                if ($this->compareItems($arr[$i], $arr[$i + 1], $field, $is_asc) > 0) {
                    $this->swap($arr, $i, $i + 1);
                }
            }
            $right--;

            // 从右到左扫描，将最小值放到左边
            for ($i = $right; $i > $left; $i--) {
                if ($this->compareItems($arr[$i - 1], $arr[$i], $field, $is_asc) > 0) {
                    $this->swap($arr, $i - 1, $i);
                }
            }
            $left++;
        }
        return $arr;
    }

    // 比较两个元素的大小
    private function compareItems($item1, $item2, $field, $is_asc)
    {
        if ($field) {
            $val1 = $item1[$field];
            $val2 = $item2[$field];
        } else {
            $val1 = $item1;
            $val2 = $item2;
        }
        if ($is_asc) {
            return $val1 - $val2;
        } else {
            return $val2 - $val1;
        }
    }
}