<?php

namespace zxf\Symfony\Component\DependencyInjection\Tests\Fixtures\Container;

class ConstructorWithOptionalArgumentsContainer
{
    public function __construct($optionalArgument = 'foo')
    {
    }
}
