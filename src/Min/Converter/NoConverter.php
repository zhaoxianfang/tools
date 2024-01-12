<?php

namespace zxf\Min\Converter;

/**
 * 不要转换路径
 */
class NoConverter implements ConverterInterface
{
    /**
     * {@inheritdoc}
     */
    public function convert($path)
    {
        return $path;
    }
}
