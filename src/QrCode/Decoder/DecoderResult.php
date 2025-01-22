<?php
/**
 * Class DecoderResult
 *
 * @created      17.01.2021
 * @author       ZXing Authors
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2021 Smiley
 * @license      Apache-2.0
 */
declare(strict_types=1);

namespace zxf\QRCode\Decoder;

use zxf\QRCode\Common\{BitBuffer, EccLevel, MaskPattern, Version};
use zxf\QRCode\Data\QRMatrix;
use function property_exists;

/**
 * Encapsulates the result of decoding a matrix of bits. This typically
 * applies to 2D barcode formats. For now, it contains the raw bytes obtained
 * as well as a String interpretation of those bytes, if applicable.
 *
 * @property \zxf\QRCode\Common\BitBuffer   $rawBytes
 * @property string                                $data
 * @property \zxf\QRCode\Common\Version     $version
 * @property \zxf\QRCode\Common\EccLevel    $eccLevel
 * @property \zxf\QRCode\Common\MaskPattern $maskPattern
 * @property int                                   $structuredAppendParity
 * @property int                                   $structuredAppendSequence
 */
final class DecoderResult{

	private BitBuffer   $rawBytes;
	private Version     $version;
	private EccLevel    $eccLevel;
	private MaskPattern $maskPattern;
	private string      $data = '';
	private int         $structuredAppendParity = -1;
	private int         $structuredAppendSequence = -1;

	/**
	 * DecoderResult constructor.
	 *
	 * @phpstan-param array<string, mixed> $properties
	 */
	public function __construct(iterable|null $properties = null){

		if(!empty($properties)){

			foreach($properties as $property => $value){

				if(!property_exists($this, $property)){
					continue;
				}

				$this->{$property} = $value;
			}

		}

	}

	public function __get(string $property):mixed{

		if(property_exists($this, $property)){
			return $this->{$property};
		}

		return null;
	}

	public function __toString():string{
		return $this->data;
	}

	public function hasStructuredAppend():bool{
		return $this->structuredAppendParity >= 0 && $this->structuredAppendSequence >= 0;
	}

	/**
	 * Returns a QRMatrix instance with the settings and data of the reader result
	 */
	public function getQRMatrix():QRMatrix{
		return (new QRMatrix($this->version, $this->eccLevel))
			->initFunctionalPatterns()
			->writeCodewords($this->rawBytes)
			->setFormatInfo($this->maskPattern)
			->mask($this->maskPattern)
		;
	}

}
