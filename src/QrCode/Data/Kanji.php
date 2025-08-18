<?php

/**
 * Class Kanji
 *
 * @created      25.11.2015
 *
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2015 Smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace zxf\QrCode\Data;

use Throwable;
use zxf\QrCode\Common\BitBuffer;
use zxf\QrCode\Common\Mode;

use function chr;
use function implode;
use function intdiv;
use function is_string;
use function mb_convert_encoding;
use function mb_detect_encoding;
use function mb_detect_order;
use function mb_internal_encoding;
use function mb_strlen;
use function ord;
use function sprintf;
use function strlen;

/**
 * Kanji mode: 13-bit double-byte characters from the Shift-JIS character set
 *
 * ISO/IEC 18004:2000 Section 8.3.5
 * ISO/IEC 18004:2000 Section 8.4.5
 *
 * @see https://en.wikipedia.org/wiki/Shift_JIS#As_defined_in_JIS_X_0208:1997
 * @see http://www.rikai.com/library/kanjitables/kanji_codes.sjis.shtml
 * @see https://gist.github.com/codemasher/d07d3e6e9346c08e7a41b8b978784952
 */
final class Kanji extends QRDataModeAbstract
{
    /**
     * possible values: SJIS, SJIS-2004
     *
     * SJIS-2004 may produce errors in PHP < 8
     *
     * @var string
     */
    public const ENCODING = 'SJIS';

    public const DATAMODE = Mode::KANJI;

    protected function getCharCount(): int
    {
        return mb_strlen($this->data, self::ENCODING);
    }

    public function getLengthInBits(): int
    {
        return $this->getCharCount() * 13;
    }

    public static function convertEncoding(string $string): string
    {
        mb_detect_order([mb_internal_encoding(), 'UTF-8', 'SJIS', 'SJIS-2004']);

        $detected = mb_detect_encoding($string, null, true);

        if ($detected === false) {
            throw new QRCodeDataException('mb_detect_encoding error');
        }

        if ($detected === self::ENCODING) {
            return $string;
        }

        $string = mb_convert_encoding($string, self::ENCODING, $detected);

        if (! is_string($string)) {
            throw new QRCodeDataException(sprintf('invalid encoding: %s', $detected));
        }

        return $string;
    }

    /**
     * checks if a string qualifies as SJIS Kanji
     */
    public static function validateString(string $string): bool
    {

        try {
            $string = self::convertEncoding($string);
        } catch (Throwable) {
            return false;
        }

        $len = strlen($string);

        if ($len < 2 || ($len % 2) !== 0) {
            return false;
        }

        for ($i = 0; $i < $len; $i += 2) {
            $byte1 = ord($string[$i]);
            $byte2 = ord($string[($i + 1)]);

            // byte 1 unused and vendor ranges
            if ($byte1 < 0x81 || ($byte1 > 0x84 && $byte1 < 0x88) || ($byte1 > 0x9F && $byte1 < 0xE0) || $byte1 > 0xEA) {
                return false;
            }

            // byte 2 unused ranges
            if ($byte2 < 0x40 || $byte2 === 0x7F || $byte2 > 0xFC) {
                return false;
            }

        }

        return true;
    }

    /**
     * {@inheritDoc}
     *
     * @throws \zxf\QrCode\Data\QRCodeDataException on an illegal character occurence
     */
    public function write(BitBuffer $bitBuffer, int $versionNumber): static
    {

        $bitBuffer
            ->put(self::DATAMODE, 4)
            ->put($this->getCharCount(), $this::getLengthBits($versionNumber));

        $len = strlen($this->data);

        for ($i = 0; ($i + 1) < $len; $i += 2) {
            $c = (((0xFF & ord($this->data[$i])) << 8) | (0xFF & ord($this->data[($i + 1)])));

            if ($c >= 0x8140 && $c <= 0x9FFC) {
                $c -= 0x8140;
            } elseif ($c >= 0xE040 && $c <= 0xEBBF) {
                $c -= 0xC140;
            } else {
                throw new QRCodeDataException(sprintf('illegal char at %d [%d]', ($i + 1), $c));
            }

            $bitBuffer->put((((($c >> 8) & 0xFF) * 0xC0) + ($c & 0xFF)), 13);
        }

        if ($i < $len) {
            throw new QRCodeDataException(sprintf('illegal char at %d', ($i + 1)));
        }

        return $this;
    }

    /**
     * {@inheritDoc}
     *
     * @throws \zxf\QrCode\Data\QRCodeDataException
     */
    public static function decodeSegment(BitBuffer $bitBuffer, int $versionNumber): string
    {
        $length = $bitBuffer->read(self::getLengthBits($versionNumber));

        if ($bitBuffer->available() < ($length * 13)) {
            throw new QRCodeDataException('not enough bits available');  // @codeCoverageIgnore
        }

        // Each character will require 2 bytes. Read the characters as 2-byte pairs and decode as SJIS afterwards
        $buffer = [];
        $offset = 0;

        while ($length > 0) {
            // Each 13 bits encodes a 2-byte character
            $twoBytes = $bitBuffer->read(13);
            $assembledTwoBytes = ((intdiv($twoBytes, 0x0C0) << 8) | ($twoBytes % 0x0C0));

            $assembledTwoBytes += ($assembledTwoBytes < 0x01F00)
                ? 0x08140  // In the 0x8140 to 0x9FFC range
                : 0x0C140; // In the 0xE040 to 0xEBBF range

            $buffer[$offset] = chr(0xFF & ($assembledTwoBytes >> 8));
            $buffer[($offset + 1)] = chr(0xFF & $assembledTwoBytes);
            $offset += 2;
            $length--;
        }

        return mb_convert_encoding(implode('', $buffer), mb_internal_encoding(), self::ENCODING);
    }
}
