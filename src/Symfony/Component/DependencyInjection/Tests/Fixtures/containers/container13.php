<?php

use zxf\Symfony\Component\DependencyInjection\ContainerBuilder;
use zxf\Symfony\Component\DependencyInjection\Reference;

$container = new ContainerBuilder();
$container->
    register('foo', 'FooClass')->
    addArgument(new Reference('bar'))
    ->setPublic(true)
;
$container->
    register('bar', 'BarClass')
    ->setPublic(true)
;
$container->compile();

return $container;
