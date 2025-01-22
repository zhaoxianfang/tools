<?php

namespace zxf\BarCode\Types;

/*
 * POSTNET and PLANET barcodes.
 * Used by U.S. Postal Service for automated mail sorting
 *
 * @param $code (string) zip code to represent. Must be a string containing a zip code of the form DDDDD or
 *     DDDDD-DDDD.
 * @param $planet (boolean) if true print the PLANET barcode, otherwise print POSTNET
 */

use zxf\BarCode\Barcode;
use zxf\BarCode\BarcodeBar;

class TypePostnet implements TypeInterface
{
    protected array $barlen = [
        0 => [2, 2, 1, 1, 1],
        1 => [1, 1, 1, 2, 2],
        2 => [1, 1, 2, 1, 2],
        3 => [1, 1, 2, 2, 1],
        4 => [1, 2, 1, 1, 2],
        5 => [1, 2, 1, 2, 1],
        6 => [1, 2, 2, 1, 1],
        7 => [2, 1, 1, 1, 2],
        8 => [2, 1, 1, 2, 1],
        9 => [2, 1, 2, 1, 1]
    ];

    public function getBarcode(string $code): Barcode
    {
        $code = str_replace(['-', ' '], '', $code);
        $len = strlen($code);

        $barcode = new Barcode($code);

        // calculate checksum
        $sum = 0;
        for ($i = 0; $i < $len; ++$i) {
            $sum += intval($code[$i]);
        }
        $chkd = ($sum % 10);
        if ($chkd > 0) {
            $chkd = (10 - $chkd);
        }
        $code .= $chkd;
        $len = strlen($code);

        // start bar
        $barcode->addBar(new BarcodeBar(1, 2, true));
        $barcode->addBar(new BarcodeBar(1, 2, false));

        for ($i = 0; $i < $len; ++$i) {
            for ($j = 0; $j < 5; ++$j) {
                $h = $this->barlen[$code[$i]][$j];
                $p = (int)floor(1 / $h);
                $barcode->addBar(new BarcodeBar(1, (int)$h, true, $p));
                $barcode->addBar(new BarcodeBar(1, 2, false));
            }
        }

        // end bar
        $barcode->addBar(new BarcodeBar(1, 2, true));

        return $barcode;
    }
}
