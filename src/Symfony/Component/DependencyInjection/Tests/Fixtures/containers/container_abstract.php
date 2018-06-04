<?php

use zxf\Symfony\Component\DependencyInjection\ContainerBuilder;

$container = new ContainerBuilder();

$container
    ->register('foo', 'Foo')
    ->setAbstract(true)
    ->setPublic(true)
;

return $container;
