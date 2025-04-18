<?php
/**
 * Class QROptions
 *
 * @created      08.12.2015
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2015 Smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace zxf\QrCode;

use zxf\QrCode\Settings\SettingsContainerAbstract;

/**
 * The QrCode settings container
 */
class QROptions extends SettingsContainerAbstract{
	use QROptionsTrait, QRCodeReaderOptionsTrait;
}
