<?php
/**
 * CssColorModuleValueTrait.php
 *
 * @created      04.05.2024
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2024 smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace zxf\QrCode\Output;

use function is_string, preg_match, strip_tags, trim;

/**
 * Module value checks for output classes that use CSS colors
 */
trait CssColorModuleValueTrait{

	/**
	 * note: we're not necessarily validating the several values, just checking the general syntax
	 * note: css4 colors are not included
	 *
	 * implements \zxf\QrCode\Output\QROutputInterface::moduleValueIsValid()
	 *
	 * @todo: XSS proof
	 *
	 * @see https://developer.mozilla.org/en-US/docs/Web/CSS/color_value
	 *
	 * @param string $value
	 */
	public static function moduleValueIsValid(mixed $value):bool{

		if(!is_string($value)){
			return false;
		}

		$value = trim(strip_tags($value), " '\"\r\n\t");

		// hex notation
		// #rgb(a)
		// #rrggbb(aa)
		if(preg_match('/^#([\da-f]{3}){1,2}$|^#([\da-f]{4}){1,2}$/i', $value)){
			return true;
		}

		// css: hsla/rgba(...values)
		if(preg_match('#^(hsla?|rgba?)\([\d .,%/]+\)$#i', $value)){
			return true;
		}

		// predefined css color
		if(preg_match('/^[a-z]+$/i', $value)){
			return true;
		}

		return false;
	}

	/**
	 * implements \zxf\QrCode\Output\QROutputAbstract::prepareModuleValue()
	 *
	 * @param string $value
	 */
	protected function prepareModuleValue(mixed $value):string{
		return trim(strip_tags($value), " '\"\r\n\t");
	}

	/**
	 * implements \zxf\QrCode\Output\QROutputAbstract::getDefaultModuleValue()
	 */
	protected function getDefaultModuleValue(bool $isDark):string{
		return ($isDark) ? '#000' : '#fff';
	}

}
