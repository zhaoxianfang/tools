<?php

namespace zxf\BarCode\Types;

use zxf\BarCode\Barcode;

interface TypeInterface
{
    public function getBarcode(string $code): Barcode;
}
