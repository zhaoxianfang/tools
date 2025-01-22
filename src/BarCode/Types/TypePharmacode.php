<?php

namespace zxf\BarCode\Types;

use zxf\BarCode\Barcode;
use zxf\BarCode\Helpers\BinarySequenceConverter;

/*
 * Pharmacode
 * Contains digits (0 to 9)
 */

class TypePharmacode implements TypeInterface
{
    public function getBarcode(string $code): Barcode
    {
        $originalCode = $code;

        $code = intval($code);

        $seq = '';
        while ($code > 0) {
            if (($code % 2) == 0) {
                $seq .= '11100';
                $code -= 2;
            } else {
                $seq .= '100';
                $code -= 1;
            }
            $code /= 2;
        }

        $seq = substr($seq, 0, -2);
        $seq = strrev($seq);

        return BinarySequenceConverter::convert($originalCode, $seq);
    }
}
