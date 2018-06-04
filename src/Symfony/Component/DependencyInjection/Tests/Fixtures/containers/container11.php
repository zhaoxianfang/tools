<?php

use zxf\Symfony\Component\DependencyInjection\ContainerBuilder;
use zxf\Symfony\Component\DependencyInjection\Definition;

$container = new ContainerBuilder();
$container->
    register('foo', 'FooClass')->
    addArgument(new Definition('BarClass', array(new Definition('BazClass'))))
    ->setPublic(true)
;

return $container;
