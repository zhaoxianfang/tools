<?php

/**
 * Class ECI
 *
 * @created      20.11.2020
 *
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2020 smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace zxf\QrCode\Data;

use zxf\QrCode\Common\BitBuffer;
use zxf\QrCode\Common\ECICharset;
use zxf\QrCode\Common\Mode;

use function mb_convert_encoding;
use function mb_detect_encoding;
use function mb_internal_encoding;
use function sprintf;

/**
 * Adds an ECI Designator
 *
 * ISO/IEC 18004:2000 8.4.1.1
 *
 * Please note that you have to take care for the correct data encoding when adding with QrCode::add*Segment()
 */
final class ECI extends QRDataModeAbstract
{
    public const DATAMODE = Mode::ECI;

    /**
     * The current ECI encoding id
     */
    private int $encoding;

    /**
     * {@inheritDoc}
     *
     * @throws \zxf\QrCode\Data\QRCodeDataException
     *
     * @noinspection PhpMissingParentConstructorInspection
     */
    public function __construct(int $encoding)
    {

        if ($encoding < 0 || $encoding > 999999) {
            throw new QRCodeDataException(sprintf('invalid encoding id: "%s"', $encoding));
        }

        $this->encoding = $encoding;
    }

    public function getLengthInBits(): int
    {

        if ($this->encoding < 128) {
            return 8;
        }

        if ($this->encoding < 16384) {
            return 16;
        }

        return 24;
    }

    /**
     * Writes an ECI designator to the bitbuffer
     *
     * {@inheritDoc}
     *
     * @throws \zxf\QrCode\Data\QRCodeDataException
     */
    public function write(BitBuffer $bitBuffer, int $versionNumber): static
    {
        $bitBuffer->put(self::DATAMODE, 4);

        if ($this->encoding < 128) {
            $bitBuffer->put($this->encoding, 8);
        } elseif ($this->encoding < 16384) {
            $bitBuffer->put(($this->encoding | 0x8000), 16);
        } elseif ($this->encoding < 1000000) {
            $bitBuffer->put(($this->encoding | 0xC00000), 24);
        } else {
            throw new QRCodeDataException('invalid ECI ID');
        }

        return $this;
    }

    /**
     * Reads and parses the value of an ECI designator
     *
     * @throws \zxf\QrCode\Data\QRCodeDataException
     */
    public static function parseValue(BitBuffer $bitBuffer): ECICharset
    {
        $firstByte = $bitBuffer->read(8);

        // just one byte
        if (($firstByte & 0b10000000) === 0) {
            $id = ($firstByte & 0b01111111);
        }
        // two bytes
        elseif (($firstByte & 0b11000000) === 0b10000000) {
            $id = ((($firstByte & 0b00111111) << 8) | $bitBuffer->read(8));
        }
        // three bytes
        elseif (($firstByte & 0b11100000) === 0b11000000) {
            $id = ((($firstByte & 0b00011111) << 16) | $bitBuffer->read(16));
        } else {
            throw new QRCodeDataException(sprintf('error decoding ECI value first byte: %08b', $firstByte)); // @codeCoverageIgnore
        }

        return new ECICharset($id);
    }

    /**
     * @codeCoverageIgnore Unused, but required as per interface
     */
    public static function validateString(string $string): bool
    {
        return true;
    }

    /**
     * Reads and decodes the ECI designator including the following byte sequence
     *
     * @throws \zxf\QrCode\Data\QRCodeDataException
     */
    public static function decodeSegment(BitBuffer $bitBuffer, int $versionNumber): string
    {
        $eciCharset = self::parseValue($bitBuffer);
        $nextMode = $bitBuffer->read(4);
        $encoding = $eciCharset->getName();

        // this is definitely weird, but there are QR Codes out in the wild
        // that have ECI followed by numeric and alphanum segments
        // @see https://github.com/chillerlan/php-qrcode/discussions/289
        $data = match ($nextMode) {
            Mode::NUMBER => Number::decodeSegment($bitBuffer, $versionNumber),
            Mode::ALPHANUM => AlphaNum::decodeSegment($bitBuffer, $versionNumber),
            Mode::BYTE => Byte::decodeSegment($bitBuffer, $versionNumber),
            default => throw new QRCodeDataException(
                sprintf('ECI designator followed by invalid mode: "%04b"', $nextMode),
            ),
        };

        if ($encoding === null) {
            // The spec isn't clear on this mode; see
            // section 6.4.5: it does not say which encoding to assuming
            // upon decoding. I have seen ISO-8859-1 used as well as
            // Shift_JIS -- without anything like an ECI designator to
            // give a hint.
            $encoding = mb_detect_encoding($data, ['ISO-8859-1', 'Windows-1252', 'SJIS', 'UTF-8'], true);

            if ($encoding === false) {
                throw new QRCodeDataException('could not determine encoding in ECI mode'); // @codeCoverageIgnore
            }
        }

        return mb_convert_encoding($data, mb_internal_encoding(), $encoding);
    }
}
