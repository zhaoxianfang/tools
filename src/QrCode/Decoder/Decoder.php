<?php

/**
 * Class Decoder
 *
 * @created      17.01.2021
 *
 * @author       ZXing Authors
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2021 Smiley
 * @license      Apache-2.0
 */
declare(strict_types=1);

namespace zxf\QrCode\Decoder;

use Throwable;
use zxf\QrCode\Common\BitBuffer;
use zxf\QrCode\Common\EccLevel;
use zxf\QrCode\Common\LuminanceSourceInterface;
use zxf\QrCode\Common\MaskPattern;
use zxf\QrCode\Common\Mode;
use zxf\QrCode\Common\Version;
use zxf\QrCode\Data\AlphaNum;
use zxf\QrCode\Data\Byte;
use zxf\QrCode\Data\ECI;
use zxf\QrCode\Data\Hanzi;
use zxf\QrCode\Data\Kanji;
use zxf\QrCode\Data\Number;
use zxf\QrCode\Detector\Detector;
use zxf\QrCode\QROptions;
use zxf\QrCode\Settings\SettingsContainerInterface;

use function chr;
use function str_replace;

/**
 * The main class which implements QR Code decoding -- as opposed to locating and extracting
 * the QR Code from an image.
 *
 * @author Sean Owen
 */
final class Decoder
{
    private SettingsContainerInterface|QROptions $options;

    private ?Version $version = null;

    private ?EccLevel $eccLevel = null;

    private ?MaskPattern $maskPattern = null;

    private BitBuffer $bitBuffer;

    public function __construct(SettingsContainerInterface|QROptions $options = new QROptions)
    {
        $this->options = $options;
    }

    /**
     * Decodes a QR Code represented as a BitMatrix.
     * A 1 or "true" is taken to mean a black module.
     *
     * @throws \Throwable|\zxf\QrCode\Decoder\QRCodeDecoderException
     */
    public function decode(LuminanceSourceInterface $source): DecoderResult
    {
        $matrix = (new Detector($source))->detect();

        try {
            // clone the BitMatrix to avoid errors in case we run into mirroring
            return $this->decodeMatrix(clone $matrix);
        } catch (Throwable $e) {

            try {
                /*
                 * Prepare for a mirrored reading.
                 *
                 * Since we're here, this means we have successfully detected some kind
                 * of version and format information when mirrored. This is a good sign,
                 * that the QR code may be mirrored, and we should try once more with a
                 * mirrored content.
                 */
                return $this->decodeMatrix($matrix->resetVersionInfo()->mirrorDiagonal());
            } catch (Throwable) {
                // Throw the exception from the original reading
                throw $e;
            }

        }

    }

    /**
     * @throws \zxf\QrCode\Decoder\QRCodeDecoderException
     */
    private function decodeMatrix(BitMatrix $matrix): DecoderResult
    {
        // Read raw codewords
        $rawCodewords = $matrix->readCodewords();
        $this->version = $matrix->getVersion();
        $this->eccLevel = $matrix->getEccLevel();
        $this->maskPattern = $matrix->getMaskPattern();

        if ($this->version === null || $this->eccLevel === null || $this->maskPattern === null) {
            throw new QRCodeDecoderException('unable to read version or format info'); // @codeCoverageIgnore
        }

        $resultBytes = (new ReedSolomonDecoder($this->version, $this->eccLevel))->decode($rawCodewords);

        return $this->decodeBitStream($resultBytes);
    }

    /**
     * Decode the contents of that stream of bytes
     *
     * @throws \zxf\QrCode\Decoder\QRCodeDecoderException
     */
    private function decodeBitStream(BitBuffer $bitBuffer): DecoderResult
    {
        $this->bitBuffer = $bitBuffer;
        $versionNumber = $this->version->getVersionNumber();
        $symbolSequence = -1;
        $parityData = -1;
        $fc1InEffect = false;
        $result = '';

        // While still another segment to read...
        while ($this->bitBuffer->available() >= 4) {
            $datamode = $this->bitBuffer->read(4); // mode is encoded by 4 bits

            // OK, assume we're done
            if ($datamode === Mode::TERMINATOR) {
                break;
            } elseif ($datamode === Mode::NUMBER) {
                $result .= Number::decodeSegment($this->bitBuffer, $versionNumber);
            } elseif ($datamode === Mode::ALPHANUM) {
                $result .= $this->decodeAlphanumSegment($versionNumber, $fc1InEffect);
            } elseif ($datamode === Mode::BYTE) {
                $result .= Byte::decodeSegment($this->bitBuffer, $versionNumber);
            } elseif ($datamode === Mode::KANJI) {
                $result .= Kanji::decodeSegment($this->bitBuffer, $versionNumber);
            } elseif ($datamode === Mode::STRCTURED_APPEND) {

                if ($this->bitBuffer->available() < 16) {
                    throw new QRCodeDecoderException('structured append: not enough bits left');
                }
                // sequence number and parity is added later to the result metadata
                // Read next 8 bits (symbol sequence #) and 8 bits (parity data), then continue
                $symbolSequence = $this->bitBuffer->read(8);
                $parityData = $this->bitBuffer->read(8);
            } elseif ($datamode === Mode::FNC1_FIRST || $datamode === Mode::FNC1_SECOND) {
                // We do little with FNC1 except alter the parsed result a bit according to the spec
                $fc1InEffect = true;
            } elseif ($datamode === Mode::ECI) {
                $result .= ECI::decodeSegment($this->bitBuffer, $versionNumber);
            } elseif ($datamode === Mode::HANZI) {
                $result .= Hanzi::decodeSegment($this->bitBuffer, $versionNumber);
            } else {
                throw new QRCodeDecoderException('invalid data mode');
            }

        }

        return new DecoderResult([
            'rawBytes' => $this->bitBuffer,
            'data' => $result,
            'version' => $this->version,
            'eccLevel' => $this->eccLevel,
            'maskPattern' => $this->maskPattern,
            'structuredAppendParity' => $parityData,
            'structuredAppendSequence' => $symbolSequence,
        ]);
    }

    private function decodeAlphanumSegment(int $versionNumber, bool $fc1InEffect): string
    {
        $str = AlphaNum::decodeSegment($this->bitBuffer, $versionNumber);

        // See section 6.4.8.1, 6.4.8.2
        if ($fc1InEffect) { // ???
            // We need to massage the result a bit if in an FNC1 mode:
            $str = str_replace(chr(0x1D), '%', $str);
            $str = str_replace('%%', '%', $str);
        }

        return $str;
    }
}
