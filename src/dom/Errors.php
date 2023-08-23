<?php

declare(strict_types=1);

namespace zxf\dom;

class Errors
{
    /**
     * @var bool
     */
    protected static $internalErrors;

    /**
     * @var bool
     */
    protected static $disableEntities;

    /**
     * Disable error reporting.
     */
    public static function disable()
    {
        self::$internalErrors = libxml_use_internal_errors(true);

        if (\LIBXML_VERSION < 20900 && PHP_VERSION_ID < 80000) {
            self::$disableEntities = libxml_disable_entity_loader(true);
        }
    }

    /**
     * Restore error reporting.
     *
     * @param bool $clear
     */
    public static function restore($clear = true)
    {
        if ($clear) {
            libxml_clear_errors();
        }

        libxml_use_internal_errors(self::$internalErrors);

        if (\LIBXML_VERSION < 20900 && PHP_VERSION_ID < 80000) {
            libxml_disable_entity_loader(self::$disableEntities);
        }
    }
}
