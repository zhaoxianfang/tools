<?php

namespace zxf\Office\Excel\Imports;

// 跳过表头（不读取第一行）
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class ExcelToCollection implements ToCollection, WithBatchInserts, WithChunkReading
{
    public function collection(Collection $collection)
    {
        return $collection;
    }

    public function chunkSize(): int
    {
        // 设置每次读取的行数
        return 1000;
    }

    public function batchSize(): int
    {
        return 1000;
    }
}
