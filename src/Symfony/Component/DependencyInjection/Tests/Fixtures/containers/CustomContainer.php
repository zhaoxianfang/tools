<?php

namespace zxf\Symfony\Component\DependencyInjection\Tests\Fixtures\containers;

use zxf\Symfony\Component\DependencyInjection\Container;
use zxf\Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

class CustomContainer extends Container
{
    public function getBarService()
    {
    }

    public function getFoobarService()
    {
    }
}
