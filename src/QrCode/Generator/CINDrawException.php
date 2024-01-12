<?php
/**
 *--------------------------------------------------------------------
 *
 * Draw Exception
 *
 *--------------------------------------------------------------------
 */

namespace zxf\Qrcode\Generator;

use Exception;

class CINDrawException extends Exception
{
    /**
     * Constructor with specific message.
     *
     * @param string $message
     */
    public function __construct($message)
    {
        parent::__construct($message, 30000);
    }
}
