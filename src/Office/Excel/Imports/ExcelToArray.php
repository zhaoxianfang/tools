<?php

namespace zxf\Office\Excel\Imports;

use Maatwebsite\Excel\Concerns\WithHeadingRow; // 跳过表头（不读取第一行）
use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class ExcelToArray implements ToArray, WithBatchInserts, WithChunkReading
{
    public function array(array $array): array
    {
        return $array;
    }

    /**
     * @return int
     */
    public function batchSize(): int
    {
        return 1000;
    }

    /**
     * @return int
     */
    public function chunkSize(): int
    {
        return 1000;
    }
}
