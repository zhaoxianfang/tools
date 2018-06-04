<?php

namespace zxf\Symfony\Component\DependencyInjection\Tests\Fixtures\includes;

use zxf\Symfony\Component\DependencyInjection\Tests\Compiler\Foo;

class FooVariadic
{
    public function __construct(Foo $foo)
    {
    }

    public function bar(...$arguments)
    {
    }
}
