<?php

/**
 * Class GDLuminanceSource
 *
 * @created      17.01.2021
 *
 * @author       Ashot Khanamiryan
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2021 Smiley
 * @license      MIT
 *
 * @noinspection PhpComposerExtensionStubsInspection
 */
declare(strict_types=1);

namespace zxf\QrCode\Common;

use const IMG_FILTER_BRIGHTNESS;
use const IMG_FILTER_CONTRAST;
use const IMG_FILTER_GRAYSCALE;
use const IMG_FILTER_NEGATE;

use GdImage;
use zxf\QrCode\QROptions;
use zxf\QrCode\Settings\SettingsContainerInterface;

use function file_get_contents;
use function imagecolorat;
use function imagecolorsforindex;
use function imagecreatefromstring;
use function imagefilter;
use function imagesx;
use function imagesy;

/**
 * This class is used to help decode images from files which arrive as GD Resource
 * It does not support rotation.
 */
final class GDLuminanceSource extends LuminanceSourceAbstract
{
    private GdImage $gdImage;

    /**
     * GDLuminanceSource constructor.
     *
     * @throws \zxf\QrCode\Decoder\QRCodeDecoderException
     */
    public function __construct(GdImage $gdImage, SettingsContainerInterface|QROptions $options = new QROptions)
    {
        parent::__construct(imagesx($gdImage), imagesy($gdImage), $options);

        $this->gdImage = $gdImage;

        if ($this->options->readerGrayscale) {
            imagefilter($this->gdImage, IMG_FILTER_GRAYSCALE);
        }

        if ($this->options->readerInvertColors) {
            imagefilter($this->gdImage, IMG_FILTER_NEGATE);
        }

        if ($this->options->readerIncreaseContrast) {
            imagefilter($this->gdImage, IMG_FILTER_BRIGHTNESS, -100);
            imagefilter($this->gdImage, IMG_FILTER_CONTRAST, -100);
        }

        $this->setLuminancePixels();
    }

    private function setLuminancePixels(): void
    {

        for ($j = 0; $j < $this->height; $j++) {
            for ($i = 0; $i < $this->width; $i++) {
                $argb = imagecolorat($this->gdImage, $i, $j);
                $pixel = imagecolorsforindex($this->gdImage, $argb);

                $this->setLuminancePixel($pixel['red'], $pixel['green'], $pixel['blue']);
            }
        }

    }

    public static function fromFile(string $path, SettingsContainerInterface|QROptions $options = new QROptions): static
    {
        return new self(imagecreatefromstring(file_get_contents(self::checkFile($path))), $options);
    }

    public static function fromBlob(string $blob, SettingsContainerInterface|QROptions $options = new QROptions): static
    {
        return new self(imagecreatefromstring($blob), $options);
    }
}
